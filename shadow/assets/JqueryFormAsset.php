<?php

namespace shadow\assets;


use yii\web\AssetBundle;

/**
 * Class JqueryFormAsset
 * @package shadow\assets
 */
class JqueryFormAsset extends AssetBundle {
	public $sourcePath = '@bower/jquery-form';
	public $js = [
		'jquery.form.js',
	];
	public $depends = [
		'yii\web\JqueryAsset',
	];
}