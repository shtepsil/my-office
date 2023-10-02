<?php
namespace shadow;

use common\components\Debugger as d;
use Imagine\Image\Box;
use Imagine\Image\Point;
use shadow\helpers\SFileHelper;
use shadow\plugins\imagine\Image;
use yii\helpers\FileHelper;

/**
 * SResizeImg trait. Used in SActiveRecord to override @see SActiveRecord::findOne()
 * Modify SActiveRecord query for multilingual support
 * @package shadow
 */
trait SResizeImg
{
    /**
     * @var array Массив размеров изображений
     */
//    public static $_size_img_a = [
//        'mini'=>[
//            'width'=>150,
//            'height'=>150
//        ]
//    ];

    /**
     * Массив размеров изображений
     * для resize img
     * @var array
     */
    public static $_size_img_a = [
        'item_1220'=>[
            'width' => 1220,
            'height' => 1220,
            'watermark'=>true
        ],
        'item_920'=>[
            'width' => 920,
            'height' => 920,
            'watermark'=>true
        ],
        'item_320'=>[
            'width' => 320,
            'height' => 320,
            'watermark'=>true
        ],
        'mini_list_recipe' => [
            'width' => 400,
            'height' => 400,
            'watermark'=>true
        ],
        'mini_list' => [
            'width' => 300,
            'height' => 300,
            'watermark'=>true
        ],
        'mini' => [
            'width' => 240,
            'height' => 190,
            'watermark'=>true
        ],
        'small' => [
            'width' => 352,
            'height' => 235,
            'watermark'=>true
        ],
        'big' => [
            'width' => 1056,
            'height' => 705
        ],
    ];

    /**
     * @var bool|string
     */
    public $dir_name = false;
    /**
     * @var string
     */
    public $_urlField = 'url';
    public function resizeImg($size_type = 'mini',$field=false,$opposite_size=false)
    {
        $result = '';
        if($field!==false){
            $this->_urlField = $field;
        }
        $url = $this->getAttribute($this->_urlField);
        $url_path = \Yii::getAlias('@web_frontend') . $url;
        if (!is_file(\Yii::getAlias('@web_frontend') . $url)) {
            return '';
        }
        if (is_array($size_type)) {
            $height = $size_type['height'];
            $width = $size_type['width'];
            $add_watermark = isset($size_type['watermark']) ? $size_type['watermark'] : false;
        } elseif (isset(self::$_size_img_a) && isset(self::$_size_img_a[$size_type])) {
            $height = self::$_size_img_a[$size_type]['height'];
            $width = self::$_size_img_a[$size_type]['width'];
            $add_watermark = isset(self::$_size_img_a[$size_type]['watermark']) ? self::$_size_img_a[$size_type]['watermark'] : false;
        } else {
            return $url;
        }
        $dir_name = $this->dir_name($url);
        $save_path = \Yii::getAlias('@web_frontend/uploads/cache/' . $dir_name);
        if (!file_exists($save_path)) {
            FileHelper::createDirectory($save_path);
        }
        $path_info = pathinfo($url);

        // =================================================
        /*
         * Если изображения создаются по ширине,
         * то в имя файла изображения запишем требуемую ширину
         * и полученную высоту из текущей ширины
         */
        if($opposite_size AND $opposite_size == 'by_width'){

            $image_prop = Image::getInstance($url);
            $sizeH = $image_prop['height'];
            $sizeW = $image_prop['width'];
            $prop = $width/$sizeW;
            $sizeWi = floor($sizeH*$prop);

            $new_file_name = $width . 'x' . $sizeWi . '_' . $this->getPrimaryKey() .'.' . $path_info['extension'];
        }elseif($opposite_size AND $opposite_size == 'by_height'){
        /*
         * Если изображения создаются по высоте,
         * то в имя файла изображения запишем требуемую высоту
         * и полученную ширину из текущей высоты
         */
            $image_prop = Image::getInstance($url);
            $sizeH = $image_prop['height'];
            $sizeW = $image_prop['width'];

            $prop = $height/$sizeH;
            $sizeWi = floor($sizeW*$prop);

            $new_file_name = $width . 'x' . $sizeWi . '_' . $this->getPrimaryKey() .'.' . $path_info['extension'];
        }else{
            $new_file_name = $width . 'x' . $height . '_' . $this->getPrimaryKey() .'.' . $path_info['extension'];
        }
        // =================================================

        $file_path = $save_path . '/' . $new_file_name;
        $result = '/uploads/cache/' . $dir_name . '/' . $new_file_name;

        if (!is_file($file_path)) {
            // =================================================
            if(!$opposite_size){
                $img_res = Image::thumbnail($url_path, $width, $height,'inset');
            }else{
                if($opposite_size AND $opposite_size == 'by_width'){
                    $img_res = Image::thumbnailPropByWidth($url_path, $width);
                }elseif($opposite_size AND $opposite_size == 'by_height'){
                    $img_res = Image::thumbnailPropByHeight($url_path, $height);
                }else{
                    $img_res = Image::thumbnail($url_path, $width, $height,'inset');
                }
            }
            // =================================================
            $extension = (SFileHelper::getMimeTypeByExtension(\Yii::getAlias('@web_frontend') . $url));
            $options = [];
            if(!$extension){
                $options['format'] = 'jpeg';
            }
            $img_res->save($file_path,$options);
            if($add_watermark&&isset($this->watermark_path)){
                $this->addWaterMark($file_path, \Yii::getAlias('@web_frontend') . $this->watermark_path);
            }
        }
        if (!is_file($file_path)) {
            $result = '';
        }
        return $result;
    }
    private function addWaterMark($file_path,$watermark_path){
        if(is_file($file_path)&&is_file($watermark_path)){
            $img_create = Image::getImagine();
            $img = $img_create->open($file_path);
            $size = $img->getSize();
            $w = $size->getWidth();
            $h = $size->getHeight();
            $img_watermark = $img_create->open($watermark_path);
            $size_watermark = $img_watermark->getSize();
            $w_watermark = $size_watermark->getWidth();
            $h_watermark = $size_watermark->getHeight();
            if ($w_watermark >= $w || $h_watermark >= $h) {
                $new_w = $w_watermark;
                $new_h = $h_watermark;
                if($w_watermark>$w){
                    $new_w = $w_watermark-(($w_watermark - $w) + 20);
                    $koe = $w_watermark / $new_w;
                    $new_h = ceil($new_h/$koe);
                }elseif ($w_watermark==$w){
                    $new_w = $w_watermark - 20;
                    $koe = $w_watermark / $new_w;
                    $new_h = ceil($new_h/$koe);
                }
                if($new_h > $h){
                    $new_h_temp = $new_h-(($new_h - $h) + 20);
                    $koe = $new_h / $new_h_temp;
                    $new_w = ceil($new_w/$koe);
                    $new_h = $new_h_temp;
                }elseif ($new_h == $h){
                    $new_h_temp = $new_h - 20;
                    $koe = $new_h / $new_h_temp;
                    $new_w = ceil($new_w/$koe);
                    $new_h = $new_h_temp;
                }
                $img_watermark->resize(new Box($new_w, $new_h));
                $x = ($w - $new_w) / 2;
                $y = ($h - $new_h) / 2;
            } else {
                $x = ($w - $w_watermark) / 2;
                $y = ($h - $h_watermark) / 2;
            }
            $img->paste($img_watermark, new Point($x, $y));
            $extension = (SFileHelper::getMimeTypeByExtension($file_path));
            $options = [];
            if(!$extension){
                $options['format'] = 'jpeg';
            }
            $img->save($file_path,$options);
        }
    }
    protected function dir_name($url)
    {
        if ($this->dir_name === false) {
            if (preg_match('|/([^/]*)/[^/]*\..*$|', $url, $matches)) {
                $this->dir_name = $matches[1];
            }
        }
        return $this->dir_name;
    }
}