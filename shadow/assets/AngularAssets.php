<?php
/**
 * Created by PhpStorm.
 * Project: yii2-cms
 * User: lxShaDoWxl
 * Date: 28.04.15
 * Time: 10:53
 */

namespace shadow\assets;


use yii\web\AssetBundle;

class AngularAssets extends AssetBundle {
	/**
	 * @inheritdoc
	 */
	public $sourcePath = '@bower/angular';
	/**
	 * @inheritdoc
	 */
	public $js = [
		'angular.js',
//		'http://nervgh.github.io/js/es5-shim.min.js',
//		'http://nervgh.github.io/js/es5-sham.min.js',
//		'http://code.angularjs.org/1.1.5/angular.min.js',
//		'https://ajax.googleapis.com/ajax/libs/angularjs/1.3.15/angular.min.js',
	];
	public $js_min=[
		'angular.min.js'
	];
	public function init()
	{
		if (!YII_DEBUG) {
			$this->js = $this->js_min;
		}
	}

}