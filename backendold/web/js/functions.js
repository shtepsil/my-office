/**
 * Переменная (zero) задается в AdminAppAsset через init()
 */

var sec_work_timer = 2;
sec_work_timer = 1000;

/**
 * Страница "Time Трекер"
 * ===================================================
 */

/**
 * Страница "Time Трекер"
 * ======================
 * Запуск/стоп/пауза счетчика времени
 */
function timeCounter() {
    var wrap = $('.tetr'),
        sec = 0,
        min = 0,
        hours = 0,
        hoursDiv = wrap.find('.time-counter'),
        sec2display = '',
        min2display = '',
        hour2display = '',
        btn_pause = wrap.find('[name=pause]'),
        btn_stop = wrap.find('[name=stop]'),
        v_relax = wrap.find('[name=v_relax]'),
        TimeData = {};

    setInterval(function () {
        // Проверка кнопки "Стоп"
        if (btn_stop.attr('data-type') == 'stop') {
            sec = 0;
            min = 0;
            hours = 0;
            sec2display = '';
            min2display = '';
            hour2display = '';
            return;
        }
        // Проверка кнопки "Пауза"
        if (btn_pause.attr('data-type') == 'pause') return;

        sec += 1;
        if (sec >= 60) {
            min += 1;
            sec = 0;
        }
        if (min >= 60) {
            hours += 1;
            min = 0;
        }
        if (hours >= 24) hours = 0;

        if (sec < 10) sec2display = '0' + sec;
        else sec2display = sec;

        if (min < 10) min2display = '0' + min;
        else min2display = min;

        if (hours < 10) hour2display = '0' + hours;
        else hour2display = hours;

        hoursDiv.find('.hs').html(hour2display);
        hoursDiv.find('.ms').html(min2display);
        hoursDiv.find('.ss').html(sec2display);

        // TimeData['h'] = hours;
        // TimeData['m'] = min;
        // TimeData['s'] = sec;
        TimeData['h'] = hour2display;
        TimeData['m'] = min2display;
        TimeData['s'] = sec2display;

        if (!timeWorkRelax(TimeData)) return;
    }, sec_work_timer);
}
// Запускаем функции только на странице Time Трекер
if (location.pathname.indexOf('time-trecker') != '-1') {
    // Счётчик времени
    timeCounter();
    // Настройка страницы (подставляем нужные данные соответственно настройкам)
    updateSettings();
}

/**
 * Определяет текущий режим счётчика
 * рабочее время или отдых и в зависимости от этого
 * производит соответствующие действия.
 */
function timeWorkRelax(TimeData) {
    var wrap = $('.tetr'),
        project = wrap.find('[name=projects]'),
        btn_pause = wrap.find('[name=pause]'),
        work_limit = wrap.find('[name=v_relax]').attr('data-timework'),
        r_limit = wrap.find('[name=v_relax]'),
        // stop/do
        btn_start_stop = wrap.find('[name=stop]'),
        // value relax
        v_r = wrap.find('[name=ch_relax]'),
        label_ch_relax = wrap.find('[for=ch_relax]'),
        current_timer = TimeData.h + ':' + TimeData.m + ':' + TimeData.s;

    if (v_r.is(':checked')) {
        /**
         * ОТДЫХ
         * Если минуты равно установленому лимиту pomodoro - время отдыха.
         * Останавливаем скрипт, если лимит отдыха времени по pomodoro исчерпан.
         */
        label_ch_relax.css({ color: 'red' });
        // if (TimeData['m'] == r_limit.val()) {
        if (current_timer >= r_limit.val()) {
            // Если счетчик включен, то остановим его
            if (btn_start_stop.attr('data-type') == 'do') {
                /**
                 * Действия после отановки таймера по лимиту работы
                 *
                 */

                if (
                    project.find('option:selected').attr('data-code') ==
                    'cr_softorium'
                ) {
                    /*
                     * Запуск кнопки "Стоп"
                     * Время отдыха Софториум нужно сохранять.
                     * Потом в интерфейсе нужно сделать чекбокс - "сохранять время отдыха"
                     * и в верхнем if'e уже проверять этот чекбокс, а не код проекта.
                     */
                    btn_start_stop.trigger('click');
                } else {
                    btn_start_stop
                        .attr('data-type', 'stop')
                        .find('span')
                        .html('Старт');
                }
                btn_pause.attr('data-type', 'pause').prop('disabled', true);
                wrap.find('[name=projects]').prop('disabled', false);
                wrap.find('[name=tasks]').prop('disabled', false);
            }
            v_r.prop('checked', false).prop('disabled', false);
            r_limit.val(r_limit.attr('data-time-relax-min'));
            label_ch_relax.css({ color: 'rgba(55,58,60)' });

            // Остановка отдыха waitout.mp3 - пурум пум пум пу пум
            // Конец работы offline.mp3 - дверь закрывается

            var audio = new Audio(); // Создаём новый элемент Audio
            audio.src = 'uploads/audio/waitout.mp3'; // Указываем путь к звуку "клика"
            audio.autoplay = true; // Автоматически запускаем

            sendNotification('Отдых закончен', {
                body: 'Начем работу!',
                dir: 'auto',
                icon: 'images/work.jpg',
            });

            return false;
        }
    } else {
        /**
         * РАБОТА
         * Если минуты равно установленому лимиту pomodoro - время работы.
         * Останавливаем скрипт, если рабочий лимит времени по pomodoro исчерпан.
         */
        label_ch_relax.css({ color: 'rgba(55,58,60,.4)' });
        // if (TimeData['h'] >= p_limit_hour) {
        if (current_timer >= work_limit) {
            // Если счетчик включен, то остановим его
            if (btn_start_stop.attr('data-type') == 'do') {
                // Запуск кнопки "Стоп"
                btn_start_stop.trigger('click');
            }
            v_r.prop('checked', true);
            r_limit.val(r_limit.attr('data-time-relax-min'));
            label_ch_relax.css({ color: 'red' });

            // Остановка отдыха waitout.mp3 - пурум пум пум пу пум
            // Конец работы offline.mp3 - дверь закрывается

            var audio = new Audio(); // Создаём новый элемент Audio
            audio.src = 'uploads/audio/offline.mp3'; // Указываем путь к звуку "клика"
            audio.autoplay = true; // Автоматически запускаем

            sendNotification('При тормози работу', {
                body: 'Отдохни!',
                dir: 'auto',
                icon: 'images/relaxation.jpg',
            });

            return false;
        }
    }
} // f timeWorkRelax()

/**
 * Страница "Time Трекер"
 * ======================
 * Обновляем настройки страницы - динамически каждые 4 минуты
 */
function updateSettings() {
    var wrap = $('.tetr'),
        res = $('.res'),
        v_relax = wrap.find('[name=v_relax]'),
        Data = {};

    setInterval(
        function () {
            cl('Запрос отправлен');
            $.ajax({
                url: 'ajax/get-settings',
                type: 'post',
                dataType: 'json',
                cache: 'false',
                data: Data,
                // beforeSend:function(){}
            })
                .done(function (data) {
                    // cl(data);
                    // res.html('Done<br>'+JSON.stringify(data));
                    /**
                     * Так как тайм трекер,
                     * время для отдыха берёт из value поля name=v_relax,
                     * то впринципе атрибут data-time-relax-min время отдыха не нужен.
                     * На текущий момент, атрибут data-time-relax-min ни где не используется.
                     */
                    v_relax.attr('data-timework', data.time_work);
                })
                .fail(function (data) {
                    // res.html('Fail<br>'+JSON.stringify(data));
                });
        },
        // 4 минуты
        240000
        // 7000
    );
}

/**
 * Суммируем время
 */
function calc_up(DataTime) {
    var form = $('.tetr'),
        time_in = form.find('.time-in .time-counter'),
        time_out = form.find('.time-out'),
        h = 0,
        m = 0,
        s = 0,
        s_in = 0,
        s_out = 0,
        hs_in = 0,
        ms_in = 0,
        ss_in = 0,
        days_out = 0,
        hs_out = 0,
        ms_out = 0,
        ss_out = 0,
        days = 0,
        Time = {};

    // Получаем входные данные
    hs_in += Number(time_in.find('span.hs').html()) * 60 * 60;
    ms_in += Number(time_in.find('span.ms').html()) * 60;
    ss_in += Number(time_in.find('span.ss').html());

    // Получаем выходные данные
    days_out += DataTime['days'] * 86400;
    hs_out += DataTime['hours'] * 60 * 60;
    ms_out += DataTime['minutes'] * 60;
    ss_out += DataTime['seconds'];

    s_in += hs_in + ms_in + ss_in;
    s_out += days_out + hs_out + ms_out + ss_out;
    s += s_in + s_out;

    // Считаем количество минут
    if (s > 59) {
        m = Math.floor(s / 60);
        // Получаем остаток секунд (должно быть меньше 60)
        s = s - m * 60;
        //        console.log(s);
    }
    // Считаем количество часов
    if (m > 59) {
        h = Math.floor(m / 60);
        // Получаем остаток минут (должно быть меньше 60)
        m = m - h * 60;
        //        console.log(m);
    }
    if (s < 10) {
        s = '0' + s;
    }
    if (m < 10) {
        m = '0' + m;
    }

    var hh = h;

    if (h >= 24) {
        /**
         * Дни
         * s_in_h - количество секунд в полученных часах
         *    (h * 60 * 60)
         * Делим секунды(полученных часов) на количество секунд в дне
         *   округлим и получим количество дней в полученных часах "Math.floor(s_in_h/86400)"
         * Умножаем количество секунд одного дня на количество дней
         *    Math.floor(s_in_h/86400) * 86400)
         * далее от секунд(полученных часов) минус секунд дней
         *    (s_in_h - (Math.floor(s_in_h/86400) * 86400))
         * и далее делим два раза на 60, получим количество часов последнего дня
         * hld - hour last day - часов последнего дня
         */

        var s_in_h = h * 60 * 60;
        var days = Math.floor(s_in_h / 86400);

        hh = (s_in_h - days * 86400) / 60 / 60;
        //        if (hh < 10) {
        //            hh = "0" + hh;
        //        }
    }

    if (hh < 10) {
        hh = '0' + hh;
    }

    if (days < 10) {
        days = '0' + days;
    }

    //    time_out.find('span.days').html(days);
    //    time_out.find('span.hs').html(hh);
    //    time_out.find('span.ms').html(m);
    //    time_out.find('span.ss').html(s);

    //    console.log(days);

    Time['days'] = days;
    Time['hours'] = hh;
    Time['minutes'] = m;
    Time['seconds'] = s;

    //    return '{"days":"'+days+'","hours":"'+hh+'","minutes":"'+m+'","seconds":"'+s+'"}';
    //    return '{"days":"23","hours":"06","minutes":"02","seconds":"04"}';
    return Time;
}

function calc_down() {}

function timeNumbersToCost(data, curse, rate, dollar) {
    if (typeof curse === 'undefined') return false;
    if (typeof rate === 'undefined') return false;

    var h,
        m,
        s,
        ss = 0;

    // Если входной параметр это массив
    if (isArray(data)) {
        h = Number(data[0]);
        m = Number(data[1]);
        s = Number(data[2]);
    }
    // Если data это объект
    if (isObject(data)) {
        h = Number(data['h']);
        m = Number(data['m']);
        s = Number(data['s']);
    }

    ss += h * 60 * 60 + m * 60 + s;
    if (dollar !== undefined) {
        return (Number(rate) / 60 / 60) * ss;
    } else {
        return ((Number(curse) * Number(rate)) / 60 / 60) * ss;
    }
    //        Math.round10(
    //        ((curse * Number(rate)) / 60 / 60) * ss
    //    );
    // Стоимость одной секунды
    //    cl(cost);
}

/**
 * Переводим строку времени формата 00:05:00 в секудны.
 * Пример
 * 00:05:00 -> 300 seconds
 */
function timeStringToSeconds(time_string) {
    if (typeof time_string === 'undefined') return 0;
    var data = time_string.split(':');
    var h,
        m,
        s,
        ss = 0;

    // Если входной параметр это массив
    if (isArray(data)) {
        h = Number(data[0]);
        m = Number(data[1]);
        s = Number(data[2]);
    }
    // Если data это объект
    if (isObject(data)) {
        h = Number(data['h']);
        m = Number(data['m']);
        s = Number(data['s']);
    }

    ss += h * 60 * 60 + m * 60 + s;

    return ss;
}

/**
 * Переводим сумму секунд в часы/минуты/секунды
 * На вход приходит одна цифра - секунды
 */
function toTimeFormat(s) {
    var h = 0,
        m = 0,
        Time = {};
    if (s > 0) {
        // Переводим собранные секунды в часов/минут/секунд
        // Считаем количество минут
        if (s > 59) {
            m = Math.floor(s / 60);
            // Получаем остаток секунд (должно быть меньше 60)
            s = s - m * 60;
        }
        // Считаем количество часов
        if (m > 59) {
            h = Math.floor(m / 60);
            // Получаем остаток минут (должно быть меньше 60)
            m = m - h * 60;
        }
    }

    if (s < 10) s = '0' + s;
    if (m < 10) m = '0' + m;
    if (h < 10) h = '0' + h;

    Time['s'] = s;
    Time['m'] = m;
    Time['h'] = h;

    return Time;
}

/**
 * Переводим строку времени формата 00:05:00 в объект {s: '00', m: '05', h: '00'}
 * Пример
 * 00:05:00 -> {s: '00', m: '05', h: '00'}
 */
function stringTimeToObject(time_string) {
    if (typeof time_string === 'undefined') return 0;
    return toTimeFormat(timeStringToSeconds(time_string));
}

/**
 * Страница "Time Трекер"
 * ======================
 * При выборе проекта, загружаем список задач.
 */
function reloadTasks(obj) {
    var $this = $(obj),
        form = $('.tetr'),
        ch_relax = form.find('[name=ch_relax]'),
        load = $this.next(),
        res = form.find('.res'),
        time_counter = form.find('.time-counter'),
        tasks = form.find('[name=tasks]'),
        time_out = form.find('.total-work-time .time-out'),
        task_time_out = form.find('.task-total-work-time .time-out'),
        total_time = form.find('.total-time'),
        btn_start = form.find('[name=stop]'),
        table = form.find('.table tbody'),
        Data = {};

    /**
     * Если в списке проектов ничего не выбрано
     * то обнуляем счетчики
     */
    if ($this.val() == '') {
        // Чекбокс "Отдых" снимаем отметку и делаем не активным
        ch_relax.prop('checked', false).prop('disabled', true);

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

        // Обнуляем время (по трачено времени на задачу)
        total_time.find('.hs').html(zero_time);
        total_time.find('.ms').html(zero_time);
        total_time.find('.ss').html(zero_time);

        // Деактивируем кнопку "Старт"
        btn_start.prop('disabled', true);
        // Опустошаем выпадающий список "Выберите задачу"
        tasks.html(no_tasks_yet);

        // Опустошаем таблицу
        table.html(tr_empty);

        return;
    }

    Data['project'] = $this.find('option:selected').attr('data-code');
    Data['project_id'] = $this.find('option:selected').val();

    // console.log(JSON.stringify(Data));
    // return;

    $.ajax({
        url: $this.attr('data-url'),
        type: $this.attr('method'),
        dataType: 'json',
        cashe: 'false',
        data: Data,
        beforeSend: function () {
            load.fadeIn(100);
        },
    })
        .done(function (data) {
            res.html('<pre>' + prettyPrintJson.toHtml(data) + '</pre>');
            LoadAlert(data.header, data.message, 3000, data.type_message);

            time_out.find('.days').html(data.days);
            time_out.find('.hs').html(data.hours);
            time_out.find('.ms').html(data.minutes);
            time_out.find('.ss').html(data.seconds);

            // Заполняем выпадающий список "Выберите проект"
            tasks.html(data.tasks);

            // Опустошаем таблицу
            table.html(tr_empty);

            // Деактивируем кнопку "Старт"
            btn_start.prop('disabled', true);

            // Чекбокс "Отдых" снимаем отметку и делаем не активным
            ch_relax.prop('checked', false).prop('disabled', true);

            if (data.status == 200) {
            } else {
            }
            load.fadeOut(100);
        })
        .fail(function (data) {
            res.html('Fail<br>' + JSON.stringify(data));
            load.fadeOut(100);
        });
}

/**
 * Страница "Time Трекер"
 * ======================
 * При выборе задачи, загружаем информацию по задаче.
 */
function getTaskInfo(obj) {
    var $this = $(obj),
        form = $('.tetr'),
        ch_relax = form.find('[name=ch_relax]'),
        load = $this.next(),
        res = form.find('.res'),
        time_counter = form.find('.time-counter'),
        project = form.find('[name=projects]'),
        time_out = form.find('.task-total-work-time .time-out'),
        total_time = form.find('.total-time'),
        btn_start = form.find('[name=stop]'),
        table = form.find('.table tbody'),
        tt = form.find('.total-time'),
        s = 0,
        Data = {};

    res.html('result');

    // Опустошаем таблицу
    table.html(tr_empty);

    //            ch_relax = form.find('[name=ch_relax]'),
    //            // Чекбокс "Отдых" снимаем отметку и делаем не активным
    //            ch_relax.prop('checked',false).prop('disabled',true);

    /**
     * Если в списке задач ничего не выбрано
     * то обнуляем счетчики
     */
    if ($this.val() == '') {
        // Деактивируем кнопку "Старт"
        btn_start.prop('disabled', true);

        // Чекбокс "Отдых" снимаем отметку и делаем не активным
        ch_relax.prop('checked', false).prop('disabled', true);

        time_out.find('.days').html(zero_time);
        time_out.find('.hs').html(zero_time);
        time_out.find('.ms').html(zero_time);
        time_out.find('.ss').html(zero_time);

        // Обнуляем данные суточного счетчика
        time_counter.find('.hs').html(zero_time);
        time_counter.find('.ms').html(zero_time);
        time_counter.find('.ss').html(zero_time);

        // Обнуляем время (по трачено времени на задачу)
        total_time.find('.hs').html(zero_time);
        total_time.find('.ms').html(zero_time);
        total_time.find('.ss').html(zero_time);

        return;
    }

    Data['project_id'] = project.find('option:selected').val();
    Data['task_id'] = $this.val();

    //    cl(Data);
    //    return;

    $.ajax({
        url: $this.attr('data-url'),
        type: $this.attr('method'),
        dataType: 'json',
        cashe: 'false',
        data: Data,
        beforeSend: function () {
            load.fadeIn(100);
        },
    })
        .done(function (data) {
            //            res.html('Done<br>'+JSON.stringify(data));
            LoadAlert(data.header, data.message, 3000, data.type_message);

            time_out.find('.days').html(data.days);
            time_out.find('.hs').html(data.hours);
            time_out.find('.ms').html(data.minutes);
            time_out.find('.ss').html(data.seconds);

            // Если что то выбралось, то вставляем строки tr в table
            if (data.row != '') table.html(data.row);

            // По считаем общее время работы на сегодня
            table.find('tr').each(function () {
                // Если таблица отчета пуста
                if ($(this).attr('class') == 'empty') return false;
                s +=
                    Number($(this).find('.work-time').attr('data-hs')) *
                    60 *
                    60;
                s += Number($(this).find('.work-time').attr('data-ms')) * 60;
                s += Number($(this).find('.work-time').attr('data-ss'));
            });

            // Если в таблице что нибудь есть
            if (s != 0) {
                // TF - Time Format
                var TF = toTimeFormat(s);

                // Вставляем собранное время в HTML
                tt.find('.hs').html(TF['h']);
                tt.find('.ms').html(TF['m']);
                tt.find('.ss').html(TF['s']);
            }

            // Деактивируем кнопку "Старт"
            btn_start.prop('disabled', true);

            // Чекбокс "Отдых" снимаем отметку и делаем не активным
            ch_relax.prop('checked', false).prop('disabled', true);

            // =====================================================
            // =====================================================

            // Активируем кнопку "Старт"
            btn_start.prop('disabled', '');

            // Чекбокс "Отдых" снимаем отметку и делаем активным
            ch_relax.prop('checked', false).prop('disabled', false);

            // =====================================================
            // =====================================================

            if (data.status == 200) {
            } else {
            }
        })
        .fail(function (data) {
            res.html('Fail<br>' + JSON.stringify(data));
        })
        .always(function () {
            load.fadeOut(100);
        });
}

/**
 * ===================================================
 * END Страница "Time Трекер"
 */

/**
 * ===================================================
 * Страница "Задачи"
 * ===================================================
 */

/**
 * Страница "Задачи"
 * =================
 * Сбрасыаем страницу
 */
function resetTasks() {
    var form = $('.tsks');

    form.find('select,textarea').val('');
}

/**
 * ===================================================
 * END Страница "Задачи"
 * ===================================================
 */

/**
 * ===================================================
 * Страница "Проекты"
 * ===================================================
 */

/**
 * Страница "Проекты"
 * ==================
 * Сбрасыаем страницу
 */
function resetProject() {
    var form = $('.prts');

    form.find('input[type=text],select').val('');
}

/**
 * ===================================================
 * END Страница "Проекты"
 * ===================================================
 */

//if (!('serviceWorker' in navigator)) {
//  cl('Браузер НЕ поддерживает сервис-воркеры');
//}else{
//    cl('Браузер ПОДДЕРЖИВАЕТ сервис-воркеры');
//}
//
//if (!('PushManager' in window)) {
//  cl('Браузер НЕ поддерживает push-уведомления');
//}else{
//    cl('Браузер ПОДДЕРЖИВАЕТ push-уведомления');
//}

if (!('Notification' in window)) {
    cl(
        'Ваш браузер не поддерживает HTML Notifications, его необходимо обновить.'
    );
} else {
    cl('Браузер HTML Notifications ПОДДЕРЖИВАЕТ');
}

// ================================================
// ==== Notifications =============================
function sendNotification(title, options) {
    var notification = new Notification(title, options);

    function clickFunc() {
        $('[name=stop]').trigger('click');
    }

    notification.onclick = clickFunc;
}
function sendNotification2(title, options) {
    cl('sendNotification запущен');
    var notification = new Notification(title, options);

    Notification.requestPermission(function (permission) {
        // переменная permission содержит результат запроса
        console.log('Результат запроса прав:', permission);
    });

    // Проверим, поддерживает ли браузер HTML5 Notifications
    if (!('Notification' in window)) {
        alert(
            'Ваш браузер не поддерживает HTML Notifications, его необходимо обновить.'
        );
    }

    // Проверим, есть ли права на отправку уведомлений
    else if (Notification.permission === 'granted') {
        //        cl('else granted');
        // Если права есть, отправим уведомление
        var notification = new Notification(title, options);

        function clickFunc() {
            alert('Пользователь кликнул на уведомление');
        }

        notification.onclick = clickFunc;
    }

    // Если прав нет, пытаемся их получить
    else if (Notification.permission !== 'denied') {
        //        cl('else denied');
        Notification.requestPermission(function (permission) {
            // Если права успешно получены, отправляем уведомление
            if (permission === 'granted') {
                var notification = new Notification(title, options);
            } else {
                alert('Вы запретили показывать уведомления'); // Юзер отклонил наш запрос на показ уведомлений
            }
        });
    } else {
        //        cl('Самый последний else');
        // Пользователь ранее отклонил наш запрос на показ уведомлений
        // В этом месте мы можем, но не будем его беспокоить. Уважайте решения своих пользователей.
        var notification = new Notification(title, options);
    }
}
