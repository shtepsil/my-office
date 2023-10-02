<?php
/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var \shadow\sgii\model\Generator $generator
 */

echo $form->field($generator, 'tableName');
//echo $form->field($generator, 'tablePrefix');
echo $form->field($generator, 'modelClass');
echo $form->field($generator, 'ns');
echo $form->field($generator, 'nameModule');
echo $form->field($generator, 'baseClass');
echo $form->field($generator, 'db');
echo $form->field($generator, 'generateRelations')->dropDownList([
    \yii\gii\generators\model\Generator::RELATIONS_NONE => Yii::t('yii', 'No relations'),
    \yii\gii\generators\model\Generator::RELATIONS_ALL => Yii::t('yii', 'All relations'),
    \yii\gii\generators\model\Generator::RELATIONS_ALL_INVERSE => Yii::t('yii', 'All relations with inverse'),
]);
echo $form->field($generator, 'generateLabelsFromComments')->checkbox();
echo $form->field($generator, 'generateModelClass')->checkbox();
echo $form->field($generator, 'enableI18N')->checkbox();
echo $form->field($generator, 'messageCategory');
echo $form->field($generator, 'multilangs')->dropDownList([
    \shadow\sgii\model\Generator::MULTILANGS_NONE => 'Не создавать',
    \shadow\sgii\model\Generator::MULTILANGS_YES => 'Создать на основании базы',
    \shadow\sgii\model\Generator::MULTILANGS_FORCE => 'Создать код и таблицы',
]);
$this->registerJs(<<<JS

// model generator: hide class name inputs when table name input contains *
$('#s-gii-generator #generator-tablename').change(function () {
    var show = ($(this).val().indexOf('*') === -1);
    $('.field-generator-modelclass').toggle(show);
    if ($('#generator-generatequery').is(':checked')) {
        $('.field-generator-queryclass').toggle(show);
    }
});
// model generator: translate table name to model class
$('#s-gii-generator #generator-tablename').on('blur', function () {
    var tableName = $(this).val();
    var tablePrefix = $(this).attr('table_prefix') || '';
    if (tablePrefix.length) {
        // if starts with prefix
        if (tableName.slice(0, tablePrefix.length) === tablePrefix) {
            // remove prefix
            tableName = tableName.slice(tablePrefix.length);
        }
    }
    if ($('#generator-modelclass').val() === '' && tableName && tableName.indexOf('*') === -1) {
        var modelClass = '';
        $.each(tableName.split('_'), function () {
            if (this.length > 0)
                modelClass += this.substring(0, 1).toUpperCase() + this.substring(1);
        });
        $('#generator-modelclass').val(modelClass).blur();
    }
});

// model generator: translate model class to query class
$('#s-gii-generator #generator-modelclass').on('blur', function () {
    var modelClass = $(this).val();
    if (modelClass !== '') {
        var queryClass = $('#generator-queryclass').val();
        if (queryClass === '') {
            queryClass = modelClass + 'Query';
            $('#generator-queryclass').val(queryClass);
        }
    }
});

// model generator: synchronize query namespace with model namespace
$('#s-gii-generator #generator-ns').on('blur', function () {
    var stickyValue = $('#s-gii-generator .field-generator-queryns .sticky-value');
    var input = $('#s-gii-generator #generator-queryns');
    if (stickyValue.is(':visible') || !input.is(':visible')) {
        var ns = $(this).val();
        stickyValue.html(ns);
        input.val(ns);
    }
});
JS
);