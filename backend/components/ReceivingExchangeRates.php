<?php
/**
 * Created by PhpStorm.
 * User: Сергей
 * Date: 01.11.2019
 * Time: 12:33
 */

namespace backend\components;

use backend\controllers\MainController as d;
use backend\models\WebmasterSettings;
use Yii;
use yii\base\Exception;

class ReceivingExchangeRates
{
//    const url = 'http://cbrates.rbc.ru/tsv/';
    const url = 'http://cbrates.rbc.ru/tsv/';
    const file = '.tsv';
    private $date = 0;
    public $curse = 0;
    public $rate = [
        'curse'=>0,
    ];
    private $state_course = 0;
    public function __construct($date = null){
        if ($date == null){
            $date = time();
        }
        $this->date = $date;
        $this->state_course = Yii::$app->ws->getRate('state_course');

//        $this->curse = $this->curs(840)['curs'];
        $this->rate['curse'] = $this->getRate();
        if(!isset($this->rate['error'])){
            if(is_array($this->rate) AND isset($this->rate['curse']['curse'])){
                $this->curse = $this->rate['curse']['curse'];
            }else{
                $this->curse = $this->rate['curse'];
            }
        }
    }

    /*
     * Получаем курс валют сбербанка
     */
    public function getRate(){

        $cache = Yii::$app->cache;
        // Время жизни кэша 12 часов
        $sec = 43200;

        // DEBUG ===
        $cache->delete('rate');
        $sec = 1;
        // /DEBUG ===

        if($rate = $cache->get('rate')){
            $this->rate = $rate;
        }else{
            $xml_file = 'http://www.cbr.ru/scripts/XML_daily.asp';

            if ($xml_file) {

                $languages = @simplexml_load_file($xml_file);
                $curse_rub = 0;
//                d::pri($languages);
                if(count($languages) AND isset($languages->Valute)){
                    // Перебираем валюты
                    foreach ($languages->Valute as $lang) {
                        // Тип валюты (Доллар США)
                        if ($lang["ID"] == 'R01235') {
                            // Стоимость текущей валюты в рублях
                            $curse_rub = round(str_replace(',','.',$lang->Value), 2);
                        }
                    }
                }

                if ($this->state_course > 0) {
                    $this->rate['curse'] = $this->state_course;
                } else {
                    $this->rate['curse'] = $curse_rub;
                }

            } else $this->rate['error'] = 'Не удалось получить курс из url: '.$xml_file;
            $cache->set('rate', $this->rate, $sec);
        }

        return $this->rate;
    }


    public function getRate2(){
        $xml_file = 'http://www.cbr.ru/scripts/XML_daily.asp';
        $context = $this;

        if ($xml_file) {
            $languages = simplexml_load_file($xml_file);

            $curse_rub = '';
            // Перебираем валюты
            foreach ($languages->Valute as $lang) {
                // Тип валюты (Доллар США)
                if ($lang["ID"] == 'R01235') {
                    // Стоимость текущей валюты в рублях
                    $curse_rub = round(str_replace(',','.',$lang->Value), 2);
                }
            }
            if($curse_rub != ''){
                $context->rate['curse'] = $curse_rub;
            }

            if ($this->state_course > 0) {
                $context->rate['curse'] = $this->state_course;
            }


        }else $context->rate['error'] = 'Не удалось получить курс из url: '.$xml_file;

        return $context->rate;
    }

    public function testGetRate(){
//        $curse_rub = '';
//
//        $xml_file = 'http://www.cbr.ru/scripts/XML_daily2.asp';
//
//        if (file_exists($xml_file)) {
//            $xml = simplexml_load_file($xml_file);
//
//            print_r($xml);
//        } else {
//            exit('Не удалось открыть файл: '.$xml_file);
//        }
//
//        return;
//
//
//
//        // Перебираем валюты
//        foreach ($languages->Valute as $lang) {
//            // Тип валюты (Доллар США)
//            if ($lang["ID"] == 'R01235') {
//                // Стоимость текущей валюты в рублях
//                $curse_rub = round(str_replace(',','.',$lang->Value), 2);
//            }
//        }
//
//        d::pe($curse_rub);
    }




















    /**
     * Получаем курс RBC - http://cbrates.rbc.ru/tsv/
     * @param $currency_code
     * @return array
     */

    public function curs($currency_code){
        $url = self::url;
        $return = [];
        $return['curs'] = 0;
        try{
            if (!is_numeric($currency_code)){
                throw new \Exception('Передан неверный код валюты');
            }
            $url .= $currency_code . '/';
            if ($this -> date <= 0){
                throw new \Exception('Передана неверная дата');
            }
            $url .= date('Y/m/d', $this -> date);
            $url .= self::file;

            $page = file_get_contents($url);

            $return['curs'] = $this -> parse($page);
        }
        catch (\Exception $e) {
            $return['error'] = 'Не удалось получить курс валюты. '.$e -> getMessage();
        }
        return $return;
    }
    private function parse($file){
        if (empty($file)){
            throw new \Exception('Возможно указан неверный код валюты, также возможно на указанную дату еще не установлен курс валюты, либо сервер "cbrates.rbc.ru" недоступен.');
        }
        $curs = explode("\t", $file);
        if (!empty($curs[1])){
            $arr_course = explode('.',$curs[1]);
            return $arr_course[0].'.'.substr($arr_course[1], 0, -2);
        }
        else{
            throw new \Exception('Сервер не выдал результатов по данной валюте на указнную дату');
        }
    }

}//Class