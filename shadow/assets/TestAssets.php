<?php
/**
 * Created by PhpStorm.
 * Project: yii2-cms
 * User: lxShaDoWxl
 * Date: 28.04.15
 * Time: 14:56
 */

namespace shadow\assets;


use yii\web\AssetBundle;

class TestAssets extends AssetBundle {
    /**
     * @inheritdoc
     */
    public $sourcePath = '@webroot/angular';
    /**
     * @inheritdoc
     */
    public $js = [
        '/angular-file-upload/angular-file-upload.js',
        'uploadCtrl.js'
    ];
//    public $js_min=[
//        '/min/dropzone.min.js'
//    ];
    public function init()
    {
//        if (!YII_DEBUG) {
//            $this->js = $this->js_min;
//        }
    }
    public $depends = [
//        'shadow\assets\AngularUploaderAssets',
        'shadow\assets\AngularAssets',
        'shadow\assets\AngularLoadBarAssets'
    ];
}