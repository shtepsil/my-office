<?php

use backend\controllers\MainController as d;
use yii\helpers\Html;
use backend\components\Bootstrap as bp;
use yii\helpers\Json;
use yii\widgets\MaskedInput;

$this->title = 'Time Трекер';

//$arrr = ['first'=>'123','second'=>'456','thirst'=>'789'];
//$kk = key($arrr);

//$kk = ;

//d::pre(date('Y-m-d H:i:s',$kk));

$zero_time = Yii::getAlias('@zero_time');

/*
 * Класс tetr, это сокращение: time-trecker
 */

//$this->registerJsFile('js/countdown.js',  ['position' => yii\web\View::POS_END]);
$this->registerCssFile('css/counter_time.css');
$params = Yii::$app->params;
//d::pe($settings_time_trecker);
$stt = $settings_time_trecker;
//d::pri($stt);

$pomodoro = $stt['settings']['pomodoro'];
//d::pri($pomodoro);

?>

<div class="wrap tetr">
    <div class="text-center h3 header"><?= Html::encode($this->title) ?></div>

    <div class="container main-container">

        <div class="row">
            <div class="col-md-3 time-in">

                <div class="time-counter">
                    <!-- Основной счетчик часов -->
                    <div id="CDT" class="time-counter">
                        <div class="custom-time">
                            <input type="text" name="hs" class="form-control" placeholder="00" maxlength="2">
                            <input type="text" name="ms" class="form-control" placeholder="00" maxlength="2">
                            <input type="text" name="ss" class="form-control" placeholder="00" maxlength="2">
                            <button name="custom_time_save" class="btn btn-success btn-sm" disabled>
                                <?= bp::gi('check_circle') ?></button>
                        </div>
                        <span class="number-wrapper">
                            <div class="line"></div>
                            <div class="caption">ЧАСОВ</div>
                            <span class="number hour hs"><?= $zero_time ?></span></span>
                        <span class="number-wrapper">
                            <div class="line"></div>
                            <div class="caption">МИНУТ</div>
                            <span class="number min ms"><?= $zero_time ?></span></span>
                        <span class="number-wrapper">
                            <div class="line"></div>
                            <div class="caption">СЕКУНД</div>
                            <span class="number sec ss"><?= $zero_time ?></span></span>
                    </div>
                    <!-- /основной счетчик часов -->
                </div>

            </div>
            <div class="col-md-2">
                <div class="pomodoro">
                    <style>
                        .custom-checkbox {
                            position: absolute;
                            z-index: -1;
                            opacity: 0;
                        }
                        .custom-checkbox+label {
                            display: inline-flex;
                            align-items: center;
                            user-select: none;
                        }
                        .custom-checkbox+label::before {
                            content: '';
                            display: inline-block;
                            width: 1em;
                            height: 1em;
                            flex-shrink: 0;
                            flex-grow: 0;
                            border: 1px solid #adb5bd;
                            border-radius: 0.25em;
                            margin-right: 0.5em;
                            background-repeat: no-repeat;
                            background-position: center center;
                            background-size: 50% 50%;
                        }
                        .custom-checkbox:checked+label::before {
                            border-color: #0b76ef;
                            background-color: #0b76ef;
                            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23fff' d='M6.564.75l-3.59 3.612-1.538-1.55L0 4.26 2.974 7.25 8 2.193z'/%3e%3c/svg%3e");
                        }
                        /* стили при наведении курсора на checkbox */
                        .custom-checkbox:not(:disabled):not(:checked)+label:hover::before {
                            border-color: #b3d7ff;
                        }
                        /* стили для активного состояния чекбокса (при нажатии на него) */
                        .custom-checkbox:not(:disabled):active+label::before {
                            background-color: #b3d7ff;
                            border-color: #b3d7ff;
                        }
                        /* стили для чекбокса, находящегося в фокусе */
                        .custom-checkbox:focus+label::before {
                            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
                        }
                        /* стили для чекбокса, находящегося в фокусе и не находящегося в состоянии checked */
                        .custom-checkbox:focus:not(:checked)+label::before {
                            border-color: #80bdff;
                        }
                        /* стили для чекбокса, находящегося в состоянии disabled */
                        .custom-checkbox:disabled+label::before {
                            background-color: #e9ecef;
                        }
                    </style>
                    <?= MaskedInput::widget([
                        'name' => 'v_relax',
                        'mask' => '99:99:99',
                        'value' => $pomodoro['time_relax'],
                        'definitions' => [
                            'maskSymbol' => '_'
                        ],
                        'options' => [
                            'placeholder' => 'Время отдыха',
                            'class' => 'form-control',
                            'data-time-relax-min' => $pomodoro['time_relax'],
                            'data-timework' => $pomodoro['time_work'],
                        ]
                    ]) ?>

                    <input
                        type="checkbox"
                        class="custom-checkbox"
                        id="ch_relax"
                        name="ch_relax"
                        value="yes" <?= 'disabled="disabled"' ?>
                    />
                    <label for="ch_relax">Отдых</label>
                </div>
            </div>
            <div class="col-md-3">

                <button
                    type="button"
                    name="pause"
                    class="btn btn-sm btn-primary"
                    data-type="pause"
                    disabled
                >Пауза</button>
                <button
                    type="button"
                    name="stop"
                    class="btn btn-sm btn-primary"

                                           <?= 'data-type="stop"' ?>
                    <? 'data-type="do"' ?>
                    data-url="ajax/input-time"
                    method="post"
                    <?= 'disabled' ?>
                >
                    <?= Html::img('@web/images/animate/loading.gif', ['alt' => 'Загрузка', 'width' => '20', 'class' => 'loading']) ?>
                    <span>Старт</span>
                </button>
            </div>
            <div class="col-md-3 settings">
                <div class="projects">
                    <?php // получаем список проектов
                    $projects = \backend\models\Projects::find()
                        ->orderBy(['sort' => SORT_DESC])
                        ->andWhere(['active' => '1'])
                        ->asArray()->all();
                    $option_attrs = [];
                    foreach ($projects as $pt) {
                        $color = '';

                        // Если цвет текста не задан, значит черный будет по умолчанию
                        if ($pt['color'] != '')
                            $color = ' color: white;';

                        $option_attrs[$pt['id']] = [
                            'data-code' => $pt['code'],
                            'data-color' => strtolower($pt['color']),
                            'style' => 'background-color:' . $pt['color'] . ';' . $color,
                        ];
                    }
                    $items = \yii\helpers\ArrayHelper::map($projects, 'id', 'name');
                    $options = [
                        'prompt' => 'Выберите проект',
                        'title' => 'Выберите проект',
                        'class' => 'form-control c-input-sm',
                        'id' => 'projects',
                        'data-url' => 'ajax/get-project',
                        'method' => 'post',
                        'options' => $option_attrs,
                    ];
                    //$items = \yii\helpers\ArrayHelper::merge(['value'=>'text'],$items);
                    ?>
                    <?= Html::dropDownList('projects', '', $items, $options); ?>
                    <?= Html::img('@web/images/animate/loading.gif', ['alt' => 'Загрузка', 'width' => '20', 'class' => 'loading']) ?>
                </div>

                <div class="tasks">
                    <select
                            name="tasks"
                            class="form-control c-input-sm"
                            data-url="ajax/get-task-by-project"
                            method="post"
                    >
                        <?= Yii::getAlias('@no_tasks_yet') ?>
                    </select>
                    <?= Html::img('@web/images/animate/loading.gif', ['alt' => 'Загрузка', 'width' => '20', 'class' => 'loading']) ?>
                    <?= bp::gi('reload') ?>
                </div>

<!--                <div class="f-b">-->
<!--                    <select name="task_type" class="form-control c-input-sm" disabled>-->
<!--                        <option value="">Вид задачи (0)</option>-->
<!--                    </select>-->
<!--                </div>-->
            </div>
        </div><!-- row -->

        <br>

        <div class="row">
            <div class="view-common-timing">Показать общее время</div>
            <div class="common-timing dn">
                <div class="col-md-3">
                    <div class="total-work-time">
                        <div class="h6">Общее время проекта</div>
                        <!-- Результирующий счетчик общего времени проекта -->
                        <div id="CDT" class="time-out">
                            <span class="number-wrapper">
                                <div class="line"></div>
                                <div class="caption">ДНЕЙ</div>
                                <span class="number day days">00</span></span>
                            <span class="number-wrapper">
                                <div class="line"></div>
                                <div class="caption">ЧАСОВ</div>
                                <span class="number hour hs">00</span></span>
                            <span class="number-wrapper">
                                <div class="line"></div>
                                <div class="caption">МИНУТ</div>
                                <span class="number min ms">00</span></span>
                            <span class="number-wrapper">
                                <div class="line"></div>
                                <div class="caption">СЕКУНД</div>
                                <span class="number sec ss">00</span></span>
                        </div>
                    </div>
                    <!-- /результирующий счетчик общего времени проекта -->
                </div>
                <div class="col-md-3">
                    <div class="task-total-work-time">
                        <div class="h6">Общее время задачи</div>
                        <!-- Результирующий счетчик общего времени задачи -->
                        <div id="CDT" class="time-out">
                        <span class="number-wrapper">
                            <div class="line"></div>
                            <div class="caption">ДНЕЙ</div>
                            <span class="number day days">00</span></span>
                            <span class="number-wrapper">
                            <div class="line"></div>
                            <div class="caption">ЧАСОВ</div>
                            <span class="number hour hs">00</span></span>
                            <span class="number-wrapper">
                            <div class="line"></div>
                            <div class="caption">МИНУТ</div>
                            <span class="number min ms">00</span></span>
                            <span class="number-wrapper">
                            <div class="line"></div>
                            <div class="caption">СЕКУНД</div>
                            <span class="number sec ss">00</span></span>
                        </div>
                        <!-- /результирующий счетчик общего времени задачи -->
                    </div>
                </div>
            </div>
        </div><!-- row -->

<? d::res() ?>

        <div class="row">
            <div class="col-md-12">
                Сегодня: (по трачено времени на задачу) -
                <span class="total-time">
                    <span class="hs">00</span> :
                    <span class="ms">00</span> :
                    <span class="ss">00</span>
                </span>
            </div><br><br>
            <div class="col-md-12">
                <table class="table">
                    <thead><tr>
                        <th>Дата</th>
                        <th>Время</th>
                        <th>Время создания</th>
                    </tr></thead>
                    <tbody><?= $tr_empty ?></tbody>
                </table>
            </div>
        </div>

    </div><!-- /container -->

</div>
<?php

$this->registerJs(<<<JS

$('.tetr .view-common-timing').on('click', function(){
    var tthis = $(this),
        timing = tthis.closest('div.row').find('.common-timing');
    
    if(!timing.is(':visible')){
        tthis.html('Скрыть общее время');
        timing.slideDown(100);
    }else{
        tthis.html('Показать общее время');
        timing.slideUp(100);
    }
});

function resetPageTimeTrecker(){
    var form = $('.tetr'),
        ch_relax = form.find('[name=ch_relax]'),
        projects = form.find('[name=projects]'),
        v_relax = form.find('[name=v_relax]'),
        time_counter = form.find('.time-counter'),
        tasks = form.find('[name=tasks]'),
        task_type = form.find('[name=task_type]'),
        time_out = form.find('.total-work-time .time-out'),
        task_time_out = form.find('.task-total-work-time .time-out'),
        total_time = form.find('.total-time'),
        btn_start = form.find('[name=stop]'),
        table = form.find('.table tbody');
    
    // Снимаем чекбокс "Отдых"
    ch_relax.prop('checked',false);

    /**
     * Если в списке проектов ничего не выбрано
     * то обнуляем счетчики
     */
    time_out.find('.days').html(zero_time);
    time_out.find('.hs').html(zero_time);
    time_out.find('.ms').html(zero_time);
    time_out.find('.ss').html(zero_time);

    task_time_out.find('.days').html(zero_time);
    task_time_out.find('.hs').html(zero_time);
    task_time_out.find('.ms').html(zero_time);
    task_time_out.find('.ss').html(zero_time);

    // Обнуляем данные суточного счетчика
    time_counter.find('.hs').html(zero_time);
    time_counter.find('.ms').html(zero_time);
    time_counter.find('.ss').html(zero_time);
//        time_counter.find('.hs').html('01');
//        time_counter.find('.ms').html('15');
//        time_counter.find('.ss').html('18');

    // Обнуляем время (по трачено времени на задачу)
    total_time.find('.hs').html(zero_time);
    total_time.find('.ms').html(zero_time);
    total_time.find('.ss').html(zero_time);

    // Деактивируем кнопку "Старт"
    btn_start.prop('disabled',true);
    // Опустошаем и деактивируем выпадающий список "Выберите вид задачи"
    task_type.html(task_view_empty).prop('disabled',true);
    // Опустошаем выпадающий список "Выберите задачу"
    tasks.html(no_tasks_yet);

    // Опустошаем таблицу
    table.html(tr_empty);
    
    // Сбросим выпадающий список "Выберите проект"
    projects.val('');
    
    // Поле ввода "Вермя отдыха"
    v_relax.val('{$pomodoro['time_relax']}');

}// f resetPageTimeTrecker()
    
setTimeout(function(){
    // Сбросим вообще всю страницу
    resetPageTimeTrecker();
}, 1000);

//=== скрипт вызывающий звук при входе и выходе гостей ==========
var onlinePlus = '<audio autoplay="autoplay"> \
 <source src="../files/audio/online.ogg" type="audio/ogg; codecs=vorbis"> \
 <source src="../files/audio/online.mp3" type="audio/mpeg"> \
 </audio>';

 var offlineMinus = '<audio autoplay="autoplay"> \
 <source src="../files/audio/offline.ogg" type="audio/ogg; codecs=vorbis"> \
 <source src="../files/audio/offline.mp3" type="audio/mpeg"> \
 </audio>';

// setInterval(function(){
//     cl('audio');
//     var t = 2;
//     var o = 1;
//
//     if(o == t){
//
//     }
//     else if(t < o){ //offline minus
//         $(".o").val(t);
//         $('.music').html(offlineMinus);
//     }
//     else if(t > o){ //online plus
//         $(".o").val(t);
//         $('.music').html(onlinePlus);
//     }
//
// }, 1000);

//=== END ================================================

JS
);

?>


<? /*
// Input mask
<style type="text/css">
label {
display: inline-block;
width: 320px;
text-align: right;
padding-right: 1em;
}
</style>
<div class="row">
<div class="col-md-12">
<p>
<label>Date Example
<input type="text" name="date" />
</label>
</p>
<p>
<label>Postal Code Example
<input type="text" name="postal-code" />
</label>
</p>
<p>
<label>Phone Number Example
<input type="text" name="phone-number" />
</label>
</p>
<p>
<label>SIN Example (HTML)
<input type="text" name="social-insurance" data-mask="000 000 000" />
</label>
</p>
</div>
</div>
<br>
<script type="text/javascript">
$('input[name="date"]').mask('00/00/0000');
$('input[name="time"]').mask('00:00');
$('input[name="phone-number"]').mask('(000) 000 0000');
$('input[name="postal-code"]').focusout(function() {
$('input[name="postal-code"]').val( this.value.toUpperCase() );
});
</script>
*/?>
