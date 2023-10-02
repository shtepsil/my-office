<?php
/**
 * @var array $form_action
 * @var \yii\db\ActiveRecord | \yii\base\Model $item
 * @var \yii\web\View $this
 * @var $context \shadow\widgets\AdminForm
 */
//@var \shadow\widgets\AdminForm $this->context
use shadow\helpers\SArrayHelper;
use shadow\widgets\AdminActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$context = $this->context;
$switcher = false;
?>
<div id="pageEdit">
    <?php $form = AdminActiveForm::begin([
        'action' => isset($form_action) ? $form_action : '',
        'enableAjaxValidation' => false,
//        'layout' => 'horizontal',
        'options' => ['enctype' => 'multipart/form-data'],
        'fieldConfig' => [
            'options' => ['class' => 'form-group simple'],
//            'template' => "{label}<div class=\"col-md-10\">{input}\n{error}</div>",
//            'labelOptions' => ['class' => 'col-md-2 control-label'],
        ],
    ]); ?>
    <?= Html::hiddenInput('id', $item->id) ?>
    <div style="position: relative;">
        <div class="form-actions panel-heading" style="padding-left: 0px;">
            <?= Html::submitButton('<i class="fa fa-retweet"></i> Сохранить', ['class' => 'btn-success btn-save btn', 'data-hotkeys' => 'ctrl+s', 'name' => 'continue']) ?>
            <?php if (isset($cancel)): ?>
                &nbsp;&nbsp;
                <button name="commit" type="submit" class="btn-save-close btn-default hidden-xs btn" onclick="$(this).val(1)">
                    <i class="fa fa-check"></i> Сохранить и Закрыть
                </button>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <a href="<?= Url::to(isset($cancel) ? $cancel : '') ?>" class="btn btn-close btn-sm btn-outline">
                    <i class="fa fa-ban"></i>
                    <span class="hidden-xs hidden-sm">Отмена</span>
                </a>
            <?php endif; ?>
            <?php if (method_exists($item, 'addButtons')): ?>
                <?= $item->addButtons() ?>
            <?php endif; ?>
        </div>
        <?php if (isset($groups) && $groups): ?>
            <?php
            $i_li = 0;
            $this->registerJs(<<<JS
function params_unserialize(p) {
    p = p.substr(1);
    var ret = {},
        seg = p.replace(/^\?/, '').split('&'),
        len = seg.length, i = 0, s;
    for (; i < len; i++) {
        if (!seg[i]) {
            continue;
        }
        s = seg[i].split('=');
        ret[s[0]] = s[1];
    }
    return ret;
}
if (location.hash !== '') {
    var hash = location.hash;
    if(hash){
        var hashs = params_unserialize(hash);
        if (hashs['tab']) {
            var obj=$('a[href="#' + hashs['tab'] + '"]');
            if($(obj).is(':visible')){
                $(obj).tab('show');
            }
        }
    }
}
$('a[data-toggle="tab"]','.tabs-admin').on('shown.bs.tab', function (e) {
    if (location.hash) {
        //location.hash = 'tab=' + $(e.target).attr('href').substr(1);
        var hash = location.hash;
        var hashs = params_unserialize(hash);
        hashs['tab'] = $(e.target).attr('href').substr(1);
        location.hash= $.param(hashs);
    } else {
        location.hash = 'tab=' + $(e.target).attr('href').substr(1);
    }
});
JS
            );
            ?>
            <?= Html::ul($groups,
                [
                    'class' => 'nav nav-tabs tabs-generated tabs-admin',
                    'item' => function ($item, $index) use (&$i_li) {
                        $context = '';
                        $options = ['id' => "page-$index-panel-li"];
                        $class_span = null;
                        if (isset($item['icon']) && $item['icon']) {
                            $context = "<i class=\"fa fa-{$item['icon']}\"></i> ";
                            $class_span = 'hidden-xs hidden-sm';
                        }
                        $context .= Html::tag('span', isset($item['title']) ? $item['title'] : $index, ['class' => $class_span]);
                        if ($i_li == 0) {
                            $options['class'] = 'active';
                        }
                        if (isset($item['options'])) {
                            $options = SArrayHelper::mergeOptions($options, $item['options']);
                        }
                        $result = Html::tag('li', Html::a($context, "#page-$index-panel", ['data-toggle' => 'tab']), $options);
                        $i_li++;
                        return $result;
                    }
                ]) ?>
        <?php endif ?>
    </div>
    <div class="panel">
        <?php if (isset($fields) && $fields): ?>
            <div class="panel-heading">
                <? foreach ($fields as $key_field => $config_field): ?>
                    <?= $context->getRow($form, $key_field, $config_field) ?>
                <? endforeach; ?>
            </div>
            <hr class="no-margin-vr" />
        <?php endif; ?>

        <?php if (isset($groups) && $groups): ?>
            <div class="tab-content no-padding-vr">
                <?php $i = 0; ?>
                <?php foreach ($groups as $key_group => $group): ?>
                    <?php
                    $class = 'fade';
                    $fields = [];
                    $relation = false;
                    if ($i == 0) {
                        $class = 'active';
                    }
                    $i++;
                    if (isset($group['fields'])) {
                        $fields = $group['fields'];
                    }
                    if (isset($group['relation'])) {
                        $relation = $group['relation'];
                    }
                    if (isset($group['groups'])) {
                        $groups_fields = $group['groups'];
                    }
                    ?>
                    <div class="tab-pane <?= $class ?>" id="page-<?= $key_group ?>-panel">
                        <div class="panel-body">
                            <? if (isset($groups_fields)): ?>
                                <?
                                $i_li_sub = 0;
                                $i_sub = 0;
                                ?>
                                <?= Html::ul($groups_fields,
                                    [
                                        'class' => 'nav nav-tabs tabs-generated',
                                        'item' => function ($item, $index) use (&$i_li_sub) {
                                            $context = '';
                                            $options = ['id' => "page-$index-panel-li-sub"];
                                            if (isset($item['icon']) && $item['icon']) {
                                                $context = "<i class=\"fa fa-{$item['icon']}\"></i> ";
                                            }
                                            $context .= Html::tag('span', isset($item['title']) ? $item['title'] : $index, ['class' => 'hidden-xs hidden-sm']);
                                            if ($i_li_sub == 0) {
                                                $options['class'] = 'active';
                                            }
                                            if (isset($item['options'])) {
                                                $options = SArrayHelper::mergeOptions($options, $item['options']);
                                            }
                                            $result = Html::tag('li', Html::a($context, "#page-$index-panel-sub", ['data-toggle' => 'tab']), $options);
                                            $i_li_sub++;
                                            return $result;
                                        }
                                    ]) ?>
                                <? foreach ($groups_fields as $key_group_sub => $group_sub): ?>
                                    <?php
                                    $class = 'fade';
                                    $fields = [];
                                    if ($i == 0) {
                                        $class = 'active';
                                    }
                                    $i_sub++;
                                    if (isset($group_sub['fields'])) {
                                        $fields = $group_sub['fields'];
                                    }
                                    if (isset($group_sub['relation'])) {
                                        $relation = $group_sub['relation'];
                                    }
                                    ?>
                                    <div class="tab-content no-padding-vr">
                                        <div class="tab-pane <?= $class ?>" id="page-<?= $key_group_sub ?>-panel-sub">
                                            <div class="panel-body">
                                                <?php if (isset($fields) && $fields): ?>
                                                    <? foreach ($fields as $key_field => $config_field): ?>
                                                        <?= $context->getRow($form, $key_field, $config_field) ?>
                                                    <? endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <? endforeach; ?>
                            <? endif; ?>
                            <?php if (isset($fields) && $fields): ?>
                                <?
                                $i_checkbox = 0;
                                $fields_content = '';
                                $fields_checkbox_content = '';
                                ?>
                                <? foreach ($fields as $key_field => $config_field): ?>
                                    <?
                                    if(isset($config_field['type'])&&$config_field['type']=='checkbox'){
                                        $i_checkbox++;
                                        $switcher = true;
                                        $fields_checkbox_content .= $context->getRow($form, $key_field, $config_field);
                                    }else{
                                        if($fields_checkbox_content){
                                            if ($i_checkbox==1) {
                                                echo $fields_content;
                                                echo $fields_checkbox_content;
                                            }else{
                                                echo '<div class="row list_switcher">';
                                                echo $fields_checkbox_content;
                                                echo '</div>';
                                                echo $fields_content;
                                            }
                                            $fields_content = '';
                                            $fields_checkbox_content = '';
                                            $i_checkbox = 0;
                                        }
                                        $fields_content .= $context->getRow($form, $key_field, $config_field);

                                    }
                                    ?>
                                <? endforeach; ?>
                                <?=$fields_content?>
                                <?= $fields_checkbox_content;?>
                            <?php endif; ?>
                            <?php if (isset($group['meta']) && $group['meta'] == true): ?>
                                <?= $this->render('meta', $_params_) ?>
                            <?php endif; ?>
                            <?php if (isset($group['render']) && isset($group['render']['view'])): ?>
                                <?php $render = $group['render'] ?>
                                <?php if (!isset($render['data'])): ?>
                                    <?= Yii::$app->controller->renderPartial($render['view'], $_params_ + ['form' => $form]) ?>
                                <?php else: ?>
                                    <?= Yii::$app->controller->renderPartial($render['view'], $render['data']) ?>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if (isset($relation) && $relation): ?>
                                <?= $context->getRelation($relation) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <? endforeach; ?>
            </div>
        <?php endif ?>
    </div>
    <?php AdminActiveForm::end(); ?>
</div>
<?
if($switcher){
	$this->registerJs(<<<JS
$('input[type=checkbox]','.list_switcher').switcher({
    // theme: 'square',
    on_state_content: '<span class="fa fa-check"></span>',
    off_state_content: '<span class="fa fa-times"></span>'
});	
JS
);
}
$this->registerJs(<<<JS
function scrolledIntoActions()
{
    var docViewTop = $(window).scrollTop();
    var docViewBottom = docViewTop + $(window).height();
    var element = $('.form-actions');
    if (((action_form_bottom <= docViewBottom) && (action_form_top >= docViewTop))) {
        if ($(element).hasClass('fixed-panel-actions')) {
            $(element).removeClass('fixed-panel-actions');
            if ($('#waypoint_form_actions').length) {
                $('#waypoint_form_actions').addClass('hidden');
            }
        }
    } else {
        if (!$(element).hasClass('fixed-panel-actions')) {
            $(element).addClass('fixed-panel-actions');
            if ($('#waypoint_form_actions').length) {
                $('#waypoint_form_actions').removeClass('hidden');
            } else {
                $(element).after($('<div id="waypoint_form_actions"></div>'));
                $('#waypoint_form_actions').height($(element).outerHeight(true))

            }
        }

    }
}

var action_form_top = $('.form-actions').offset().top;
var action_form_bottom = (action_form_top + $('.form-actions').outerHeight(true)) ;
$(window).on('scroll', function () {
    scrolledIntoActions();
});
scrolledIntoActions();


JS
)
?>