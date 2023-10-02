<?php

/**
 * Класс для работы с датами
 */

namespace shadow\helpers;

use common\components\Debugger as d;

class SDateHelper
{

    public static $time = 0;

    /**
     * Пока что можем принимать либо цифры time, либо строку флага.
     * Можно ещё доработать чтобы определять строку даты.
     * Проверку сделать вместо is_numeric($data)
     * @param $data
     * @return array|mixed
     */
    public static function getDayOfWeek($data = 'all', $flag = false)
    {
        // Если $time не задано из вне.
        if(self::$time === 0){
            // Зададим time тут
            $str_time = time();
        }else{
            // Если $time было задано из вне.
            $str_time = self::$time;
        }
        // Если $data не одно из строк массива и это число
        if(!in_array($data, ['all', 'name', 'number']) AND is_numeric($data)){
            // Зададим time метода из $data
            $str_time = $data;
        }
        $day_eng = date ('l', $str_time);

        switch($day_eng){
            case 'Monday':
                $day = [
                    'name' => 'Понедельник',
                    'number' => 1
                ];
                break;
            case 'Tuesday':
                $day = [
                    'name' => 'Вторник',
                    'number' => 2
                ];
                break;
            case 'Wednesday':
                $day = [
                    'name' => 'Среда',
                    'number' => 3
                ];
                break;
            case 'Thursday':
                $day = [
                    'name' => 'Четверг',
                    'number' => 4
                ];
                break;
            case 'Friday':
                $day = [
                    'name' => 'Пятница',
                    'number' => 5
                ];
                break;
            case 'Saturday':
                $day = [
                    'name' => 'Суббота',
                    'number' => 6
                ];
                break;
            default:
                $day = [
                    'name' => 'Воскресенье',
                    'number' => 7
                ];
        }

        if($data == 'name'){ $day = $day['name']; }
        if($data == 'number'){ $day = $day['number']; }

        if($flag) d::ajax($day);
        return $day;
    }

    public static function getMonths($lang = 'ru')
    {
        $months = [
            '01' => 'Январь',
            '02' => 'Февраль',
            '03' => 'Март',
            '04' => 'Апрель',
            '05' => 'Май',
            '06' => 'Июнь',
            '07' => 'Июль',
            '08' => 'Август',
            '09' => 'Сентябрь',
            '10' => 'Октябрь',
            '11' => 'Ноябрь',
            '12' => 'Декабрь'
        ];
        if ($lang == 'en'){
            $months = [
                '01' => 'January',
                '02' => 'February',
                '03' => 'March',
                '04' => 'April',
                '05' => 'May',
                '06' => 'June',
                '07' => 'July',
                '08' => 'August',
                '09' => 'September',
                '10' => 'October',
                '11' => 'November',
                '12' => 'December'
            ];
        }
        return $months;
    }

    public static function getMonthRu($month_number = '01')
    {
        $months = self::getMonths();
        return (isset($months[$month_number]) ? $months[$month_number] : '');
    }

    public static function getMonthEn($month_number = '01')
    {
        $months = self::getMonths('en');
        return (isset($months[$month_number]) ? $months[$month_number] : '');
    }

    /**
     * Проверка выходных дней
     * @return bool
     */
    public static function weekendCheck()
    {
        $result = false;
        $current_day = self::getDayOfWeek('number');
        if(in_array($current_day, [6, 7])){
            $result = true;
        }
        return $result;
    }

    /**
     * Проверка выходных дней
     * @return bool
     */
    public static function currentDayStart()
    {
        return strtotime( date('Y-m-d' . ' 00:00:00') );
    }

}//Class