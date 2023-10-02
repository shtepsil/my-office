<?php
/**
 * Created by PhpStorm.
 * Project: yii2-cms
 * User: viktor
 * Date: 20.04.15
 * Time: 16:13
 */

namespace shadow\assets;


use yii\web\AssetBundle;

class AdminActiveFormAsset extends AssetBundle {
	public $sourcePath = '@shadow/js_css_lib/form';
	public $js = [
		'ajax.activeForm.js',
	];
	public $depends = [
		'yii\web\YiiAsset',
		'shadow\assets\JqueryFormAsset'
	];
}