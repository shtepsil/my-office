<?php
/**
 * Контроллер для работы с Ajax запросами
 */
namespace backend\controllers;

use backend\models\BalanceBeginMonth;
use backend\models\Projects;
use backend\models\Reports;
use backend\models\Tags;
use backend\models\Tasks;
use backend\models\TaskView;
use backend\models\WorkingHours;
use backend\controllers\MainController as d;
use backend\models\Ajax;
use backend\models\WebmasterSettings;
use common\models\Payment;
use common\models\User;
//use http\Client;
use shadow\helpers\SArrayHelper;
use shadow\helpers\SDateHelper;
use shadow\helpers\SNumberHelper;
use shadow\helpers\StringHelper;
use yii\helpers\BaseHtml;
use frontend\models\SignupForm;
use Yii;
use backend\components\ReceivingExchangeRates as Rate;
use shadow\helpers\StringHelper as SH;

class AjaxController extends MainController
{

    /**
     * Страница "Отправка Eamil"
     * =========================
     * Кнопка "Отправить Email"
     */
    public function actionSendMail()
    {
        //        sleep(2);
        $data = [];
        $data['status'] = d::getMessage('AJAX_STATUS_ERROR');

        if (isset($_POST['send_mail'])) {

            $to = "akvarius_84@mail.ru";

            $subject = "Оплата за обслуживание сайта";

            //            $message = $this->renderAjax('shortcodes/email/serebros');
            $message = 'shortcodes/email/serebros';

            $headers = "Content-type: text/html; charset=utf-8 \r\n";
            $headers .= "From: От Unionkaper <peresecheniya@xn--e1aaaap3ajjk5d1e.xn--p1ai>\r\n";
            $headers .= "Reply-To: peresecheniya@xn--e1aaaap3ajjk5d1e.xn--p1ai\r\n";

            if (mail($to, $subject, $message, $headers)) {
                $data['status'] = d::getMessage('AJAX_STATUS_SUCCESS');
                $data['header'] = d::getMessage('HEADER_SUCCESS');
                $data['message'] = d::getMessage('SUCCES_SAND_MAIL');
            } else {
                $data['type_message'] = d::getMessage('TYPE_WARNING');
                $data['header'] = d::getMessage('HEADER_WARNING');
                $data['message'] = d::getMessage('ERROR_SAND_MAIL');
            }

        } else {
            $data['type_message'] = d::getMessage('TYPE_WARNING');
            $data['header'] = d::getMessage('HEADER_WARNING');
            $data['message'] = d::getMessage('NO_SAND_MAIL');
        }

        d::echoAjax($data);

    }

    /**
     * Страница "Time Трекер"
     * ======================
     * Кнопка "Старт/Стоп"
     * Действие при нажатии на стоп
     */
    public function actionInputTime()
    {
        //        sleep(2);
        $data = [];
        $data['status'] = d::getMessage('AJAX_STATUS_ERROR');
        $data['row'] = '';

        $post = d::secureEncode($_POST);

        /*
         * Сначала надо обновить время в БД
         * общее время работы над задачей
         */
        $result = Ajax::updateTimeWork($post);
        if (!isset($result['errors'])) {

            $file = Yii::getAlias('@timetrecker') . '/' . Yii::$app->params['project_time_file'];

            // Получаем данные из файла
            $json_projects = file_get_contents($file);
            // Преобразуем json в массив
            $arr_projects = d::jsonToArray($json_projects);

            /*
             * Получаем массив нового времени проекта
             * суммированное время
             */
            $project = $post['project'];

            /*
             * Если проект в файле уже есть
             * переписываем время
             */
            if ($arr_projects[key($project)]) {
                foreach ($arr_projects[key($project)] as $time_key => $time) {
                    $arr_projects[key($project)][$time_key] =
                        ($project[key($project)][$time_key]) ? $project[key($project)][$time_key] : '00';
                }
            } else {
                /*
                 * Если проекта в файле ещё нет
                 * Добавляем новый проект
                 */
                foreach ($project[key($project)] as $key => $time) {
                    $arr_projects[key($project)][$key] =
                        ($project[key($project)][$key]) ? $project[key($project)][$key] : '00';
                }
            }
            // Кодируем массив с проектами в json
            $json_time = json_encode($arr_projects);

            // Пишем в файл
            if (file_put_contents($file, $json_time)) {

                /*
                 * Получаем новые данные
                 * для вывода на экран
                 */
                $json_last_time = file_get_contents($file);
                // Преобразуем в массив
                $arr_last_time = d::jsonToArray($json_last_time);

                // Дни
                $data['days'] =
                    ($arr_last_time[key($project)]['days']) ?
                    $arr_last_time[key($project)]['days'] : '00';
                // Часы
                $data['hours'] =
                    ($arr_last_time[key($project)]['hours']) ?
                    $arr_last_time[key($project)]['hours'] : '00';
                // Минуты
                $data['minutes'] =
                    ($arr_last_time[key($project)]['minutes']) ?
                    $arr_last_time[key($project)]['minutes'] : '00';
                // Секунды
                $data['seconds'] =
                    ($arr_last_time[key($project)]['seconds']) ?
                    $arr_last_time[key($project)]['seconds'] : '00';

                $data['status'] = d::getMessage('AJAX_STATUS_SUCCESS');
                $data['header'] = d::getMessage('HEADER_SUCCESS');
                $data['message'] = d::getMessage('TIME_SAVED');

                $time = d::jsonToArray($result['tasks']['time_work']);

                $data['t_days'] = $time['days'];
                $data['t_hs'] = $time['hours'];
                $data['t_ms'] = $time['minutes'];
                $data['t_ss'] = $time['seconds'];

                // Данные последнего нажатия кнопки "Стоп"
                $whtr['working_hours'] = $result['working_hours']['working_hours'];
                $whtr['created_at'] = $result['working_hours']['created_at'];
                $data['row'] .=
                    $this->renderAjax('shortcodes/working-hours-tr', $whtr);

            } else {
                // Ошибка записи в файл
                $data['type_message'] = d::getMessage('TYPE_WARNING');
                $data['header'] = d::getMessage('HEADER_WARNING');
                $data['message'] = d::getMessage('ERROR_TIME_SAVE');
            }
        } else {
            // Ошибка обновления данных в БД
            $data['type_message'] = d::getMessage('TYPE_WARNING');
            $data['header'] = d::getMessage('HEADER_WARNING');
            $data['message'] = $result['errors'];
        }

        d::echoAjax($data);

    }

    /**
     * Страница "Time Трекер"
     * ======================
     * Выпадающий список "Выберите проект"
     * Получаем общее время работы по проекту
     */
    public function actionGetProject()
    {
        //        sleep(2);
        $data = [];
        $data['tasks'] = '';
        $data['status'] = d::getMessage('AJAX_STATUS_ERROR');
        $file = Yii::getAlias('@timetrecker') . '/' . Yii::$app->params['project_time_file'];

        $post = d::secureEncode($_POST);

        if ($json_projects = file_get_contents($file)) {

            $arr_last_time = d::jsonToArray($json_projects);

            // Получаем список задач по проекту
            $tasks = Tasks::find()
                ->where([
                    'project_id' => $post['project_id'],
                    'active' => '1',
                ])
                ->orderBy('id DESC')
                ->asArray()->all();

            // Если проект в файле есть
            if ($arr_last_time[$post['project']]) {
                $data['days'] =
                    ($arr_last_time[$post['project']]['days']) ? $arr_last_time[$post['project']]['days'] : '00';
                $data['hours'] =
                    ($arr_last_time[$post['project']]['hours']) ? $arr_last_time[$post['project']]['hours'] : '00';
                $data['minutes'] =
                    ($arr_last_time[$post['project']]['minutes']) ? $arr_last_time[$post['project']]['minutes'] : '00';
                $data['seconds'] =
                    ($arr_last_time[$post['project']]['seconds']) ? $arr_last_time[$post['project']]['seconds'] : '00';

                $data['status'] = d::getMessage('AJAX_STATUS_SUCCESS');
                $data['header'] = d::getMessage('HEADER_SUCCESS');
                $data['message'] = d::getMessage('PROJECT_TIME_RECEIVED');

                if ($tasks) {
                    $attr = [];
                    $data['tasks'] .= Yii::getAlias('@select_task');
                    foreach ($tasks as $task) {

                        $attr['value'] = $task['id'];

                        $data['tasks'] .= $this->renderAjax(
                            'shortcodes/options-list',
                            [
                                'attributes' => BaseHtml::renderTagAttributes($attr),
                                'string' => $task['name'],
                            ]
                        );
                    }
                } else
                    $data['tasks'] .= Yii::getAlias('@tasks_not_found');

            } else {
                // Проекта в файле нет
                $data['days'] = '00';
                $data['hours'] = '00';
                $data['minutes'] = '00';
                $data['seconds'] = '00';

                $data['type_message'] = d::getMessage('TYPE_WARNING');
                $data['header'] = d::getMessage('HEADER_WARNING');
                $data['message'] = d::getMessage('ERROR_GETTING_TIME_PROJECT_MISSING');
            }
        } else {
            $data['type_message'] = d::getMessage('TYPE_WARNING');
            $data['header'] = d::getMessage('HEADER_WARNING');
            $data['message'] = d::getMessage('ERROR_GETTING_TIME');
        }

        d::echoAjax($data);

    }

    /**
     * Страница "Time Трекер"
     * ======================
     * Выпадающий список "Выберите задачу"
     * Получаем общее время, потраченное на задачу
     */
    public function actionGetTaskByProject()
    {
        //        sleep(2);
        $data = [];
        $data['status'] = d::getMessage('AJAX_STATUS_ERROR');
        $data['row'] = '';

        $post = d::secureEncode($_POST);

        /*
         * Получаем задачу по ID проекта
         * и по ID задачи, выбранной в выпадающем списке "Выберите задачу"
         */
        $task = Tasks::findOne([
            'project_id' => $post['project_id'],
            'id' => $post['task_id']
        ]);

        // Если выборка задачи не пуста
        if ($task) {

            // Выбираем все строки времени на сегодняшний день
            if ($todays_work = Ajax::getTodayTime($post)) {
                foreach ($todays_work as $row) {
                    $data['row'] .=
                        $this->renderAjax('shortcodes/working-hours-tr', $row);
                    //                        $this->renderAjax('shortcodes/test-tr', $row);
                }
            }

            $time_taks = d::jsonToArray($task['time_work']);

            $data['days'] = $time_taks['days'];
            $data['hours'] = $time_taks['hours'];
            $data['minutes'] = $time_taks['minutes'];
            $data['seconds'] = $time_taks['seconds'];

            $data['status'] = d::getMessage('AJAX_STATUS_SUCCESS');
            $data['header'] = d::getMessage('HEADER_SUCCESS');
            $data['message'] = d::getMessage('TASK_TIME_RECEIVED');

        } else {

            $data['days'] = '00';
            $data['hours'] = '00';
            $data['minutes'] = '00';
            $data['seconds'] = '00';

            $data['type_message'] = d::getMessage('TYPE_WARNING');
            $data['header'] = d::getMessage('HEADER_WARNING');
            $data['message'] = d::getMessage('TASK_NOT_FOUND');
        }

        d::echoAjax($data);

    }

    /**
     * Страница "Статистика WebMaster"
     * ===============================
     * Выпадающий список "Выберите проект"
     * Получаем задачи по выбранному проекту
     */
    public function actionGetTasksByProject()
    {
        //        sleep(2);
        $data = [];
        $data['status'] = d::getMessage('AJAX_STATUS_ERROR');
        $data['options'] = '';

        $post = d::secureEncode($_POST);

        $tasks = Tasks::find()
            ->where(['project_id' => $post['project_id']])
            ->asArray()->all();

        if ($tasks) {

            $data['status'] = d::getMessage('AJAX_STATUS_SUCCESS');
            $data['header'] = d::getMessage('HEADER_SUCCESS');
            $data['message'] = d::getMessage('TASKS_RECEIVED');

            $data['options'] .= Yii::getAlias('@select_task');

            // Собираем HTML select options
            foreach ($tasks as $task) {

                $attr['value'] = $task['id'];
                $attr['data-desc'] = $task['description'];

                $data['options'] .=
                    $this->renderAjax(
                        'shortcodes/options-list',
                        [
                            'attributes' => BaseHtml::renderTagAttributes($attr),
                            'string' => $task['name'],
                        ]
                    );
            }

        } else {

            $data['type_message'] = d::getMessage('TYPE_WARNING');
            $data['header'] = d::getMessage('HEADER_WARNING');
            $data['message'] = d::getMessage('TASK_NOT_FOUND');
            $data['options'] .= Yii::getAlias('@no_tasks_yet');
        }

        d::echoAjax($data);

    } // f actionGetTasksByProject()

    /**
     * Страница "Проекты"
     * ==================
     * Выпадающий список "Выберите проект"
     * Получаем задачи по выбранному проекту
     */
    public function actionGetTasksByProject2()
    {
        //        sleep(2);
        $data = [];
        $data['status'] = d::getMessage('AJAX_STATUS_ERROR');
        $data['options'] = '';

        $post = d::secureEncode($_POST);

        $tasks = Tasks::find()
            ->where(['project_id' => $post['project_id']])
            ->orderBy([
                'created_at' => SORT_DESC,
                'active' => SORT_ASC
            ])->asArray()->all();

        if ($tasks) {

            $data['options'] .= Yii::getAlias('@select_task2');

            // Собираем HTML select options
            foreach ($tasks as $task) {

                $attr['value'] = $task['id'];
                $attr['data-description'] = $task['description'];
                $attr['data-active'] = $task['active'];

                if ($task['active'] == '2') {
                    $attr['style'] = 'color:#E600FF;';
                }
                if ($task['active'] == '1') {
                    $attr['style'] = 'color:#272BFF';
                }
                if ($task['active'] == '0') {
                    $attr['style'] = 'color:red';
                }

                $data['options'] .=
                    $this->renderAjax(
                        'shortcodes/options-list',
                        [
                            'attributes' => BaseHtml::renderTagAttributes($attr),
                            'string' => htmlspecialchars_decode($task['name']),
                        ]
                    );
            }

        } else {
            $data['options'] .= Yii::getAlias('@no_tasks_yet_new');
        }

        $data['status'] = d::getMessage('AJAX_STATUS_SUCCESS');
        $data['header'] = d::getMessage('HEADER_SUCCESS');
        $data['message'] = d::getMessage('DATA_RECEIVED');

        d::echoAjax($data);

    } // f actionGetTasksByProject2()

    /**
     * Страница "Статистика WebMaster"
     * ===============================
     * Выпадающий список "Выберите задачу"
     * Получаем тип задачи по выбранной задаче
     */
    public function actionGetTaskView()
    {
        //        sleep(2);
        $data = [];
        $data['status'] = d::getMessage('AJAX_STATUS_ERROR');
        $data['options'] = '';

        $post = d::secureEncode($_POST);

        $tasks = TaskView::find()
            ->where(['project_id' => $post['project_id']])
            //            ->andWhere(['task_id'=>$post['task_id']])
            ->asArray()->all();

        if ($tasks) {

            $data['status'] = d::getMessage('AJAX_STATUS_SUCCESS');
            $data['header'] = d::getMessage('HEADER_SUCCESS');
            $data['message'] = d::getMessage('TASK_TYPES_RECEIVED');

            $data['options'] .= Yii::getAlias('@select_task');

            // Собираем HTML select options
            foreach ($tasks as $task) {

                $attr['value'] = $task['id'];

                $data['options'] .=
                    $this->renderAjax(
                        'shortcodes/options-list',
                        [
                            'attributes' => BaseHtml::renderTagAttributes($attr),
                            'string' => $task['name'],
                        ]
                    );
            }

        } else {

            $data['type_message'] = d::getMessage('TYPE_WARNING');
            $data['header'] = d::getMessage('HEADER_WARNING');
            $data['message'] = d::getMessage('TASK_NOT_FOUND');
            $data['options'] .= Yii::getAlias('@no_tasks_yet');
        }

        d::echoAjax($data);

    }

    /**
     * Страница "Статистика WebMaster"
     * ===============================
     * Кнопка "Найти"
     */
    public function actionGetStatisticsWebmaster()
    {
        //        sleep(2);
        $data = [];
        $data['seconds'] = 0;
        $days = [];
        $data['status'] = d::getMessage('AJAX_STATUS_ERROR');

        $post = d::secureEncode($_POST);

        if ($report = Ajax::getStatisticsWebmaster($post)) {
            $data['status'] = d::getMessage('AJAX_STATUS_SUCCESS');
            $data['header'] = d::getMessage('HEADER_SUCCESS');
            $data['message'] = d::getMessage('DONE');

            foreach ($report as $ss) {

                // Суммируем все секунды в одну сумму
                $data['seconds'] += $ss['working_hours'];

                // Собираем уникальные даты и общее время работы за дату
                if (array_key_exists(date('Y-m-d', $ss['created_at']), $days)) {
                    $days[date('Y-m-d', $ss['created_at'])] =
                        ($days[date('Y-m-d', $ss['created_at'])] + $ss['working_hours']);
                } else {
                    $days[date('Y-m-d', $ss['created_at'])] = $ss['working_hours'];
                }
            }

            // Среднее время в день (в секундах)
            $data['average_time'] =
                number_format($data['seconds'] / count($days), 0, '.', '');

            // Количество потраченных дней
            $data['working_days'] = count($days);

            $today = new Rate();
            // Курс сегодня
            // Курс долара, в скобках официальный код валюты
            $data['current_curs'] = $today->getRate()['curse'];

            //Курс на завтра
//        $tommorow= new Rate(strtotime("+1 day"));
//        $data['tommorow'] = $tommorow -> curs(840)['curs'];

        } else {
            $data['type_message'] = d::getMessage('TYPE_WARNING');
            $data['header'] = d::getMessage('HEADER_WARNING');
            $data['message'] = d::getMessage('HISTORY_EMPTY');
        }

        d::echoAjax($data);

    }

    /**
     * Страница "Проекты"
     * ==================
     * Кнопка "Изменить"
     * -----------------
     * "Редактирование/добавление нового" проекта
     * "Редактирование/добавление новой" задачи
     */
    public function actionProject()
    {
        //        sleep(2);
        $data = [];
        $data['status'] = d::getMessage('AJAX_STATUS_ERROR');
        $project_id = false;
        $projects_options_html = false;
        $tasks_options_html = false;
        $params = Yii::$app->params;
        $active_colors = $params['active_colors'];

        $post = Yii::$app->request->post();
//        d::ajax($post);

        // Если добавляется новый проект
        if ($post['project']['type'] == 'add') {
            $result = Ajax::addProject($post);
            // Если проект добавлен
            if ($result['project_id']) {
                $project_id = $result['project_id'];
                $data['status'] = d::getMessage('AJAX_STATUS_SUCCESS');
                $data['header'] = d::getMessage('HEADER_SUCCESS');
                $data['message'] = d::getMessage('PROJECT_ADDED');
                $projects_options_html = true;
            } else {
                $data['type_message'] = d::getMessage('TYPE_WARNING');
                $data['header'] = d::getMessage('HEADER_WARNING');
                $data['message'] = d::getMessage('PROJECT_ADDED_ERROR');
            }
        } else {
            // Если проект редактируется
            $result = Ajax::editProject($post);
            // Если проект изменен
            if ($result['project_id']) {
                $project_id = $result['project_id'];
                $data['status'] = d::getMessage('AJAX_STATUS_SUCCESS');
                $data['header'] = d::getMessage('HEADER_SUCCESS');
                $data['message'] = d::getMessage('PROJECT_CHANGED');
                $projects_options_html = true;
            } else {
                $data['type_message'] = d::getMessage('TYPE_WARNING');
                $data['header'] = d::getMessage('HEADER_WARNING');
                $data['message'] = d::getMessage('PROJECT_EDIT_ERROR');
            }
        }

        // Соберем новый список проектов, чтобы обновить выпадающий список проектов
        if ($projects_options_html) {
            // Получим все проекты из БД
            $projects = Projects::find()
                ->orderBy('name')->orderBy('active DESC')->asArray()->all();

            $data['options_projects'] = Yii::getAlias('@select_project_new');

            foreach ($projects as $pt) {

                $attr['value'] = $pt['id'];
                $attr['data-code'] = $pt['code'];
                if ($pt['id'] != '0') {
                    $attr['data-active'] = $pt['active'];
                }

                // Цвет из БД - пока отключил, не работает.
//                $attr['style'] = 'color:' . $active_colors[$pt['active']] . ';';

                if (isset($pt['active']) and $pt['active'] == '1') {
                    $attr['style'] = 'color:#272BFF';
                }
                if (isset($pt['active']) and $pt['active'] == '0') {
                    $attr['style'] = 'color:red';
                }

                if ($pt['id'] == $project_id) {
                    $attr['selected'] = 'selected';
                } else
                    unset($attr['selected']);

                $data['options_projects'] .=
                    $this->renderAjax(
                        'shortcodes/options-list',
                        [
                            'attributes' => BaseHtml::renderTagAttributes($attr),
                            'string' => $pt['name'],
                        ]
                    );
            }
        }

        // Если существует ID проекта
        if ($project_id !== false) {

            /*
             * Проверяем, нужно ли добавить новую
             * или редактировать существующу задачу
             */
            if (isset($post['task'])) {
                $post['task']['project_id'] = $project_id;
                // Если нужно добавить новую задачу
                if ($post['task']['type'] == 'add') {
                    if ($task_id = Ajax::addTask($post)) {
                        $tasks_options_html = true;
                    } else {
                        $data['type_message'] = d::getMessage('TYPE_WARNING');
                        $data['header'] = d::getMessage('HEADER_WARNING');
                        $data['message'] = d::getMessage('TASK_ADDED_ERROR');
                    }
                } else {
                    // Если нужно редактировать существующую задачу
                    if (Ajax::editTask($post)) {
                        $task_id = $post['task']['id'];
                        $tasks_options_html = true;
                    } else {
                        $data['type_message'] = d::getMessage('TYPE_WARNING');
                        $data['header'] = d::getMessage('HEADER_WARNING');
                        $data['message'] = d::getMessage('TASK_EDIT_ERROR');
                    }
                }

                // Соберем новый список задач, чтобы обновить выпадающий список задач
                if ($tasks_options_html) {

                    $tasks = Tasks::find()
                        ->where(['project_id' => $project_id])
                        ->orderBy([
                            'created_at' => SORT_DESC,
                            'active' => SORT_ASC
                        ])->asArray()->all();

                    if ($tasks) {
                        $data['options_tasks'] = Yii::getAlias('@select_task2');
                    } else {
                        $data['options_tasks'] = Yii::getAlias('@no_tasks_yet_new');
                    }

                    foreach ($tasks as $t) {

                        $attr['value'] = $t['id'];
                        $attr['data-description'] = $t['description'];
                        $attr['data-active'] = $t['active'];

                        // Вариант для цвета, который должен задаваться для БД, пока что отключил.
                        // Позже доработаю
//                        $attr['style'] = 'color:' . $active_colors[$t['active']] . ';';
                        if ($t['active'] == '2') {
                            $attr['style'] = 'color:#E600FF;';
                        }
                        if ($t['active'] == '1') {
                            $attr['style'] = 'color:#272BFF';
                        }
                        if ($t['active'] == '0') {
                            $attr['style'] = 'color:red';
                        }

                        if ($t['id'] == $task_id) {
                            $attr['selected'] = 'selected';
                        } else
                            unset($attr['selected']);

                        $data['options_tasks'] .=
                            $this->renderAjax(
                                'shortcodes/options-list',
                                [
                                    'attributes' => BaseHtml::renderTagAttributes($attr),
                                    'string' => $t['name'],
                                ]
                            );
                    }
                }

            } // if($post[task])

        } // if($project_id !== false)

        d::echoAjax($data);

    } // f actionProject()

    /**
     * Страница "Проекты"
     * ==================
     * Кнопка "Удалить проект"
     */
    public function actionDeleteProject()
    {
        //        sleep(2);
        $data = [];
        $data['status'] = d::getMessage('AJAX_STATUS_ERROR');

        $post = d::secureEncode($_POST);

        // Выбираем проект по ID
        $project = Projects::findOne($post['project_id']);
        // Удаляем проект
        $project->delete();

        // Удаляем все задачи по поекту
        Tasks::deleteAll(['project_id' => $post['project_id']]);

        /*
         * Добавим проект в файл общего времени "project_time.txt"
         * =======================================================
         * Получим содержимое файла
         */
        $time = file_get_contents(Yii::getAlias('@timetrecker') . '/' . Yii::$app->params['project_time_file']);
        // Преобразуем в массив
        $arr = d::objectToArray(json_decode($time));

        /*
         * Удалим проект из массива файла
         * по ключу (по коду проекта)
         */
        unset($arr[$post['project_code']]);

        // Массив преобразуем в JSON
        $json = json_encode($arr);
        // Перепишем файл с новыми данными
        @file_put_contents(Yii::getAlias('@timetrecker') . '/' . Yii::$app->params['project_time_file'], $json);

        // Получим все проекты из БД
        $projects = Projects::find()
            ->orderBy('name')->orderBy('active DESC')->asArray()->all();

        $data['options_projects'] = Yii::getAlias('@select_project_new');

        foreach ($projects as $pt) {

            $attr['value'] = $pt['id'];
            $attr['data-code'] = $pt['code'];
            $attr['data-active'] = $pt['active'];

            if ($pt['active'] == '1') {
                $attr['style'] = 'color:#272BFF';
            }
            if ($pt['active'] == '0') {
                $attr['style'] = 'color:red';
            }

            $data['options_projects'] .=
                $this->renderAjax(
                    'shortcodes/options-list',
                    [
                        'attributes' => BaseHtml::renderTagAttributes($attr),
                        'string' => $pt['name'],
                    ]
                );
        }

        $data['status'] = d::getMessage('AJAX_STATUS_SUCCESS');
        $data['header'] = d::getMessage('HEADER_SUCCESS');
        $data['message'] = d::getMessage('PROJECT_DELETED');

        d::echoAjax($data);

    } // f actionDeleteProject()

    /**
     * Страница "Проекты"
     * ==================
     * Кнопка "Удалить задачу"
     */
    public function actionDeleteTask()
    {
        //        sleep(2);
        $data = [];
        $data['status'] = d::getMessage('AJAX_STATUS_ERROR');

        $post = d::secureEncode($_POST);

        // Выбираем задачу по ID задачи и по ID проекта
        $task = Tasks::findOne([
            'id' => $post['task_id'],
            'project_id' => $post['project_id'],
        ]);

        // Удаляем задачу
        $task->delete();

        // Выбираем все строки "часы работы" по ID задачи и по ID проекта
        $working_hours = WorkingHours::findAll([
            'task_id' => $post['task_id'],
            'project_id' => $post['project_id'],
        ]);

        // Удаляем строки "время работы" из БД
        if (count($working_hours)) {
            foreach ($working_hours as $w_hour) {
                $w_hour->delete();
            }
        }

        // Получим все задачи из БД
        $tasks = Tasks::find()
            ->where(['project_id' => $post['project_id']])
            ->orderBy('name')->asArray()->all();

        // Если выбрались какие то задачи
        if ($tasks) {
            $data['options_tasks'] = Yii::getAlias('@select_task2');

            foreach ($tasks as $t) {

                $attr['value'] = $t['id'];
                $attr['data-code'] = $t['code'];
                $attr['data-description'] = $t['description'];
                $attr['data-active'] = $t['active'];

                if ($t['active'] == '1') {
                    $attr['style'] = 'color:#272BFF';
                }
                if ($t['active'] == '0') {
                    $attr['style'] = 'color:red';
                }

                $data['options_tasks'] .=
                    $this->renderAjax(
                        'shortcodes/options-list',
                        [
                            'attributes' => BaseHtml::renderTagAttributes($attr),
                            'string' => $t['name'],
                        ]
                    );
            }

        } else {
            // Если никаких задач не осталось
            $data['options_tasks'] = Yii::getAlias('@no_tasks_yet_new');
        }

        $data['status'] = d::getMessage('AJAX_STATUS_SUCCESS');
        $data['header'] = d::getMessage('HEADER_SUCCESS');
        $data['message'] = d::getMessage('TASK_DELETED');

        d::echoAjax($data);

    } // f actionDeleteProject()

    /**
     * Страница "Задачи"
     * =================
     * Кнопка "Закрыть задачу по ... "
     */
    public function actionCloseTask()
    {
        //        sleep(2);
        $data = [];
        $data['status'] = d::getMessage('AJAX_STATUS_ERROR');

        $time = time();
        $post = d::secureEncode($_POST);

        $pt = Projects::findOne(['id' => $post['project_id']]);

        if ($pt) {

            $today = new Rate();
            $rate = $today->getRate();
            $course = $rate['curse'];

            if ($post['state_course'] != $rate['curse']) {
                $course = $post['state_course'];
            }

            if (!isset($rate['error'])) {

                // IDs working_hours - строку разбиваем по запятой, делаем массив.
                $wh_ids = explode(',', $post['wh_ids']);
                // Выбираем из БД сумму секунд по этим строкам
                $whs_current_sum = WorkingHours::find()->where(['IN', 'id', $wh_ids])->sum('working_hours');
                // Получаем стоимость суммы строк working_hours закрывающейся задачи
                $cost = SNumberHelper::getCost($whs_current_sum);
//                $cost = $pt->tasks[0]->getCost($whs_current_sum);
                //                d::ajax($cost);

                // И записываем данные в таблицу Payments
                $payment = new Payment();
                $payment->project_id = $post['project_id'];
                $payment->task_id = $post['task_id'];
                $payment->ids_working_hours = $post['wh_ids'];
                $payment->working_hours = $whs_current_sum;
                // Текущий курс валюты(пока что USD), по которому закрываются wh строки (задача)
                $payment->currency_value = $pt['rate']['value'];
                $payment->currency = Yii::$app->params['default_currency'];
                /*
                 * Все строки wh, которые закрываются, для текущей задачи,
                 * должны быть зафиксированы по текущему курсу.
                 * Т.е. у всех строк wh - закрывающихся в текущий момент
                 * всегда будет одинаковый курс закрытия,
                 * который фиксируется в поле rate.
                 */
                $payment->rate = $course;
                // Общая стоимость всех закрывающихся wh строк на текущий момент
                $payment->cost = $cost;
                $payment->created_at = $time;
                $payment->updated_at = $time;

                /*
                 * Если в табилцу payments строка закрытия добавилась,
                 * то нужно, у всех закрывающихся строк wh, в поле status установить:
                 * 2-не оплачен
                 */
                if ($payment->save()) {

                    WorkingHours::updateAll(['status' => '2'], ['IN', 'id', $wh_ids]);

                    $whs_all = WorkingHours::find()
                        ->where(['task_id' => $post['task_id']])->all();

                    //                d::pe($whs_all);

                    $data['status'] = d::getMessage('AJAX_STATUS_SUCCESS');
                    $data['header'] = d::getMessage('HEADER_SUCCESS');
                    $data['message'] = d::getMessage('TASK_CLOSED');
                } else {
                    $data['type_message'] = d::getMessage('TYPE_WARNING');
                    $data['header'] = d::getMessage('HEADER_WARNING');
                    $data['message'] = d::getMessage('TASK_CLOSING_ERROR');
                }
            } else {
                $data['type_message'] = d::getMessage('TYPE_WARNING');
                $data['header'] = d::getMessage('HEADER_WARNING');
                $data['message'] = $rate['error'];
            }
        } else {
            $data['type_message'] = d::getMessage('TYPE_WARNING');
            $data['header'] = d::getMessage('HEADER_WARNING');
            $data['message'] = d::getMessage('PROJECT_NOT_FOUND');
        }

        //        d::pe($post);

        d::echoAjax($data);

    }

    /**
     * Страница "Задачи"
     * =================
     * Кнопка "Закрыть задачу"
     */
    public function actionEditTask()
    {

        $post = Yii::$app->request->post();
        //        d::pe($post);
        // Если нужно редактировать существующую задачу
        if (Ajax::editTask($post)) {
            $data['status'] = d::getMessage('AJAX_STATUS_SUCCESS');
            $data['header'] = d::getMessage('HEADER_SUCCESS');
            $data['message'] = d::getMessage('TASK_CHANGED');
        } else {
            $data['type_message'] = d::getMessage('TYPE_WARNING');
            $data['header'] = d::getMessage('HEADER_WARNING');
            $data['message'] = d::getMessage('TASK_EDIT_ERROR');
        }

        d::echoAjax($data);

    }

    public function actionCreateReport()
    {
        $data = [];
        $time = time();
        $reports = new Reports();

        $data['status'] = d::getMessage('AJAX_STATUS_ERROR');
        $status = false;

        $post = d::secureEncode($_POST);
//        d::ajax($post);

//        // Если создать отчёт
//        if ($post['request_type'] == 'create_report') {
//            $status = '3';
//            $data['message'] = d::getMessage('PAYMENT_REQUEST_COMPLETED');
//        }
//        // Если подтверждение оплаты
//        if ($post['request_type'] == 'proof_payment') {
//            $status = '4';
//            $data['message'] = d::getMessage('PAYMENT_CONFIRMED');
//        }

        $date_from = (date('Y') . '-' . date('m', $time) . '-01 00:00:00');
        $d = new \DateTime($date_from);
        $date_to = $d->format('Y-m-t') . ' 23:59:59';
        $date_from = strtotime($date_from);
        $date_to = strtotime($date_to);

        $report = $reports::find()
            ->andWhere(['>', 'created_at', $date_from])
            ->andWhere(['<', 'created_at', $date_to])
            ->one();

//        d::ajax($report);

        /*
         * Если отчёт за текущий месяц найден,
         * значит добавим в него закрытые задачи
         */
        if ($report) {
            $report_id = $report->id;
            /*
             * Если у текущего отчёта есть данные по часам
             * (в принципе этот параметр никогда не должен быть пустым)
             */
            if (
                $report->ids_projects_tasks_working_hours AND StringHelper::isJson($report->ids_projects_tasks_working_hours)
                AND $report->ids_payment AND StringHelper::isJson($report->ids_payment)
            ) {
//                d::ajax($post);

                // Обновляем IDs wh
                $report_ids_projects_tasks_working_hours_array = json_decode($report->ids_projects_tasks_working_hours, true);
                $report_ids_projects_tasks_working_hours_array = SArrayHelper::merge(
                    $report_ids_projects_tasks_working_hours_array,
                    $post['wh_ids']
                );

                // Объединяем IDs payment
                $report_ids_payment_array = json_decode($report->ids_payment, true);
                $report_ids_payment_array = SArrayHelper::merge(
                    $report_ids_payment_array,
                    $post['p_ids']
                );

                // IDs wh возращаем в json для записи в БД
                $report->ids_projects_tasks_working_hours = json_encode($report_ids_projects_tasks_working_hours_array);
                // IDs payment возращаем в json для записи в БД
                $report->ids_payment = json_encode($report_ids_payment_array);

                if ($report->save()) {
                    // В таблице working_hours добалим ID отчёта тем строкам, которые добавлены в отчёт.
                    // Обновляем поле reports_id в таблице WorkingHours
                    WorkingHours::addReportsId($report_id, $post['wh_ids']);
                    // Обновляем поле reports_id в таблице Payment
                    Payment::addReportsId($report_id, $post['p_ids']);
                    $data['status'] = d::getMessage('AJAX_STATUS_SUCCESS');
                    $data['header'] = d::getMessage('HEADER_SUCCESS');
                    $data['message'] = d::getMessage('REPORT_UPDATED');
                } else {
                    $data['type_message'] = d::getMessage('TYPE_WARNING');
                    $data['header'] = d::getMessage('HEADER_WARNING');
                    $data['message'] = d::getMessage('REPORT_UPDATE_ERROR');
                }
            }
        } else {
        // Если за текущий месяц ни один отчёт не найден, создадим новый
            $reports->project_id = $post['project_id'];
            $reports->title = 'Отчёт за ' . StringHelper::mb_lcfirst(SDateHelper::getMonthRu(date('m', $time)));
            $reports->ids_projects_tasks_working_hours = json_encode($post['wh_ids']);
            $reports->ids_payment = json_encode($post['p_ids']);
            if ($reports->save()) {
                $report_id = Yii::$app->db->lastInsertID;
                // В таблице working_hours добалим ID отчёта тем строкам, которые добавлены в отчёт.
                // Обновляем поле reports_id в таблице WorkingHours
                WorkingHours::addReportsId($report_id, $post['wh_ids']);
                // Обновляем поле reports_id в таблице Payment
                Payment::addReportsId($report_id, $post['p_ids']);
                $data['status'] = d::getMessage('AJAX_STATUS_SUCCESS');
                $data['header'] = d::getMessage('HEADER_SUCCESS');
                $data['message'] = d::getMessage('REPORT_CREATED');
            } else {
                $data['type_message'] = d::getMessage('TYPE_WARNING');
                $data['header'] = d::getMessage('HEADER_WARNING');
                $data['message'] = d::getMessage('REPORT_CREATION_ERROR');
            }
        }

        d::echoAjax($data);

    }

    /**
     * Страница "Отчёты"
     * =================
     * Кнопки "Отправить отчёт"
     */
    public function actionChangeStatusPayment()
    {
        //        sleep(2);
        $data = [];
        $data['status'] = d::getMessage('AJAX_STATUS_ERROR');
        $status = false;

        $post = d::secureEncode($_POST);
        //        d::pe($post);

        // Если запрос оплаты
        if ($post['request_type'] == 'payment_request') {
            $status = '3';
            $data['message'] = d::getMessage('PAYMENT_REQUEST_COMPLETED');
        }
        // Если подтверждение оплаты
        if ($post['request_type'] == 'proof_payment') {
            $status = '4';
            $data['message'] = d::getMessage('PAYMENT_CONFIRMED');
        }

        if ($status !== false) {

            $p_ids = explode(',', $post['p_ids']);
            $wh_ids = explode(',', $post['wh_ids']);

            if (
                Payment::updateAll(['status' => $status], ['IN',
                    'id', $p_ids]) and
                WorkingHours::updateAll(['status' => $status], ['IN',
                    'id', $wh_ids])
            ) {
                $data['status'] = d::getMessage('AJAX_STATUS_SUCCESS');
                $data['header'] = d::getMessage('HEADER_SUCCESS');
            } else {
                $data['type_message'] = d::getMessage('TYPE_WARNING');
                $data['header'] = d::getMessage('HEADER_WARNING');
                $data['message'] = d::getMessage('ERROR_UPDATE');
            }
        } else {
            $data['type_message'] = d::getMessage('TYPE_WARNING');
            $data['header'] = d::getMessage('HEADER_WARNING');
            $data['message'] = d::getMessage('STATUS_ERROR');
        }

        //        d::pe($post);

        d::echoAjax($data);

    }

    /**
     * Страница "Проекты/задачи"-список проектов
     * =========================================
     * Кнопка "Зелёная галочка" изменить тариф проекта
     */
    public function actionEditRate()
    {

        $data = [];
        $data['status'] = d::getMessage('AJAX_STATUS_ERROR');
        $post = Yii::$app->request->post();
        $pt = Projects::find()
            ->where(['id' => $post['project_id']])
            ->one();

        if ($pt) {

            $pt['rate'] = json_encode([
                'name' => $pt->rate['name'],
                'value' => $post['value']
            ]);

            if ($pt->save()) {
                $data['status'] = d::getMessage('AJAX_STATUS_SUCCESS');
                $data['header'] = d::getMessage('HEADER_SUCCESS');
                $data['message'] = d::getMessage('SAVED');
            } else {
                $data['type_message'] = d::getMessage('TYPE_WARNING');
                $data['header'] = d::getMessage('HEADER_WARNING');
                $data['message'] = d::getMessage('ERROR_SAVE');
            }
        }

        d::echoAjax($data);
    }

    /**
     * Страница "Проекты/задачи"-список проектов
     * =========================================
     * Кнопки "Запрос оплаты","Подтвердить оплату"
     */
    public function actionProjectChangeStatusPayment()
    {
        //        sleep(2);
        $data = [];
        $data['status'] = d::getMessage('AJAX_STATUS_ERROR');
        $status = false;
        $status_where = false;

        $post = d::secureEncode($_POST);
        //        d::pe($post);

        // Если запрос оплаты
        if ($post['request_type'] == 'payment_request') {
            $status_where = '2';
            $status = '3';
            $data['message'] = d::getMessage('PAYMENT_REQUEST_COMPLETED');
        }
        // Если подтверждение оплаты
        if ($post['request_type'] == 'proof_payment') {
            $status_where = '3';
            $status = '4';
            $data['message'] = d::getMessage('PAYMENT_CONFIRMED');
        }

        if ($status and $status_where) {

            if (
                Payment::updateAll(
                    ['status' => $status],
                    [
                        'project_id' => $post['project_id'],
                        'status' => $status_where,
                    ]
                ) and
                WorkingHours::updateAll(
                    ['status' => $status],
                    [
                        'project_id' => $post['project_id'],
                        'status' => $status_where,
                    ]
                )
            ) {
                $data['status'] = d::getMessage('AJAX_STATUS_SUCCESS');
                $data['header'] = d::getMessage('HEADER_SUCCESS');
            } else {
                $data['type_message'] = d::getMessage('TYPE_WARNING');
                $data['header'] = d::getMessage('HEADER_WARNING');
                $data['message'] = d::getMessage('ERROR_UPDATE');
            }

        } else {
            $data['type_message'] = d::getMessage('TYPE_WARNING');
            $data['header'] = d::getMessage('HEADER_WARNING');
            $data['message'] = d::getMessage('STATUS_ERROR');
        }

        d::echoAjax($data);

    }

    /*
     * Странци "Настройки WebMaster"
     * =============================
     * Кнопка "Сохранить"
     */
    public function actionSaveSettings()
    {
        //        sleep(2);
        $data = [];
        $data['status'] = d::getMessage('AJAX_STATUS_ERROR');

        $post = Yii::$app->request->post();

        if (Ajax::saveSettings($post)) {
            $data['status'] = d::getMessage('AJAX_STATUS_SUCCESS');
            $data['header'] = d::getMessage('HEADER_SUCCESS');
            $data['message'] = d::getMessage('SAVED');
        } else {
            $data['type_message'] = d::getMessage('TYPE_WARNING');
            $data['header'] = d::getMessage('HEADER_WARNING');
            $data['message'] = d::getMessage('ERROR_SAVE');
        }

        d::echoAjax($data);

    }

    /*
     * Странци "Настройки WebMaster"
     * Вкладка "Статистика"
     * =============================
     * Кнопка "Получить курс доллара"
     */
    public function actionGetRate()
    {
        //        sleep(2);
        $data = [];
        $data['header'] = d::getMessage('HEADER_SUCCESS');
        $data['message'] = d::getMessage('COURSE_RECEIVED');

        $today = new Rate();
        // Курс сегодня
        // Курс долара, в скобках официальный код валюты
        /**
         * В методе getRate2() - происходит только получение информации
         * т.е. в кэш ничего не записывается.
         * в кэш записываем уже здесь, то что получили, то зписали в кэш тут.
         */
        $arr_rate = $today->getRate2();
        //        d::pe($arr_rate);

        $cache = Yii::$app->cache;
        // Время жизни кэша 12 часов
        $sec = 43200;

        // ==========================
//        d::pe($cache->get('rate'));
//        $cache->delete('rate');
        // ==========================

        $cache->set('rate', $arr_rate['curse'], $sec);

        $data['current_curs'] = $arr_rate['curse'];

        d::echoAjax($data);

    }

    /*
     * Странци "Настройки WebMaster"
     * Вкладка "Статистика"
     * =============================
     * Кнопка "Получить курс доллара"
     */
    public function actionGetSettings()
    {
        //        sleep(2);
        $data = [];

        $ss = WebmasterSettings::find()
            ->indexBy('code')
            ->where(['code' => 'time_trecker'])
            ->one();
        //        d::pe($ss);

        $po = $ss->settings['pomodoro'];
        $data['time_work'] = $po['time_work'];
        $data['time_relax'] = $po['time_relax'];
        $data['long_relax'] = $po['long_relax'];

        //        d::pe($data);

        d::echoAjax($data);

    }

    public function actionChangeTaskStatus()
    {
        $post = Yii::$app->request->post();
        //        d::pe($post);
        if (isset($post['id'])) {
            $t = Tasks::findOne(['id' => $post['id']]);
            //            d::pe($t);
            $t->active = $post['status'];
            if ($t->save()) {
                $data = d::success('Статус изменён');
            } else {
                $data = d::error('Не сохранилось почему то');
            }
        } else {
            $data = d::warning('ID задачи не передан!');
        }
        d::echoAjax($data);
    }

    public function beforeAction($action)
    {
        //         sleep(2);
//         exit('haha');
        return parent::beforeAction($action); // TODO: Change the autogenerated stub
    }























    /**
     * Тестовый action
     */
    public function actionBeget()
    {
        //        sleep(2);

        $ss = WebmasterSettings::find()
            ->indexBy('code')
            ->where(['code' => 'main'])
            ->one();

        d::ajax($ss);

    }


    /**
     * Тестовый action
     * ===============
     * Кнопка ".test"
     */
    public function actionDebug()
    {
        //        sleep(2);
        $data = [];

        d::echoAjax($data);

    }


    /**
     * Тестовый action
     * ===============
     * Кнопка ".test"
     */
    public function actionTest()
    {
        //        sleep(2);

        $data = ['status' => 200];
        $post = Yii::$app->request->post();
        $post = d::secureEncode($post);

        $data['m'] = Ajax::editData($post);

        d::echoAjax($data);

    }

} // End Class
