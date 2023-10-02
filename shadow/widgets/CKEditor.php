<?php
namespace shadow\widgets;

use backend\controllers\MainController as d;
use shadow\assets\CKEditorAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;

/**
 * Class CKEditor
 * @package shadow\widgets
 * для обновление кеша у все надо написать например CKEDITOR.timestamp='ABCD';
 */
class CKEditor extends InputWidget
{
    public $content = '';
    /**
     * @var \yii\db\ActiveRecord  the data model that this widget is associated with.
     */
    public $model;
    /** @var array */
    public $editorOptions = [];
    /**
     * @var array Настройки редактора, по умолчанию стоит вместо <p></p> ставить </br>
     */
    protected $default_options = [
        'enterMode' => 2,
    ];

    public function run()
    {
        CKEditorAsset::register($this->view);
        $id = $this->id;

        if($this->hasModel()&&$this->model->hasAttribute($this->attribute)) {
            $html = Html::activeTextarea($this->model, $this->attribute, $this->options);
            $id = Html::getInputId($this->model, $this->attribute);
        } else {
            $html = Html::textarea($this->name, $this->value, $this->options);
        }
        $this->editorOptions = ArrayHelper::merge($this->default_options, $this->editorOptions);

        /**
         * Чтобы запустить не ограниченное количество экземпляров CKEditor на странице,
         * нужно для поля textarea в inputOptions задать уникальный ID для текущего textarea 'id' => 'uniqid',
         * и точ такой же ID задать для ckeditor - editorOptions[ 'options' => [ 'id' => 'uniqid' ] ]
         * Пример:
         * $form->field($model, 'field_name', ['inputOptions' => [
         *     'id' => 'uniqid',
         * ]])->widget(CKEditor::className(), [
         * 'editorOptions' => [
         * 'multiple' => true,
         * 'options' => [ 'id' => 'uniqid' ],
         * ...
         */
        if(isset($this->editorOptions['options']['id'])){
            $id = $this->editorOptions['options']['id'];
        }
        $editorOptions = Json::encode($this->editorOptions);
        $this->view->registerJs(
            <<<JS
CKEDITOR.replace( '{$id}',{$editorOptions});
// instinct.ckEditorWidget.registerOnChangeHandler('{$id}');
JS
        );
        return $html;
    }



    public function run_old()
    {
        CKEditorAsset::register($this->view);
        $id = $this->id;
        if ($this->hasModel() && $this->hasAttribute()) {
            $html = Html::activeTextarea($this->model, $this->attribute, $this->options);
            $id = Html::getInputId($this->model, $this->attribute);
        } else {
            $html = Html::textarea($this->name, $this->value, $this->options);
        }
        $this->editorOptions = ArrayHelper::merge($this->default_options, $this->editorOptions);
        $editorOptions = Json::encode($this->editorOptions);
        $this->content = htmlspecialchars($this->content);
        $this->view->registerJs(
            <<<JS
CKEDITOR.replace( '{$id}',{$editorOptions});
var editor = CKEDITOR.instances['{$id}'];
editor.setData("{$this->content}");
// instinct.ckEditorWidget.registerOnChangeHandler('{$id}');
JS
        );
        return $html;
    }
    protected function hasAttribute()
    {
        /**@var $ml \shadow\multilingual\behaviors\MultilingualBehavior*/
        return ($this->model->hasAttribute($this->attribute)
            ||
            (($ml = $this->model->getBehavior('ml')) && $ml->hasLangAttribute($this->attribute))
        );
    }
}