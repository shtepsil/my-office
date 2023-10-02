<?php
namespace shadow\helpers;

use backend\models\Projects;
use backend\controllers\MainController as d;
use backend\models\WebmasterSettings;
use Yii;
use shadow\helpers\StringHelper as SH;

class SNumberHelper
{
    public static function discount($price, $discount)
    {
        $discount = preg_replace("#([^-\d%]*)#u", '', $discount);
        if ($discount) {
            if (preg_match("#\%$#u", $discount)) {
                $discount = preg_replace("#\%$#u", '', $discount);
                $price = round(((double)$price * (double)$discount) / 100);
            } else {
                $price = $discount;
            }
        } else {
            $price = 0;
        }
        return $price;
    }

    /**
     * Конвертируем секунды в Часы Минуты Секунды - HMS
     * @param bool $sec
     * @return array|int
     */
    public static function whSecToHMS($sec = false){

        $time = [];

        if($sec !== false){

            $hours = floor($sec / 3600);
            $s_hours = (3600 * $hours);
            $pre_minutes_seconds = $sec - $s_hours;
            $minutes = floor($pre_minutes_seconds / 60);
            $s_minutes = (60 * $minutes);
            $seconds = ($pre_minutes_seconds - $s_minutes);

            if($hours == '0') $hours = '00';
            elseif($hours < 10) $hours = '0'.$hours;

            if($minutes == '0') $minutes = '00';
            elseif($minutes < 10) $minutes = '0'.$minutes;

            if($seconds == '0' OR $seconds < 0) $seconds = '00';
            elseif($seconds < 10) $seconds = '0'.$seconds;

            $time['h'] = $hours;
            $time['m'] = $minutes;
            $time['s'] = $seconds;
            $time['his'] = $hours.':'.$minutes.':'.$seconds;
            $time['h_i_s'] = $hours.' : '.$minutes.' : '.$seconds;

        }else return 0;

//        d::pex($time);

        return $time;

    }

    /**
     * @param $sec
     * @param bool $params
     * @return array|float
     */
    public static function getCost($sum_sec, $params = [], $flag = false)
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
             * Если request существует ID проекта,
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
                if($rt['state_course'] != $course){
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
            $result['time'] = self::whSecToHMS($sum_sec)['his'];
            $result['rate'] = $course;
            $result['cost'] = $cost;
        }

        return $result;

    }

}//Class