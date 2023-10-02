<?php
/**
 * Created by PhpStorm.
 * Project: morkovka
 * User: lxShaDoWxl
 * Date: 05.08.15
 * Time: 17:25
 */
namespace shadow\assets;

use yii\web\AssetBundle;

class Select2Assets extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/select2/dist';
    /**
     * @inheritdoc
     */
    public $js = [
        'js/select2.full.js',
    ];
    public $js_min=[
        'js/select2.full.min.js',
    ];
    public $css =[
        'css/select2.css',
    ];
    public function init()
    {
        if (!YII_DEBUG) {
            $this->js = $this->js_min;
        }
    }
    public $depends = [
        'backend\assets\AdminAsset',
    ];
}