<?php

namespace backend\models;

use backend\controllers\MainController as d;
use backend\models\WebmasterSettings;
use common\models\Payment;
use shadow\helpers\SArrayHelper;
use shadow\helpers\SNumberHelper;
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
                $this->getCost(
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
//        $wh = $this->workinghours;
        $wh = WorkingHours::find()
            ->where(['task_id' => $this->id])
            ->andWhere(['is', 'reports_id', NULL])
            ->all();

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
            'cost' => $this->getCost($this->whinworksum),
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

    /**
     * @param $sec
     * @param bool $params
     * @return array|float
     */
    public function getCost($sum_sec, $params = [], $flag = false)
    {
        $get = Yii::$app->request->get();
        $post = Yii::$app->request->post();
        // Request
        $rt = SArrayHelper::merge($get, $post);
//        d::ajax($rt);

        /*
         * Если нужно закрыть задачу по новому тарифу,
         * указанному в поле нового тарифа (перед кнопкой "Закрыть задачу по ХХ USD")
         */
        if (isset($rt['close_by_rate'])) {
            $p_cost = $rt['close_by_rate'];
        } elseif (isset($rt['id']) AND $rt['id'] AND !isset($params['c_value'])) {
            /*
             * Если в request существует ID проекта,
             * то получаем тариф из настроек проекта
             */
            $pt = Projects::findOne(['id' => $rt['id']]);
            $p_cost = $pt['rate']['value'];
//            d::tdfa('По проекту');
        } elseif (isset($params['c_value'])) {
            // Получаем тариф проекта на момент закрытия задачи
            $p_cost = $params['c_value'];
//            d::tdfa('Из общих настроек');
        } else {
            // Получаем общие настройки "Настройки WebMaster"
            $ws = WebmasterSettings::findOne(['code' => 'statistics']);
            // Получаем тариф проекта
            $p_cost = $ws->settings['rate']['cost'];
//            d::tdfa('Из общих настроек');
        }

        $result = [];

        /*
         * Если нужно получить стоимость рабочего времени по курсу доллара,
         * который был зафиксирован при закрытии задачи, то сработает IF,
         * а если нужно получить стоимость рабочего времени
         * по текущему курсу системы (по курсу сегодняшнего дня), то сработает ELSE
         */
        if (isset($params['rate'])) {
            $course = $params['rate'];
        } else {
//            $course = Yii::$app->rbc->curse;
            $course = Yii::$app->ws->getRate('state_course');
            if(Yii::$app->request->isAjax){
                if(isset($rt['state_course']) AND $rt['state_course'] != $course){
                    $course = $rt['state_course'];
                }
            }
        }

        /*
         * Получаем стоимость работы, по текущему тарифу.
         * Сначала получаем цену одной секунды,
         * полученную цену одной секунды умножаем на секунд работы,
         * получим стоимость общего числа секунд.
         */
        $wh_cost = (($p_cost) / 60 / 60) * (int)$sum_sec;

        /*
         * Полученную сумму стоимости
         * умножаем на текущий курс валюты
         *
         * т.е. сначала получили стоимость работы по тарифу
         * потом полученную сумму умножили на текущий курс валюты
         */
        if (is_array($course) AND isset($course['curse'])) {
            $cost = round($course['curse'] * $wh_cost, 2);
        } else {
            $cost = round($course * $wh_cost, 2);
        }


        if (!count($params)) {
            $result = $cost;
        } else {
            $result['time'] = SNumberHelper::whSecToHMS($sum_sec)['his'];
            $result['rate'] = $course;
            $result['cost'] = $cost;
        }

        return $result;

    }

} //Class
