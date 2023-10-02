<?php

use common\components\Debugger as d;
use yii\helpers\Html;
use shadow\widgets\CKEditor;
use backendold\models\Projects;
use backendold\helpers\SArrayHelper;

$this->title = 'Прокеты';



?>
<div class="wrap prts">
    <div class="text-center h3 header">
        <?= Html::encode($this->title) ?>
    </div>
    <br>

    <? d::res() ?>

    <div class="row">
        <div class="col-md-4 col-md-offset-4">

            <!-- Поля проекта -->
            <div class="projects">
                <div class="wrap-color-project dn">
                    <input type="hidden" name="color_project" id="" class="color-project" value="#000000" size="7">
                </div>
                <script type="text/javascript">
//                    $(function() {
//                        $('[name=color_project]').minicolors();
//                    });
                </script>
                <?php // получаем список проектов
                $projects = Projects::find()
                    ->orderBy('name')
                    ->orderBy('sort DESC')
                    ->asArray()->all();
                array_unshift($projects, [
                    'id' => '0',
                    'code' => 'new',
                    'name' => 'Добавить новый проект'
                ]);
                $option_attrs = [];

                foreach ($projects as $pt) {
                    $option_attrs[$pt['id']] = [
                        'data-code' => isset($pt['code']) ? $pt['code'] : '',
                        'data-active' => isset($pt['active']) ? $pt['active'] : '',
                        'data-color' => isset($pt['color']) ? $pt['color'] : '',
                    ];

                    if (isset($pt['active']) and $pt['active'] == '1') {
                        $option_attrs[$pt['id']]['style'] = 'color:#272BFF';
                    }
                    if (isset($pt['active']) and $pt['active'] == '0') {
                        $option_attrs[$pt['id']]['style'] = 'color:red';
                    }

                }
                $items = SArrayHelper::map($projects, 'id', 'name');
                $options = [
                    'prompt' => 'Выберите проект',
                    'class' => 'form-control',
                    'id' => 'projects',
                    'data-url' => 'ajax/get-tasks-by-project2',
                    'data-type-method' => 'post',
                    'options' => $option_attrs,
                ];
                //$items = \yii\helpers\ArrayHelper::merge(['value'=>'text'],$items);
                ?>
                <?= Html::dropDownList('projects', '', $items, $options); ?>
                <?= Html::img('@web/images/animate/loading.gif', ['alt' => 'Загрузка', 'width' => '20', 'class' => 'l-projects loading']) ?>
                <span class="glyphicon glyphicon-trash dn" aria-hidden="true" data-toggle="modal"
                    data-target="#delete-project"></span>
                <?= Yii::$app->view->renderFile('@app/views/site/shortcodes/modal-md.php', [
                    'modal_class' => 'delete-project',
                    'header' => 'Удаление проекта',
                    'body' => 'Вы действительно хотите удалить выбранный проект?',
                    'name_btn_success' => 'delete_project',
                    'btn_success' => 'Удалить',
                    'btn_close' => 'Отмена',
                    'data_url' => 'ajax/delete-project',
                    'method' => 'post'
                ]); ?>
            </div>

            <div class="tasks">
                <select name="task_list" class="form-control" data-url="ajax/get-task-by-project" method="post"
                    disabled>
                    <option value="">Задач пока нет</option>
                    <option value="new_task">Добавить задачу</option>
                </select>
                <? Html::img('@web/images/animate/loading.gif', ['alt' => 'Загрузка', 'width' => '20', 'class' => 'loading']) ?>
                <span class="glyphicon glyphicon-trash dn" aria-hidden="true" data-toggle="modal"
                    data-target="#delete-taks"></span>
                <?= Yii::$app->view->renderFile('@app/views/site/shortcodes/modal-md.php', [
                    'modal_class' => 'delete-taks',
                    'header' => 'Удаление задачи',
                    'body' => 'Вы действительно хотите удалить выбранную задачу?',
                    'name_btn_success' => 'delete_taks',
                    'btn_success' => 'Удалить',
                    'btn_close' => 'Отмена',
                    'data_url' => 'ajax/delete-task',
                    'method' => 'post'
                ]); ?>
            </div>

            <input type="text" name="project_name" class="form-control" placeholder="Имя проекта" disabled />

            <input type="text" name="project_code" class="form-control" placeholder="Код проекта латиницей"
                data-code="<?= '' ?>" disabled />

            <select name="active_project" class="form-control" disabled>
                <option value="">Активность проекта</option>
                <option value="1">Проект включен</option>
                <option value="0">Проект отключен</option>
            </select>
            <!-- /поля проекта -->

        </div>
    </div>

    <div class="row row-tasks dn">
        <div class="col-md-4 col-md-offset-4">
            <input type="text" id="datetimepicker" class="form-control" />
        </div>
    </div>

    <!-- TASKS -->
    <div class="row row-tasks">
        <?
        $datepicker = '<div class="col-md-4 col-md-offset-4">
            <div class="form-group">
                <div class="input-group date" id="datetimepicker_payment_from">
                    <input type="text" class="form-control" placeholder="0000-00-00">
                    <span class="input-group-addon">
                    <span class="fa fa-calendar"></span></span>
                </div>
                <div class="input-group date" id="datetimepicker_payment_to">
                    <input type="text" class="form-control" placeholder="0000-00-00">
                    <span class="input-group-addon">
                    <span class="fa fa-calendar"></span></span>
                </div>
            </div>
        </div>';
        ?>
        <? $datepicker ?>



        <div class="col-md-8 col-md-offset-2">

            <!-- Поля задачи -->
            <input type="text" name="task_name" class="form-control dn" placeholder="Имя задачи" disabled />

            <div class="task-description dn">
                <hr>
                <?= CKEditor::widget([
                    'id' => 'task_description',
                    'name' => 'task_description',
                    'editorOptions' => [
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
                ]) ?>
            </div>

            <select name="active_task" class="form-control dn" disabled>
                <option value="">Активность задачи</option>
                <? foreach (Yii::$app->params['active_status'] as $s_k => $s_v): ?>
                    <option value="<?= $s_k ?>">Задача <?= $s_v ?></option>
                <? endforeach; ?>
            </select>
            <!-- /поля задачи -->
        </div>
    </div>
    <!-- /tasks -->

    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <button type="button" name="input_change" class="btn btn-success input-change" data-url="ajax/project"
                data-type-method="post">
                <?= Html::img('@web/images/animate/loading.gif', ['alt' => 'Загрузка', 'width' => '20', 'class' => 'loading']) ?>
                Изменить
            </button>
        </div>
    </div>

    <? d::res() ?>

</div>
<?php

$this->registerJs(<<<JS
    
    var editorTaskDescriptionDefault = CKEDITOR.instances['task_description'];

    setTimeout(function(){
        $('[name=projects]').val('');
        $('[name=task_list]').html('<option value="">Задач пока нет</option><option value="new_task">Добавить задачу</option>').prop('disabled',true);
        $('[name=project_name]').val('').prop('disabled',true);
        $('[name=project_code]').val('').prop('disabled',true);
        $('[name=active_project]').val('').prop('disabled',true);
        $('[name=task_name]').val('').prop('disabled',true);
        editorTaskDescriptionDefault.setData('');
        editorTaskDescriptionDefault.setReadOnly(true);
        $('[name=active_task]').val('').prop('disabled',true);
    }, 500);

    /**
     * Страница "Проекты"
     * ==================
     * Выпадающий список "Выберите проект"
     */
    $('.prts [name=projects]').on('change', function () {
        var ttihs = $(this),
            form = $('.prts'),
            load = ttihs.next(),
            res = form.find('.res'),
            // Поля проекта
            wrap_color_project = form.find('.wrap-color-project'),
            project_name = form.find('[name=project_name]'),
            project_code = form.find('[name=project_code]'),
            active_project = form.find('[name=active_project]'),
            project_trash = form.find('.projects .glyphicon-trash'),
            task_trash = form.find('.tasks .glyphicon-trash'),
            // Поля задачи
            task_list = form.find('[name=task_list]'),
            task_name = form.find('[name=task_name]'),
            w_task_description = form.find('.task-description'),
            editorTaskDescription = CKEDITOR.instances['task_description'],
            active_task = form.find('[name=active_task]'),
            Data = {};

        // В color-picker input задаем цвет и показываем его
        wrap_color_project.find('input').val(ttihs.find('option:selected').attr('data-color')).promise().done(function () {
            wrap_color_project.fadeIn(100);
        });

        /**
         * Если в списке проектов ничего не выбрано
         * то сбрасываем форму
         */
        if (ttihs.val() == '') {

            // Опустошаем и деактивируем выпадающий список "Выберите задачу"
            task_list.html(no_tasks_yet_new).prop('disabled', true);

            // Отключаем поле "Имя проекта"
            project_name.val('').prop('disabled', true);

            // Отключаем поле "Код проекта"
            project_code.val('').prop('disabled', true);

            // Отключаем поле "Активность проекта"
            active_project.val('').prop('disabled', true);

            // Опустошаем, деактивируем и скрываем поле "Имя задачи"
            task_name.val('').prop('disabled', true).fadeOut(100);

            // Опустошаем, деактивируем и скрываем поле "Описание задачи"
            editorTaskDescription.setData('');
            editorTaskDescription.setReadOnly(true);
            w_task_description.fadeOut(100);

            // Сбрасываем, деактивируем и скрываем поле "Активность задачи"
            active_task.val('').prop('disabled', true).fadeOut(100);

            // Скрываем иконку удаления проекта
            project_trash.fadeOut(100);

            // Скрываем иконку удаления задачи
            task_trash.fadeOut(100);

            return;
        }

        /**
         * Если выбрано "Добавить новый проект"
         */
        if (ttihs.find('option:selected').val() == '0') {

            // Активируем выпадающий список "Выберите задачу"
            task_list.html(no_tasks_yet_new).prop('disabled', '');

            // Делаем активным поле "Имя проекта"
            project_name.val('').prop('disabled', '');

            // Делаем активным поле "Код проекта"
            project_code.val('').prop('disabled', '');

            // Делаем активным поле "Активность проекта"
            active_project.val('').prop('disabled', '');

            // Опустошаем, деактивируем и скрываем поле "Имя задачи"
            task_name.val('').prop('disabled', true).fadeOut(100);

            // Опустошаем, деактивируем и скрываем поле "Описание задачи"
            editorTaskDescription.setData('');
            editorTaskDescription.setReadOnly(true);
            w_task_description.fadeOut(100);

            // Сбрасываем, деактивируем и скрываем поле "Активность задачи"
            active_task.val('').prop('disabled', true).fadeOut(100);

            // Скрываем иконку удаления проекта
            project_trash.fadeOut(100);

            // Скрываем иконку удаления задачи
            task_trash.fadeOut(100);

            return;

        }

        Data['project'] = ttihs.find('option:selected').attr('data-code');
        Data['project_id'] = ttihs.find('option:selected').val();

        // cl(Data);
        // return;

        $.ajax({
            url: ttihs.attr('data-url'),
            type: ttihs.attr('data-type-method'),
            dataType: 'json',
            cashe: 'false',
            data: Data,
            beforeSend: function () {
                load.fadeIn(100);
            }
        }).done(function (data) {
            res.html('Done<br>' + JSON.stringify(data));
            LoadAlert(data.header, data.message, 3000, data.type_message);

            // Заполняем выпадающий список "Задачи проекта"
            task_list.html(data.options).prop('disabled', '');

            /**
             * Делаем активным
             * и вставляем имя в поле "Имя проекта"
             */
            project_name.val(ttihs.find('option:selected').html()).prop('disabled', '');

            /**
             * Делаем активным поле "Код проекта"
             * в поле вставляем код проекта
             * в атрибут data-code тоже вставляем код проекта
             */
            project_code
                .val(ttihs.find('option:selected').attr('data-code'))
                .attr('data-code', ttihs.find('option:selected').attr('data-code'))
                .prop('disabled', '');

            /**
             * Делаем активным
             * и вставляем вид активности в поле "Активность проекта"
             */
            active_project.val(ttihs.find('option:selected').attr('data-active')).prop('disabled', '');

            // Опустошаем, деактивируем и скрываем поле "Имя задачи"
            task_name.val('').prop('disabled', true).fadeOut(100);

            // Опустошаем, деактивируем и скрываем поле "Описание задачи"
            editorTaskDescription.setData('');
            editorTaskDescription.setReadOnly(true);
            w_task_description.fadeOut(100);

            // Сбрасываем, деактивируем и скрываем поле "Активность задачи"
            active_task.val('').prop('disabled', true).fadeOut(100);

            // Показываем иконку удаления проекта
            project_trash.fadeIn(100);

            if (data.status == 200) { } else { }
            load.fadeOut(100);
        }).fail(function (data) {
            res.html('Fail<br>' + JSON.stringify(data));
            load.fadeOut(100);
        });
    });

    /**
     * Страница "Проекты"
     * ==================
     * Выпадающий список "Выберите задачу"
     */
    $('.prts [name=task_list]').on('change', function () {
        var ttihs = $(this),
            form = $('.prts'),
            load = ttihs.next(),
            res = form.find('.res'),
            // Поля проекта
            //            project_name = form.find('[name=project_name]'),
            //            project_code = form.find('[name=project_code]'),
            //            active_project = form.find('[name=active_project]'),
            trash = form.find('.tasks .glyphicon-trash'),
            // Поля задачи
            task_list = form.find('[name=task_list]'),
            task_name = form.find('[name=task_name]'),
            w_task_description = form.find('.task-description'),
            editorTaskDescription = CKEDITOR.instances['task_description'],
            active_task = form.find('[name=active_task]'),
            Data = {};

        /**
         * Если в списке задач ничего не выбрано
         * то поля задач сбрасываем, деактиваруем и скрываем их
         */
        if (ttihs.val() == '') {

            // Опустошаем и деактивируем поле "Имя задачи"
            task_name.val('').prop('disabled', true).fadeOut(100);

            // Опустошаем и деактивируем поле "Описание задачи"
            editorTaskDescription.setData('');
            editorTaskDescription.setReadOnly(true);
            w_task_description.fadeOut(100);

            // Отключаем поле "Активность задачи"
            active_task.val('').prop('disabled', true).fadeOut(100);

            // Скрываем иконку удаления
            trash.fadeOut(100);

            return;
        }

        /**
         * Если выбрано "Добавить новую задачу"
         */
        if (ttihs.find('option:selected').val() == 'new_task') {

            // Показываем и вставляем в поле "Имя задачи" имя задачи
            task_name.val('').prop('disabled', '').fadeIn(100);
            task_name.focus();
            cl('Выбрана новая задача');

            // Показываем и вставляем в поле "Описание проекта"
            // w_task_description.find('[name=task_description]').val('').prop('disabled', '');
            editorTaskDescription.setData('');
            editorTaskDescription.setReadOnly(false);
            w_task_description.fadeIn(100);

            // Поле "Активность задачи" делаем активным
            active_task.val('1').prop('disabled', '').fadeIn(100);

            // Скрываем иконку удаления
            trash.fadeOut(100);

            return;

        }

        /**
         * Если выбрана задача
         */
        if (
            ttihs.val() != '' &&
            ttihs.find('option:selected').val() != 'new_task'
        ) {

            // Показываем и вставляем в поле "Имя задачи" имя задачи
            task_name.val(ttihs.find('option:selected').html()).prop('disabled', '').fadeIn(100);

            // Показываем и вставляем в поле "Описание проекта"
            // w_task_description.find('[name=task_description]').val(ttihs.find('option:selected').attr('data-description')).prop('disabled', '');
            editorTaskDescription.setData(ttihs.find('option:selected').attr('data-description'));
            editorTaskDescription.setReadOnly(false);
            w_task_description.fadeIn(100);

            // Отключаем поле "Активность задачи"
            active_task.val(ttihs.find('option:selected').attr('data-active')).prop('disabled', '').fadeIn(100);

            // Показываем иконку удаления
            trash.fadeIn(100);

            return;

        }

    });

    /**
     * Страница "Проекты"
     * ==================
     * Кнопка "Изменить"
     */
    $('.prts [name=input_change]').on('click', function () {
        var ttihs = $(this),
            form = $('.prts'),
            load = ttihs.next(),
            res = form.find('.res'),
            project_trash = form.find('.projects .glyphicon-trash'),
            task_trash = form.find('.tasks .glyphicon-trash'),
            // Поля проекта
            projects = form.find('[name=projects]'),
            project_name = form.find('[name=project_name]'),
            project_code = form.find('[name=project_code]'),
            active_project = form.find('[name=active_project]'),
            // Поля задачи
            task_list = form.find('[name=task_list]'),
            task_name = form.find('[name=task_name]'),
            task_description = form.find('[name=task_description]'),
            editorTaskDescription = CKEDITOR.instances['task_description'],
            active_task = form.find('[name=active_task]'),
            Data = {};

        // Если в поле "Выберите проект" ничего не выбрано
        if (projects.val() == '') {
            LoadAlert('Внимание', 'Выберите проект', 4000, 'warning');
            return;
        }

        Data['project'] = {};

        /**
         * Если в списке "Выберите проект"
         * выбрано "Добавить новый проект"
         */
        if (projects.find('option:selected').attr('data-code') == 'new') {
            Data['project']['type'] = 'add';

            // Проверим все необходимые поля на пустоту
            if (
                project_name.val() == '' ||
                project_code.val() == '' ||
                active_project.val() == '') {
                LoadAlert('Внимание', 'Заполните все необходимые поля', 4000, 'warning');
                return;
            }

        } else {
            /**
             * Если в списке "Выберите проект"
             * выбран какой то проект
             */
            Data['project']['type'] = 'edit';
            Data['project']['project_id'] = projects.find('option:selected').val();
        }

        // Соберем все необходимые поля
        Data['project']['name'] = project_name.val();
        Data['project']['code'] = project_code.val();
        Data['project']['code_orig'] = project_code.attr('data-code');
        Data['project']['active'] = active_project.val();

        /**
         * Если в списке "Выберите задачу"
         * вообще что то выбрано
         */
        if (task_list.val() != '') {

            // Проверим все необходимые поля на пустоту
            if (
                task_name.val() == '' ||
                editorTaskDescription.getData() == '' ||
                active_task.val() == '') {
                LoadAlert('Внимание', 'Заполните все необходимые поля', 4000, 'warning');
                return;
            }

            Data['task'] = {};
            /**
             * Если в списке "Выберите задачу"
             * выбрано "Добавить новую задачу"
             */
            if (task_list.val() == 'new_task') {
                Data['task']['type'] = 'add';
            } else {
                /**
                 * Если в списке "Выберите задачу"
                 * выбрана какая то задача
                 */
                Data['task']['type'] = 'edit';
                Data['task']['id'] = task_list.val();
            }
            
            var textareaTastDescription = cleanRN(editorTaskDescription.getData());

            Data['task']['name'] = task_name.val();
            Data['task']['description'] = textareaTastDescription;
            Data['task']['active'] = active_task.val();
        }


        cl(Data);
        //        return;

        $.ajax({
            url: ttihs.attr('data-url'),
            type: ttihs.attr('data-type-method'),
            dataType: 'json',
            cashe: 'false',
            data: Data,
            beforeSend: function () {
                load.fadeIn(100);
            }
        }).done(function (data) {
            res.html('Done<br><pre>' + prettyPrintJson.toHtml(data) + '</pre>');
            LoadAlert(data.header, data.message, 3000, data.type_message);

            // Перезаполняем выпадающий список "Выберите проект"
            projects.html(data.options_projects).prop('disabled', '');

            // Перезаполняем выпадающий список "Задачи проекта"
            task_list.html(data.options_tasks).prop('disabled', '');

            /**
             * Если в выпадающем списке "Выберите проект"
             * выбран какой то проект,
             * показываем иконку удаления
             */
            if (projects.val() != '' && projects.val() != '0') {
                project_trash.fadeIn(100);
            }

            /**
             * Если в выпадающем списке "Выберите задачу"
             * выбрана какая то задача,
             * показываем иконку удаления
             */
            if (task_list.val() != '' && task_list.val() != 'new_task') {
                task_trash.fadeIn(100);
            }

            if (data.status == 200) { } else { }
            load.fadeOut(100);
        }).fail(function (data) {
            res.html('Fail<br>' + JSON.stringify(data));
            load.fadeOut(100);
        });
    });

    /**
     * Страница "Проекты"
     * ==================
     * Кнопка "Удалить проект"
     */
    $('.prts .modal [name=delete_project]').on('click', function () {
        var ttihs = $(this),
            form = $('.prts'),
            load = ttihs.parent().find('img.loading'),
            res = form.parent().parent().find('.res'),
            close_modal = form.find('.modal .close'),
            project_trash = form.find('.projects .glyphicon-trash'),
            task_trash = form.find('.tasks .glyphicon-trash'),
            // Поля проекта
            projects = form.find('[name=projects]'),
            project_name = form.find('[name=project_name]'),
            project_code = form.find('[name=project_code]'),
            active_project = form.find('[name=active_project]'),
            // Поля задачи
            task_list = form.find('[name=task_list]'),
            task_name = form.find('[name=task_name]'),
            w_task_description = form.find('.task-description'),
            task_description = form.find('[name=task_description]'),
            editorTaskDescription = CKEDITOR.instances['task_description'],
            active_task = form.find('[name=active_task]'),
            Data = {};

        // Получаем ID проекта
        Data['project_id'] = projects.val();
        Data['project_code'] = project_code.val();

        //        cl(ttihs);
        //        cl(ttihs.attr('data-url'));
        //        cl(ttihs.attr('data-type-method'));

        //        cl(Data);
        //        return;

        $.ajax({
            url: ttihs.attr('data-url'),
            type: ttihs.attr('data-type-method'),
            dataType: 'json',
            cashe: 'false',
            data: Data,
            beforeSend: function () {
                load.fadeIn(100);
            }
        }).done(function (data) {
            //            res.html('Done<br>'+JSON.stringify(data));
            LoadAlert(data.header, data.message, 3000, data.type_message);

            /**
             * Перезаполняем и сбрасываем
             * выпадающий список "Выберите проект"
             */
            projects.html(data.options_projects).val('').prop('disabled', '');

            // Опустошаем и деактивируем выпадающий список "Выберите задачу"
            task_list.html(no_tasks_yet_new).prop('disabled', true);

            // Отключаем поле "Имя проекта"
            project_name.val('').prop('disabled', true);

            // Отключаем поле "Код проекта"
            project_code.val('').prop('disabled', true);

            // Отключаем поле "Активность проекта"
            active_project.val('').prop('disabled', true);

            // Опустошаем, деактивируем и скрываем поле "Имя задачи"
            task_name.val('').prop('disabled', true).fadeOut(100);

            // Опустошаем, деактивируем и скрываем поле "Описание задачи"
            editorTaskDescription.setData('');
            editorTaskDescription.setReadOnly(true);
            w_task_description.fadeOut(100);

            // Сбрасываем, деактивируем и скрываем поле "Активность задачи"
            active_task.val('').prop('disabled', true).fadeOut(100);

            // Скрываем иконку удаления проекта
            project_trash.fadeOut(100);

            // Скрываем иконку удаления задачи
            task_trash.fadeOut(100);

            // Закрываем модальное окно
            close_modal.trigger('click');

            if (data.status == 200) { } else { }
            load.fadeOut(100);
        }).fail(function (data) {
            res.html('Fail<br>' + JSON.stringify(data));
            load.fadeOut(100);
        });
    });

    /**
     * Страница "Проекты"
     * ==================
     * Кнопка "Удалить задачу"
     */
    $('.prts .modal [name=delete_taks]').on('click', function () {
        var ttihs = $(this),
            form = $('.prts'),
            load = ttihs.parent().find('img.loading'),
            res = form.parent().parent().find('.res'),
            close_modal = form.find('.modal .close'),
            trash = form.find('.tasks .glyphicon-trash'),
            // Поля проекта
            projects = form.find('[name=projects]'),
            project_name = form.find('[name=project_name]'),
            project_code = form.find('[name=project_code]'),
            active_project = form.find('[name=active_project]'),
            // Поля задачи
            task_list = form.find('[name=task_list]'),
            task_name = form.find('[name=task_name]'),
            w_task_description = form.find('.task-description'),
            task_description = form.find('[name=task_description]'),
            editorTaskDescription = CKEDITOR.instances['task_description'],
            active_task = form.find('[name=active_task]'),
            Data = {};

        // Получаем ID проекта
        Data['project_id'] = projects.val();
        Data['task_id'] = task_list.val();

        //        cl(Data);
        //        return;

        $.ajax({
            url: ttihs.attr('data-url'),
            type: ttihs.attr('data-type-method'),
            dataType: 'json',
            cashe: 'false',
            data: Data,
            beforeSend: function () {
                load.fadeIn(100);
            }
        }).done(function (data) {
            //            res.html('Done<br>'+JSON.stringify(data));
            LoadAlert(data.header, data.message, 3000, data.type_message);

            // Перезаполняем и сбрасываем выпадающий список "Выберите задачу"
            task_list.html(data.options_tasks);

            // Опустошаем, деактивируем и скрываем поле "Имя задачи"
            task_name.val('').prop('disabled', true).fadeOut(100);

            // Опустошаем, деактивируем и скрываем поле "Описание задачи"
            editorTaskDescription.setData('');
            editorTaskDescription.setReadOnly(true);
            w_task_description.fadeOut(100);

            // Сбрасываем, деактивируем и скрываем поле "Активность задачи"
            active_task.val('').prop('disabled', true).fadeOut(100);

            // Скрываем иконку удаления
            trash.fadeOut(100);

            // Закрываем модальное окно
            close_modal.trigger('click');

            if (data.status == 200) { } else { }
            load.fadeOut(100);
        }).fail(function (data) {
            res.html('Fail<br>' + JSON.stringify(data));
            load.fadeOut(100);
        });
    });
    
JS
);

?>