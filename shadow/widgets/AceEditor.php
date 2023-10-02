<?php
/**
 * Created by PhpStorm.
 * User: lxShaDoWxl
 * Date: 24.04.15
 * Time: 11:32
 */

namespace shadow\widgets;


use shadow\assets\AceAssets;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;

class AceEditor extends InputWidget
{

	/** @var string */
	public $mode = 'php';
	/** @var string */
	public $theme = 'github';
	/** @var bool Static syntax highlight */
	public $editable = true;
	/** @var bool */
	public $autocompletion = false;
	/** @var array */
	public $extensions = [];
	/** @var array */
	public $aceOptions = [
		"maxLines" => 100,
		"minLines" => 5,
	];
	/** @var array Div options */
	public $containerOptions = [
		'style' => 'width: 100%; min-height: 400px'
	];
	/**
	 * @return string
	 */
	public function run()
	{
		if(!$this->editable) {
			$this->extensions[] = 'static_highlight';
		}
		if($this->autocompletion) {
			$this->extensions[] = 'language_tools';
		}
		AceAssets::register($this->view, $this->extensions);
		return $this->editable ? $this->editable() : $this->readable();
	}
	protected function editable()
	{
		$id = $this->id;
		$autocompletion = $this->autocompletion ? 'true' : 'false';
		if($this->autocompletion) {
			$this->aceOptions['enableBasicAutocompletion'] = true;
			$this->aceOptions['enableSnippets'] = true;
			$this->aceOptions['enableLiveAutocompletion'] = false;
		}
		$aceOptions = Json::encode($this->aceOptions);
		$editor_var = '_aceeditor_'.$id;
		$textarea_var = '_acetextarea_'.$id;
		$textarea_id = $id . '_acetextarea';
		$this->view->registerJs(
			<<<JS
(function(){
    if({$autocompletion}) {
        ace.require("ace/ext/language_tools");
    }
    var {$editor_var} = ace.edit("{$id}");
    {$editor_var}.setTheme("ace/theme/{$this->theme}");
    {$editor_var}.getSession().setMode("ace/mode/{$this->mode}");
    {$editor_var}.setOptions({$aceOptions});
    var {$textarea_var} = $('#{$textarea_id}').hide();
            {$editor_var}.getSession().setValue({$textarea_var}.val());
            {$editor_var}.getSession().on('change', function(){
                {$textarea_var}.val({$editor_var}.getSession().getValue());
            });
})();
JS
		);
		Html::addCssStyle($this->options, 'display: none');
		$this->containerOptions['id'] = $id;
		$this->options['id'] = $textarea_id;
		$html = Html::tag('div', '', $this->containerOptions);
		if($this->hasModel()&&$this->model->hasAttribute($this->attribute)) {
			$html .= Html::activeTextarea($this->model, $this->attribute, $this->options);
		} else {
			$html .= Html::textarea($this->name, $this->value, $this->options);
		}
		return $html;
	}
	/**
	 * @return string
	 */
	protected function readable()
	{
		$this->options['id'] = $this->id;
		$this->view->registerJs(
			<<<JS
(function(){
    var _highlight = ace.require("ace/ext/static_highlight");
    _highlight($('#{$this->id}')[0], {
        mode: "ace/mode/{$this->mode}",
        theme: "ace/theme/{$this->theme}",
        startLineNumber: 1,
        showGutter: true,
        trim: true
    });
})();
JS
		);
		return Html::tag('pre', htmlspecialchars($this->value), $this->options);
	}
}