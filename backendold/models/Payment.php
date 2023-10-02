<?php

namespace backendold\models;

use Yii;
use backendold\models\Tasks;

/**
 * This is the model class for table "payment".
 *
 * @property string $id
 * @property int $task_id ID задачи таблицы tasks
 * @property string $currency Код валюты, по которой расчитывается час оплаты
 * @property double $rate Текущий курс валюты
 * @property string $ids_working_hours IDs строк working_hours, которые оплачиваются по зафиксированному курсу валюты
 *
 * @property Tasks $task
 */
class Payment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['task_id'], 'integer'],
            [['rate'], 'number'],
            [['ids_working_hours'], 'string'],
            [['currency'], 'string', 'max' => 20],
            [['task_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tasks::className(), 'targetAttribute' => ['task_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'task_id' => 'ID задачи таблицы tasks',
            'currency' => 'Код валюты, по которой расчитывается час оплаты',
            'rate' => 'Текущий курс валюты',
            'ids_working_hours' => 'IDs строк working_hours, которые оплачиваются по зафиксированному курсу валюты',
            'working_hours' => 'Время работы',
            'cost' => 'Стоимость закрытых строк working_hours',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTask()
    {
        return $this->hasOne(Tasks::className(), ['id' => 'task_id']);
    }
}
