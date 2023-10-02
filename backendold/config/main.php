<?php

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-backend-old',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backendold\controllers',
    'bootstrap' => ['log'],
    'modules' => [],
    'sourceLanguage' => 'en',
    'language' => 'ru-RU',
    /*
     * Таймзона кемеровской области
     * после установки сайта на сервер - надо проверить
     * правильно ли показывает время, и если что подкорректировать.
     */
    'aliases' => [
        // для функции number_format
        '@ko' => 2,// количество знаков "копеек" kopecks
        '@fl' => '.',// "знак" между "рублями" и "копейками" FLoat
        '@th' => '',// "знак между сотнями/тысячами THousand
        // =========================
        // значение для нулевых числовых значений
        '@zero' => '0.00',
        '@zero,' => '0,00',
        '@zero_one' => '0',

        // Значение для таймера по умполчанию
        '@zero_time' => '00',
        /*
         * Строка для поля code в справочниках
         * потому что в справочниках поля code
         * пока не задействованы (кроме бренд|пол|товарная группа)
         */
        '@empty_data_field' => '00',
        '@files_excel' => '@common/files/excel',
        '@timetrecker' => '@common/files/timetrecker',
        /*
         * Шаблон пустой строки tr
         * переменную можно получить и в PHP
         * и в JS
         * Один шаблон для всех частей сайта
         */
        '@tr_empty' => '<tr class="empty"><td colspan="16">Пока пусто</td></tr>',
        /*
         * Шаблон option, если строка пуста
         * переменную можно получить и в PHP
         * и в JS
         * Один шаблон для всех частей сайта
         */
        '@tasks_not_found' => '<option value="">Нет ни одной задачи</option>',
        /*
         * Шаблон option, если строка не пуста
         * Первое значение
         */
        '@select_task' => '<option value="">Выберите задачу</option>',
        '@select_task2' => '<option value="">Выберите задачу</option><option value="new_task">Новая задача</option>',
        '@select_project_new' => '<option value="">Выберите проект</option><option value="0" data-code="new">Добавить новый проект</option>',
        '@no_tasks_yet' => '<option value="">Задач пока нет</option>',
        '@no_tasks_yet_new' => '<option value="">Задач пока нет</option><option value="new_task">Новая задача</option>',
        '@task_view_empty' => '<option value="">Вид задачи (0)</option>',
        '@select_task_type' => '<option value="">Выберите вид задачи</option>',

    ],
    'components' => [
        'request' => [
            'baseUrl' => '/admin-old',
            'csrfParam' => '_csrf-backend-old',
        ],
        'ws' => [
            'class' => 'backendold\components\WebSettings',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'rbc'=>[
            'class'=>'backendold\components\ReceivingExchangeRates',
//            function () { return new \backendold\components\ReceivingExchangeRates(time()); },
        ],
        'formatter' => [
            'dateFormat' => 'Y-MM-dd',
            'timeFormat' => 'H_mm_ss',
            'datetimeFormat' => 'd.MM.Y H:mm',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            //'class' => 'backendold\components\User', // extend User component
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend-old', 'httpOnly' => true],
//            'loginUrl' => ['site/login'],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'admin-old',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
//            'maxSourceLines' => 5,
        ],
        'urlManager' => [
            'enablePrettyUrl' =>true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                'tab-debug-ajax' => 'site/tab-debug-ajax',
                '<action>' => 'site/<action>',
            ],
        ],
        'assetManager' => [
//            'basePath' => '@webroot/assets',
//            'baseUrl' => '@web/assets',
//            'bundles' => [
//                'yii\web\JqueryAsset' => [
//                    'js'=>['http://code.jquery.com/jquery-latest.min.js']
//                ],
//                'yii\bootstrap\BootstrapPluginAsset' => [
////                    'js'=>[]
//                ],
//                'yii\bootstrap\BootstrapAsset' => [
////                    'css' => [],
//                ],
//            ],
        ],
    ],
    'params' => $params,
];
