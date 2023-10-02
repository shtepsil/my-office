<?php
namespace shadow\plugins\seo\behaviors;

use backend\modules\seo\models\SSeoRedirects;
use backend\modules\seo\models\SSeoUrls;
use shadow\plugins\seo\SUrlRule;
use Yii;
use yii\base\Behavior;
use yii\base\UnknownPropertyException;
use yii\base\InvalidConfigException;
use yii\caching\TagDependency;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\validators\Validator;

class SSeoBehavior extends Behavior
{
    /**
     * @var \yii\db\ActiveRecord the owner of this behavior
     */
    public $owner;
    /**
     * Поле которое будем делать транслит нужны для js
     * @var string
     */
    public $nameTranslate;
    /**
     * Название контроллера
     * @var string
     */
    public $controller;
    /**
     * Название action
     * @var string
     */
    public $action;
    /**
     * Название связи верхнего уровня, необходимо для выстраивания path в текушей модели
     * @var string|boolean
     */
    public $parentRelation = false;
    /**
     * Название связи нижнего уровня, необходимо для выстраивания path в нижнем уровне
     * @var string|boolean|array
     */
    public $childrenRelation = false;
    /**
     * Path по умолчанию необходим для модулей
     * Например: в новостях равен news/
     * @var string
     */
    public $defaultPath = '';
    /**
     * Автоматически создавать редирект при изменение ссылки
     * @var bool
     */
    public $createRedirect = true;

    private $relatedSeo = false;
    private $ownerClassName;
    private $ownerPrimaryKey;
    private $ownerPrimaryKeyValue;

    private $seoOldAttributes = [
        'seo_url' => '',
        'seo_path' => ''
    ];
    private $seoAttributes = [
        'seo_url' => '',
        'seo_path' => ''
    ];
    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
        ];
    }

    /**
     * @inheritdoc
     */
    public function attach($owner)
    {
        /** @var ActiveRecord $owner */
        parent::attach($owner);
        if (empty($this->nameTranslate)) {
            throw new InvalidConfigException('Не задан параметр $nameTranslate для ' . get_class($this) . ' в модели '
                . get_class($this->owner), 101);
        }
        if (empty($this->controller)) {
            throw new InvalidConfigException('Не задан параметр $controller для ' . get_class($this) . ' в модели '
                . get_class($this->owner), 101);
        }
        if (empty($this->action)) {
            throw new InvalidConfigException('Не задан параметр $action для ' . get_class($this) . ' в модели '
                . get_class($this->owner), 101);
        }
        $validators = $owner->getValidators();
        $validators[] = Validator::createValidator('required', $owner, 'seo_url');
        $validators[] = Validator::createValidator(function ($attribute, $params) {
            $path = $this->getSeoAttribute('seo_path');
            $url = $this->getSeoAttribute('seo_url');
            preg_match('~(.+/)~ui', $path, $m);
            if (isset($m[1]) && ($m[1] = trim($m[1], '/'))) {
                $path = $m[1] . '/' . $url;
            } else {
                $path = $url;
            }
            $length = mb_strlen($path,  Yii::$app->charset);
            if($length>255){
                $this->owner->addError($attribute, 'Общая длина ссылки не должна быть больше 255 символов');
            }else{
                $q = SSeoUrls::find()->andWhere(['path' => $path]);
                if(!$this->owner->isNewRecord){
                    $q->andWhere(['<>', 'resource_id', $this->owner->getPrimaryKey()]);
                }
                if ($q->exists()) {
                    $this->owner->addError($attribute, 'Данная ссылка уже существует!');
                }
            }

        }, $owner, 'seo_url');
        $validators[] = Validator::createValidator(function ($attribute, $params) {
            $path = $this->getSeoAttribute('seo_path');
            $url = $this->getSeoAttribute('seo_url');
            preg_match('~(.+/)~ui', $path, $m);
            $search_path = [];
            if (isset($m[1]) && ($m[1] = trim($m[1], '/'))) {
                $search_path[] = $m[1] . '/' . $url;
            } else {
                $search_path[] = $url;
            }
            //если есть потомки то создаём предварительное регулярное выражения
            if ($this->childrenRelation) {
                $search_path[] = $path . '/<item>';
            }
            if (SSeoRedirects::find()->andWhere(['old_url' => $search_path])->exists()) {
                $this->owner->addError($attribute, 'Данная ссылка есть в редиректах, удалите либо введите другую!');
            }
        }, $owner, 'seo_url');
        $validators[] = Validator::createValidator('match', $owner, 'seo_url', [
            'pattern' => '/^[A-Za-z0-9_-]+$/u',
            'message' => 'Не допустимые символы (Разрешены только латинские буквы и цифры, и символы _-)'
        ]);
        $this->ownerClassName = get_class($this->owner);
        /** @var ActiveRecord $className */
        $className = $this->ownerClassName;
        $this->ownerPrimaryKey = $className::primaryKey()[0];
        if (!$owner->isNewRecord) {
            $this->ownerPrimaryKeyValue = $owner->getPrimaryKey();
        }
    }
    public static function enableSeoEdit()
    {
        return
            (Yii::$app->id == 'app-backend' && in_array(Yii::$app->controller->action->id, ['control', 'save', 'deleted']))
            &&
            (Yii::$app->seo->enable)
            ;
    }
    public function configField()
    {
        $inputTemplate = '{input}';
        $path = '/' . $this->defaultPath;
        if ($this->parentRelation) {
            if (($path = $this->getSeoAttribute('seo_path'))) {
                $url = $this->getSeoAttribute('seo_url');
                $path = str_replace($url, '', $path);
                if (!$path) {
                    $path = '/';
                } else {
                    $path = '/' . rtrim(str_replace($url, '', $path), '/') . '/';
                }
                $inputTemplate = '<div class="input-group"><span class="input-group-addon">' . $path . '</span>{input}</div>';
            }
        } else {
            $inputTemplate = '<div class="input-group"><span class="input-group-addon">' . $path . '</span>{input}</div>';
        }
        $url_input = Html::getInputId($this->owner, 'seo_url');
        \Yii::$app->controller->view->registerJs(<<<JS
$('#{$url_input}').on('change blur', function (e) {
    if ($('#{$url_input}').val() != '') {
        $('#{$url_input}').val(instinct.translit_url($(this).val()))
    }
})
JS
        );
        if ($this->nameTranslate) {
            $name_input = Html::getInputId($this->owner, $this->nameTranslate);
            \Yii::$app->controller->view->registerJs(<<<JS
$('#{$name_input}').on('change blur', function (e) {
    if ($('#{$url_input}').val() == '') {
        $('#{$url_input}').val(instinct.translit_url($(this).val()))
    }
})
JS
            );
        }
        $result = [
            'field_options' => [
                'inputTemplate' => $inputTemplate,
            ]
        ];
        return $result;
    }
    /**
     * Relation to model SSeoUrls
     * @return ActiveQuery
     */
    public function getSeoUrl()
    {
        return $this->owner->hasOne(SSeoUrls::className(), ['resource_id' => $this->ownerPrimaryKey])->andWhere(['resource' => $this->ownerClassName]);
    }
    /**
     * @param $q ActiveQuery
     */
    public static function modificationSeoQuery(&$q)
    {
        if (self::enableSeoEdit()) {
            if (isset($q->with['seoUrl'])) {
                unset($q->with['seoUrl']);
            }
            $q->with('seoUrl');
        }
    }
    /**
     * Handle 'beforeValidate' event of the owner.
     */
    public function beforeValidate()
    {
        $this->setSeoAttribute('seo_url', strtolower($this->getSeoAttribute('seo_url')));
        $url = $this->getSeoAttribute('seo_url');
        $path = '';
        if ($this->parentRelation) {
            /** @var ActiveRecord $owner */
            $owner = $this->owner;
            $parentRelation = $owner->getRelation($this->parentRelation);
            $parent_id = $owner->getAttribute($parentRelation->link['id']);
            if ($parentRelation && $parent_id) {
                /** @var SSeoUrls $relation */
                $relation = SSeoUrls::find()
                    ->andWhere([
                        'resource' => $parentRelation->modelClass,
                        'resource_id' => $parent_id
                    ])->one();
                if ($relation) {
                    $path = $relation->path . '/' . $url;
                }
            }
            if (!$path) {
                $path = $this->defaultPath . $url;
            }
        } else {
            $path = $this->defaultPath . $url;
        }
        $this->setSeoAttribute('seo_path', $path);
    }
    /**
     * Handle 'afterFind' event of the owner.
     */
    public function afterFind()
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;
        /** @var SSeoUrls $related */
        if ($owner->isRelationPopulated('seoUrl') && $related = $owner->getRelatedRecords()['seoUrl']) {
            $this->relatedSeo = $related;
            $this->seoAttributes = [
                'seo_url' => $related->url,
                'seo_path' => $related->path
            ];
            $this->seoOldAttributes = $this->seoAttributes;
        }
    }
    /**
     * Handle 'afterInsert' event of the owner.
     */
    public function afterInsert()
    {
        $this->saveSeo();
    }
    /**
     * Handle 'afterUpdate' event of the owner.
     */
    public function afterUpdate()
    {
        if ($this->saveSeo($this->relatedSeo) && $this->createRedirect) {
            $new_path = $this->getSeoAttribute('seo_path');
            $old_path = $this->getSeoOldAttribute('seo_path');
            if ($old_path && $new_path != $old_path) {
                if (SSeoRedirects::find()->andWhere(['new_url' => $old_path])->exists()) {
                    SSeoRedirects::updateAll(['new_url' => $new_path, 'updated_at' => time()], ['new_url' => $old_path]);
                }
                $redirect = new SSeoRedirects();
                $redirect->old_url = $old_path;
                $redirect->new_url = $new_path;
                $redirect->type = '301';
                $redirect->save();
                if ($this->childrenRelation) {
                    if ($new_path != $old_path) {
                        //TODO тут написать запрос по регвыражению для изменения у дочерних ссылки
                        $new_url = $this->getSeoAttribute('seo_url');
                        $old_url = $this->getSeoOldAttribute('seo_url');
                        $a_old_path = explode('/', $old_path);
                        $old_path = '';
                        foreach ($a_old_path as $val) {
                            $old_path .= $val . '/';
                            if ($val == $old_url) {
                                $old_path .= '<item>';
                                break;
                            }
                        }
                        $old_path = trim($old_path, '/');
                        $a_new_path = explode('/', $new_path);
                        $new_path = '';
                        foreach ($a_new_path as $val) {
                            $new_path .= $val . '/';
                            if ($val == $new_url) {
                                $new_path .= '<item>';
                                break;
                            }
                        }
                        $new_path = trim($new_path, '/');
                        if (SSeoRedirects::find()->andWhere(['new_url' => $old_path])->exists()) {
                            SSeoRedirects::updateAll(['new_url' => $new_path, 'updated_at' => time()], ['new_url' => $old_path]);
                        }
                        $redirect = new SSeoRedirects();
                        $redirect->old_url = $old_path;
                        $redirect->new_url = $new_path;
                        $redirect->type = '301';
                        $redirect->isRegex = 1;
                        $redirect->save();
                        \yii\caching\TagDependency::invalidate(Yii::$app->frontend_cache, SUrlRule::CACHE_KEY_TAG);

                    }
                }
            }
        }
    }
    /**
     * Handle 'afterDelete' event of the owner.
     */
    public function afterDelete()
    {
        SSeoUrls::deleteAll(['resource' => $this->ownerClassName, 'resource_id' => $this->owner->getPrimaryKey()]);
        TagDependency::invalidate(Yii::$app->frontend_cache, SUrlRule::CACHE_KEY_TAG);
    }
    public function saveSeo($seo = false)
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;
        if (!$seo) {
            $seo = new SSeoUrls();
        }
        if($seo->path!==$this->getSeoAttribute('seo_path')){
            $seo->on($seo->isNewRecord ? $seo::EVENT_AFTER_INSERT : $seo::EVENT_AFTER_UPDATE, function ($event) {
                TagDependency::invalidate(Yii::$app->frontend_cache, SUrlRule::CACHE_KEY_TAG);
            });
        }
        $seo->resource = $this->ownerClassName;
        $seo->resource_id = $owner->getPrimaryKey();
        $seo->url = $this->getSeoAttribute('seo_url');
        $seo->path = $this->getSeoAttribute('seo_path');
        $seo->controller = $this->controller;
        $seo->action = $this->action;
        return $seo->save();
    }
    /**
     * @inheritdoc
     */
    public function canGetProperty($name, $checkVars = true)
    {
        return method_exists($this, 'get' . $name) || $checkVars && property_exists($this, $name)
        || $this->hasSeoAttribute($name);
    }

    /**
     * @inheritdoc
     */
    public function canSetProperty($name, $checkVars = true)
    {
        return $this->hasSeoAttribute($name);
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        try {
            return parent::__get($name);
        } catch (UnknownPropertyException $e) {
            if ($this->hasSeoAttribute($name)) {
                return $this->getSeoAttribute($name);
            } // @codeCoverageIgnoreStart
            else {
                throw $e;
            }
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        try {
            parent::__set($name, $value);
        } catch (UnknownPropertyException $e) {
            if ($this->hasSeoAttribute($name)) {
                $this->setSeoAttribute($name, $value);
            } // @codeCoverageIgnoreStart
            else {
                throw $e;
            }
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function __isset($name)
    {
        if (!parent::__isset($name)) {
            return $this->hasSeoAttribute($name);
        } else {
            return true;
        }
    }

    /**
     * Whether an attribute exists
     * @param string $name the name of the attribute
     * @return boolean
     */
    public function hasSeoAttribute($name)
    {
        return array_key_exists($name, $this->seoAttributes);
    }

    /**
     * @param string $name the name of the attribute
     * @return string the attribute value
     */
    public function getSeoAttribute($name)
    {
        return $this->hasSeoAttribute($name) ? $this->seoAttributes[$name] : null;
    }

    /**
     * @param string $name the name of the attribute
     * @param string $value the value of the attribute
     */
    public function setSeoAttribute($name, $value)
    {
        $this->seoAttributes[$name] = $value;
    }
    public function setSeoAttributes($values)
    {
        if (is_array($values)) {
            foreach ($values as $name => $value) {
                if ($this->hasSeoAttribute($name)) {
                    $this->setSeoAttribute($name, $value);
                }
            }
        }
    }
    /**
     * Whether an attribute exists
     * @param string $name the name of the attribute
     * @return boolean
     */
    public function hasSeoOldAttribute($name)
    {
        return array_key_exists($name, $this->seoOldAttributes);
    }

    /**
     * @param string $name the name of the attribute
     * @return string the attribute value
     */
    public function getSeoOldAttribute($name)
    {
        return $this->hasSeoOldAttribute($name) ? $this->seoOldAttributes[$name] : null;
    }

    /**
     * @param string $name the name of the attribute
     * @param string $value the value of the attribute
     */
    public function setSeoOldAttribute($name, $value)
    {
        $this->seoAttributes[$name] = $value;
    }
    public function setSeoOldAttributes($values)
    {
        if (is_array($values)) {
            foreach ($values as $name => $value) {
                if ($this->hasSeoOldAttribute($name)) {
                    $this->setSeoOldAttribute($name, $value);
                }
            }
        }
    }
}
