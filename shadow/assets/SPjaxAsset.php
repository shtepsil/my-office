<?php
/**
 * Created by PhpStorm.
 * Project: morkovka
 * User: lxShaDoWxl
 * Date: 28.08.15
 * Time: 17:52
 */
namespace shadow\assets;

use yii\web\AssetBundle;

class SPjaxAsset extends AssetBundle
{
    public $sourcePath = '@shadow/js_css_lib/pjax';
    public $js = [
        'jquery.pjax.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
    ];

}