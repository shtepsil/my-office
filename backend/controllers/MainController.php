<?php
namespace backend\controllers;

use common\components\Debugger;
use Yii;

class MainController extends Debugger {
    /*
     * Скрипты для разработки
     */
    public static function pri($arr){
        echo '<pre>';
        print_r($arr);
        echo '</pre>';
    }
    public static function pre($str){
        self::s();
        echo '<pre>';
        print_r($str);
        echo '</pre>';
        self::e();
    }

    public static function prebl($str){
        self::sbl();
        echo '<pre>';
        print_r($str);
        echo '</pre>';
        self::e();
    }

    public static function pretr($str){
        self::s_tr();
        echo '<pre>';
        print_r($str);
        echo '</pre>';
        self::e();
    }

    public static function prebr($str){
        self::s_br();
        echo '<pre>';
        print_r($str);
        echo '</pre>';
        self::e();
    }

    private static function s(){
        echo '<div
        style="
            position: fixed;
            top: 60px;
            left: 0px;
            padding: 15px;
            background-color: black;
            min-width: 265px;
            z-index: 999;
            color: white;
            overflow: auto;
        ">
        ';
    }

    private static function sbl(){
        echo '<div
          style="
            position: fixed;
            bottom: 5px;
            left: 0px;
            padding: 15px;
            background-color: black;
            min-width: 265px;
            z-index: 999;
            color: white;
            overflow: auto;
          ">
        ';
    }

    private static function s_tr(){
        echo '<div
          style="
            position: fixed;
            top: 60px;
            right: 0px;
            padding: 15px;
            background-color: black;
            min-width: 265px;
            z-index: 999;
            color: white;
            overflow: auto;
          ">
        ';
    }

    private static function s_br(){
        echo '<div
          style="
            position: fixed;
            bottom: 5px;
            right: 0px;
            padding: 15px;
            background-color: black;
            min-width: 265px;
            z-index: 999;
            color: white;
            overflow: auto;
          ">
        ';
    }

    private static function e(){
        echo '</div>';
    }

    /**
     * Получение расширения файла.
     *
     * @return string,
     */
    public static function getExtension($string){
        $revstr = strrev($string);

        $position = strpos($revstr, '.');
        $str_itog_rev = substr($revstr,0,$position);
        $str_itog = strrev($str_itog_rev);

        return $str_itog;
    }

    /*
     * Обезопасиваем данные
    */
    public static function secureEncode($data, $array = true) {

        // По умолчанию обрабатывается массив

        if(!$array) {

            // если нужно обработать строку
            $data = trim($data);
//            $data = htmlspecialchars($data, ENT_QUOTES);
            $data = htmlspecialchars($data, ENT_NOQUOTES);
            $data = str_replace('\\r\\n', '<br>', $data);
            $data = str_replace('\\r', '<br>', $data);
            $data = str_replace('\\n\\n', '<br><br>', $data);
            $data = str_replace('\\n\\n\\n', '<br><br><br>', $data);
            $data = str_replace('\\n', '<br>', $data);
            $data = stripslashes($data);
            $data = str_replace('&amp;#', '&#', $data);
            $data = str_replace('&amp;', '&', $data);
        }else{
            // если нужно обработать массив
            $response_array = array();
            foreach($data as $key=>$value){
                /*
                 * Если массив многомерный
                 * и в рекурсии вместо строки пришел массив
                 */
                if(is_array($value)) $response_array[$key] = self::secureEncode($value);
                else $response_array[$key] = self::secureEncode($value,false);
            }
            $data = $response_array;
        }

        return $data;
    }

    /*
     * Перевод языка онлайн
    */
    public static function translation($str, $lang_from, $lang_to) {

        $query_data = array(
            'client' => 'x',
            'q' => $str,
            'sl' => $lang_from,
            'tl' => $lang_to
        );
        $filename = 'http://translate.google.ru/translate_a/t';
        $options = array(
            'http' => array(
                'user_agent' => 'Mozilla/5.0 (Windows NT 6.0; rv:26.0) Gecko/20100101 Firefox/26.0',
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query($query_data)
            )
        );
        $context = stream_context_create($options);
        $response = file_get_contents($filename, false, $context);

        return $response;
    }

    public static function active($action){
        return (Yii::$app->request->pathInfo == $action);
    }

    /*
     * Получение ошибок из validation models
     * $model - модель валидатора
     * $arr_error - массив ошибок: $model->errors
    */
    public static function getErrors($model,$arr_error) {
        $error_string = '';
        foreach($arr_error as $key=>$val){
            $arr_labels = $model->attributeLabels();
            $error_string .= '<b>'.$arr_labels[$key].'</b> - '.$val[0].'<br>';
        }
        return $error_string;
    }

    /*
     * Возврат json строки
     * для отладки
    */
    public static function eje($arr) {
        echo json_encode($arr, 256);
        exit();
    }

    /*
     * Возврат json строки
     * для отладки
    */
    public static function pj($arr) {
        print_r(json_encode($arr, 256));
    }

    /*
     * Распечатка массива
     * для отладки в Ajax
    */
    public static function pe($arr) {
        echo '<br>';
        echo self::toString($arr);
        exit();
    }

    /*
     * Распечатка массива
     * для отладки в Ajax
    */
    public static function pex($arr) {
        echo '<pre>';
        print_r($arr);
        exit('</pre>');
    }

    /*
     * Распечатка массива
     * для отладки в Ajax
    */
    public static function jpe($arr) {
        $arr = json_encode($arr, 256);
        print_r($arr);
        exit();
    }

    /*
     * Запись результатов в файл debug.txt
     * для отладки в Ajax
    */
    public static function arrToStr($data) {
        $str = '';
        $i = 0;
        if(is_array($data) OR is_object($data)){
            foreach($data as $key=>$value){
                if(is_array($value) OR is_object($value)){
                    $str .= $key.'=='.self::arrToStr($value).' ';
                }else {
                    $str .= (($i == 0) ? '>' : '').$key.'=>'.$value.', ';
                }
                $i++;
            }
        }else $str = $data;

        return $str;
    }

    /*
     * Запись результатов в файл debug.txt
     * для отладки в Ajax
    */
    public static function tdArrStr($data) {
        $str = self::arrToStr($data);
        file_put_contents('debug.txt',$str);
    }

    /*
     * Запись результатов в файл debug.txt
     * для отладки в Ajax
    */
    public static function td($data) {
        file_put_contents('debug.txt',$data);
    }

    /*
     * Запись результатов в файл debug.txt
     * для отладки в Ajax
    */
    public static function tdfa($data) {
        file_put_contents('debug.txt',PHP_EOL.$data,FILE_APPEND);
    }

    /*
     * Запись результатов в файл debug.txt
     * для отладки в Ajax
    */
    public static function jtd($data) {
        $data = json_encode($data, 256);
        file_put_contents('debug.txt',$data);
    }

    /*
     * Запись результатов в файл debug.txt
     * для отладки в Ajax
    */
    public static function jtdfa($data) {
        $data = json_encode($data, 256);
        file_put_contents('debug.txt',PHP_EOL.$data,FILE_APPEND);
    }

    /*
     * Преобразвание массива в строку
     * для отладки в Ajax
    */
    public static function strpe($arr,$field=false) {
        $str = '<br>';
        foreach($arr as $key=>$value){
            if($field) $str .= $key.'=>'.$value[$field].'<br>';
            else $str .= $key.'=>'.$value.'<br>';
        }
        print_r($str);
        exit();
    }

    // Получение сообщений из общего текстового массива
    public static function getMessage($name, $aReplace=null)
    {
        global $MESS;
        if(isset($MESS[$name])){
            $s = $MESS[$name];
            if($aReplace!==null && is_array($aReplace))
                foreach($aReplace as $search=>$replace)
                    $s = str_replace($search, $replace, $s);
            return $s;
        }else return $name;
    }

    // Из Json в Array
    public static function jsonToArray($data){
        $array = [];
        if(self::isJson($data)){
            $data = json_decode($data);
        }
        foreach($data as $key=>$value){
            if(is_object($value)) $array[$key] = self::jsonToArray($value);
            else $array[$key] = $value;
        }
        return $array;
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
                        '==<span style=\'color: blue;\'>'.
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

    /*
     * Обрабатываем имя файла
     * Удаляем не нужные знаки
     */
    public static function clearStr($str){
        $from = array('%5B','%5D');
        $to = array('[',']');

        return str_replace($from, $to, $str);
    }

    /*
     * Преобразование объекта выборки в массив
     */
    public static function objectToArray($obj){
        $arr = [];
        foreach($obj as $key=>$item){
            if(is_object($item)) $arr[$key] = self::objectToArray($item);
            else $arr[$key] = $item;
        }
        return $arr;
    }

    // меняем форматы дат
    public static function changeDate($date,$to = '',$format=false){
        if($to === 'rus'){
            // Берем только день
            if($format == 'd') $d = date( 'd', strtotime($date));
            // Берем только месяц
            if($format == 'm') $d = date( 'F', strtotime($date));
            // Берем только день и месяц
            if($format == 'dm') $d = date( 'd F', strtotime($date));
            // Берем день, месяц и год
            else  $d = date( 'd F Y', strtotime($date));

            $d = explode(' ',$d);
            $months = [
                'January'=>'Января',
                'February'=>'Февраля',
                'March'=>'Марта',
                'April'=>'Апреля',
                'May'=>'Мая',
                'June'=>'Июня',
                'July'=>'Июля',
                'August'=>'Августа',
                'September'=>'Сентября',
                'October'=>'Октября',
                'November'=>'Ноября',
                'December'=>'Декабря'
            ];
            foreach($months as $key=>$val){
                if($key == $d[1])$d[1] = $val;
            }
            return implode(' ',$d);
        }elseif($to === 'number'){
            $d = explode(' ',$date);
            $months_num = [
                'Января'=>'01',
                'Февраля'=>'02',
                'Марта'=>'03',
                'Апреля'=>'04',
                'Мая'=>'05',
                'Июня'=>'06',
                'Июля'=>'07',
                'Августа'=>'08',
                'Сентября'=>'09',
                'Октября'=>'10',
                'Ноября'=>'11',
                'Декабря'=>'12'
            ];
            foreach($months_num as $key=>$val){
                if($key == $d[1])$d[1] = $val;
            }
            return date( 'Y-m-d', strtotime(implode('-',$d)));
        }elseif($to === 'date'){
            return self::changeDate(date( 'Y-m-d', $date),'rus');
        }
    }

    public static function success($message='Выполнено',$header='Успешно'){
        return [
            'status'=>200,
            'header'=>$header,
            'message'=>$message,
            'type_message'=>'success'
        ];
    }
    public static function warning($message='Не выполнено',$header='Внимание'){
        return [
            'status'=>407,
            'header'=>$header,
            'message'=>$message,
            'type_message'=>'warning'
        ];
    }
    public static function error($message='Не выполнено',$header='Ошибка'){
        return [
            'status'=>407,
            'header'=>$header,
            'message'=>$message,
            'type_message'=>'error'
        ];
    }

    /**
     * Получение части строки URL
     * по символу $haracter.
     * $string - Весь url без "http:"
     * $code - символ, по которому делаем ориентир в строке
     * start - Получаем всю строку, до ПЕРВОГО символа $haracter
     * last - Получаем всю строку, до ПОСЛЕДНЕГО символа $haracter
     * all_from_first - ОТ первого символа $haracter - берем всю строку до конца
     *
     * @return string
     */
    public static function getPartStrByCharacter($url,$haracter,$code = false){

        switch($code){
            case 'start':
                $pos = strpos($url, $haracter);
                if($pos != '') $str = substr($url, 0, $pos);
                else $str = $url;
                break;
            case 'last':
                $pos = mb_strripos($url, $haracter);
                if($pos != '') $str = substr($url, 0, $pos);
                else $str = $url;
                break;
            case 'all_from_first':
                $pos = strpos($url, $haracter);
                if($pos != '') $str = substr($url, $pos+1);
                else $str = $url;
                break;
            default:
                $revstr = strrev($url);
                $position = strpos($revstr, $haracter);
                $str_itog_rev = substr($revstr,0,$position);
                $str = strrev($str_itog_rev);
        }

        return $str;

    }// function getPartStrByCharacter(...)




}