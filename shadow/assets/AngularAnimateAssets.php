<?php
/**
 * Project: yii2-cms
 * User: lxShaDoWxl
 * Date: 29.04.15
 * Time: 16:12
 */

namespace shadow\assets;


use yii\web\AssetBundle;

class AngularAnimateAssets extends AssetBundle {
    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/angular-animate';
    /**
     * @inheritdoc
     */
    public $js = [
        'angular-animate.js',
    ];

    public $js_min=[
        'angular-animate.min.js'
    ];
    /*public $css = [
        ''
    ];
    public $css_min = [
        ''
    ];*/
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
        'shadow\assets\AngularAssets',
    ];
}
 