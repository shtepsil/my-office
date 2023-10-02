<?php
/**
 * Created by PhpStorm.
 * Project: yii2-cms
 * User: lxShaDoWxl
 * Date: 28.04.15
 * Time: 16:54
 */

namespace shadow\assets;


use yii\web\AssetBundle;

class AngularUploaderAssets extends AssetBundle {
    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/ng-file-upload';
    /**
     * @inheritdoc
     */
    public $js = [
        'ng-file-upload-shim.js',
        'ng-file-upload.js'
    ];
    public $js_min=[
        'ng-file-upload-shim.min.js',
        'ng-file-upload.min.js',
    ];
    public function init()
    {
        if (!YII_DEBUG) {
            $this->js = $this->js_min;
        }
    }
    public $depends = [
        'shadow\assets\AngularAssets',
//        'shadow\assets\AngularLoadBarAssets',
    ];
}