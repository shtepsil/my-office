<?php

namespace backend\actions;

use backend\models\WorkingHours;
use backend\controllers\MainController as d;
use Yii;

class Debug
{

    public $post = [];

    public function run()
    {
        $this->post = d::post();
//        d::ajax($this->post);
        switch($this->post['type']){
            case 'btn_push':
                $this->test();
                break;
            case 'get_file_debug':
                $this->getFileDebug();
                break;
            case 'clear_file_debug':
                $this->setFileDebug();
                break;
            default:
                $this->test();
                d::ajax('Debug->run()->switch:default');
        }
    }

    /*
     * Кнопка "Нажать"
     */
    public function test()
    {

        // Объект даты
        $date = new \DateTime();

        // Установка часовго пояса
//        $date->setTimezone(new \DateTimeZone('Europe/Moscow'));
//        $date->setTimezone(new \DateTimeZone('Asia/Almaty'));
//        $date->setTimezone(new \DateTimeZone('Asia/Omsk'));

        // Получение объекта timeZone
        $timeZone = $date->getTimezone();

        // Выборка времени работы
        $wh = WorkingHours::find()->orderBy('id DESC')->one();

        $cur_time = $date->format('Y-m-d H:i:s');

        // Настройка объекта DateTime на нужно время (из БД которое получил)
        $wh_time = $date->setTimestamp($wh->created_at);

        $result = [
            'id' => $wh->id,
            // Время
            'wh_date' => $wh_time->format('Y-m-d H:i:s'),
            ' --- ' => ' --- ',
            'TimeZone' => $timeZone->getName(),
            'current_time' => $cur_time,
        ];
        d::ajax($result);
        d::ajax('Debug->test(admin)');
    }

    public function getFileDebug()
    {
        $result = d::getDebug($this->post['file_debug_name']);
        d::ajax($result);
    }

    public function setFileDebug($data = '')
    {
        $result = d::clearDebug($this->post['file_debug_name']);
        d::ajax($result);
    }

}//Class
