<?php
namespace backendold\controllers;

use backendold\models\BalanceBeginMonth;
use backendold\models\FinanceMovement;
use backendold\models\Projects;
use backendold\models\Tasks;
use backendold\models\Wallets;
use backendold\models\Reports;
use backendold\models\WebmasterSettings;
use backendold\models\Payment;
use backendold\components\OtherDebugger;
use backendold\helpers\SNumberHelper;
use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use backendold\controllers\MainController as d;
use backendold\components\ReceivingExchangeRates as rbc;
use shadow\helpers\StringHelper as SH;

/**
 * Site controller
 */
class SiteController extends MainController
{

    /**
     * {@inheritdoc}
     */
    public function behaviors_orig()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
//                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }
    public function behaviors22()
    {
        return [
//            'access' => [
//                'class' => AccessControl::className(),
//                'only' => ['logout', 'signup'],
//                'rules' => [
//                    [
//                        'actions' => ['signup'],
//                        'allow' => true,
//                        'roles' => ['?'],
//                    ],
//                    [
//                        'actions' => ['logout'],
//                        'allow' => true,
//                        'roles' => ['@'],
//                    ],
//                ],
//            ],
            'access' => [
                'class' => AccessControl::className(),
                // В общем либо эта строка
//                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        // Либо эта (или или)
                        'actions' => ['logout', 'login', 'error', 'send-form', 'modals', 'index'],
                        'allow' => true,
//                        'roles' => ['?']
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
//                    'logout' => ['post'],
                    'logout' => ['get'],
                ],
            ],
        ];
    }
    public function behaviors33()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
//                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
//                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'tab-debug-ajax' => [
                'class' => 'backendold\actions\TabsAjaxActions',
                'actions' => [
                    'debug' => 'Debug',
                ],
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
//        return $this->render('index');
        Yii::$app->response->redirect(Url::to('/admin-old/time-trecker'));
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            // после авторизации - редиректим на страницу "форма поиска"
            Yii::$app->response->redirect(Url::to('/admin/time-trecker'));
//            return $this->goBack();
        } else {
            $model->password = '';

            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /* ===========================================
              Страницы WebMaster
    ============================================*/

    /**
     * Страница "Time Трекер".
     *
     * @return string
     */
    public function actionTimeTrecker()
    {
        $ws = WebmasterSettings::findOne(['code'=>'time_trecker']);
        return $this->render('webmaster/time-trecker',[
            // Подсказки
            'alerts'    => $this->renderAjax('shortcodes/alerts'),
            'tr_empty' => $this->renderAjax('shortcodes/tr-empty'),
            'settings_time_trecker' => $ws,
        ]);
    }

    /**
     * Страница "Настройки WebMaster".
     *
     * @return string
     */
    public function actionSettingsWebmaster()
    {

        $ws = WebmasterSettings::find()->indexBy('code')->all();
//        d::pex($ws);

        return $this->render('webmaster/settings-webmaster',[
            // Подсказки
            'alerts'    => $this->renderAjax('shortcodes/alerts'),
            // Большое модальное окно
//            'modal_lg' => $this->renderAjax('shortcodes/modal-lg'),
            'ss'=>$ws,
        ]);
    }

    /**
     * Страница "Добавление задач".
     *
     * @return string
     */
    public function actionProjects()
    {
        return $this->render('webmaster/projects');
    }

    /**
     * Страница "Настройки WebMaster".
     *
     * @return string
     */
    public function actionStatistics()
    {
        $ws = WebmasterSettings::findOne(['code'=>'statistics']);

        $today = new rbc();

        //Курс на завтра
//        $tommorow= new rbc(strtotime("+1 day"));
//        $data['tommorow'] = $tommorow -> curs(840)['curs'];

        return $this->render('webmaster/statistics',[
            // Подсказки
            'alerts'    => $this->renderAjax('shortcodes/alerts'),
            // Курс сегодня
            // Курс долара, в скобках официальный код валюты
            'current_course' => $today -> getRate()['curse'],
            'settings_statistics' => $ws,
            // Большое модальное окно
//            'modal_lg' => $this->renderAjax('shortcodes/modal-lg'),
        ]);
    }

    /**
     * Страница "Задачи/проекты".
     *
     * @return string
     */
    public function actionProjectsList()
    {
        $get = \backendold\controllers\MainController::secureEncode(Yii::$app->request->get());

        $data = [];
        $wh_sort = [];
        $data['inwork_total_amount'] = 0;
        // Подсказки
//        $data['alerts'] = $this->renderAjax('shortcodes/alerts');

        // Если в GET есть ID проекта
        if (isset($get['id']) and is_numeric($get['id'])) {

            $data['project'] = Projects::findOne($get['id']);

            // Получаем все задачи по проекту
            $tasks = Tasks::find()
                ->where(['project_id' => $get['id']])
                ->orderBy(['id' => SORT_DESC])
                ->all();

            /*
             * Если задачи найдены, то рассортируем их по типам
             * в работе/закрытые(не оплаченые)/ожидание оплаты/оплаченные
             */
            if ($tasks) {

                $id_ignore = [
                    '93',
                ];

                // Перебираем задачи
                foreach ($tasks as $task) {
                    // Игнор лист задач
                    if (in_array($task->id, $id_ignore)) {
                        continue;
                    }

                    $data['tasks'][$task->id] = $task;
                    $wh_sort[$task->id] = $task->sortWH();

                    // Позже глянуть, где что исчезло
//                        $data['wh_inwork']['wh_total_amount']['total_info'][] = [
//                            $task->id,
//                            $task->name,
//                            SNumberHelper::getCost($task->whinworksum)
//                        ];

                    /*
                     * В общую сумму не закрытых задач "в работе"
                     * считаем только активные задачи, которые включены.
                     */
                    if ($task->active == '1') {
                        $data['inwork_total_amount'] += SNumberHelper::getCost($task->whinworksum);
                    }

//                    }

                } //foreach($tasks)
            }
//            d::pex($wh_sort);
            $data['wh_sort'] = $wh_sort;

            /*
             * Собираем отчёт по неоплаченным задачам, для копирования.
             */
            $tasks = '';
            $for_pay = 0;
            if(count($wh_sort)){
                foreach($wh_sort as $task_id => $item){
                    $no_paid = $item['no_paid'];
                    if ( $no_paid['cost'] == 0 ) continue;

                    $tasks .=
                        'Задача ' . $task_id . ':<br>' .
                        $data['tasks'][$task_id]->name . '<br>' .
                        'Описание задачи:<br>' . $data['tasks'][$task_id]->description . '<br>' .
                        'Выполнено<br>' .
                        'Время потраченное на задачу: ' . $no_paid['time_sum']['h_i_s'] . '<br>' .
                        'К оплате: ' . number_format($no_paid['cost'], 2, '.', ' ') . ' р.<br><br>' .
                        '----------------------<br>';
                    $for_pay += $no_paid['cost'];
                }
            }

            $pmt = \common\models\Payment::findAll([
                'project_id' => $get['id'],
                //                'status'=>'2'
            ]);

            $data['total'] = [
                'no_paid' => [
                    'cost' => 0,
                    'wh_ids' => '',
                    'p_ids' => '',
                ],
                'waitpay' => [
                    'cost' => 0,
                    'wh_ids' => '',
                    'p_ids' => '',
                ],
                'paid' => [
                    'cost' => 0,
                    'wh_ids' => '',
                    'p_ids' => '',
                ],
            ];

            /*
             * Собираем информацию для таблицы, которая находится сразу над списком задач.
             * В которой показываются общие суммы "не оплаченных/ожидающих оплаты/оплаченных" задач.
             */
            if (count($pmt)) {
                foreach ($pmt as $pt) {
                    switch ($pt->status) {
                        case '2':
                            $data['total']['no_paid']['cost'] += $pt->cost;
                            $data['total']['no_paid']['wh_ids'] .= $pt->ids_working_hours . ',';
                            $data['total']['no_paid']['p_ids'] .= $pt->id . ',';
                            break;
                        case '3':
                            $data['total']['waitpay']['cost'] += $pt->cost;
                            $data['total']['waitpay']['wh_ids'] .= $pt->ids_working_hours . ',';
                            $data['total']['waitpay']['p_ids'] .= $pt->id . ',';
                            break;
                        case '4':
                            $data['total']['paid']['cost'] += $pt->cost;
                            $data['total']['paid']['wh_ids'] .= $pt->ids_working_hours . ',';
                            $data['total']['paid']['p_ids'] .= $pt->id . ',';
                            break;
                    }
                }

                $data['total']['no_paid']['wh_ids'] = substr($data['total']['no_paid']['wh_ids'], 0, -1);
                $data['total']['no_paid']['p_ids'] = substr($data['total']['no_paid']['p_ids'], 0, -1);
                $data['total']['waitpay']['wh_ids'] = substr($data['total']['waitpay']['wh_ids'], 0, -1);
                $data['total']['waitpay']['p_ids'] = substr($data['total']['waitpay']['p_ids'], 0, -1);
                $data['total']['paid']['wh_ids'] = substr($data['total']['paid']['wh_ids'], 0, -1);
                $data['total']['paid']['p_ids'] = substr($data['total']['paid']['p_ids'], 0, -1);

            }

            if ($for_pay > 0) {
                $data['report'] =
                    $tasks . '<br>Итого к оплате по проекту ' .
                    $data['project']->name . ': ' . number_format($for_pay, 2, '.', ' ') . ' р.';
            }

            return $this->render('webmaster/tasks', $data);
        } else {

            $ps = Projects::find()
                ->orderBy(['sort' => SORT_DESC])
                ->all();

            $ws = \backendold\models\WebmasterSettings::findOne(['code' => 'statistics']);

            $data['ws'] = $ws;
            $data['projects'] = $ps;

            return $this->render('webmaster/projects-list', $data);
        }

    }

    public function actionTaskList()
    {
        $pid = (Yii::$app->request->get('pid'))?:'1';
        $tasks = Tasks::find()
            ->where(['active'=>'1'])
            ->andWhere(['IN','project_id', ['12','13','18'] ])
            ->orderBy('project_id')
            ->all();

//        d::pex($tasks);
        return $this->render('webmaster/task-list',['tasks'=>$tasks]);

    }

    /**
     * Страница "Расчёты"
     * @return string
     */
    public function actionCalculations()
    {

        $sber = new rbc;
        $course = $sber->getRate();
        $settings = WebmasterSettings::findOne(['code' => 'statistics']);
//        d::pri();

        return $this->render('webmaster/calculations',[
            // Подсказки
            'alerts'    => $this->renderAjax('shortcodes/alerts'),
            // Большое модальное окно
//            'modal_lg' => $this->renderAjax('shortcodes/modal-lg'),
            'course' => $course,
            'rate' => $settings->settings['rate']['cost'],
        ]);
    }

    /**
     * Страница "Расчёты"
     * @return string
     */
    public function actionReports()
    {
        $reports = Reports::find()->all();
        return $this->render('webmaster/reports', [
            'reports' => $reports,
        ]);
    }

































    /**
     * Страница "Отправка Eamil".
     *
     * @return string
     */
    public function actionEmail()
    {

        return $this->render('email',[
            // Подсказки
            'alerts'    => $this->renderAjax('shortcodes/alerts'),
            // Большое модальное окно
//            'modal_lg' => $this->renderAjax('shortcodes/modal-lg'),
        ]);
    }

    /**
     * Страница "Отправка Eamil".
     *
     * @return string
     */
    public function actionTest()
    {

        return $this->render('test',[
            // Подсказки
            'alerts'    => $this->renderAjax('shortcodes/alerts'),
            // Большое модальное окно
//            'modal_lg' => $this->renderAjax('shortcodes/modal-lg'),
        ]);
    }

    public function actionDebug()
    {
        //        d::ajax('haha');
        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if (isset($post['inputs'])) {
                $inputs = \common\components\Debugger::serializeToArray($post['inputs']);
                $post = array_merge($post, $inputs);
            }
            //            d::ajax($post);

            //Debug files (для tab1 - Debug скрипты)
            if (isset($post['type'])) {
                $result_type = [];
                switch ($post['type']) {
                    case 'onesignal_send_notification':
                        //d::ajax(Yii::$app->user->id);

                        // 19299
                        $one_data = [
                            'header' => $post['header'],
                            'message' => $post['message']
                        ];
                        if ((isset($post['user_id']) and $post['user_id'] != '')) {
                            $one_data['user_ids'] = [(string) $post['user_id']];
                        }
                        OtherDebugger::onesignal($one_data);
                        break;
                    case 'send_maxma':
                        OtherDebugger::maxma();
                        break;
                    default:
                        $result_type = 'Ничего не произошло';
                }
                d::ajax($result_type);
            }
            d::ajax('Не задан name кнопки');
        } else {
            return $this->render('debug');
        }
    }

}//Class
