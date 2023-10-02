<?php
/**
 * Created by PhpStorm.
 * User: Сергей
 * Date: 27.07.2020
 * Time: 20:13
 */

namespace common\components;

use shadow\helpers\SArrayHelper;
use yii\web\Controller;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use Yii;

class Debugger extends Controller
{
    // Для ajax ответов
    public static function res($btn = false, $res_class = 'res'){
        $html = '<div style="font-size:18px;">';

        if($btn !== false){
            $html .= '<button type="button" name="'.$btn.'" class="btn btn-primary btn-xs" style="position:relative;"><img src="/images/animate/loading.gif" class="loading" style="position: absolute;top: -2px;left: -40px;display:none;" />Нажать</button><br><br>';
        }

        $html .= '<div class="' . $res_class . '">result</div></div>';
        return $html;
    }

    // Проверка на Json
    public static function isJson($string) {
        return ((is_string($string) &&
            (is_object(json_decode($string)) ||
                is_array(json_decode($string))))) ? true : false;
    }

    // Делаем строку из массива/объекта
    public static function toString($data){
        $str = '';
        $i = 0;
        if(is_array($data) OR is_object($data)){
            foreach($data as $key=>$value){
                if(is_array($value) OR is_object($value)){
                    $str .= '<br>' .
                        '<span style=\'color: red;\'>'.$key.'</span>'.
                        '<span style=\'font-weight:bold;\'>==</span><span style=\'color: blue;\'>'.
                        self::toString($value).'</span>'.' ';
                }else {
                    $str .=
                        (($i == 0) ? '>' : '') .
                        '<span style=\'color: red;\'>'.$key.'</span>'.
                        '=><span style=\'color: blue;\'>'.$value.'</span>'.', ';
                }
                $i++;
            }
        }else $str = '<span style=\'color: blue;\'>'.$data.'</span>';
        return $str.'<br>';
    }

    // Ответ Ajax запроса
    public static function echoAjax($data){
//        header('Accept: application/json');
        header("Content-type: application/json");
        echo json_encode($data, 256);
        exit();
    }

    public static function ajax($str = '', $key_response = 'response'){
        if(self::isJson($str)){
            $str = json_decode($str, true);
        }
        if(is_object($str)){
            $str = (array)$str;
        }
        if(is_array($str) AND count($str)){
            $new_str = [];
            foreach($str as $key => $s){
                $array = self::objectToArray($s);
                $new_str[$key] = $array;
            }
            $str = $new_str;
        }
        self::echoAjax([$key_response => $str]);
    }

    public static function clearEscapeU0000($str = ''){
        if($str == '') return $str;
        $str = preg_replace('/(\*)/i', '', $str);
        return trim($str);
    }

    public static function objectToArray($obj){
        $result = [];
        if(is_object($obj)){
            $array = (array)$obj;
            if(count($array)){
                foreach($array as $ar_key => $ar_val){
                    if(is_object($ar_val)){
                        $ar_val = self::objectToArray($ar_val);
                    }
                    $ar_key = self::clearEscapeU0000($ar_key);
                    $result[$ar_key] = $ar_val;
                }
            }
        }
        if(is_array($obj) OR is_string($obj) OR is_numeric($obj) OR is_bool($obj)){
            $result = $obj;
        }
        return $result;
    }

    /**
     * @param array $data
     * @return array
     */
    public static function serializeToArray($data = []){
        $array = [];
        if(count($data)){
            foreach($data as $d){
                if($d['value'] == 'on'){
                    $array[$d['name']] = true;
                }else{
                    $array[$d['name']] = $d['value'];
                }
            }
        }
        return $array;
    }

    public static function isLocal()
    {
        $local = false;
        if(!preg_match('~.kz~', $_SERVER['HTTP_HOST'])){
            $local = true;
        }
        return $local;
    }

    public static function forBy($data, $prop, $show = false)
    {
        $result = [];
        if(count($data) AND $prop){
            foreach($data as $item){
                if(is_object($data)){
                    $result[] = $item->$prop;
                }
                if(is_array($data)){
                    $result[] = $item[$prop];
                }
            }
        }
        if($show){
            if($show == 'pri') {
                static::pri($result);
            }
            if($show == 'pre') {
                static::pre($result);
            }
            return false;
        }
        return $result;
    }

    public static function post()
    {
        $post = Yii::$app->request->post();
//        self::ajax($post);
        if(isset($post['inputs'])){
            $post_inputs = self::serializeToArray($post['inputs']);
            $post = SArrayHelper::merge($post, $post_inputs);
        }
        return $post;
    }

    public static function date($timestamp)
    {
        $result = '';
        if(is_numeric($timestamp)){
            $result = date('Y-m-d H:i:s', $timestamp);
        }
        return $result;
    }

    /*
     * Получить debug.txt
    */
    public static function getDebug($file = 'debug.txt') {
        if(file_exists($file)){
            $debug = file_get_contents($file);
        }else{
            $debug = 'Файл ' . $file . ' не существует';
        }
        return $debug;
    }

    /*
     * Получить debug.txt
    */
    public static function clearDebug($file = 'debug.txt') {
        if(file_exists($file)){
            file_put_contents($file, '');
            $result = $file.' очищен';
        }else{
            $result = 'Файл ' . $file . ' не существует';
        }
        return $result;
    }

}