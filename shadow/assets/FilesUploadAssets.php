<?php
/**
 * Project: yii2-cms
 * User: lxShaDoWxl
 * Date: 30.04.15
 * Time: 14:01
 */

namespace shadow\assets;


use yii\web\AssetBundle;

class FilesUploadAssets extends AssetBundle {
    /**
     * @inheritdoc
     */
    public $sourcePath = '@webroot/angular';
    /**
     * @inheritdoc
     */
    public $js = [
        'angular-file-upload/angular-file-upload.js',
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
 