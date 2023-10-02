/**
 * Переменная (zero) задается в AdminAppAsset через init()
 */

$(function () {
    /**
     * ===================================================
     * Страница "Time Трекер"
     * ===================================================
     */

    /**
     * Страница "Time Трекер"
     * ======================
     * Поля катсомного времени
     * -----------------------
     * Если при вводе любое поле будет не пустым,
     * то кнопку сохранения кастомного времени делаем активной.
     */
    $('.tetr .custom-time input').on('input', function () {
        var $this = $(this),
            form = $('.tetr .custom-time'),
            disabled = true;

        form.find('input').each(function () {
            if ($(this).val() != '' && disabled !== false) {
                disabled = false;
            }
        });

        form.find('button[name=custom_time_save]').prop('disabled', disabled);
        if ($this.val().length == 2) {
            if ($this.next().attr('name') != 'custom_time_save') {
                $this.next().focus();
            }
        }
    });

    /**
     * Страница "Time Трекер"
     * ======================
     * Кнопка "Старт/Стоп"
     * -------------------
     * Меняем атрибут data-type stop или do
     * Если stop, то обнуляем счетчик
     * Если do, запускаем счетчик
     */
    $('.tetr [name=stop], .tetr button[name=custom_time_save]').on(
        'click',
        function () {
            var $this = $(this),
                form = $('.tetr');

            if ($this.attr('name') == 'custom_time_save') {
                $this = form.find('[name=stop]');
            }

            var res = form.find('.res'),
                load = $this.find('img'),
                btn_pause = $('[name=pause]'),
                btn_custom_save = form.find('button[name=custom_time_save]'),
                // value relax
                v_r = form.find('[name=ch_relax]'),
                ch_relax = form.find('[name=ch_relax]'),
                time_counter = form.find('.time-counter'),
                project_time_out = form.find('.total-work-time .time-out'),
                task_time_out = form.find('.task-total-work-time .time-out'),
                project = form.find('[name=projects]'),
                task = form.find('[name=tasks]'),
                table = form.find('.table tbody'),
                tt = form.find('.total-time'),
                ProjectTime = {},
                TaskTime = {},
                Time = {},
                s = 0,
                custom_time = 0,
                change_time = false,
                label_ch_relax = $('.tetr').find('[for=ch_relax]');

            res.html('result');

            // Проверка, выбрана ли задача
            //        if(task.val() == ''){
            //            LoadAlert('Внимание','Выберите задачу',3000,'warning');
            //            return;
            //        }

            // Закрываем все оповещающие окна
            cea();

            // Чекбокс "Отдых" делаем не активным
            v_r.prop('disabled', true);

            // Если надо стартануть счетчик
            if ($this.attr('data-type') == 'stop') {
                $this.attr('data-type', 'do');
                $this.find('span').html('Стоп');
                btn_pause
                    .attr('data-type', 'do')
                    .prop('disabled', '')
                    .html('Пауза');

                // Деактивируем выпадающие списки
                project.prop('disabled', true);
                task.prop('disabled', true);

                label_ch_relax.css({ color: 'rgba(55,58,60,.4)' });

                // Обнуляем данные счетчика
                //            time_counter.find('.hs').html(zero_time);
                //            time_counter.find('.ms').html(zero_time);
                //            time_counter.find('.ss').html(zero_time);
            } else {
                // Если надо остановить счетчик
                $this.attr('data-type', 'stop');
                $this.find('span').html('Старт');
                btn_pause.attr('data-type', 'pause').html('Пауза');

                ProjectTime['days'] = Number(
                    project_time_out.find('.days').html()
                );
                ProjectTime['hours'] = Number(
                    project_time_out.find('.hs').html()
                );
                ProjectTime['minutes'] = Number(
                    project_time_out.find('.ms').html()
                );
                ProjectTime['seconds'] = Number(
                    project_time_out.find('.ss').html()
                );

                TaskTime['days'] = Number(task_time_out.find('.days').html());
                TaskTime['hours'] = Number(task_time_out.find('.hs').html());
                TaskTime['minutes'] = Number(task_time_out.find('.ms').html());
                TaskTime['seconds'] = Number(task_time_out.find('.ss').html());

                label_ch_relax.css({ color: 'rgba(55,58,60)' });

                /**
                 * По ключу project получаем в PHP
                 * строку JSON для файла с общим временем проекта
                 */
                Time['project'] = {};

                // Код проекта
                Time['project'][
                    project.find('option:selected').attr('data-code')
                ] = calc_up(ProjectTime);
                // ID проекта
                Time['project']['id'] = project.val();

                /**
                 * По ключу task в PHP
                 * получаем строку JSON для поля БД - общее время работы над задачей
                 */
                Time['task'] = {};
                Time['task']['json_time'] = calc_up(TaskTime);
                Time['task']['id'] = task.find('option:selected').val();

                // Время от начала до нажатия на стоп
                Time['working_hours'] =
                    Number(time_counter.find('.hs').html()) * 60 * 60 +
                    Number(time_counter.find('.ms').html()) * 60 +
                    Number(time_counter.find('.ss').html());
                time_counter.find('input').each(function () {
                    var this_input = $(this);
                    if (this_input.val() != '') {
                        if (this_input.attr('name') == 'hs') {
                            custom_time += Number(this_input.val()) * 60 * 60;
                        }
                        if (this_input.attr('name') == 'ms') {
                            custom_time += Number(this_input.val()) * 60;
                        }
                        if (this_input.attr('name') == 'ss') {
                            custom_time += Number(this_input.val());
                        }
                    }
                });
                if (custom_time > 0) {
                    Time['working_hours'] = custom_time;
                }

                /**
                 * Если чекбокс "Отдых" не отмечен,
                 * значит время работы надо сохранить в БД.
                 * Иначе:
                 * Если счётчик считает время отдыха,
                 * то время отдыха в БД добавлять не нужно.
                 */
                if (
                    !ch_relax.is(':checked') ||
                    /*
                     * Время отдыха Софториум нужно сохранять.
                     * Потом в интерфейсе нужно сделать чекбокс - "сохранять время отдыха"
                     * и в этом if'e уже проверять этот чекбокс, а не код проекта.
                     */
                    project.find('option:selected').attr('data-code') ==
                        'cr_softorium'
                ) {
                    change_time = true;
                } else {
                    // Кнопку "Пауза" делаем неактивной
                    btn_pause.prop('disabled', true);
                    // Три выпадающих списка делаем активными
                    project.prop('disabled', false);
                    task.prop('disabled', false);
                    // Чекбокс "Отдых" отключаем
                    v_r.prop('checked', false).prop('disabled', false);
                }
            }

            if (change_time) {
                //        if(0){
                //            cl('Вносим время в БД');return;

                //            console.log(JSON.stringify(Time));
                //            return;

                $.ajax({
                    url: $this.attr('data-url'),
                    type: $this.attr('method'),
                    cashe: 'false',
                    dataType: 'json',
                    data: Time,
                    beforeSend: function () {
                        load.fadeIn(100);
                    },
                })
                    .done(function (data) {
                        res.html(
                            'Done<br>' + prettyPrintJson.toHtml(data) + '</pre>'
                        );
                        LoadAlert(
                            data.header,
                            data.message,
                            live,
                            data.type_message
                        );
                        if (data.status == 200) {
                            project_time_out.find('.days').html(data.days);
                            project_time_out.find('.hs').html(data.hours);
                            project_time_out.find('.ms').html(data.minutes);
                            project_time_out.find('.ss').html(data.seconds);

                            task_time_out.find('.days').html(data.t_days);
                            task_time_out.find('.hs').html(data.t_hs);
                            task_time_out.find('.ms').html(data.t_ms);
                            task_time_out.find('.ss').html(data.t_ss);

                            // Делаем активным поле выбора проекта
                            project.prop('disabled', '');
                            // Делаем активным поле выбора задачи
                            task.prop('disabled', '');
                            // Деактивируем кнопку "Пауза"
                            btn_pause.prop('disabled', true);

                            if (
                                typeof table.find('.empty').html() !==
                                'undefined'
                            ) {
                                // Вставляем первую строку
                                table.html(data.row);
                            } // Добавляем строку в начало таблицы к существующим строкам
                            else table.prepend(data.row);

                            // По считаем общее время работы на сегодня
                            table.find('tr').each(function () {
                                // Если таблица отчета пуста
                                if ($(this).attr('class') == 'empty')
                                    return false;
                                s +=
                                    Number(
                                        $(this)
                                            .find('.work-time')
                                            .attr('data-hs')
                                    ) *
                                    60 *
                                    60;
                                s +=
                                    Number(
                                        $(this)
                                            .find('.work-time')
                                            .attr('data-ms')
                                    ) * 60;
                                s += Number(
                                    $(this).find('.work-time').attr('data-ss')
                                );
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

                            // Обнуляем рабочий счётчик
                            time_counter.find('.hs').html('00');
                            time_counter.find('.ms').html('00');
                            time_counter.find('.ss').html('00');

                            // Опустошаем катомные поля времени
                            time_counter.find('input').val('');

                            // Кнопку сохранения кастомного времени делаем неактивной
                            btn_custom_save.prop('disabled', true);
                        } else {
                            popUp(
                                '.tetr',
                                'Done !200<br>' + JSON.stringify(data),
                                'danger'
                            );
                        }

                        v_r.prop('disabled', false);
                    })
                    .fail(function (data) {
                        res.html('Fail<br>' + JSON.stringify(data));
                        LoadAlert('Error', 'Ошибка PHP', live, 'error');
                    })
                    .always(function () {
                        load.fadeOut(100);
                    });
            }
        }
    );

    /**
     * Страница "Time Трекер"
     * ======================
     * Кнопка "Пауза"
     * --------------
     * Меняем атрибут data-type stop или do
     * Если stop, то обнуляем счетчик
     * Если do, запускаем счетчик
     */
    $('[name=pause]').on('click', function () {
        var $this = $(this),
            btn_stop = $('[name=stop]');

        if (btn_stop.attr('data-type') == 'stop') return;

        if ($this.attr('data-type') == 'pause')
            $this.attr('data-type', 'do').html('Пауза');
        else $this.attr('data-type', 'pause').html('Продолжить');
    });

    /**
     * Страница "Time Трекер"
     * ============================
     * Калькулятор времени
     */
    $('button[name=calc_time]').on('click', function () {
        calc_up();
    });

    /**
     * Страница "Time Трекер"
     * ======================
     * Выпадающий список "Выберите проект"
     */
    $('.tetr [name=projects]').on('change', function () {
        reloadTasks(this);
    });

    /**
     * Страница "Time Трекер"
     * ======================
     * Кнопка "Обновить список задач"
     */
    $('.tetr .tasks svg').on('click', function () {
        reloadTasks('.tetr [name=projects]');
    });

    /**
     * Страница "Time Трекер"
     * ======================
     * Выпадающий список "Выберите задачу"
     */
    $('.tetr [name=tasks]').on('change', function () {
        getTaskInfo(this);
    });

    /**
     * Страница "Time Трекер"
     * ======================
     * Чекбокс "Отдых"
     * ---------------
     * Если чекбокс не отмечен, то label->текст сделаем красным,
     * иначе сделаем цвет label->текст по умолчанию
     */
    $('.tetr [name=ch_relax]').change(function () {
        if ($(this).is(':checked')) {
            $(this).next().css({ color: 'red' });
        } else {
            $(this).next().css({ color: 'rgba(55,58,60)' });
        }
    });

    /**
     * ===================================================
     * END Страница "Time Трекер"
     * ===================================================
     */

    /**
     * ===================================================
     * Страница "Отправка Email"
     * ===================================================
     */

    /**
     * Страница "Отправка Email"
     * =========================
     * Кнопка "Отправить Email"
     */
    $('.eml .send-mail').on('click', function () {
        var $this = $(this),
            form = $('.eml'),
            res = form.find('.res'),
            load = $this.find('img'),
            type_mail = $this.attr('name'),
            Data = {};

        // Убираем с экрана все попыещающие окна
        cea();

        Data['send_mail'] = true;
        Data['type_mail'] = type_mail;

        $.ajax({
            url: $this.attr('action'),
            type: $this.attr('method'),
            cashe: 'false',
            dataType: 'json',
            data: Data,
            beforeSend: function () {
                load.fadeIn(100);
            },
        })
            .done(function (data) {
                //            res.html('Done<br>'+JSON.stringify(data));

                LoadAlert(data.header, data.message, live, data.type_message);
                if (data.status == 200) {
                } else {
                    popUp(
                        '.eml',
                        'Done !200<br>' + JSON.stringify(data),
                        'danger'
                    );
                }

                load.fadeOut(100);
            })
            .fail(function (data) {
                //            res.html('Fail<br>'+JSON.stringify(data));
                popUp('.eml', 'Fail<br>' + JSON.stringify(data));
                LoadAlert('Error', 'Ошибка PHP', live, 'error');
                load.fadeOut(100);
            });
    });

    /**
     * ===================================================
     * END Страница "Отправка Email"
     * ===================================================
     */

    /**
     * Страница "Проекты/задачи"-список проектов
     * =========================================
     * Кнопки "Запос оплаты","Подтвердить оплату"
     */
    $('.pts .p-row button').on('click', function () {
        var ttihs = $(this),
            wrap = $('.pts'),
            tr = ttihs.parent().parent(),
            load = tr.find('img.loading'),
            res = wrap.find('.res'),
            type = ttihs.attr('data-type'),
            Data = {};

        //        load.fadeIn(100);
        //        return;

        // Запрос оплаты
        if (type == 'payment_request') {
            var cost = tr.find('.t-nopaid').find('span').attr('data-cost');
        }
        // Подтверждение оплаты
        if (type == 'proof_payment') {
            var cost = tr.find('.t-waitpay').find('span').attr('data-cost');
        }

        // Тип кнопки
        Data['request_type'] = type;

        // Получаем ID проекта
        Data['project_id'] = tr.attr('data-project-id');

        cl(Data);
        //        return;

        $.ajax({
            url: tr.attr('data-url'),
            type: tr.attr('data-type-method'),
            dataType: 'json',
            cashe: 'false',
            data: Data,
            beforeSend: function () {
                load.fadeIn(100);
            },
        })
            .done(function (data) {
                //            res.html('Done<br>'+JSON.stringify(data));
                LoadAlert(data.header, data.message, 4000, data.type_message);

                if (data.status == 200) {
                    // Запрос оплаты
                    if (type == 'payment_request') {
                        /**
                         * Блок "Не оплачено"
                         * удаляем сумму и скрываем кнопку
                         */
                        tr.find('.t-nopaid span').attr('data-cost', '');
                        tr.find('.t-nopaid span').html('');
                        ttihs.fadeOut(100);

                        /**
                         * Блок "Подтверждение оплаты"
                         * вставляем сумму и показываем кнопку
                         */
                        tr.find('.t-waitpay span').attr('data-cost', cost);
                        tr.find('.t-waitpay span').html(cost);
                        tr.find('.t-waitpay button').fadeIn(100);
                    }
                    // Подтверждение оплаты
                    if (type == 'proof_payment') {
                        /**
                         * Блок "Подтверждение оплаты"
                         * удаляем сумму и скрываем кнопку
                         */
                        tr.find('.t-waitpay span').attr('data-cost', '');
                        tr.find('.t-waitpay span').html('');
                        ttihs.fadeOut(100);

                        /**
                         * Блок "Оплачено"
                         * вставляем сумму
                         */
                        tr.find('.t-paid span').attr('data-cost', cost);
                        tr.find('.t-paid span').html(cost);
                    }
                } else {
                }
                load.fadeOut(100);
            })
            .fail(function (data) {
                res.html('Fail<br>' + JSON.stringify(data));
                load.fadeOut(100);
            });
    });

    /**
     * Страница "Проекты/задачи"-список проектов
     * =========================================
     * Невидимый блок для включения поля редактирования тарифа
     * -------------------------------------------------------
     * При клике по невидимому блоку, скрываем span тарифа
     * и показываем поле для редактирвоания тарифа
     */
    $('.pts .table .go-edit').on('click', function () {
        var ttihs = $(this),
            wrap = $('.pts'),
            table = wrap.find('.table'),
            p = ttihs.parent(),
            rate = p.find('span'),
            w_edit_rate = p.find('.w-edit-rate'),
            r_input = w_edit_rate.find('input'),
            all_w_edit_rate = table.find('.w-edit-rate'),
            all_wrap_rate_project_span = table.find('.wrap-rate-project span');

        all_w_edit_rate.hide(1);
        all_wrap_rate_project_span.show(1);
        table.find('svg.er-close').fadeOut(10);
        table.find('svg.er-save').fadeIn(10);

        rate.fadeOut(100, function () {
            w_edit_rate.fadeIn(100);

            // Ставим на поле ввода фокус и курсор в конец строки
            r_input.focus();
            r_input.val('');
            r_input.val(r_input.attr('data-rate'));
        });
    });

    /**
     * Страница "Проекты/задачи"-список проектов
     * =========================================
     * Кнопка "Закрыть редактирование тарифа проекта" (красный квадратик с крестиком)
     */
    $('.pts .table svg.er-close').on('click', function () {
        var ttihs = $(this),
            p = ttihs.parent().parent(),
            rate = p.find('span'),
            w_edit_rate = p.find('.w-edit-rate'),
            r_input = w_edit_rate.find('input');

        w_edit_rate.fadeOut(100, function () {
            rate.html(r_input.attr('data-rate')).fadeIn();
            r_input.val(r_input.attr('data-rate'));
            ttihs.fadeOut(100, function () {
                w_edit_rate.find('svg.er-save').fadeIn(100);
            });
        });
    });

    /**
     * Страница "Проекты/задачи"-список проектов
     * =========================================
     * Редактирование поля тарифа проекта
     */
    $('.pts .table .wrap-rate-project input').on('keyup', function () {
        var ttihs = $(this),
            p = ttihs.parent(),
            rate = p.find('span'),
            save = p.find('svg.er-save'),
            close = p.find('svg.er-close');

        setTimeout(function () {
            if (ttihs.val() == '') {
                save.fadeOut(100, function () {
                    close.fadeIn(100);
                });
            } else {
                close.fadeOut(100, function () {
                    save.fadeIn(100);
                });
            }
        }, 200);
    });

    /**
     * Страница "Проекты/задачи"-список проектов
     * =========================================
     * Кнопка "Сохранить тариф проекта" (зеленый квадратик с галочкой)
     */
    $('.pts .table svg.er-save').on('click', function () {
        var ttihs = $(this),
            wrap = $('.pts'),
            res = wrap.find('.res'),
            p = ttihs.parent().parent(),
            load = p.find('img.loading'),
            rate = p.find('span'),
            w_edit_rate = p.find('.w-edit-rate'),
            r_input = p.find('input'),
            Data = {};

        /**
         * Если поле пустое, прячем поле для редактирования,
         * вставляем в поле не редактированное значение тарифа,
         * и показываем span тарифа
         */
        if (r_input.val() == '' || r_input.val() == r_input.attr('data-rate')) {
            w_edit_rate.fadeOut(100, function () {
                rate.fadeIn(100);
                r_input.val(r_input.attr('data-rate'));
            });
            return;
        }

        // Получаем ID проекта
        Data['project_id'] = p.find('.w-edit-rate').attr('data-pid');
        Data['value'] = r_input.val();

        cl(Data);
        //        return;

        $.ajax({
            url: r_input.attr('data-url'),
            type: r_input.attr('data-type-method'),
            dataType: 'json',
            cashe: 'false',
            data: Data,
            beforeSend: function () {
                load.fadeIn(100);
            },
        })
            .done(function (data) {
                //            res.html('Done<br>'+JSON.stringify(data));
                res.html(
                    'Done<br><pre>' + prettyPrintJson.toHtml(data) + '</pre>'
                );
                LoadAlert(data.header, data.message, 4000, data.type_message);

                if (data.status == 200) {
                    r_input.attr('data-rate', Data['value']);
                    w_edit_rate.fadeOut(100, function () {
                        rate.html(r_input.val()).fadeIn(100);
                        r_input.val(r_input.attr('data-rate'));
                    });
                } else {
                }
                load.fadeOut(100);
            })
            .fail(function (data) {
                //            res.html('Fail<br>'+JSON.stringify(data));
                res.html(
                    'Fail<br><pre>' + prettyPrintJson.toHtml(data) + '</pre>'
                );
                load.fadeOut(100);
            });
    });

    /**
     * Страница "Настройки WebMaster"
     * ==============================
     * Плавающий placeholder
     */
    var $inputItem = $('.js-inputWrapper');
    $inputItem.length &&
        $inputItem.each(function () {
            var $this = $(this),
                $input = $this.find('.formRow--input'),
                placeholderTxt = $input.attr('placeholder'),
                $placeholder;

            $input.after(
                '<span class="placeholder">' + placeholderTxt + '</span>'
            ),
                $input.attr('placeholder', ''),
                ($placeholder = $this.find('.placeholder')),
                $input.val().length
                    ? $this.addClass('active')
                    : $this.removeClass('active'),
                $input
                    .on('focusout', function () {
                        $input.val().length
                            ? $this.addClass('active')
                            : $this.removeClass('active');
                    })
                    .on('focus', function () {
                        $this.addClass('active');
                    });
        });

    /**
     * Странци "Настройки WebMaster"
     * =============================
     * Кнопка "Сохранить"
     */
    $('.segs .save-settings').on('click', function () {
        var $this = $(this),
            wrap = $('.segs'),
            load = $this.find('img.loading'),
            res = wrap.find('.res'),
            $this_name = $this.attr('name'),
            Data = {};

        // Собираем поля только текущего таба
        $('[data-tab=' + $this_name + '] :input')
            .serializeArray()
            .map(function (x) {
                var start = x.name.indexOf('[');
                var end = x.name.indexOf(']');

                // Если атрибут "name" не содержит в себе квадратных скобок
                if (start == '-1') {
                    /**
                     * Поле, в котором не указан тип настройки,
                     * просто добавим в общий объект
                     */
                    Data[x.name] = x.value;
                } else {
                    // Получаем индекс первой квадратной скобки
                    var name = x.name.slice(start + 1, end);
                    // Получаем индекс второй квадратной скобки
                    var s_type = x.name.slice(0, start);
                    // Делаем имя типа настройки в нижний регистр
                    s_type = s_type.toLowerCase();

                    // Если в объекте нет текущего типа настройки
                    if (typeof Data[s_type] === 'undefined') {
                        /**
                         * Добавим новый тип настройки,
                         * и тут же добавим в него текущую настройку.
                         */
                        Data[s_type] = {};
                        Data[s_type][name] = x.value;
                    } else {
                        /**
                         * Если в объекте уже есть тип настройки,
                         * то просто добавим к нему текущую настройку.
                         */
                        Data[s_type][name] = x.value;
                    }
                }
            });

        cl(Data);
        //        return;

        $.ajax({
            url: $this.attr('data-url'),
            type: $this.attr('data-type-method'),
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

                if (data.status == 200) {
                } else {
                }
                load.fadeOut(100);
            })
            .fail(function (data) {
                res.html('Fail<br>' + JSON.stringify(data));
                load.fadeOut(100);
            });
    });

    /**
     * Странци "Настройки WebMaster"
     * Вкладка "Статистика"
     * =============================
     * Кнопка "Получить курс доллара"
     */
    $('.segs .get-rate').on('click', function () {
        var $this = $(this),
            wrap = $('.segs'),
            load = $this.find('img.loading'),
            res = wrap.find('.res'),
            cc = wrap.find('.current-curse span'),
            Data = {};

        res.html('result');

        //        cl(Data);
        //        return;

        $.ajax({
            url: $this.attr('data-url'),
            type: $this.attr('data-type-method'),
            dataType: 'json',
            cashe: false,
            data: Data,
            beforeSend: function () {
                load.fadeIn(100);
            },
        })
            .done(function (data) {
                //            res.html('Done<br>'+JSON.stringify(data));
                LoadAlert(data.header, data.message, 3000, data.type_message);

                cc.html(data.current_curs);

                if (data.status == 200) {
                } else {
                }
                load.fadeOut(100);
            })
            .fail(function (data) {
                res.html('Fail<br>' + JSON.stringify(data));
                load.fadeOut(100);
            });
    });

    /**
     * Странци "Расёты"
     * ================
     * Поле ввода "Введите цену"
     * -------------------------
     * При вводе суммы, получим вермя,
     * которое нужно по тратить на зарабатывание введёной суммы.
     */
    $('.wrap-calculations [name=price]').on('input', function () {
        var $this = $(this),
            wrap = $('.wrap-calculations'),
            time_res = wrap.find('.time-res'),
            course = Number(wrap.attr('data-course')),
            rate = Number(wrap.attr('data-rate')),
            $this_val = $this.val().replace(/[ ]*/g, '');

        var cost_second = (course * rate) / 60 / 60; // Стоимость одной секунды
        var sec_input_sum = Math.round(Number($this_val) / cost_second); // Секунд в введёной сумме

        var Time = toTimeFormat(sec_input_sum);

        time_res.html(Time['h'] + ':' + Time['m'] + ':' + Time['s']);
    });

    /**
     * Странци "Расёты"
     * ================
     * Поле ввода "Введите время"
     * -------------------------
     * При вводе времени, получим стоимость этого времени по текущему курсу.
     */

    //    $('.wrap-calculations .time').on(function(){});
    //    $('.wrap-calculations .time').on('input',function(){
    //        cl('inputttt');return;
    //        var $this = $(this),
    //            wrap = $('.wrap-calculations'),
    //            price_res = wrap.find('.price-res'),
    //            course = Number(wrap.attr('data-course')),
    //            val_arr = $this.val().split(':');
    //
    //        cl(val_arr);
    //
    //        var cost_time = timeNumbersToCost(val_arr,course);
    //        price_res.html(
    //            number_format(cost_time, 2, '.', ' ')
    //            +'<br>По курсу: <span style="color:red;">'+course+'</span> $');
    //
    //
    //    });

    /**
     * Странци "Расёты"
     * ================
     * Поле ввода "Расчёт стоимости от времени"
     * ----------------------------------------
     * При вводе времени, получим сумму стоимости.
     */
    $('.wrap-calculations .time')
        .mask('99:99:99', {
            completed: function () {
                var $this = $(this),
                    wrap = $('.wrap-calculations'),
                    price_res = wrap.find('.price-res'),
                    course = Number(wrap.attr('data-course')),
                    rate = Number(wrap.attr('data-rate')),
                    val_arr = $this.val().split(':');

                var rub = timeNumbersToCost(val_arr, course, rate);
                var dollars = timeNumbersToCost(val_arr, course, rate, true);

                price_res.html(
                    '<br><b><span style="color: red">' +
                        number_format(dollars, 2, '.', ' ') +
                        '</span> $</b>' +
                        '<br><b><span style="color: #04AC00">' +
                        number_format(rub, 2, '.', ' ') +
                        '</span> р</b>.' +
                        '<br>По курсу: <span style="color:red;">' +
                        course +
                        '</span> $'
                );
            },
        })
        .on('keyup', function () {
            var $this = $(this),
                wrap = $('.wrap-calculations'),
                price_res = wrap.find('.price-res');

            if ($this.val() == '__:__:__') {
                price_res.html('0.00');
                return;
            }
        });

    /**
     * Странци "Расёты"
     * ================
     * Поле ввода "Калькулятор времени"
     * -------------------------------
     * При вводе времени, в список добавляем строки с введённым временем.
     */
    $('.wrap-calculations .time-calc').mask('99:99:99', {
        completed: function () {
            var wrap = $('.wrap-calculations'),
                one_item = wrap.find('[name=one_item]'),
                c_price_res = wrap.find('.c-price-res');

            if (one_item.prop('checked')) {
                c_price_res.html('0.00');
                $('.rows-time').html('');
            }

            $('.rows-time').append(
                '<li><div>' +
                    $(this).val() +
                    '</div><span class="del">Х</span></li>'
            );

            if (one_item.prop('checked')) {
                setTimeout(function () {
                    $('button.calculate-cost').trigger('click');
                }, 100);
            }
        },
    });

    /**
     * Странци "Расёты"
     * ===============
     * Красный кружочек удаления добавленных строк
     */
    $('.rows-time').on('click', 'li span.del', function () {
        $(this).parent().remove();
    });

    /**
     * Странци "Расёты"
     * ===============
     * Кнопка "Расчитать стоимость"
     * ----------------------------
     * Расчёт времени по добавленным строкам
     */
    $('button.calculate-cost').on('click', function () {
        var $this = $(this),
            wrap = $('.wrap-calculations'),
            c_price_res = wrap.find('.c-price-res'),
            course = Number(wrap.attr('data-course')),
            rate = Number(wrap.attr('data-rate')),
            in_dollars = 0,
            rub = 0,
            ss = 0,
            rows = 0;

        $('.rows-time li').each(function () {
            rows = $(this).find('div').html().split(':');
            rub += Number(timeNumbersToCost(rows, course, rate));
            in_dollars += Number(timeNumbersToCost(rows, course, rate, true));

            h = Number(rows[0]);
            m = Number(rows[1]);
            s = Number(rows[2]);

            ss += h * 60 * 60 + m * 60 + s;
        });

        var Time = toTimeFormat(ss);

        c_price_res.html(
            '--------------------------' +
                '<br><b><span style="color: red">' +
                number_format(in_dollars, 2, '.', ' ') +
                '</span> $</b>' +
                '<br><b><span style="color: #04AC00">' +
                number_format(rub, 2, '.', ' ') +
                '</span> р</b>.' +
                '<br>Общее время: <span style="color: #025aa5;">' +
                Time['h'] +
                ':' +
                Time['m'] +
                ':' +
                Time['s'] +
                '</span>' +
                '<br>По курсу: <span style="color:red;">' +
                course +
                '</span> $'
        );
    });

    /**
     * ===================================================
     * Тестовые функции
     * ===================================================
     */

    $('.test,[name=but]').on('click', function () {
        var $this = $(this),
            load = $('img.loading'),
            res = $('.res'),
            Data = {};

        //cl();// pathname: "/admin/settings-webmaster"

        //        if(location.pathname.indexOf('settings-webmaster2') != '-1'){
        //            cl('Есть');
        //        } else cl('Нету');

        sendNotification('Отдых закончен', {
            body: 'Начнём работу!',
            dir: 'auto',
            icon: 'images/work2.jpg',
        });

        return;

        // Убираем с экрана все попыещающие окна
        cea();

        //        cl(Data);
        //        return;

        $.ajax({
            url: $this.attr('data-url'),
            type: $this.attr('data-ajax-method'),
            //            url:'post',
            //            type:'http://cbrates.rbc.ru/tsv/840/2019/11/01.tsv',
            cashe: 'false',
            dataType: 'json',
            data: Data,
            beforeSend: function () {
                load.fadeIn(100);
            },
        })
            .done(function (data) {
                res.html('Done<br>' + JSON.stringify(data));

                //            LoadAlert(data.header,data.message,live,data.type_message);
                if (data.status == 200) {
                } else {
                    //                popUp('.stcs','Done !200<br>'+JSON.stringify(data),'danger');
                }

                load.fadeOut(100);
            })
            .fail(function (data) {
                res.html('Fail<br>' + JSON.stringify(data));
                //            LoadAlert('Error','Ошибка PHP',live,'error');
                load.fadeOut(100);
            });
    });

    $('.test2').on('click', function () {
        var $this = $(this),
            res = $('.res'),
            load = $this.find('img'),
            div = $('.div'),
            ttt = $('[name=ttt]'),
            Data = {};

        res.html('');
        //        Data['csrf'] = $('meta[name=csrf-token]').attr('content');
        //        Data['s'] = $this.attr('data-c');
        Data['s'] = $('[name=cc]').val();

        //        console.log($this.attr('data-url'));
        console.log(Data);
        //        return;

        $.ajax({
            url: $this.attr('data-url'),
            type: 'post',
            dataType: 'json',
            cashe: 'false',
            data: Data,
            beforeSend: function () {
                load.fadeIn(100);
            },
        })
            .done(function (data) {
                res.html('Done<br>' + JSON.stringify(data));
                div.html(data.m);
                ttt.val(data.m);

                load.fadeOut(100);
            })
            .fail(function (data) {
                res.html('Fail<br>' + JSON.stringify(data));

                load.fadeOut(100);
            });
    });
}); // JQuery
