<?php
/**
 * Created by PhpStorm.
 * Project: yii2-cms
 * User: lxShaDoWxl
 * Date: 29.04.15
 * Time: 15:56
 */

namespace shadow\assets;


use yii\web\AssetBundle;

class AngularLoadBarAssets extends AssetBundle {
    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/angular-loading-bar/build';
    /**
     * @inheritdoc
     */
    public $js = [
        'loading-bar.js',
    ];
    public $css = [
        'loading-bar.css'
    ];
    public $js_min=[
        'loading-bar.min.js'
    ];
    public $css_min = [
        'loading-bar.min.css'
    ];
    public function init()
    {
        if (!YII_DEBUG) {
            if (isset($this->js_min)) {
                $this->js = $this->js_min;
            }
            if (isset($this->css_min)) {
                $this->css = $this->css_min;
            }
        }
    }
    public $depends = [
        'shadow\assets\AngularAnimateAssets',
    ];
}