<?php
/**
 * Created by PhpStorm.
 * Project: morkovka
 * User: lxShaDoWxl
 * Date: 03.08.15
 * Time: 10:09
 */
namespace shadow\behaviors;

use shadow\helpers\SArrayHelper;
use shadow\SBehavior;
use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;

/**
 * Class SaveRelationBehavior
 * @package shadow\behaviors
 * @property ActiveRecord $owner
 * @property ActiveRecord $model
 */
class SaveRelationBehavior extends SBehavior
{
    /** @var array Name of relation. */
    public $relations = [
        'ItemImg' => [
            'type' => 'img',
            'attribute' => 'item_id'
        ]
    ];
    /** @var array Settings of type relation */
    public $settings = [
        'img' => [
            'filePath' => '@web_frontend/uploads/[[model]]/[[uniqid]].[[extension]]',
            'fileUrl' => '/uploads/[[model]]/[[uniqid]].[[extension]]',
        ],
        'relation' => [],
        'MANY_MANY' => [],
    ];
    public $copy = 0;
    protected $model;
    /**
     * @var array Массив уникального ключа для каждой записи
     */
    protected $models_uniqid = [];
    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
        ];
    }
    /**
     * After save event.
     */
    public function afterSave()
    {
        /**
         * @var ActiveRecord $model
         */
        foreach ($this->relations as $key => $value) {
            if ($this->model = new $key()) {
                $type = '';
                if (isset($value['type'])) {
                    $type = $value['type'];
                }
                if (isset($value['name'])) {
                    $name = $value['name'];
                } else {
                    $r = new \ReflectionClass($this->model->className());
                    $name = lcfirst($r->getShortName());
                }
                $relations = $this->model->find()->where([$value['attribute'] => $this->owner->getPrimaryKey()])->indexBy('id')->all();
                switch ($type) {
                    case 'img':
                        $this->saveImg($name, $relations, $value['attribute'], (isset($value['extra_attributes']) ? $value['extra_attributes'] : []));
                        break;
                    case 'MANY_MANY':
                        if (isset($value['attribute_main'])) {
                            $main_column = $value['attribute_main'];
                        } else {
                            $main_column = $value['attribute'];
                        }
                        if (isset($value['attributes'])) {
                            $attributes = $value['attributes'];
                        } else {
                            $attributes = [$value['attribute']];
                        }
                        $this->saveManyMany($name, $relations, $value['attribute'], $main_column, $attributes, $value['attribute_group'], $value['many_many']);
                        break;
                    default:
                        if (isset($value['attribute_main'])) {
                            $main_column = $value['attribute_main'];
                        } else {
                            $main_column = $value['attribute'];
                        }
                        if (isset($value['attributes'])) {
                            $attributes = $value['attributes'];
                        } else {
                            $attributes = [$value['attribute']];
                        }
                        $this->saveRelation($name, $relations, $value['attribute'], $main_column, $attributes);
                        break;
                }
            } else {
                throw new Exception('Not ' . $key . ' Class ' . $this->owner->className());
            }
        }
    }

    /**
     * Сохранение/изменение связанных изображений
     *
     * @param $relation string Название связи
     * @param $items ActiveRecord[] Данные которые уже есть в  таблице связи
     * @param $column string Название поля ключа связи
     * @param $extra_attributes array Дополнительные поля
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function saveImg($relation, $items, $column, $extra_attributes = [])
    {
        $data = Yii::$app->request->post($relation, []);
        $save_path = Yii::getAlias('@web_frontend/uploads/' . Inflector::camel2id($relation));
        $tmp_path = Yii::getAlias('@backend/tmp');
        $settings = $this->settings['img'];
        $insert = [];
        if ($data) {
            if (!is_dir($save_path)) {
                FileHelper::createDirectory($save_path);
            }
            foreach ($data as $key => $img) {
                if (
                    !isset($items[$key]) &&
                    (
                        is_file($tmp_path . $img['url'])
                        ||
                        (
                            $this->copy
                            &&
                            is_file(Yii::getAlias('@web_frontend') . $img['url'])
                        )
                    )
                ) {
                    $this->models_uniqid[$key] = uniqid();
                    $save_url = $this->resolvePath($settings['fileUrl'], $img['url'], $key);
                    $attribute_insert = [
                        $column => $this->owner->getPrimaryKey(),
                        'url' => $save_url
                    ];
                    if ($extra_attributes) {
                        foreach ($extra_attributes as $extra_attribute) {
                            if (isset($img[$extra_attribute])) {
                                switch ($extra_attribute){
                                    case 'sort':
                                        $attribute_insert[$extra_attribute] = intval($img[$extra_attribute]);
                                        break;
                                    default:
                                        $attribute_insert[$extra_attribute] = $img[$extra_attribute];
                                        break;
                                }
                            }
                        }
                    }
                    $insert[] = $attribute_insert;
                    $save_file = $this->resolvePath($settings['filePath'], $img['url'], $key);
                    if ($this->copy && is_file(Yii::getAlias('@web_frontend') . $img['url'])) {
                        @copy(Yii::getAlias('@web_frontend') . $img['url'], $save_file);
                    } elseif (is_file($tmp_path . $img['url'])) {
                        @rename($tmp_path . $img['url'], $save_file);
                    }
                } else {
                    if (isset($items[$key])) {
                        if ($extra_attributes) {
                            $update = false;
                            foreach ($extra_attributes as $extra_attribute) {
                                if (isset($img[$extra_attribute]) && $img[$extra_attribute] != $items[$key]->getAttribute($extra_attribute)) {
                                    $update = true;
                                    $items[$key]->setAttribute($extra_attribute, $img[$extra_attribute]);
                                }
                            }
                            if ($update) {
                                $items[$key]->save(false);
                            }
                        }
                        unset($items[$key]);
                    }
                }
            }
        }
        if ($items) {
            $deleted = [];
            foreach ($items as $item) {
                $deleted[] = $item->id;
                @unlink(Yii::getAlias('@web_frontend') . $item->url);
            }
            if ($deleted) {
//                $this->model->deleteAll(array('id' => $deleted));
                Yii::$app->db->createCommand()->delete($this->model->tableName(), array('id' => $deleted))->execute();
            }
        }
        if ($insert) {
            $column_table = SArrayHelper::merge([$column, 'url'], $extra_attributes);
            Yii::$app->db->createCommand()->batchInsert($this->model->tableName(), $column_table, $insert)->execute();
        }
    }
    /**
     * Replaces all placeholders in path variable with corresponding values
     *
     * @param string $path
     * @param string $file
     * @param string $id
     * @return string
     */
    public function resolvePath($path, $file, $id = null)
    {
        if (!isset($this->models_uniqid[$id])) {
            $this->models_uniqid[$id] = $id;
        }
        $model = $this->model;
        $path = Yii::getAlias($path);
        $pi = pathinfo($file);
        $fileName = ArrayHelper::getValue($pi, 'filename');
        $extension = strtolower(ArrayHelper::getValue($pi, 'extension'));
        return preg_replace_callback('|\[\[([\w\_/]+)\]\]|', function ($matches) use ($fileName, $extension, $model, $id) {
            $name = $matches[1];
            switch ($name) {
                case 'extension':
                    return $extension;
                case 'filename':
                    return $fileName;
                case 'basename':
                    return $fileName . '.' . $extension;
                case 'app_root':
                    return Yii::getAlias('@app');
                case 'web_root':
                    return Yii::getAlias('@web_frontend');
                case 'base_url':
                    return Yii::getAlias('@web');
                case 'model':
                    $r = new \ReflectionClass($model->className());
                    return Inflector::camel2id($r->getShortName());
                case 'id':
                case 'pk':
                    if (!$this->owner->isNewRecord) {
                        $pk = implode('_', $this->owner->getPrimaryKey(true));
                        return lcfirst($pk);
                    } else {
                        return $this->models_uniqid[$id];
                    }
                case 'uniqid':
                    return $this->models_uniqid[$id];
                case 'id_path':
                    return static::makeIdPath($model->getPrimaryKey());
                case 'parent_id':
                    return $model->{$this->parentRelationAttribute};
            }
            if (preg_match('|^attribute_(\w+)$|', $name, $am)) {
                $attribute = $am[1];
                return $model->{$attribute};
            }
            return '[[' . $name . ']]';
        }, $path);
    }
    /**
     * Сохранение/изменение связи HAS_MANY
     *
     * @param $relation string Название связи
     * @param $items ActiveRecord[] Данные которые уже есть в  таблице связи
     * @param $column string Название поля ключа связи
     * @param $main_column string Название поля которое будет основное и проверяться заполнение
     * @param $attributes array Сохраняемые поля
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function saveRelation($relation, $items, $column, $main_column, $attributes)
    {
        $data = Yii::$app->request->post($relation, []);
        $settings = $this->settings['relation'];
        $insert = [];
        $ml = $this->model->getBehavior('ml');
        if ($data) {
            $isAdd = [];
            foreach ($data as $key => $value) {
                $item_data = $value;
                $value_main = ArrayHelper::getValue($item_data, $main_column);
                if (!isset($items[$key])) {
                    if ($value_main && !isset($isAdd[$value_main])) {
                        $isAdd[$value_main] = 1;
                        $item_data[$column] = $this->owner->getPrimaryKey();
                        $insert[] = $item_data;
                    }
                } else {
                    $update = false;
                    $target_item = $items[$key];
                    $isAdd[$value_main] = 1;
                    foreach ($item_data as $key_i => $value_i) {
                        if ($target_item->hasAttribute($key_i) && ((string)$target_item->getAttribute($key_i)) !== ((string)$value_i)) {
                            $target_item->setAttribute($key_i, $value_i);
                            $update = true;
                        }
                        if ($ml && $target_item->hasLangAttribute($key_i) && ((string)$target_item->getLangAttribute($key_i)) !== ((string)$value_i)) {
                            $target_item->setLangAttribute($key_i, $value_i);
                            $update = true;
                        }
                    }
                    if ($update) {
                        $target_item->save(false);
                    }
                    unset($items[$key]);
                }
            }
        }
        if ($items) {
            $deleted = [];
            foreach ($items as $item) {
                $deleted[] = $item->getPrimaryKey();
            }
            if ($deleted) {
//                $this->model->deleteAll(array('id' => $deleted));
                Yii::$app->db->createCommand()->delete($this->model->tableName(), array('id' => $deleted))->execute();
            }
        }
        if ($insert) {
            if ($ml) {
                foreach ($insert as $values) {
                    $record = clone $this->model;
                    $record->setAttributes($values, false);
                    $record->setLangAttributes($values);
                    $record->save(false);
                }
            } else {
                $attributes[] = $column;
                Yii::$app->db->createCommand()->batchInsert($this->model->tableName(), $attributes, $insert)->execute();
            }
        }
    }
    /**
     * Сохранение/изменение связи MANY_MANY
     *
     * @param $relation string Название связи
     * @param $items_old ActiveRecord[] Данные которые уже есть в  таблице связи
     * @param $column string Название поля ключа связи
     * @param $main_column string Название поля которое будет основное и проверяться заполнение
     * @param $attributes array Сохраняемые поля
     * @param $attribute_group string Название поля для группировки
     * @param $many_many array Параметры для создание нового значения
     * @throws \yii\db\Exception
     */
    public function saveManyMany($relation, $items_old, $column, $main_column, $attributes, $attribute_group, $many_many)
    {
        /**
         * @var ActiveRecord $target_item
         * @var ActiveRecord $record
         */
        $data = Yii::$app->request->post($relation, []);
        $settings = $this->settings['MANY_MANY'];
        $insert = [];
        $items = ArrayHelper::map(
            $items_old,
            function ($element) use ($main_column) {
                return $element->{$main_column};
            },
            function ($element) {
                return $element;
            },
            $attribute_group
        );
        if ($data) {
            $i = 0;
            foreach ($data as $key => $value) {
                $item_data = $value;
                foreach ($item_data as $key_i => $value_i) {
                    if ($main_column == $key_i) {
                        foreach ($value_i as $value_select) {
                            //TODO дописать что бы добавлял целочисленные и дробные значения, либо добавить ещё одно поле в форме с вводом новых значений
                            $id_select = (int)$value_select;
                            if (!is_numeric($value_select)) {
                                $record = new $many_many['model']();
                                $record->attributes = $many_many['attributes']($key, $value_select);
                                if ($record->save(false)) {
                                    $insert[$i++] = [
                                        $attribute_group => $key,
                                        $main_column => $record->getPrimaryKey(),
                                        $column => $this->owner->getPrimaryKey(),
                                    ];
                                }
                            } else {
                                if (isset($items[$key]) && isset($items[$key][$id_select])) {
                                    $target_item = $items[$key][$id_select];
                                    unset($items_old[$target_item->getPrimaryKey()]);
                                } else {
                                    $insert[$i++] = [
                                        $attribute_group => $key,
                                        $main_column => $id_select,
                                        $column => $this->owner->getPrimaryKey(),
                                    ];
                                }
                            }
                        }
                    } else {
//                                foreach ($target_items as $target_item) {
//                                    if ($target_item->hasAttribute($key_i)&& $target_item->getAttribute($key_i) != $value_i) {
//                                        $target_item->setAttribute($key_i, $value_i);
//                                        $update = true;
//                                    }
//                                }
                    }
                }
            }
        }
        if ($items_old) {
            $deleted = [];
            foreach ($items_old as $item) {
                $deleted[] = $item->getPrimaryKey();
            }
            if ($deleted) {
//                $this->model->deleteAll(array('id' => $deleted));
                Yii::$app->db->createCommand()->delete($this->model->tableName(), array('id' => $deleted))->execute();
            }
        }
        if ($insert) {
            $attributes[] = $column;
            Yii::$app->db->createCommand()->batchInsert($this->model->tableName(), $attributes, $insert)->execute();
        }
    }
}