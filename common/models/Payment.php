<?php

namespace common\models;

use Yii;
use backend\models\Tasks;

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
            'project_id' => 'ID проекта',
            'task_id' => 'ID задачи таблицы tasks',
            'ids_working_hours' => 'IDs строк working_hours, которые оплачиваются по зафиксированному курсу валюты',
            'working_hours' => 'Время работы',
            'currency_value' => 'Тариф за час в валюте. По какому тарифу за час была закрыта задача.',
            'currency' => 'Код валюты, по которой рассчитывается час оплаты',
            'rate' => 'Текущий курс валюты',
            'cost' => 'Стоимость закрытых строк working_hours',
            'status' => '2-не оплачено/3-ожидание оплаты/4-оплачено',
            'created_at' => '',
            'updated_at' => '',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTask()
    {
        return $this->hasOne(Tasks::className(), ['id' => 'task_id']);
    }

    public static function addReportsId($report_id, $project_payment_ids)
    {
        $payment_ids_update = [];
        foreach($project_payment_ids as $payment_ids) {
            foreach($payment_ids as $payment_id){
                $payment_ids_update[] = $payment_id;
            }
        }
        Payment::updateAll(['reports_id' => $report_id], ['IN', 'id', $payment_ids_update]);
    }
}
