<?php
/**
 * Created by PhpStorm.
 * Project: morkovka
 * User: lxShaDoWxl
 * Date: 20.08.15
 * Time: 15:53
 */
namespace shadow\assets;

use yii\web\AssetBundle;

class SAuthChoiceAsset extends AssetBundle
{
    public $sourcePath = '@yii/authclient/assets';
    public $js = [
        'authchoice.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}