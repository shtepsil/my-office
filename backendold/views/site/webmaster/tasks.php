<?php

/**
 * Информация по строкам working_hours:
 * 1-в работе/2-не оплачен/3-ожидание оплаты/4-оплачено
 * ----------------------------------------------------
 */

use backendold\controllers\MainController as d;
use yii\helpers\Html;
use backendold\components\Bootstrap as bp;
use yii\helpers\Url;
use backendold\helpers\SNumberHelper;
use backendold\models\WorkingHours;
use yii\bootstrap\ActiveForm;
use shadow\widgets\CKEditor;

$this->title = 'Задачи: ' . $project->name;

$get = Yii::$app->request->get();
$params = Yii::$app->params;
$ws = Yii::$app->ws;

//d::pri(SNumberHelper::getCost(66438));


//$wh_ids = explode(',','688,689,690,691,692');
//
//$whs_current = WorkingHours::find()->where(['IN','id', $wh_ids ])->sum('working_hours');
//
//d::pri($whs_current);

//d::pri($total_nopaid);

$filter = [
    'all' => 'Все',
    'inwork' => 'В работе',
    'new' => 'Новые задачи',
    'closed' => 'Закрытые',
    'waitpay' => 'Ожидание оплаты',
    'nopaid' => 'Не оплачено',
    'paid' => 'Оплачено',
];

// Получить стоимость времени вручную
//$secc = '324';// Секунд работы
//$rrate = '73.55';// Курс валюты
//$cc_value = '50';// Тариф в за час
//$cc = SNumberHelper::getCost($secc,['rate'=>$rrate,'c_value'=>$cc_value]);
//d::pri($cc);

//d::pe();

//d::pri(Yii::$app->rbc);

// tss - tasks
?>
    <div class="wrap tss" data-project-id="<?= Yii::$app->request->get('id') ?>">
        <div class="float-total-amount"><span>
            <?= number_format($inwork_total_amount, 2, '.', ' ') ?>
        </span>
            .р</div>

        <div class="text-center h3 header">
            <?= Html::encode($this->title) ?>
        </div>

        <? if (Yii::$app->rbc->curse == 0): ?>
            <div class="alert alert-danger text-center" role="alert" style="display: block;">
                Внимание!<br>
                Курс доллара не получен!
            </div>
        <? endif ?>

        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="main-current-curse h3 text-center" data-course="<?= Yii::$app->rbc->curse ?>">
                    Текущий курс: <b>
                        <font color="red"><?= '$' . Yii::$app->rbc->curse ?></font>
                    </b>
                </div>
                <div class="main-current-rate h5 text-center" data-rate="<?= $project['rate']['value'] ?>">
                    Тариф проекта: <b>
                        <font color="red">
                            <?= $project['rate']['value'] ?> $
                        </font> за час
                    </b>
                </div>

                <span id="p-top"></span>

                <div class="wh-total-amount text-center" style="color: #818182;">
                    Общая сумма не закрытых задач "в работе":<br>
                    По текущему курсу (<span style="color:red"><?= '$' . Yii::$app->rbc->curse ?></span>):<br>
                    <span style="color:red" class="total--amount">
                    <span>
                        <?= number_format($inwork_total_amount, 2, '.', ' ') ?>
                    </span>
                    р.</span>
                </div>
                <br><br>

                <div class="row">
                    <div class="col-md-3">
                        <a class="btn btn-primary btn-sm all-projects-link" href="<?= Url::to(['/projects-list']) ?>">
                            &lt;&lt;&lt; К списку проектов
                        </a><br>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="exampleFormControlSelect1">Сортировать задачи:</label>
                            <select class="form-control sort-tasks" id="exampleFormControlSelect1"
                                    data-task-id="<?= $get['id'] ?>">
                                <? foreach ($filter as $k => $f): ?>
                                    <option value="<?= $k ?>" <?= (isset($get['sort']) and $get['sort'] == $k) ? 'selected' : '' ?>>
                                        <?= $f ?></option>
                                <? endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- ================================================ -->
                <div class="report">

                    <? if (!isset($report)): ?>
                        <p>Отчета нет</p>
                    <? else: ?>
                        <div class="r-label label1" style="float: left;">
                            <span>Открыть отчет</span>
                        </div>
                        <br>

                        <div class="r-info dn">

                            <div class="copy-report" data-copy="<?= (isset($copy)) ? $copy : '' ?>">
                                <?= $project->name ?>
                                <br>
                                <br>

                                <?= $report ?><br><br>
                            </div>
                            =====================<br>

                            <br>
                            <div class="r-label" style="float: left;">
                                <span>Закртыть отчет</span>
                            </div>
                            <br>
                            <br>

                        </div>
                    <? endif ?>
                    <br>
                </div>
                <!-- ================================================ -->

                <table class="table head-info" data-url="ajax/change-status-payment" data-type-method="post">
                    <thead>
                    <th>Проект</th>
                    <th class="no-paid">
                        Не оплачено<br>
                        <span class="notices">Выполенно, но оплата не запрошена</span>
                    </th>
                    <th class="waiting-payment">
                        Ожидание оплаты<br>
                        <span class="notices">Оплата запрошена</span>
                    </th>
                    <th>Оплачено</th>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="list-group-item-danger">
                            <a href="//<?= $project->name ?>" target="_blank"><?= $project->name ?></a>
                        </td>
                        <td class="list-group-item-warning t-nopaid" data-wh-ids="<?= $total['no_paid']['wh_ids'] ?>"
                            data-p-ids="<?= $total['no_paid']['p_ids'] ?>">
                            <span data-cost="<?= $total['no_paid']['cost'] ?>">
                                <?= number_format($total['no_paid']['cost'], 2, '.', ' ') ?>
                            </span>
                            <button <?= ($total['no_paid']['p_ids'] == '' and $total['no_paid']['wh_ids'] == '') ? 'disabled' : '' ?>
                                    name="total_payment" data-type="payment_request" class="btn btn-primary btn-sm">Запрос
                                оплаты</button>
                        </td>
                        <td class="list-group-item-primary t-waitpay" data-wh-ids="<?= $total['waitpay']['wh_ids'] ?>"
                            data-p-ids="<?= $total['waitpay']['p_ids'] ?>">
                            <span data-cost="<?= $total['waitpay']['cost'] ?>">
                                <?= number_format($total['waitpay']['cost'], 2, '.', ' ') ?></span>
                            <button <?= ($total['waitpay']['p_ids'] == '' and $total['waitpay']['wh_ids'] == '') ? 'disabled' : '' ?>
                                    name="total_payment" data-type="proof_payment"
                                    class="btn btn-success btn-sm">Подтвердить оплату</button>
                        </td>
                        <td class="list-group-item-success t-paid" data-wh-ids="<?= $total['paid']['wh_ids'] ?>"
                            data-p-ids="<?= $total['paid']['p_ids'] ?>">
                            <span data-cost="<?= $total['paid']['cost'] ?>"><?= number_format($total['paid']['cost'], 2, '.', ' ') ?></span>
                            <div class="w-load">
                                <?= Html::img(
                                    '@web/images/animate/loading.gif',
                                    [
                                        'alt' => 'Загрузка',
                                        'width' => '20',
                                        'class' => 'loading'
                                    ]
                                ) ?>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <? d::res() ?>

            </div>
            <div class="col-md-10 col-md-offset-1">

                <div class="tasks-list">
                    <ul class="list-group tasks-list">
                        <? if (isset($tasks) and count($tasks)):
                            //                        d::pe($tasks);
                            $i = 0;
                            ?>
                            <? foreach ($tasks as $task): ?>
                            <?
                            $p = $wh_sort[$task->id]['paid'];
                            $wp = $wh_sort[$task->id]['waitpay'];
                            $np = $wh_sort[$task->id]['no_paid'];
                            $iw = $wh_sort[$task->id]['inwork'];

                            $pmnwh = isset($p['date']['min']) ? $p['date']['min'] : false;
                            $pmxwh = isset($p['date']['max']) ? $p['date']['max'] : false;
                            $wpmnwh = isset($wp['date']['min']) ? $wp['date']['min'] : false;
                            $wpmxwh = isset($wp['date']['max']) ? $wp['date']['max'] : false;
                            $npmnwh = isset($np['date']['min']) ? $np['date']['min'] : false;
                            $npmxwh = isset($np['date']['max']) ? $np['date']['max'] : false;
                            $iwmnwh = isset($iw['date']['min']) ? $iw['date']['min'] : false;
                            $iwmxwh = isset($iw['date']['max']) ? $iw['date']['max'] : false;

                            $work = true;

                            /*
                             * Если задача создана, но
                             * по ней работы пока не было.
                             * Зададим флаг в false
                             */
                            if (!$pmnwh and !$npmnwh and !$iwmnwh and !$wpmnwh)
                                $work = false;

                            // 1-в работе/2-не оплачен/3-ожидание оплаты/4-оплачено

                            $status = (isset($task->workinghours[0])) ? $task->workinghours[0]->status : false;

                            //                        d::pri($task->workinghours);

                            if (isset($get['sort'])) {

                                /*
                                 * Если нужно показать только в работе,
                                 * то попускаем все задачи, у которых статус времени работы не равен 1
                                 */
                                if ($get['sort'] == 'inwork' and $status != '1') {
                                    continue;
                                }

                                /*
                                 * Если нужно показать только не оплачено,
                                 * то попускаем все задачи, у которых статус времени работы не равен 2
                                 */
                                if ($get['sort'] == 'nopaid' and $status != '2') {
                                    continue;
                                }

                                /*
                                 * Если нужно показать только новые(у которых время работы не начато),
                                 * то попускаем все задачи, у которых время работы есть
                                 */
                                if ($get['sort'] == 'new' and $status) {
                                    continue;
                                }

                                /*
                                 * Если нужно показать только закрытые,
                                 * то попускаем все задачи, у которых nopaid_max_working_hours = false.
                                 */
                                if ($get['sort'] == 'closed' and !$npmxwh) {
                                    continue;
                                }

                                /*
                                 * Если нужно показать только в ожидающие оплаты,
                                 * то попускаем все задачи, у которых статус времени работы не равен 3
                                 */
                                if ($get['sort'] == 'waitpay' and $status != '3') {
                                    continue;
                                }

                                /*
                                 * Если нужно показать только оплаченые,
                                 * то попускаем все задачи, у которых статус времени работы не равен 4
                                 */
                                if ($get['sort'] == 'paid' and $status != '4') {
                                    continue;
                                }

                                /*
                                 * Если нужно показать только оплаченые,
                                 * то попускаем все задачи, у которых статус времени работы не равен 4
                                 */
                                if ($get['sort'] == 'all' and $status != '4') {
                                    continue;
                                }

                            }

                            ?>
                            <li class="list-group-item">

                                <div class="toolbar panel">
                                    <ul class="tools">
                                        <!--                                    <li>-->
                                        <!--                                        -->
                                        <? //=bp::gi('tools')?>
                                        <!--                                    </li>-->
                                        <li class="edit">
                                            <?= bp::gi('pencil') ?>
                                        </li>
                                    </ul>
                                    <ul class="tools-status" data-url="ajax/change-task-status" data-task-id="<?= $task->id ?>">
                                        <!--                                    <li>-->
                                        <!--                                        --><? //=bp::gi('tools')?>
                                        <!--                                    </li>-->
                                        <li class="<?= ($task->active == '1') ? 'this-active ' : '' ?>active" data-status="1"
                                            data-class="">
                                            <?= bp::gi('check_circle') ?>
                                        </li>
                                        <li class="<?= ($task->active == '2') ? 'this-active ' : '' ?>pause" data-status="2"
                                            data-class="">
                                            <?= bp::gi('pause_circle') ?>
                                        </li>
                                        <li class="<?= ($task->active == '0') ? 'this-active ' : '' ?>disabled" data-status="0"
                                            data-class="font-disabled">
                                            <?= bp::gi('x_circle') ?>
                                        </li>
                                        <?= Html::img('@web/images/animate/loading.gif', ['alt' => 'Загрузка', 'width' => '20', 'class' => 'loading']) ?>
                                    </ul>
                                </div>

                                <div class="task-content panel">
                                    <?php
                                    $attr['class'] = [$params['active_colors'][$task->active], 'task-header'];
                                    $class = Html::renderTagAttributes($attr);
                                    ?>
                                    <h5 class="list-group-item-heading h-t-n"><?= $task->id ?> -
                                        <span<?= $class ?>><span class="t-n"><?= $task->name ?></span></span>
                                    </h5>
                                    <p class="list-group-item-text t-t-n"><?= $task->description ?></p>
                                </div>

                                <div class="edit-block panel dn">

                                    <?php $form = ActiveForm::begin([
                                        'id' => 'edit_block',
                                        'action' => 'ajax/edit-task',
                                        'enableAjaxValidation' => false,
                                        'options' => ['enctype' => 'multipart/form-data', 'class' => 'save'],
                                        'fieldConfig' => [
                                            'options' => ['class' => 'form-group simple'],
                                        ],
                                    ]); ?>

                                    <div class="payment-date">

                                        <div class="form-group date-range dn">
                                            <label>Оплатить период</label><br>
                                            <div class="d-from">
                                                <input type="text" class="form-control c-input-sm" name="date_from"
                                                       id="date-from" placeholder="От">
                                                <span>-</span>
                                            </div>
                                            <div class="d-to">
                                                <input type="text" class="form-control c-input-sm" name="date_to" id="date-to"
                                                       placeholder="До">
                                                <span></span>
                                            </div>
                                            <div class="save-payment-dates">
                                                <button type="submit" name="save-payment" class="btn btn-sm btn-primary">
                                                    Изменить время оплаты
                                                    <?= Html::img('@web/images/animate/loading.gif', ['alt' => 'Загрузка', 'width' => '20', 'class' => 'loading']) ?>
                                                </button>
                                                <span></span>
                                            </div>
                                        </div>
                                        <div style="clear: left"></div>

                                    </div>
                                    <div class="task-name">
                                        <div class="form-group">
                                            <?= $form->field($task, 'name') ?>
                                        </div>
                                    </div>
                                    <div class="task-description">
                                        <div class="form-group">
                                            <?= $form->field($task, 'description', [
                                                'inputOptions' => [
                                                    'id' => 'editTask' . $task->id,
                                                ]
                                            ])->widget(CKEditor::className(), [
                                                'editorOptions' => [
                                                    'multiple' => true,
                                                    'options' => ['id' => 'editTask' . $task->id],
                                                    'preset' => 'custom',
                                                    'inline' => false,
                                                    'breakBeforeOpen' => true,
                                                    'breakAfterOpen' => false,
                                                    'breakBeforeClose' => false,
                                                    'breakAfterClose' => true,
                                                    'toolbarGroups' => [
                                                        ['name' => 'basicstyles', 'groups' => ['basicstyles']],
                                                    ]
                                                ]
                                            ]); ?>
                                        </div>
                                    </div>
                                    <div class="form-group change-task-info">
                                        <?= Html::button(
                                            'Сохранить' . Html::img(
                                                '@web/images/animate/loading.gif',
                                                ['alt' => 'Загрузка', 'width' => '20', 'class' => 'loading']
                                            ),
                                            [
                                                'name' => 'save_payment',
                                                'data-task-id' => $task->id,
                                                'class' => 'btn btn-sm btn-primary'
                                            ]
                                        ) ?>
                                        <?= d::res() ?>
                                    </div>
                                    <?php ActiveForm::end(); ?>
                                </div>

                                <div style="clear: left"></div>
                                <? if ($work): ?>
                                    <div class="information-panel">
                                        <div class="row">
                                            <div class="col-md-12">

                                                <table class="table table-bordered payment-periods" data-task-id="<?= $task->id ?>">
                                                    <tbody>
                                                    <? if ($p['ids']): ?>
                                                        <tr class="list-group-item-success">
                                                            <td>Оплачено:</td>
                                                            <td class="dates" data-ids="<?= $p['ids'] ?>">
                                                                <?= ($pmnwh) ? $pmnwh . ' -' : '' ?>
                                                                <?= ($pmxwh) ?: '' ?>
                                                            </td>
                                                            <td>
                                                                <?= $p['time_sum']['h_i_s'] ?>
                                                            </td>
                                                            <td colspan="3">
                                                                <?= number_format($p['cost'], 2, '.', ' ') ?>
                                                            </td>
                                                        </tr>
                                                    <? endif ?>
                                                    <? if ($wp['ids']): ?>
                                                        <tr class="list-group-item-primary">
                                                            <td>Ожидание оплаты:</td>
                                                            <td class="dates" data-ids="<?= $wp['ids'] ?>">
                                                                <?= ($wpmnwh) ? $wpmnwh . ' -' : '' ?>
                                                                <?= ($wpmxwh) ?: '' ?>
                                                            </td>
                                                            <td>
                                                                <?= $wp['time_sum']['h_i_s'] ?>
                                                            </td>
                                                            <td colspan="3">
                                                                <?= number_format($wp['cost'], 2, '.', ' ') ?>
                                                            </td>
                                                        </tr>
                                                    <? endif ?>
                                                    <? if ($np['ids']): ?>
                                                        <tr class="list-group-item-warning">
                                                            <td>Не оплачено:</td>
                                                            <td class="dates" data-ids="<?= $np['ids'] ?>">
                                                                <?= ($npmnwh) ? $npmnwh . ' -' : '' ?>
                                                                <?= ($npmxwh) ?: '' ?>
                                                            </td>
                                                            <td>
                                                                <?= $np['time_sum']['h_i_s'] ?>
                                                            </td>
                                                            <td colspan="4">
                                                                <?= number_format($np['cost'], 2, '.', ' ') ?>
                                                            </td>
                                                        </tr>
                                                    <? endif ?>
                                                    <? if ($iw['ids']): ?>
                                                        <? //if(1):?>
                                                        <tr class="list-group-item-light">
                                                            <td>В работе:</td>
                                                            <td class="dates" data-ids="<?= $iw['ids'] ?>">
                                                                <?= ($iwmnwh) ? $iwmnwh . ' -' : '' ?>
                                                                <?= date('Y-m-d', time()) ?>
                                                            </td>
                                                            <td class="task-time">
                                                                <?= $iw['time_sum']['h_i_s'] ?>
                                                            </td>
                                                            <td colspan="2" class="task-cost">
                                                                <div class="checkbox">
                                                                    <input class="custom-checkbox" type="checkbox"
                                                                           id="color-<?= $task->id ?>" name="color-<?= $task->id ?>"
                                                                           value="indigo" <?= ($task->active != '1') ? 'disabled' : '' ?>>
                                                                    <label for="color-<?= $task->id ?>">
                                                                        <?= number_format($iw['cost'], 2, '.', ' ') ?></label>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="wrap-icon-pay">
                                                                    <? bp::gi('check_circle') ?>
                                                                    <?php

                                                                    if ($task->active == 0 or $task->active == 2) {
                                                                        $btn_dis = 'disabled';
                                                                        $btn_title = 'title="Задача ' . mb_strtolower(Yii::$app->params['active_status'][$task->active]) . '"';
                                                                    } else {
                                                                        $btn_dis = '';
                                                                        $btn_title = '';
                                                                    }

                                                                    ?>
                                                                    <input type="text" name="state_course" class="form-control"
                                                                           value="<?= $ws->getRate('state_course') ?>" <?= $btn_dis ?>>
                                                                    <input type="text" name="close_by_course" class="form-control"
                                                                           value="<?= $project['rate']['value'] ?>" <?= $btn_dis ?>>
                                                                    <button type="button" name="close_task"
                                                                            class="btn btn-primary btn-sm" data-url="ajax/close-task"
                                                                            data-type-method="post" <?= $btn_dis ?>                 <?= $btn_title ?>>
                                                                        <?= Html::img(
                                                                            '@web/images/animate/loading.gif',
                                                                            [
                                                                                'alt' => 'Загрузка',
                                                                                'width' => '20',
                                                                                'class' => 'loading'
                                                                            ]
                                                                        ) ?>
                                                                        Закрыть задачу по <span class="btn-rate">
                                                                                <?= $project['rate']['value'] ?>
                                                                            </span> <?= $project->rate['name'] ?>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <? endif ?>
                                                    </tbody>
                                                </table>

                                            </div>
                                        </div>
                                    </div>
                                <? endif ?>
                            </li>
                        <? endforeach; ?>
                        <? endif ?>
                    </ul>
                </div>

            </div>
        </div>

    </div>
<?php
$this->registerJs(<<<JS

/**
     * Страница "Задачи"
     * =================
     * Ссылка "Открыть отчет"
     */
    $('.tss .report .r-label').on('click', function () {

        var tthis = $(this),
            wrap = $('.tss .report');

        if (!wrap.find('.r-info').is(':visible')) {
            wrap.find('.label1 span').html('Закрыть отчет');
            wrap.find('.r-info').slideDown(100);
        } else {
            wrap.find('.label1 span').html('Открыть отчет');
            wrap.find('.r-info').slideUp(100);

            $('html, body').animate({
                scrollTop: $('#p-top').offset().top  // класс объекта к которому приезжаем
            }, 100); // Скорость прокрутки

        }

    });

    /**
     * Страница "Задачи"
     * =================
     * Иконка карандаша "Редактировать"
     */
    $('.tss .tasks-list .toolbar .edit').on('click', function () {
        var tthis = $(this),
            wrap = $('.tss'),
            edit_block = tthis.parent().parent().parent().find('.edit-block');

        wrap.find('.edit-block').slideUp(100);

        if (edit_block.is(':visible')) return false;

        if (edit_block.is(':hidden')) {
            edit_block.slideDown(100);
        }
    });

    /**
     * Страница "Задачи"
     * =================
     * Иконки изменения статуса задачи
     */
    $('.tss .tasks-list .toolbar .tools-status li').on('click', function () {
        var tthis = $(this),
            res = $('.res'),
            wrap = $('.tss'),
            i_item = tthis.parent().parent().parent(),
            task_name = i_item.find('.task-content .task-header'),
            active_block = tthis.parent(),
            load = active_block.find('img.loading'),
            btn_disabled = false,
            task_rate_input_disabled = false,
            btn_title = '',
            task_status = tthis.attr('data-status'),
            Data = {};

        if (tthis.hasClass('this-active')) return;

        if (task_status != '1') {
            btn_disabled = true;
            task_rate_input_disabled = true;
            btn_title = 'Задача ' + active_status[task_status].toLowerCase();
        }

        Data['id'] = active_block.attr('data-task-id');
        Data['status'] = task_status;

        //		cl(Data);
        //		return;

        $.ajax({
            url: active_block.attr('data-url'),
            type: 'post',
            dataType: 'json',
            cache: false,
            data: Data,
            beforeSend: function () {
                load.fadeIn(100);
                tthis.css({ opacity: '1' });
            }
        }).done(function (data) {
            LoadAlert(data.header, data.message, 3000, data.type_message);
            res.html('Done<br>' + JSON.stringify(data));

            active_block.find('li').removeClass('this-active');
            tthis.addClass('this-active');

            active_block.find('li').css({ opacity: '.3' });
            tthis.css({ opacity: '1' });

            i_item.find('[name=close_task]')
                .prop('disabled', btn_disabled)
                .attr('title', btn_title);

            i_item.find('[name=close_by_course]')
                .prop('disabled', task_rate_input_disabled);

            i_item.find('.task-cost .custom-checkbox').prop('disabled', btn_disabled);

            task_name.removeClass(active_colors.join(' ')).addClass(active_colors[task_status]);

        }).fail(function (data) {
            LoadAlert('Внимание', 'Не известная ошибка', 3000, 'error');
            res.html('Fail<br>' + JSON.stringify(data));
            tthis.css({ opacity: '.3' });
        }).always(function () {
            load.fadeOut(100);
        });

    });

    /**
     * Страница "Задачи"
     * ==================
     * Поля "Диапозон дат"
     */
    var dates = $(".tss #date-from,.tss #date-to").datepicker({
        monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрьr', 'Октябрь', 'Ноябрь', 'Декабрь'],
        dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
        firstDay: 1,
        dateFormat: 'yy-mm-dd',
        maxDate: new Date(), // добавил вот эту строку
        //        minDate: new Date(),
        onSelect: function (selectedDate) {
            var option = this.id == "date-from" ? "minDate" : "maxDate",
                instance = $(this).data("datepicker"),
                date = $.datepicker.parseDate(
                    instance.settings.dateFormat ||
                    $.datepicker._defaults.dateFormat,
                    selectedDate, instance.settings);
            dates.not(this).datepicker("option", option, date);
            // Сбрасываем поля "Выберите дату" и "Выберите период"
            $('.tss [name=date_history],.tss [name=period]').val('');
        }
    });

    /**
     * Страница "Задачи"
     * =================
     * Чекбокс/checkbox - исключить задачу из итоговой суммы не закрытых задач
     */
    $('.tss .payment-periods input[type=checkbox]').on('change', function () {
        var tthis = $(this),
            wrap = $('.tss'),
            td = tthis.parent().parent(),
            total_amount = wrap.find('.wh-total-amount .total--amount span'),
            float_total_amount = wrap.find('.float-total-amount span'),
            end_sum = number_format(total_amount.html(), 2, '.', '');

        if (tthis.prop('checked')) {
            end_sum = getNumber(end_sum) - getNumber(td.find('label').html());
        } else {
            end_sum = getNumber(end_sum) + getNumber(td.find('label').html());
        }

        total_amount.html(number_format(end_sum, 2, '.', ' '));
        float_total_amount.html(number_format(end_sum, 2, '.', ' '));

        //		cl(td.html());

    });

    /**
     * Страница "Задачи"
     * =================
     * Поле "Новый тариф для закрытия задачи"
     * --------------------------------------
     * Закрываем задачу по новому тарифу
     */
    $('.tss .wrap-icon-pay input').on('input', function () {
        var tthis = $(this),
            wrap = $('.tss'),
            tr = tthis.parent().parent().parent(),
            task_time = tr.find('.task-time'),
            task_cost = tr.find('.task-cost label'),
            arr_task_time = task_time.html().split(':'),
            course = tr.find('.wrap-icon-pay [name=state_course]').val(),
            rate = tr.find('.wrap-icon-pay [name=close_by_course]').val();

        var task_cost_sum = timeNumbersToCost(arr_task_time, course, rate);
        task_cost.html(number_format(task_cost_sum, 2, '.', ' '));
        var btn_rate = (tthis.val() != '') ? tthis.val() : '0';
        tr.find('button[name=close_task] span.btn-rate').html(btn_rate);

    });

    /**
     * Страница "Задачи"
     * =================
     * Кнопка "Закрыть задачу по XX USD"
     * ---------------------------------
     * Закрываем задачу по тарифу
     */
    $('.tss [name=close_task]').on('click', function () {
        var tthis = $(this),
            wrap = $('.tss'),
            load = tthis.find('img.loading'),
            res = wrap.find('.res'),
            table = tthis.parent().parent().parent().parent().parent(),
            tr = tthis.parent().parent().parent(),
            new_rate = tthis.parent().find('[name=close_by_course]'),
            Data = {};

        res.html('result');

        // Получаем ID проекта
        Data['project_id'] = wrap.data('project-id');
        Data['id'] = wrap.data('project-id');
        Data['task_id'] = table.data('task-id');
        Data['wh_ids'] = tr.find('.dates').data('ids');
        Data['state_course'] = tr.find('[name=state_course]').val();

        /*
         * Если полученый тариф отличается от тарифа системы
         * то берем новый тариф
         */
        if (wrap.find('.main-current-rate').attr('data-rate') != new_rate.val()) {
            Data['close_by_rate'] = new_rate.val();
        }

        cl(Data);
        //        return;

        $.ajax({
            url: tthis.attr('data-url'),
            type: tthis.attr('data-type-method'),
            dataType: 'json',
            cache: false,
            data: Data,
            beforeSend: function () {
                load.fadeIn(100);
            }
        }).done(function (data) {
            res.html('Done<br><pre>' + prettyPrintJson.toHtml(data) + '</pre>');
            LoadAlert(data.header, data.message, 3000, data.type_message);

            if (data.status == 200) {
                tr.remove();
            } else {

            }
        }).fail(function (data) {
            res.html('Fail<br>' + JSON.stringify(data));
        }).always(function () {
            load.fadeOut(100);
        });
    });

    /**
     * Страница "Задачи"
     * =================
     * Кнопка "Сохранить" заголовок и описание задачи
     */
    $('.tss .edit-block .change-task-info [name=save_payment]').on('click', function () {
        var tthis = $(this),
            wrap = $('.tss'),
            load = tthis.find('img.loading'),
            res = wrap.find('.res'),
            li = tthis.closest('li'),
            edit_block = tthis.closest('form#edit_block'),
            editorTaskDescription = CKEDITOR.instances['editTask' + tthis.attr('data-task-id')],
            Task = {},
            Project = {},
            Data = {};

        //        cl(li.html());return;

        // Получаем ID проекта
        Project['project_id'] = wrap.data('project-id');

        Task['id'] = tthis.data('task-id');
        Task['name'] = edit_block.find('.task-name input').val();
        Task['description'] = cleanRN(editorTaskDescription.getData());

        Data['project'] = Project;
        Data['task'] = Task;

        // cl(Data);
        // return;

        $.ajax({
            url: edit_block.attr('action'),
            type: edit_block.attr('method'),
            dataType: 'json',
            cashe: 'false',
            data: Data,
            beforeSend: function () {
                load.fadeIn(100);
                res.html('');
            }
        }).done(function (data) {
            //            res.html('Done<br>'+JSON.stringify(data));
            LoadAlert(data.header, data.message, 3000);

            if (data.status == 200) {
                li.find('.task-content .h-t-n .t-n').html(Task['name']);
                li.find('.task-content .t-t-n').html(Task['description']);
            } else {

            }
            load.fadeOut(100);
        }).fail(function (data) {
            res.html('Fail<br>' + JSON.stringify(data));
            load.fadeOut(100);
        });
    });

    /**
     * Страница "Задачи"
     * =================
     * Кнопки "Запрос оплаты","Подтвердить оплату"
     */
    $('.tss [name=total_payment]').on('click', function () {
        var tthis = $(this),
            wrap = $('.tss'),
            load = wrap.find('.head-info .w-load img.loading'),
            res = wrap.find('.res'),
            table = tthis.parent().parent().parent().parent(),
            tr = tthis.parent().parent(),
            request_payment = tthis.attr('data-type'),
            other_button = false,
            nopaid = tr.find('.t-nopaid'),
            waitpay = tr.find('.t-waitpay'),
            paid = tr.find('.t-paid'),
            Data = {};

        if (request_payment != 'payment_request' && request_payment != 'proof_payment') return;

        if (request_payment == 'payment_request') {
            var get_sum = nopaid,
                set_sum = waitpay,
                other_button = tr.find('[data-type=proof_payment]'),
                get_wh_ids = get_sum.attr('data-wh-ids'),
                set_wh_ids = set_sum.attr('data-wh-ids'),
                get_p_ids = get_sum.attr('data-p-ids'),
                set_p_ids = set_sum.attr('data-p-ids');
        } else if (request_payment == 'proof_payment') {
            var get_sum = waitpay,
                set_sum = paid,
                get_wh_ids = get_sum.attr('data-wh-ids'),
                set_wh_ids = set_sum.attr('data-wh-ids'),
                get_p_ids = get_sum.attr('data-p-ids'),
                set_p_ids = set_sum.attr('data-p-ids');
        } else return;

        // ==================================================





        // ==================================================

        // Получаем ID проекта
        Data['project_id'] = wrap.data('project-id');
        Data['p_ids'] = tthis.parent().data('p-ids');
        Data['wh_ids'] = tthis.parent().data('wh-ids');
        Data['request_type'] = request_payment;

        //        cl(Data);
        //        return;

        //        UPDATE `payment` SET `status`='2' WHERE `status`='3'
        //        UPDATE `working_hours` SET `status`='2' WHERE `status`='3'

        $.ajax({
            url: table.attr('data-url'),
            type: table.attr('data-type-method'),
            dataType: 'json',
            cashe: 'false',
            data: Data,
            beforeSend: function () {
                load.fadeIn(100);
            }
        }).done(function (data) {
            //            res.html('Done<br>'+JSON.stringify(data));
            LoadAlert(data.header, data.message, 3000, data.type_message);

            if (data.status == 200) {
                var summ =
                    Number(get_sum.find('span').attr('data-cost')) +
                    Number(set_sum.find('span').attr('data-cost'));

                set_sum.find('span').attr('data-cost', summ);
                set_sum.find('span').html(number_format(summ, 2, '.', ' '));
                get_sum.find('span').attr('data-cost', '');
                get_sum.find('span').html(zero);

                // Соединяем две строки через запятую
                var wh_ids_first = set_wh_ids + ',' + get_wh_ids;
                var p_ids_first = set_p_ids + ',' + get_p_ids;

                /**
                 * Если get_wh_ids/get_p_ids пустая строка, то в wh_ids_full/p_ids_full
                 * будет лишняя запятая в начале строки,
                 * нужно удалить все запятые с начала и с конца строки
                 */
                var wh_ids_full = wh_ids_first.replace(/^,|,$/g, '');
                var p_ids_full = p_ids_first.replace(/^,|,$/g, '');

                set_sum.attr('data-wh-ids', wh_ids_full);
                set_sum.attr('data-p-ids', p_ids_full);
                get_sum.attr('data-wh-ids', '');
                get_sum.attr('data-p-ids', '');

                tthis.prop('disabled', true);
                if (other_button !== false) {
                    other_button.prop('disabled', false);
                }

            } else {

            }
            load.fadeOut(100);
        }).fail(function (data) {
            res.html('Fail<br>' + JSON.stringify(data));
            load.fadeOut(100);
        });
    });

    /**
     * Страница "Задачи"
     * =================
     * Выпадающий список "Сортировать задачи"
     */
    $('.tss .sort-tasks').on('change', function () {
        var tthis = $(this),
            current_option = tthis.find('option:selected'),
            url = '/';

        cl('haha');

        //        if(location.search != ''){
        //            
        //        }
        //        
        url = '?id=' + tthis.attr('data-task-id') + '&sort=' + current_option.val();
        //        
        cl(location);
        //        cl(url);
        //        return;

        if (current_option.val() == 'all') {
            location.href = location.pathname + '?id=' + tthis.attr('data-task-id');
        } else {
            location.href = url;
        }
    });

JS
);
