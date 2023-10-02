<?php
/**
 * Created by PhpStorm.
 * Project: yii2-cms
 * User: lxShaDoWxl
 * Date: 08.05.15
 * Time: 11:30
 */
namespace shadow;

use shadow\helpers\SArrayHelper;
use shadow\helpers\StringHelper;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class SActiveRecord extends ActiveRecord
{
    public function saveUniqueName()
    {
        if (isset($this->unique_name)) {
            $this->unique_name = StringHelper::TranslitRuToEn($this->unique_name);
        }
    }
    public function saveAll($event)
    {
//        $this->saveUniqueName();
    }
    public function saveClear($event)
    {
    }
    public function validateAll()
    {
        return true;
    }
    /**
     * @param string $name
     * @param array $new_relation
     * @param \yii\base\Event $event
     * @param array $extraColumns
     * @param string $indexBy
     * @throws InvalidConfigException
     */
    public function saveRelation($name, $new_relation, $event, $extraColumns = [], $indexBy = '')
    {
        $relation = $this->getRelation($name);
        if ($relation->via !== null && is_array($relation->via)) {
            /* @var $viaRelation \yii\db\ActiveQuery */
            list($viaName, $viaRelation) = $relation->via;
            $viaClass = $viaRelation->modelClass;
            /**
             * @var $old_relation \yii\db\ActiveRecord[]
             * @var $model \yii\db\ActiveRecord
             */
            $insert_data = [];
            $model = new $viaClass();
            if ($event->name == $this::EVENT_AFTER_INSERT) {
                $old_relation = [];
            } else {
                $old_relation = $viaRelation->indexBy($relation->link['id'])->all();
            }
            $table_columns = [];
            $add_ids = [];
            foreach ($new_relation as $key => $value) {
                if(!trim($value)){
                    continue;
                }
                if (!isset($old_relation[$value])) {
                    if(isset($add_ids[$value])){
                        continue;
                    }
                    $columns = [];
                    foreach ($viaRelation->link as $a => $b) {
                        $columns[$a] = $this->$b;
                    }
                    foreach ($relation->link as $a => $b) {
                        $columns[$b] = $value;
                    }
                    foreach ($extraColumns as $k => $v) {
                        $columns[$k] = $v;
                    }
                    if (!$table_columns) {
                        $table_columns = array_keys($columns);
                    }
                    $insert_data[] = $columns;
                } else {
                    unset($old_relation[$value]);
                }
                $add_ids[$value] = $value;
            }
            if ($old_relation) {
                $delete_data = [];
                foreach ($old_relation as $key => $value) {
                    $delete_data[] = $value->id;
                }
                if ($delete_data) {
                    \Yii::$app->db->createCommand()->delete($model->tableName(), ['id' => $delete_data])->execute();
                }
            }
            if ($insert_data && $table_columns) {
                \Yii::$app->db->createCommand()->batchInsert($model->tableName(),
                    $table_columns,
                    $insert_data)->execute();
            }
        } else {
            if (!$indexBy) {
                throw new InvalidConfigException('"' . get_called_class() . '" not specified $indexBy.');
            }
            $relation_class = $relation->modelClass;
            /**
             * @var $old_relation \yii\db\ActiveRecord
             * @var $model \yii\db\ActiveRecord
             */
            $insert_data = [];
            $model = new $relation_class();
            if ($event->name == $this::EVENT_AFTER_INSERT) {
                $old_relation = [];
            } else {
                $old_relation = $relation->indexBy($indexBy)->all();
            }
            $table_columns = [];
            foreach ($new_relation as $key => $value) {
                if (!isset($old_relation[$value])) {
                    $columns = [];
                    foreach ($relation->link as $a => $b) {
                        $columns[$a] = $this->$b;
                    }
                    $columns[$indexBy] = $value;
                    foreach ($extraColumns as $k => $v) {
                        $columns[$k] = $v;
                    }
                    if (!$table_columns) {
                        $table_columns = array_keys($columns);
                    }
                    $insert_data[] = $columns;
                } else {
                    unset($old_relation[$value]);
                }
            }
            if ($old_relation) {
                $delete_data = [];
                foreach ($old_relation as $key => $value) {
                    $delete_data[] = $value->id;
                }
                if ($delete_data) {
                    \Yii::$app->db->createCommand()->delete($model->tableName(), ['id' => $delete_data])->execute();
                }
            }
            if ($insert_data && $table_columns) {
                \Yii::$app->db->createCommand()->batchInsert($model->tableName(),
                    $table_columns,
                    $insert_data)->execute();
            }
        }
    }
    /**
     * @param $result array параметры формы
     * @param $fields_params array параметры filed
     *
     * Генерация параметров формы для мулти язычности
     */
    public function ParamsLang(&$result, $fields_params)
    {
        /**
         * @var $ml \shadow\multilingual\behaviors\MultilingualBehavior
         */
        $ml = $this->getBehavior('ml');
        foreach (\Yii::$app->params['languages'] as $key => $lang) {
            if ($key != $ml->defaultLanguage) {
                $fields = [];
                foreach ($ml->attributes as $name_attribute) {
                    if (isset($fields_params[$name_attribute])) {
                        $fields[$name_attribute . '_' . $key] = $fields_params[$name_attribute];
                    }
                }
                $group[$key] = [
                    'title' => $lang,
                    'icon' => 'globe',
                    'options' => [],
                    'fields' => $fields,
                ];
            }
        }
        if (isset($group)) {
            if (isset($result['groups'])) {
                $result['groups'] = ArrayHelper::merge($result['groups'], $group);
            } else {
                $result['groups'] = $group;
            }
        }
    }
}