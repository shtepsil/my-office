<?php

namespace backendold\assets;

use backendold\controllers\MainController as d;
use yii\web\AssetBundle;
use Yii;
use yii\helpers\Json;
use yii\web\View;

/**
 * Main backend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/bootstrap.min.css',
        'css/jquery-ui.css',
        'css/jquery.jgrowl.css',

        // colorpicker-master
//        'vendors/bower_components/colorpicker-master/jquery.colorpicker.css',

        // Color-Picker-Plugin-jQuery-MiniColors
        'vendors/bower_components/Color-Picker-Plugin-jQuery-MiniColors/jquery.minicolors.css',

        // datetimepicker
//        'vendors/bower_components/datetimepicker-master/jquery.datetimepicker.css',

        'vendors/bower_components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css',
//        'css/dropzone.css',
        'css/pretty-print-json.css',
        'css/default.css',
        'css/debugs.css',
        'css/common.css',
        'css/style.css',
    ];
    public $js = [
//        'js/jquery-ui.min.js',
        'js/jquery-ui.js',
//        'https://code.jquery.com/ui/1.12.1/jquery-ui.js',
        'js/jquery.jgrowl.min.js',
//        simpleWeather JavaScript
        'vendors/bower_components/moment/min/moment.min.js',

        // colorpicker-master
//        'vendors/bower_components/colorpicker-master/jquery.colorpicker.js',
//        'vendors/bower_components/colorpicker-master/i18n/jquery.ui.colorpicker-nl.js',
//        'vendors/bower_components/colorpicker-master/parts/jquery.ui.colorpicker-rgbslider.js',
//        'vendors/bower_components/colorpicker-master/parts/jquery.ui.colorpicker-memory.js',

        // Color-Picker-Plugin-jQuery-MiniColors
        'vendors/bower_components/Color-Picker-Plugin-jQuery-MiniColors/jquery.minicolors.js',

        // datetimepicker
//        'vendors/bower_components/datetimepicker-master/build/jquery.datetimepicker.full.js',

        'vendors/bower_components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',
        'js/form-picker-data.js',
        'js/jquery-input-mask.js',
        'js/common.js',
        'js/plugins/jquery-color-picker.js',
        'js/plugins/jquery.maskedinput.js',
        'js/pretty-print-json.min.js',
        'js/debug.js',
        'js/scripts.js',
    ];
    public $jsOptions = [
        'position' => View::POS_HEAD
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',// сам скрипт фреймворка
        // подключает js скрипты ПОСЛЕ моих
//        'yii\bootstrap\BootstrapAsset',// зависимость от бутстрапа
        // подключает js скрипты ПЕРЕД моими
        'yii\bootstrap\BootstrapPluginAsset'
    ];

    public function init()
    {
        parent::init();
//        // resetting BootstrapAsset to not load own css files
//        \Yii::$app->assetManager->bundles['yii\\bootstrap\\BootstrapAsset'] = [
//            'css' => [],
//            'js' => []
//        ];

        /*
         * Берем данные из PHP
         * и присваиваем эти данные переменным JS
         * ======================================
         * передаем данные из PHP в JS
         * ---------------------------
         * Строка скрипта подключается полсе всех JS подключений
         * и эти переменные можно получить только внутри $(function){}
         */
//        d::pex(Yii::$app->params);
        Yii::$app->view->registerJs(
            "var active_status = " . Json::encode(Yii::$app->params['active_status']) . ";".
            "var active_colors = " . Json::encode(Yii::$app->params['active_colors']) . ";".
            "var relax_limit = " . Json::encode(Yii::$app->params['time_relax']['min']) . ";".
            "var task_view_empty = " . Json::encode(Yii::getAlias('@task_view_empty')) . ";".
            "var no_tasks_yet = " . Json::encode(Yii::getAlias('@no_tasks_yet')) . ";".
            "var no_tasks_yet_new = " . Json::encode(Yii::getAlias('@no_tasks_yet_new')) . ";".
            "var zero_time = " . Json::encode(Yii::getAlias('@zero_time')) . ";".
            "var zero_one = " . Json::encode(Yii::getAlias('@zero_one')) . ";".
            "var zero = " . Json::encode(Yii::getAlias('@zero')) . ";".
            "var zeroz = " . Json::encode(Yii::getAlias('@zero,')) . ";".
//            "var codesBP = " . Json::encode(Yii::$app->params['codesBP']) . ";".
//            "var codesG = " . Json::encode(Yii::$app->params['codesG']) . ";".
            "var common = " . Json::encode(Yii::getAlias('@common')) . ";".
            "var tr_empty = " . Json::encode(Yii::getAlias('@tr_empty')) . ";",
            View::POS_HEAD
        );
        /**
         * Простой JS скрипт подключаем перед закрывающим тегом </body>
         * чтобы переменные заданные из PHP могли попадать и в обычный JS
         */
        Yii::$app->view->registerJsFile(
            Yii::getAlias('@web').'/js/functions.js',
            ['position' => \yii\web\View::POS_END]
        );
    }
}
