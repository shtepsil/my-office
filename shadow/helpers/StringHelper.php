<?php
namespace shadow\helpers;

use backend\controllers\MainController as d;

class StringHelper extends \yii\helpers\StringHelper
{
    /**
     * Возвращает форматированую страку из RU в EN чаще всего нужно для генерации ссылки
     * @param string $string строка которую форматировать
     * @return string отформатированная строка
     */
    public static function TranslitRuToEn($string)
    {
        $replace = [
            'ый' => 'iy',
            'Ый' => 'iy',
            'ыЙ' => 'iy',
            'ЫЙ' => 'iy',
            ' ' => '_',
            'а' => 'a',
            'А' => 'a',
            'б' => 'b',
            'Б' => 'b',
            'в' => 'v',
            'В' => 'v',
            'г' => 'g',
            'Г' => 'g',
            'д' => 'd',
            'Д' => 'd',
            'е' => 'e',
            'Е' => 'e',
            'Ё' => 'e',
            'ё' => 'e',
            'ж' => 'zh',
            'Ж' => 'zh',
            'з' => 'z',
            'З' => 'z',
            'и' => 'i',
            'И' => 'i',
            'й' => 'y',
            'Й' => 'y',
            'к' => 'k',
            'К' => 'k',
            'л' => 'l',
            'Л' => 'l',
            'м' => 'm',
            'М' => 'm',
            'н' => 'n',
            'Н' => 'n',
            'о' => 'o',
            'О' => 'o',
            'п' => 'p',
            'П' => 'p',
            'р' => 'r',
            'Р' => 'r',
            'с' => 's',
            'С' => 's',
            'т' => 't',
            'Т' => 't',
            'у' => 'u',
            'У' => 'u',
            'ф' => 'f',
            'Ф' => 'f',
            'х' => 'h',
            'Х' => 'h',
            'ц' => 'c',
            'Ц' => 'c',
            'ч' => 'ch',
            'Ч' => 'ch',
            'ш' => 'sh',
            'Ш' => 'sh',
            'щ' => 'sch',
            'Щ' => 'sch',
            'ъ' => '',
            'Ъ' => '',
            'ы' => 'y',
            'Ы' => 'y',
            'ь' => '',
            'Ь' => '',
            'э' => 'e',
            'Э' => 'e',
            'ю' => 'yu',
            'Ю' => 'yu',
            'я' => 'ya',
            'Я' => 'ya'
        ];
        $string = iconv('UTF-8', 'UTF-8//IGNORE', strtr($string, $replace));
        $string = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $string);
        $string = strtolower($string);
        $string = preg_replace(
            [
                "/-{2,30}/",
                "/_{2,30}/",
            ],
            [
                '-',
                '_'
            ],
            $string
        );
        return $string;
    }
    public static function translit($string)
    {
        $replace = [
            'а' => 'a',
            'А' => 'A',
            'В' => 'B',
            'е' => 'e',
            'Е' => 'E',
            'м' => 'm',
            'М' => 'M',
            'о' => 'o',
            'О' => 'O',
            'с' => 'c',
            'С' => 'C',
            'Т' => 'T',
            'х' => 'x',
            'Х' => 'X',
        ];
        $string = iconv('UTF-8', 'UTF-8//IGNORE', strtr($string, $replace));
        return $string;
    }
    public static function mb_ucfirst($string, $enc = 'UTF-8')
    {
        if (!function_exists('mb_ucfirst')) {
            return mb_strtoupper(mb_substr($string, 0, 1, $enc), $enc) . mb_substr($string, 1, mb_strlen($string, $enc), $enc);
        } else {
            return mb_ucfirst($string, $enc);
        }
    }
    /**
     * Создание уникального кода из чисел
     * @param $n int число которое переводим в буквенное значение
     * @return string
     */
    public static function num2alpha($n)
    {
        $r = '';
        for ($i = 1; $n >= 0 && $i < 10; $i++) {
            $r = chr(0x41 + ($n % pow(26, $i) / pow(26, $i - 1))) . $r;
            $n -= pow(26, $i);
        }
        return $r;
    }
    /**
     * Обратный перевод строки в число
     * @param $a string Строка которую перевести в число
     * @return int|number
     */
    public static function alpha2num($a)
    {
        $r = 0;
        $l = strlen($a);
        for ($i = 0; $i < $l; $i++) {
            $r += pow(26, $i) * (ord($a[$l - $i - 1]) - 0x40);
        }
        return $r - 1;
    }
    public static function id_youtube($url_video)
    {
        $url_video_query = parse_url($url_video,PHP_URL_QUERY);
        if ($url_video_query) {
            parse_str($url_video_query, $query_parts);
            if (isset($query_parts['v'])) {
                return $query_parts['v'];
            }
        }
        return '';

    }

    /**
     * Очищаем строку от HTML тегов и HTML сущностей
     * @param string $string
     * @return mixed|string
     */
    public static function clearHtmlString($string = ''){
        if($string != ''){
            // Удаляем все HTML теги
            $string = strip_tags($string);
            // Сначала все HTML сущности, которые делают пробел, заменяем на пробелыб
            $string = str_replace("&nbsp;",' ', $string);
            // Удаляем все HTML сущности, содержащие в себе только числа
            $string = preg_replace("|([&#0-9;]+?)|",'', $string);
            /*
             * Нужно получить все HTML сущности, которые должны быть заменены на символы.
             * Нужно создать отдельный метод, в котором будет происходить замена
             * всех необходимых HTML сущностей на соответствующие им символы.
             */
//            $string = preg_replace("|([&a-zA-Z;]+?)|",'', $string);
            
            // Обрезаем по краям строки все пробелы
            $string = trim($string);
        }
        return $string;
    }

    public static function parseTag($content,$tg)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');

        // @ - чтобы с svg не выдавало ошибку
        @$dom->loadHTML($content);

        $attr = array();
        foreach ($dom->getElementsByTagName($tg) as $tag) {
            foreach ($tag->attributes as $attribName => $attribNodeVal)
            {
                $attr[$attribName]=$tag->getAttribute($attribName);
            }
        }
        return $attr;
    }

    public static function sJsonToObject($params){

//        d::pe($params);

        if(is_array($params)){
            $field = $params['field'];
            $object = $params['object'];
            if(is_array($object->$field)){
                $params = $object;
            }
            if(d::isJson($object->$field)){
                $object->$field = json_decode($object->$field);
                $params = $object;
            }
        }else{
            $params = json_decode($params);
        }

        return $params;
    }

    public static function isJson($string) {
        return ((is_string($string) &&
            (is_object(json_decode($string)) ||
                is_array(json_decode($string))))) ? true : false;
    }

    public static function mb_lcfirst($str) {
        return mb_strtolower(mb_substr($str, 0, 1)) . mb_substr($str, 1);
    }

//    public static function mb_ucfirst($str) {
//        return mb_strtoupper(mb_substr($str, 0, 1)) . mb_substr($str, 1);
//    }

}// Class