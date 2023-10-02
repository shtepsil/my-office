<?php

namespace backendold\models;

use backendold\models\Projects;
use backendold\models\WorkingHours;
use backendold\models\Payment;
use backendold\helpers\SNumberHelper;
use Yii;

/**
 * This is the model class for table "tasks".
 *
 * @property int $id
 * @property int $project_id ID проекта по таблице projects
 * @property string $name Наименование задачи
 * @property string $description Описание задачи
 * @property string $time_work Время, по траченное на задачу
 (от начала работы - до приема задачи заказчиком)
 * @property int $created_at Время создания задачи
 */
class Tasks extends \yii\db\ActiveRecord
{

    public $wh = NULL;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tasks';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['project_id', 'created_at', 'active'], 'integer'],
            [['name', 'description', 'time_work'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'project_id' => 'ID проекта по таблице projects',
            'name' => 'Наименование задачи',
            'description' => 'Описание задачи',
            'time_work' => 'Время, по траченное на задачу
(от начала работы - до приема задачи заказчиком)',
            'active' => 'Видимость задачи',
            'created_at' => 'Время создания задачи',
        ];
    }

    // Проект по задаче
    public function getProject()
    {
        return $this->hasOne(Projects::className(), ['id' => 'project_id']);
    }

    // Время работы по задаче
    public function getWorkinghours()
    {
        $query = $this->hasMany(WorkingHours::className(), ['task_id' => 'id'])
            ->orderBy('created_at');

        return $query;

    }

    // В работе
    // Таблица "WorkingHours" - получаем все строки в работе (сумму общего времени в работе)
    // Working hourse in work sum
    public function getWhinworksum()
    {
        return $this->hasMany(WorkingHours::className(), ['task_id' => 'id'])
            ->where(['status' => '1'])
            ->sum('working_hours');
    }

    // Оплаченные
    // Таблица "Payment" - получаем сумму общего времени не оплаченных задач
    public function getPwhnopaidsum()
    {
        return $this->hasMany(Payment::className(), ['task_id' => 'id'])
            ->where(['status' => '2'])
            ->sum('working_hours');
    }

    // Ожидающие
    // Таблица "Payment" - получаем сумму общего времени ожидающих оплаты задач
    public function getPwhwaitpaysum()
    {
        return $this->hasMany(Payment::className(), ['task_id' => 'id'])
            ->where(['status' => '3'])
            ->sum('working_hours');
    }

    // Оплаченные
    // Таблица "Payment" - получаем сумму общего времени оплаченных задач
    public function getPwhpaidsum()
    {
        return $this->hasMany(Payment::className(), ['task_id' => 'id'])
            ->where(['status' => '4'])
            ->sum('working_hours');
    }

    /*
     * Таблица "Payment" - все строки не оплаченных задач
     */
    public function getPwhnopaid()
    {
        return $this->hasMany(Payment::className(), ['task_id' => 'id'])
            ->where(['status' => '2']);
    }

    // Таблица "Payment" - все строки ожидающих оплаты задач
    public function getPwhwaitpay()
    {
        return $this->hasMany(Payment::className(), ['task_id' => 'id'])
            ->where(['status' => '3']);
    }

    // Таблица "Payment" - все строки оплаченых задач
    public function getPwhpaid()
    {
        return $this->hasMany(Payment::className(), ['task_id' => 'id'])
            ->where(['status' => '4']);
    }

    public function getDateMinMax($time)
    {
        $result = [];
        if (count($time)) {
//            d::pri(date('Y-m-d', max($time)));
            $result = [
                'max' => date('Y-m-d', max($time)),
                'min' => date('Y-m-d', min($time)),
            ];
        }
        return $result;
    }

    public function getWHCost($data)
    {
        // Получение стоимости не оплаченной задачи
        $cost = 0;
        foreach ($data as $item) {
            $cost +=
                SNumberHelper::getCost(
                // Сумма времени закрытой/не оплаченой задачи
                    $item->working_hours,
                    // Курс валюты на момент закрытия задачи
                    ['rate' => $item->rate, 'c_value' => $item->currency_value]
                )['cost'];
        }

        return $cost;
    }

    public function sortWH()
    {
        $result = [];
        $wh = $this->workinghours;

        $result['inwork']['ids'] =
        $result['no_paid']['ids'] =
        $result['waitpay']['ids'] =
        $result['paid']['ids'] = '';

        $result['inwork']['time'] =
        $result['no_paid']['time'] =
        $result['waitpay']['time'] =
        $result['paid']['time'] = [];

        if (count($wh)) {
            foreach ($wh as $item) {
                // В работе - 1
                if ($item->status == '1') {
                    $result['inwork']['time'][] = $item->created_at;
                    $result['inwork']['ids'] .= ',' . $item->id;
                } elseif ($item->status == '2') {
                    // Закрыты, не оплачено - 2
                    $result['no_paid']['time'][] = $item->created_at;
                    $result['no_paid']['ids'] .= ',' . $item->id;
                } elseif ($item->status == '3') {
                    // Ожидание оплаты - 3
                    $result['waitpay']['time'][] = $item->created_at;
                    $result['waitpay']['ids'] .= ',' . $item->id;
                } else {
                    // Оплачено - 4
                    $result['paid']['time'][] = $item->created_at;
                    $result['paid']['ids'] .= ',' . $item->id;
                }
            }

//            $data['wh_inwork']['wh_total_amount']['total_info'][] = [
//                $task->id,
//                $task->name,
//                SNumberHelper::getCost($task->whinworksum)
//            ];

//                $data['wh_inwork']['wh_total_amount']['total_amount'] += SNumberHelper::getCost($task->whinworksum);

        }
        $result['inwork'] = [
            'ids' => ($result['inwork']['ids'] != '') ? substr($result['inwork']['ids'], 1) : '',
            'time' => $result['inwork']['time'],
            'time_sum' => SNumberHelper::whSecToHMS($this->whinworksum),
            'cost' => SNumberHelper::getCost($this->whinworksum),
            'date' => $this->getDateMinMax($result['inwork']['time']),
        ];
        $result['no_paid'] = [
            'ids' => ($result['no_paid']['ids'] != '') ? substr($result['no_paid']['ids'], 1) : '',
            'time' => $result['no_paid']['time'],
            'time_sum' => SNumberHelper::whSecToHMS($this->pwhnopaidsum),
            'cost' => $this->getWHCost($this->pwhnopaid),
            'date' => $this->getDateMinMax($result['no_paid']['time'])
        ];
        $result['waitpay'] = [
            'ids' => ($result['waitpay']['ids'] != '') ? substr($result['waitpay']['ids'], 1) : '',
            'time' => $result['waitpay']['time'],
            'time_sum' => SNumberHelper::whSecToHMS($this->pwhwaitpaysum),
            'cost' => $this->getWHCost($this->pwhwaitpay),
            'date' => $this->getDateMinMax($result['waitpay']['time'])
        ];
        $result['paid'] = [
            'ids' => ($result['paid']['ids']!= '') ? substr($result['paid']['ids'], 1) : '',
            'time' => $result['paid']['time'],
            'time_sum' => SNumberHelper::whSecToHMS($this->pwhpaidsum),
            'cost' => $this->getWHCost($this->pwhpaid),
            'date' => $this->getDateMinMax($result['paid']['time'])
        ];

        return $result;
    }

} //Class
