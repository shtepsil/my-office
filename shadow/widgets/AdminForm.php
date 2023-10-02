<?php
namespace shadow\widgets;

use shadow\assets\Select2Assets;
use shadow\helpers\StringHelper;
use Yii;
use yii\bootstrap\Widget;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * Class AdminForm
 * @package shadow\widgets
 * @property \backend\models\Pages $item
 */
class AdminForm extends Widget
{
    /**
     * @var array $params параметры формы
     */
    public $params;
    /**
     * @var \yii\db\ActiveRecord | \yii\base\Model $item
     */
    public $item;
    public $title;
    public $selected;
    public $maps = false;
    /**
     * @var bool | \shadow\plugins\seo\behaviors\SSeoBehavior
     */
    public $seo = false;
    private $no_input = array('dropDownList');
    public function run()
    {
        $this->seo = $this->item->getBehavior('seo');
        if (!$this->params) {
            $this->params = $this->item->FormParams();
        }
        if (!\Yii::$app->request->isAjax) {
            return $this->render('form/index', ArrayHelper::merge(
                [
                    'item' => $this->item,
                    'selected' => $this->selected,
                    'no_input' => $this->no_input
                ],
                $this->params
            ));
        }
//		if (Yii::app()->request->isAjaxRequest) {
//			Yii::app()->getClientScript()->reset();
//			$output = $this->render('//form/form', array_merge(array(
//				'item' => $this->item,
//				'selected' => $this->selected,
//				'no_input' => $this->no_input
//			), $this->params), true);
//			Yii::app()->getClientScript()->scriptMap=array(
//				'jquery.js'=>false,
//			);
//			Yii::app()->getClientScript()->render($output);
//			echo $output;
//		} else {
//			$this->render('//form/form', array_merge(array(
//				'item' => $this->item,
//				'selected' => $this->selected,
//				'no_input' => $this->no_input
//			), $this->params));
//		}
    }
    /**
     * @param $form \shadow\widgets\AdminActiveForm
     * @param $config array
     * @param $key string
     * @return string
     */
    public function getRow($form, $key, $config)
    {
        /**
         * @var $field \shadow\widgets\AdminActiveField
         */
        if ($result = $this->getFiles($form, $key, $config)) {
            return $result;
        }
        $panel = false;
        if (isset($config['title'])) {
            $name = $config['title'];
            unset($config['title']);
        } else {
            $name = $this->item->getAttributeLabel($key);
        }
        $field_options = [
            'inputOptions' => [
                'placeholder' => $name,
//                'autocomplete'=>'off'
            ],
            'labelOptions' => [
                'label' => $name
            ]
        ];
        if (isset($config['field_options']) && $config['field_options']) {
            $config_field_options = $config['field_options'];
            if (isset($config_field_options['options']['class']) && is_array($form->fieldConfig)) {
                Html::addCssClass($config_field_options['options'], $form->fieldConfig['options']['class']);
            }
            $field_options = ArrayHelper::merge($config_field_options, $field_options);
        }
        if (isset($config['panel']) && $config['panel'] == true) {
            $panel = true;
            $field_options['template'] = "{input}\n";
            $field_options['options']['class'] = 'panel-collapse collapse';
            $field_options['options']['id'] = 'collapseOne-'.$key;
            $field_options['options']['style'] = 'height: 0px;';
        }
        $field = $form->field($this->item, $key, $field_options);
        $relation = false;
        $data = [];
        if (isset($config['relation'])) {
            /**
             * @var \yii\db\ActiveRecord | \yii\base\Model $relation_model
             */
            $relation = $config['relation'];
            $query = new ActiveQuery($relation['class'], (isset($relation['query']) ? $relation['query'] : ['orderBy' => ['id' => SORT_DESC]]));
            $relation_data = $query->all();
            $data = ArrayHelper::map($relation_data, 'id', isset($relation['label']) ? $relation['label'] : 'name');
            unset($config['relation']);
            //TODO генерация и вывод селекта
        }
        if (!$this->item->hasAttribute($key) && false) {
            if (!isset($config['widget'])) {
                $value = '';
                $type_field = 'textInput';
                if (isset($config['value'])) {
                    $value = $config['value'];
                }
                if (isset($config['type_field'])) {
                    $type_field = $config['type_field'];
                }
                $config['widget'] = [
                    'class' => 'shadow\widgets\AdminField',
                    'config' => [
                        'field' => $type_field,
                        'name' => $key,
                        'value' => $value,
                        'inputOptions' => array(
                            'placeholder' => $name,
                            'id' => Html::getInputId($this->item, $key)
                        )
                    ]
                ];
            }
        } else {
            if ($relation) {
                if (!$this->item->isAttributeRequired($key)) {
                    $data = ['' => (isset($relation['default']) ? $relation['default'] : 'Нет')] + $data;
                }
                $field->dropDownList($data);
            }
        }
        if (isset($config['type'])) {
            $params_field = isset($config['params']) ? $config['params'] : [];
            switch ($config['type']) {
                case 'dropDownList':
                    $field->dropDownList(isset($config['data']) ? $config['data'] : $data, $params_field);
                    break;
                case 'textArea':
                    $field->textarea($params_field);
                    break;
                case 'file':
                    $field->fileInput($params_field);
                    break;
                case 'img':
                    $field->imgInput($params_field);
                    break;
                case 'password':
                    $field->passwordInput($params_field);
                    break;
                case 'checkbox':
                    $field->checkbox($params_field, false);
                    break;
                default:
                    $field->input($config['type'], $params_field);
                    break;
            }
        }
        if (isset($config['widget']) && $config['widget']) {
            $widget = $config['widget'];
            $field->widget($widget['class'], (isset($widget['config'])) ? $widget['config'] : []);
        }
        $result = $field;
        if ($panel) {
            $a_panel = Html::a($name, '#collapseOne-'.$key, [
                'class' => "accordion-toggle collapsed",
                'data-toggle' => "collapse",
                'data-parent' => '#accordion-'.$key
            ]);
            $content_panel =
                Html::tag('div', $a_panel, ['class' => 'panel-heading']) . $field;
            $panel = Html::tag('div', Html::tag('div', $content_panel), ['class' => 'panel-group panel-group-success', 'id' => 'accordion-'.$key]);
            $result = $panel;
        }
        if ($this->seo && $this->seo->nameTranslate == $key) {
            $result .= $this->getRow($form, 'seo_url', $this->seo->configField());
        }
        return $result;
    }

    public function getFiles($form, $key, $config)
    {
        $result = false;
        if (isset($config['files']) && $config['files']) {
            if (!isset($config['files']['name'])) {
                $config['files']['name'] = $key;
            }
            $config_files = $config['files'];
            if (isset($config_files['relation'])) {
                $relation = $config_files['relation'];
                unset($config_files['relation']);
                if (!$this->item->isNewRecord) {
                    $query = new ActiveQuery($relation['class'], (isset($relation['query']) ? $relation['query'] : []));
                    $relation_data = $query->all();
                    $files = [];
                    foreach ($relation_data as $value) {
                        $files[] = [
                            'name' => StringHelper::basename($value->url),
                            'size' => 0,
//                            'type' => mime_content_type(Yii::getAlias('@frontend') . $value->url),
                            'url' => $value->url,
                            'id' => $value->id
                        ];
                    }
                    $config_files['value'] = $files;
                }
                $r = new \ReflectionClass($relation['class']);
                $config_files['name'] = lcfirst($r->getShortName());
            }
            $result = FilesUpload::widget($config_files);
        }
        return $result;
    }
    protected $multiple;
    protected $model;
    protected $config_relation;

    /**
     * @param $config
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getRelation($config)
    {
        /**
         * @var \yii\db\ActiveRecord | \yii\base\Model $model
         */
        $this->config_relation = $config;
        $data = $config;
        $type = '';
        if (isset($config['type'])) {
            $type = $config['type'];
        }
        switch ($type) {
            //TODO сделать для MANY_MANY
            case 'MANY_MANY';
                /**
                 * Пример настройки
                 * $group=[
                 * 'values' => [
                 * 'title' => 'Характеристики',
                 * 'icon' => 'th-list',
                 * 'options' => [],
                 * 'relation'=>[
                 * 'class'=>OptionsCategory::className(),
                 * 'type'=>'MANY_MANY',
                 * 'multiple'=>[
                 * 'class'=>ItemOptionsValue::className(),
                 * 'field'=>'item_id',
                 * 'field_group'=>'option_id',
                 * 'id'=>'option_id',
                 * 'field_value'=>function ($element) {
                 * return $element;
                 * }
                 * ],
                 * 'add'=>false,
                 * 'field'=>'cid',
                 * 'field_value'=>$this->cid,
                 * 'attributes'=>[
                 * 'option_id'=>[
                 * 'type'=>'relation',
                 * 'relation'=>'option',
                 * 'field'=>'name'
                 * ],
                 * 'option_value_id'=>[
                 * 'isNull'=>false,
                 * 'label'=>'Значение',
                 * 'type'=>'dropDownList',
                 * 'relation'=>[
                 * 'class'=>OptionsValue::className(),
                 * 'multiple_field'=>'option_id',
                 * 'field'=>'value',
                 * 'query'=>[
                 * 'where'=>'option_id=:id',
                 * 'params'=>[':id'=>true]
                 * ]
                 * ]
                 * ],
                 * ]
                 * ]
                 * ]
                 * ]
                 **/
                Select2Assets::register($this->view);
                if(isset($this->config_relation['remote_data'])){
                    $class_select2 = 'widget-select2-remote-' . strtolower($this->item->formName());
                    $url_select2 = $this->config_relation['remote_data']['url'];
                    $id_uni = hash('crc32', $this->config_relation['name']);
                    $this->view->registerJs('var select2_remote_class={};', 1,'main_select2_remote');
                    $this->view->registerJs('select2_remote_class['.Json::encode($id_uni).']='.Json::encode('.'.$class_select2).';',2);
                    $ignore_id = $this->item->id;
                    if (isset($this->config_relation['remote_data']['ignore_id'])) {
                        $ignore_id = Json::encode($this->config_relation['remote_data']['ignore_id']);
                    }else{
                        $ignore_id = Json::encode($ignore_id);
                    }
                    $this->view->registerCss(".select2-result-repository { padding-top: 4px; padding-bottom: 3px; }
.select2-result-repository__avatar { float: left; width: 60px; margin-right: 10px; }
.select2-result-repository__avatar img { width: 100%; height: auto; border-radius: 2px; }
.select2-result-repository__meta { margin-left: 70px; }
.select2-result-repository__title { color: black; font-weight: bold; word-wrap: break-word; line-height: 1.1; margin-bottom: 4px; }
.select2-result-repository__description { font-size: 13px; color: #777; margin-top: 4px; }
.select2-results__option--highlighted .select2-result-repository__title { color: white; }
.select2-results__option--highlighted .select2-result-repository__forks, .select2-results__option--highlighted .select2-result-repository__stargazers, .select2-results__option--highlighted .select2-result-repository__description, .select2-results__option--highlighted .select2-result-repository__watchers { color: #c6dcef; }
.select2-container .select2-selection--multiple .select2-selection__rendered {
    text-overflow: initial;
    white-space: initial;
}"
                    );
                    $this->view->registerJs(<<<JS
function init_remote_select_{$id_uni}(selector) {
    $(selector).select2({
        width: '100%',
        language: 'ru',
        ajax: {
            url: {$url_select2},
            dataType: 'json',
            //delay: 250,
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    id: {$ignore_id}
                };
            },
            processResults: function (data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;
                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) {
            return markup;
        },
        //minimumInputLength: 0,
        templateResult: formatRepo,
        templateSelection: formatRepoSelection
    });
}
init_remote_select_{$id_uni}('.{$class_select2}:not([disabled])');
function formatRepo(item) {
    if (item.loading) return item.text;
    return "<div class='select2-result-repository clearfix'>" +
        "<div class='select2-result-repository__avatar'><img src='" + item.img + "' /></div>" +
        "<div class='select2-result-repository__meta'>" +
        "<div class='select2-result-repository__title'>" + item.name + "</div>"+
        "<div class='select2-result-repository__description'>Артикул: " + item.vendor_code + "<br/>ID:"+item.id+"</div>"+
        "</div></div>";
}

function formatRepoSelection(item) {
    return item.name || item.text;
}
JS
                    );
                }else{
                    $this->view->registerJs(<<<JS
$('.widget-select2').select2({
    width: '250px',
    tags: true,
    language: 'ru'
});
JS
                    );
                }
                if(isset($config['items'])){
                    $data['items'] = $config['items'];
                }else{
                    $this->multiple = $config['multiple'];
                    $query = new ActiveQuery($config['class'], (isset($config['query']) ? $config['query'] : []));
                    $query->andWhere([$config['field'] => isset($config['field_value']) ? $config['field_value'] : $this->item->getPrimaryKey()]);
                    $data['items'] = $query->all();
                    $this->model = Yii::createObject($this->multiple['class']);
                    $query_multiple = new ActiveQuery($this->multiple['class'], (isset($this->multiple['query'])) ? $this->multiple['query'] : []);
                    $query_multiple->andWhere([$this->multiple['field'] => $this->item->getPrimaryKey()]);
                    $this->multiple['items'] = ArrayHelper::map(
                        $query_multiple->all(),
                        'id',
                        $this->multiple['field_value'],
                        $this->multiple['field_group']);
                }
                break;
            default:
                $this->model = Yii::createObject($config['class']);
                if (!$this->item->isNewRecord) {
                    if ((isset($config['query']) ? $config['query'] : [])) {
                        $query = new ActiveQuery($config['class'], (isset($config['query']) ? $config['query'] : []));
                    } else {
                        $query = $this->model->find();
                    }
                    $query->andWhere([$config['field'] => $this->item->getPrimaryKey()]);
                    $data['items'] = $query->all();
                } else {
                    $data['items'] = [];
                }
        }
        if (!isset($config['name'])) {
            $r = new \ReflectionClass($this->model->className());
            $data['name'] = lcfirst($r->getShortName());
        }
        $data['model'] = $this->model;
        return $this->render('form/relation', $data);
    }
    /**
     * @param null | \yii\db\ActiveRecord $item
     * @param $name
     * @param $attribute
     * @param $config
     * @param $clone
     * @return string
     */
    public function getRelationField($item = null, $name, $attribute, $config = [], $clone = false)
    {
        $id = 'new';
        $value = '';
        $options = ['class' => 'form-control', 'data-field' => $attribute];
        if (!$this->multiple) {
            if (is_object($item)) {
                $id = $item->getPrimaryKey();
                $value = $item->{$attribute};
            }elseif(is_array($item)){//TODO доработать
                $id = $item->getPrimaryKey();
                $value = $item->{$attribute};
            }
        } else {
            $id = $item->{$this->multiple['id']};
            $options['multiple'] = true;
            Html::addCssClass($options['class'], 'widget-select2');
            if (isset($this->multiple['items'][$id])) {
                $value = array_keys($this->multiple['items'][$id]);
            }
        }
        $many_many = (isset($this->config_relation['type']) && $this->config_relation['type'] == 'MANY_MANY');
        if ($clone) {
            $name = 'Clone'.$name;
        }
        if($many_many){
            $name = $name . '['.$id.']';
            if($item!==null){
                $options['disabled'] = true;
            }
            if(isset($this->config_relation['remote_data'])){
                Html::addCssClass($options, 'widget-select2-remote-'.strtolower($this->item->formName()));
            }
        }else{
            $name = $name . '[' . $id . '][' . $attribute . ']';
        }
        $type = '';
        if (isset($config['type'])) {
            $type = $config['type'];
        }
        switch ($type) {
            case 'dropDownList':
                $data = isset($config['data']) ? $config['data'] : [];
                if (isset($config['relation'])) {
                    /**
                     * @var \yii\db\ActiveRecord | \yii\base\Model $relation_model
                     */
                    $relation = $config['relation'];
                    $query_array = (isset($relation['query']) ? $relation['query'] : []);
                    $field_relation = (isset($relation['field']) ? $relation['field'] : 'name');
                    $value_relation = (isset($relation['value']) ? $relation['value'] : 'id');
                    if (isset($query_array['params'][':id'])) {
                        $query_array['params'][':id'] = $item->{$relation['multiple_field']};
                    }
                    $query = new ActiveQuery($relation['class'], $query_array);
                    $relation_data = $query->all();
                    $data = ArrayHelper::map($relation_data, $value_relation, $field_relation);
                }
                if (!isset($config['isNull']) || (isset($config['isNull']) && $config['isNull'] == true)) {
                    $data = ArrayHelper::merge(['' => 'Не выбрано'], $data);
                }
                $result = Html::dropDownList($name, $value, $data, $options);
                if(isset($options['disabled'])&&$options['disabled']){
                    $result .= Html::input('hidden',
                        $name,
                        $value
                    );
                }
                break;
            case 'relation':
                $result = $item->{$config['relation']}->{$config['field']};
                break;
            case 'name':
                $result = '';
                break;
            case 'checkbox':
                $result = Html::checkbox(
                    $name,
                    $value,
                    $options + ['value' => 1, 'uncheck' => 0]
                );
//                $result = Html::input('checkbox',
//                    ,
//                    $value,
//                    $options
//                );
                break;
            default:
                $result = Html::input('text',
                    $name,
                    $value,
                    $options
                );
                break;
        }
        return $result;
    }
}