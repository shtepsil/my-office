<?php

/**
 * Инициализация полей дат находится тут:
 * backendold\web\js\scripts.js
 */

use backendold\controllers\MainController as d;
use yii\helpers\Html;
use custom\CHtml;
use backendold\assets\JqueryUITimepickerAddonAsset;

use kartik\select2\Select2;

$this->title = 'Статистика';

// для функции number_format
$zero_time = Yii::getAlias('@zero_time');
$zero_one = Yii::getAlias('@zero_one');

// Текст подсказки
//$label_for_shortcoming = "Поля нужно заполнить суммами реальных счетов (<span style='color: red'>не счетов системы</span>)";

//d::pre(date('Y-m-d H:i:s',1549353214));



/*
 * Класс cr, это сокращение: cash-report
 * w-d-r - wrap доходы расходы
 */

//d::pex($settings_statistics);
$ssss = $settings_statistics;

$rate = $ssss['settings']['rate'];

$periods = Yii::$app->params['periods'];

$projects = \backendold\models\Projects::find()
    ->where((['active' => '1']))
    ->orderBy(['sort' => SORT_DESC])->asArray()->all();

$items = \yii\helpers\ArrayHelper::map($projects, 'id', 'name');

$options = [
    'prompt' => 'Выберите проект',
    'title' => 'Выберите проект',
    'class' => 'form-control c-input-sm wallets',
    'data-url' => 'ajax/get-tasks-by-project',
    'method' => 'post',
];
JqueryUITimepickerAddonAsset::register($this);
?>
<style>
    .active-month {
        color: red;
    }
</style>
<div class="wrap stcs">
    <div class="text-center h3 header">
        <?= Html::encode($this->title) ?>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-3 w-projects-forms">
                <div class="w-projects">
                    <?= Html::dropDownList('projects', '', $items, $options); ?>
                    <?= Html::img('@web/images/animate/loading.gif', ['alt' => 'Загрузка', 'width' => '20', 'class' => 'loading']) ?>
                </div>
                <div class="w-tasks">
                    <select name="tasks" id="tasks" class="form-control c-input-sm tasks" data-url="ajax/get-task-view"
                        method="post" disabled>
                        <option value="">Задач пока нет</option>
                    </select>
                    <?= Html::img('@web/images/animate/loading.gif', ['alt' => 'Загрузка', 'width' => '20', 'class' => 'loading']) ?>
                </div>

            </div>

            <div class="col-md-3 filter-ipnuts">
                <input type="text" id="date-day" class="form-control c-input-sm date-day" placeholder="Введите дату"
                    title="Введите дату" value="<? date('Y-m-d H:i', time()) ?>" name="date_day"
                    data-current-date="<?= date('Y-m-d', time()) ?>" />

                <select name="period" id="period" class="form-control c-input-sm period">
                    <? foreach ($periods as $period): ?>
                        <option value="<?= $period['value'] ?>"
                            class="<?=(date('m') == $period['value']) ? 'active-month' : '' ?>"><?
                                   echo $period['name']
                                       ?></option>
                    <? endforeach; ?>
                </select>

            </div>

            <div class="col-md-3">

                <div class="date-range">
                    <label>Искать по отрезку времени</label><br>
                    <input type="text" class="form-control c-input-sm" id="date-from" placeholder="От"
                        oninput="ssOfInputs(this)">
                    <input type="text" class="form-control c-input-sm" id="date-to" placeholder="До"
                        oninput="ssOfInputs(this)" value="<?= date('Y-m-d H:i', time()) ?>">
                </div>
            </div>

        </div><!-- row -->

        <br>
        <div class="row">
            <div class="col-md-6">
                <?php
                echo Select2::widget([
                    'name' => 'multi_projects',
                    'data' => $items,
                    'theme' => Select2::THEME_DEFAULT,
                    'options' => [
                        'placeholder' => 'Выберите проекты',
                        'multiple' => true,
                        'autocomplete' => 'off',
                        'class' => 'multi-projects'
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]);
                ?>
            </div>
        </div>
        <br>

        <div class="row">
            <div class="col-md-5 buttons-filter">
                <button class="btn btn-danger btn-sm" name="reset_filter">
                    Сбросить фильтр
                </button>
                <button class="btn btn-primary btn-sm get-statistics-webmaster" name="get_statistics_webmaster"
                    data-url="ajax/get-statistics-webmaster" method="post">
                    <?= Html::img('@web/images/animate/loading.gif', ['alt' => 'Загрузка', 'width' => '20', 'class' => 'loading']) ?>
                    Найти
                </button>
            </div>
        </div><!-- row -->
        <br>
        <div class="row">
            <div class="col-md-4">
                <table class="table table1">
                    <tbody>
                        <tr>
                            <td class="total" data-seconds="">Общее время: <span class="hh"><b>
                                        <?= $zero_time ?>
                                    </b></span> : <span class="mm"><b>
                                        <?= $zero_time ?>
                                    </b></span> : <span class="ss"><b>
                                        <?= $zero_time ?>
                                    </b></span></td>
                        </tr>
                        <tr>
                            <td class="working-days">
                                Потрачено дней: <span class="days"><b>
                                        <?= $zero_one ?>
                                    </b></span>
                            </td>
                        </tr>
                        <tr>
                            <td class="average">
                                Среднее время в день: <span class="hh"><b>
                                        <?= $zero_time ?>
                                    </b></span> : <span class="mm"><b>
                                        <?= $zero_time ?>
                                    </b></span> : <span class="ss"><b>
                                        <?= $zero_time ?>
                                    </b></span>
                            </td>
                        </tr>
                        <tr>
                            <td class="total-payment">
                                <div class="w-payment-amount">К оплате: <b><span class="payment-amount">0.00</span></b> р.</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-md-3 dn">
                <div class="course-dollara">
                    Текущий курс доллара: <span>
                        <?= $current_course ?>
                    </span> р.
                </div>
                <div class="w-rate">
                    Тариф ( $ за час ): <span class="rate">
                        <?= $rate['cost'] ?>
                    </span>
                </div>
            </div>
        </div><!-- row -->

        <?= $alerts ?>

        <? d::res() ?>

    </div>

</div>

<?php $this->registerJs(<<<JS
$(function(){
    /**
     * Поля "Диапозон дат ОТ и ДО"
     * ===========================
     * Инициализация календаря
     */
	var dates_stcs = $("#date-from,#date-to").datetimepicker({
        // monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрьr','Октябрь','Ноябрь','Декабрь'],
        // dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
        firstDay: 1,
        dateFormat: 'yy-mm-dd',
        maxDate: new Date(),
        onSelect: function( selectedDate ) {
            var option = this.id == "date-from" ? "minDate" : "maxDate",
                instance = $( this ).data( "datepicker" ),
                date = $.datepicker.parseDate(
                    instance.settings.dateFormat ||
                    $.datepicker._defaults.dateFormat,
                    selectedDate, instance.settings
                );
            dates_stcs.not( this ).datepicker( "option", option, date );
            ssOfInputs(this);
        }
    });
    
    /**
     * Поля "Выберите дату"
     * ====================
     * Календарь выбора даты
     * ---------------------
     * Инициализация календаря
     */
    $(".date-day").datetimepicker({
        firstDay: 1,
        dateFormat: 'yy-mm-dd'
    });

    /**
     * Поля "Выберите дату"
     * ====================
     * Календарь выбора даты
     * ---------------------
     * При изменении поля опустошаем поля диапозона дат ОТ и ДО
     */
    $(".date-day").on('change', function () {
        $('#date-from, #date-to, [name=period]').val('');
    });

    /**
     * Поле "Выберите период"
     * ======================
     * При заполнении поля, отключаем все остальные поля фильтра по датам
     */
    $('.stcs .filter-ipnuts [name=period]').on('change', function () {
        var tthis = $(this),
            date_day = $('.stcs .filter-ipnuts #date-day'),
            date_from = $('.stcs .date-range #date-from'),
            date_to = $('.stcs .date-range #date-to');

        if (tthis.val() != '') {
            date_day.val('');
            date_from.val('');
            date_to.val('');
        } else {
            date_day.val('');
            date_from.val('');
            date_to.val('');
        }
    });

    /**
     * Multi select список "Выберите проекты"
     * ======================================
     * Выбор нескольких проектов
     */
    $('.stcs .multi-projects').on('change', function () {
        //		$('#date-day').val('');
        $('[name=projects]').val('');
        $('[name=tasks]').val('').prop('disabled', true);
    });

    /** Кнопка "Сбросить фильтр" */
    $('.stcs [name=reset_filter]').on('click', function () {
        var tthis = $(this),
            form = $('.stcs'),

            project = form.find('[name=projects]'),
            tasks = form.find('[name=tasks]'),

            date_day = form.find('#date-day'),
            period = form.find('[name=period]'),
            date_from = form.find('#date-from'),
            date_to = form.find('#date-to');

        // Обнуляем все поля
        project.val('');
        tasks.html(no_tasks_yet);
        date_day.val('');
        period.val('');
        date_from.val('');
        date_to.val('');

        // Обнуляем таблицу
        form.find('.total .hh b').html(zero_time);
        form.find('.total .mm b').html(zero_time);
        form.find('.total .ss b').html(zero_time);
        form.find('.average .hh b').html(zero_time);
        form.find('.average .mm b').html(zero_time);
        form.find('.average .ss b').html(zero_time);
        form.find('.working-days .days b').html(zero_one);
        form.find('.w-payment-amount .payment-amount').html(zero);
    });

    /**
     * Выпадающий список "Выберите проект"
     * ===================================
     * После выбора проекта, заполняем выпадающий список "Выберите задачу"
     * задачами по выбранному проекту
     */
    $('.stcs [name=projects]').on('change', function () {
        var tthis = $(this),
            form = $('.stcs'),
            res = form.find('.res'),
            load = tthis.next(),
            tasks = form.find('[name=tasks]'),
            date_day = form.find('#date-day'),
            period = form.find('[name=period]'),
            date_from = form.find('#date-from'),
            date_to = form.find('#date-to'),
            Data = {};

        // Если выбрано ничего
        if (tthis.val() == '') {
            tasks.html(no_tasks_yet).prop('disabled', true);

            // Отключаем поля филтра
            date_day.val('');
            period.val('');
            date_from.val('');
            date_to.val('');

            return;
        }

        // Убираем с экрана все попыещающие окна 
        cea();

        Data['project_id'] = tthis.val();

        //        cl(Data);
        //        return;

        $.ajax({
            url: tthis.attr('data-url'),
            type: tthis.attr('method'),
            cashe: 'false',
            dataType: 'json',
            data: Data,
            beforeSend: function () {
                load.fadeIn(100);
            }
        }).done(function (data) {
            //            res.html('Done<br>'+JSON.stringify(data));

            LoadAlert(data.header, data.message, live, data.type_message);
            if (data.status == 200) {
                tasks.html(data.options).prop('disabled', '');

                // Активизируем поля фильтра
                date_day.val('').prop('disabled', '');
                period.val('').prop('disabled', '');
                date_from.val('').prop('disabled', '');
                date_to.val('').prop('disabled', '');
            } else {
                //                popUp('.stcs','Done !200<br>'+JSON.stringify(data),'danger');
            }

            load.fadeOut(100);
        }).fail(function (data) {
            //            res.html('Fail<br>'+JSON.stringify(data));
            //            popUp('.stcs','Fail<br>'+JSON.stringify(data));
            LoadAlert('Error', 'Ошибка PHP', live, 'error');
            load.fadeOut(100);
        });
    });
        
    /** Кнопка "Найти" */
    $('.get-statistics-webmaster').on('click', function(){
        var button_search = $(this),
            res = $('.res'),
            form = $('.stcs'),
            load = button_search.find('img'),
            rate = Number(form.find('span.rate').html()),
            custom_course = Number(form.find('.course-dollara span').html()),
    //        seconds = Number(tthis.attr('data-seconds')),
            payment_amount = form.find('.w-payment-amount .payment-amount'),
            itog_h = form.find('.itog-in').find('b'),
            itog_m = form.find('.itog-out').find('b'),
            hh = form.find('.hh b'),
            mm = form.find('.mm b'),
            ss = form.find('.ss b'),
            Data = {},
            empty_inputs = false,
            empty = false,
            Time = '',
    //        total = form.find('.table1 .total'),
            Average_Time = '';
        
        res.html('result');
        payment_amount.html(zero);
        
        Data['project_id'] = form.find('[name=projects]').val();
        Data['projects_ids'] = form.find('.multi-projects').val();
        Data['task_id'] = form.find('[name=tasks]').val();
        
        Data['date_day'] = form.find('#date-day').val();
        Data['period'] = form.find('[name=period]').val();
        Data['date_from'] = form.find('#date-from').val();
        Data['date_to'] = form.find('#date-to').val();
        
        /**
         * Если хотяб одно значение не будет пустым
         * значит фильтр сработает
         */
    //    for(key in Data){
    //        if(
    //			key == 'project_id'
    //			|| key == 'task_id'
    //			|| key == 'date_day'
    //			|| key == 'period'
    //			|| key == 'date_from'
    //			|| key == 'date_to'
    //		) continue;
    //        if(Data[key] != ''){
    //			cl(key);
    //			empty = true;
    //		}
    //    }
        
    //    if(!empty){
    //        LoadAlert('Внимание', 'Заполните хотя бы одно поле фильтра', 4000, 'warning');
    //        return;
    //    }
        
        
        cl(Data);
    //    return;
    
        $.ajax({
            url: button_search.attr('data-url'),
            type: button_search.attr('method'),
            cashe: 'false',
            dataType: 'json',
            data: Data,
            beforeSend: function() {
                load.fadeIn(100);
            }
        }).done(function(data) {
            res.html('Done<br><pre>' + prettyPrintJson.toHtml(data) + '</pre>');
            LoadAlert(data.header, data.message, live, data.type_message);
            if (data.status == 200) {
                
                /**
                 * Вставляем общее количество секунд
                 * в tr содержащее общее время работы задачи
                 */
    //            total.attr('data-seconds',data.seconds);
                
                /**
                 * Расчет стоимости времени работы
                 * ===============================
                 * Если курс доллара не undefinded
                 */
                if(typeof data.current_curs !== 'undefined') {
                    var course = Number(data.current_curs);
                    course = (custom_course != course) ? custom_course : course;
                    // Получаем стоимость работы
                    var cost = (((course * rate) / 60 / 60) * Number(data.seconds));
                    // Выводим сумму стоимости на экран
                    payment_amount.html(number_format(cost, 2, '.', ' '));
                    // Вставляем курс доллара в поле ввода "Курс доллара"
                    form.find('.form-cost-calculation #exampleInputAmount').val(data.current_curs);
                }
                
                // Общее время работы (часов/минут/секунд)
                Time = toTimeFormat(data.seconds);
                form.find('.total .hh b').html(Time['h']);
                form.find('.total .mm b').html(Time['m']);
                form.find('.total .ss b').html(Time['s']);
                
                // Среднее время работы в день (часов/минут/секунд)
                Average_Time = toTimeFormat(data.average_time);
                form.find('.average .hh b').html(Average_Time['h']);
                form.find('.average .mm b').html(Average_Time['m']);
                form.find('.average .ss b').html(Average_Time['s']);
                
                // Общее количество отработанных дней
                form.find('.working-days .days b').html(data.working_days);
    
            } else {
                form.find('.total .hh b').html(zero_time);
                form.find('.total .mm b').html(zero_time);
                form.find('.total .ss b').html(zero_time);
                form.find('.average .hh b').html(zero_time);
                form.find('.average .mm b').html(zero_time);
                form.find('.average .ss b').html(zero_time);
                form.find('.working-days .days b').html(zero_one);
            }
        }).fail(function(data) {
            res.html('Fail<br>' + JSON.stringify(data));
            LoadAlert('Error', 'Ошибка PHP', live, 'error');
        }).always(function() {
            load.fadeOut(100);
        });
    });
    
    /**
     * Поля "Искать по отрезку времени"
     * ================================
     * При вводе - отключаем остальные поля "времени"
     */
    function ssOfInputs(obj){
        var tthis = $(obj),
            form = $('.stcs'),
            period = $('.stcs .filter-ipnuts [name=period]'),
            date_day = $('.stcs .filter-ipnuts #date-day'),
            date_from = $('.stcs .date-range #date-from'),
            date_to = $('.stcs .date-range #date-to');
    
        if(tthis.val() != ''){
            period.val('');
            date_day.val('');
        }else{
            period.val('');
            date_day.val('');
            date_from.val('');
            date_to.val('');
        }
    }
    
    /**
     * Расчет стоимости работы
     */
    function getCostWork(){
        var tthis = $(this),
            load = tthis.find('img.loading'),
            res = $('.res'),
            wrap = $('.stcs'),
            rate = Number(wrap.find('.w-rate .rate').html()),
            seconds = Number(tthis.attr('data-seconds')),
            payment_amount = wrap.find('.w-payment-amount .payment-amount'),
            Data = {};
            
        // Если нет секунд для расчета
        if(seconds == ''){
            LoadAlert('Внимание','Вы не получили время',5000,'warning');
            return;
        }
    
    //        cl(Data);
    //        return;
    
        $.ajax({
            url:'ajax/get-course',
            type:'post',
            cashe:'false',
            dataType:'json',
            data:Data,
            beforeSend:function(){
                load.fadeIn(100);
            }
        }).done(function(data){
    //            res.html('Done<br>'+JSON.stringify(data));
    
            LoadAlert(data.header,data.message,live,data.type_message);
    
            // Если курс доллара не undefinded
            if(typeof data.current_curs !== 'undefined'){
                var course = Number(data.current_curs);
                // Получаем стоимость работы
                var cost = (((course * rate)/60/60)*seconds);
                // Выводим сумму стоимости на экран
                payment_amount.html(number_format(cost,2,'.',' '));
                // Вставляем курс доллара в поле ввода "Курс доллара"
                wrap.find('.form-cost-calculation #exampleInputAmount').val(data.current_curs);
            }
    
            if(data.status == 200){}else{}
    
            load.fadeOut(100);
        }).fail(function(data){
            res.html('Fail<br>'+JSON.stringify(data));
            LoadAlert('Error','Ошибка PHP',live,'error');
            load.fadeOut(100);
        });
        
    }// f getCostWork()
    
});//JQuery

JS
);