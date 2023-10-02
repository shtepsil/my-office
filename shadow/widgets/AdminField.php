<?php
/**
 * Created by PhpStorm.
 * Project: yii2-cms
 * User: lxShaDoWxl
 * Date: 27.04.15
 * Time: 17:41
 */

namespace shadow\widgets;


use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\InputWidget;

class AdminField extends InputWidget {
	/**
	 * @var string Название функции например
	 */
	public $field = 'textInput';
	public $value = '';
	public $name;
	public $inputOptions = array();
	public function run()
	{
		$default = array(
			'class' => 'form-control'
		);
		$this->inputOptions = ArrayHelper::merge($this->inputOptions, $default);
		switch($this->field){
			case 'textInput':
				$result=Html::textInput($this->name,$this->value,$this->inputOptions);
				break;
			default:
				$result=Html::textInput($this->name,$this->value,$this->inputOptions);
				break;
		}
		return $result;
	}
}