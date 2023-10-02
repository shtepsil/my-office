<?php
/**
 * Класс для работы с Ajax запросами 
 */
namespace backendold\models;

use backendold\models\BalanceBeginMonth;
use backendold\models\Projects;
use backendold\models\Tasks;
use backendold\models\TaskView;
use backendold\models\Wallets;
use backendold\models\WorkingHours;
use common\models\User;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use backendold\controllers\MainController as d;
use backendold\models\FinanceMovement;
use yii\helpers\ArrayHelper;
use shadow\helpers\StringHelper as SH;

class Ajax extends Model
{

    /**
     * Страница "Добавление отчета"
     * ===============================
     * Кнопка "Открыть текущий месяц"
     *
     * @return array $data
     */
    public static function openMonth($data)
    {

        $return = [];
        $return['total_amount'] = 0;
        $json_wallets = [];

        $dd = explode('-', $data['date_open']);
        // Берем из даты только год и месяц
        $date_search = $dd[0] . '-' . $dd[1];

        // Пробуем получить строку из таблицы balance_begin_month
        $bbm = BalanceBeginMonth::find()
            ->where(['like', 'date_time', $date_search])
            ->asArray()->one();

        /*
         * Если ничего не найдено
         * нужно внести новые данные балансов кошельков
         */
        if (!$bbm) {

            // Получаем все кошельки из таблицы wallets
            $wallets = Wallets::find()->asArray()->all();
            $return['wallets'] = $wallets;

            $wallets = ArrayHelper::map($wallets, 'id', 'balance');

            // Создаем json для поля wallets таблицы balance_begin_month
            $json_wallets = json_encode($wallets);

            // Порядок имен полей таблицы
            $fields = [
                'wallets',
                // кошельки
                'date_time',
                // дата открытия месяца
                'created_at', // дата создания
            ];
            $wallets_tb = [
                [
                    'wallets' => $json_wallets,
                    'date_time' => $data['date_open'],
                    'created_at' => Yii::$app->getFormatter()->asTimestamp(time() - 10800),
                ]
            ];

            $bbm_command = Yii::$app->db->createCommand()->batchInsert(
                'balance_begin_month',
                $fields,
                $wallets_tb
            );

            try {
                $bbm_command->execute();

                // Получаем ID последней записи в БД
                $last_insert_id = Yii::$app->db->getLastInsertID();
                // Получаем строку таблицы текущего месяца
                $open_m = BalanceBeginMonth::find()
                    ->where(['id' => $last_insert_id])
                    ->asArray()->one();

                if (count($open_m)) {
                    $wallets_bbm = json_decode($open_m['wallets']);
                    // Суммируем все балансы кошельков
                    foreach ($wallets_bbm as $w) {
                        $return['total_amount'] += $w;
                    }
                }

            } catch (Exception $e) {
                $result['errors'] = d::getMessage('MONTH_OPEN_ERROR');
            }

        } else {
            $return['errors'] = d::getMessage('MONTH_ALREDAY_OPEN');
        }

        return $return;

    } // function openMonth(...)

    /**
     * Страница "Добавление отчета"
     * ===============================
     * Кнопка "Сохранить отчет"
     *
     * @return array $data
     */
    public static function saveReport($data)
    {

        //        d::pe($data);

        $report = [];
        $return = [];

        /*
         * Массив, где
         * ключ - ID кошелька
         * значение - сумма, собранная в процессе итераций
         * -----------------------------------------------
         * при переборе строк, сумма будет меняться,
         * в зависимости от операции (приход/расход)
         * и на выходе получим уже итоговую сумму
         * каждого кошелька
         */
        $wallets = [];

        $cur_wallets = [];

        // Получим текущие балансы кошельков
        $cur_wall = Wallets::find()->asArray()->all();

        // В ключи массива вставим ID кошельков
        foreach ($cur_wall as $w)
            $cur_wallets[$w['id']] = $w;

        //        d::pe($cur_wallets);

        /*
         * Добавляем елемент "тип операции"
         * Ориентируясь по пустоте дат
         */

        $jj = 0;
        $ii = 0; //        d::pe($data['rows']);

        foreach ($data['rows'] as $value) {
            /*
             * Сумма, которая перемещается
             * указывается в полях для дохода/прихода.
             * По этому ориентируемся по полю "дата прихода".
             * Если поле не пусто, значит это либо приход/доход,
             * либо перемещение.
             */
            if ($value['date_in'] != '') {
                /*
                 * Если "checkbox" перемещение выключено, то это доход.
                 * Т.е. если в строке текущей итерации
                 * отсутствует значение "moving_accounts"
                 * значит это приход/доход
                 */
                if (!$value['moving_accounts']) {
                    // 1 - Приход/доход
                    $value['type_operation'] = '1';
                } else {
                    // Иначе - это переменщение
                    $value['type_operation'] = '3';
                    $created_at =
                        Yii::$app->getFormatter()->asTimestamp(time() - 10800);

                    /*
                     * Сумму перевода + (процент за перевод, если он есть)
                     * минусуем от суммы кошелька с которого делаем перевод
                     * и плюсуем к сумме кошелька на который делаем перевод
                     */

                    /*
                     * Если в массиве уже есть кошелек (с которого делается перевод)
                     * то от суммы этого кошелька
                     * минусуем процент комиссии и сумму операции
                     * и перезаписываем значение баланса кошелька
                     */
                    if (array_key_exists($value['moving_wallet_from'], $wallets)) {
                        $wallets[$value['moving_wallet_from']] =
                            (($wallets[$value['moving_wallet_from']] -
                                $value['percentage']) - $value['ammount']);
                    } else {
                        /*
                         * Если в массиве "кошелька" ещё нет
                         * то добавляем новый, где значением будет
                         * ((баланс кошелька минус процент) минус сумма операции)
                         */
                        $wallets[$value['moving_wallet_from']] =
                            (($cur_wallets[$value['moving_wallet_from']]['balance'] -
                                $value['percentage']) - $value['ammount']);
                        //                            (($value['moving_wallet_from_ammount'] -
//                                    $value['percentage']) - $value['ammount']);
                    }

                    /*
                     * Если в массиве уже есть кошелек (на который делается перевод)
                     * то к сумме этого кошелька плюсуем сумму операции
                     * и перезаписываем значение баланса кошелька
                     */
                    if (array_key_exists($value['moving_wallet_to'], $wallets)) {
                        $wallets[$value['moving_wallet_to']] =
                            ($wallets[$value['moving_wallet_to']] + $value['ammount']);
                    } else {
                        /*
                         * Если в массиве "кошелька" ещё нет
                         * то добавляем новый, где значением будет
                         * баланс кошелька плюс сумма операции
                         */
                        $wallets[$value['moving_wallet_to']] =
                                //                            ($value['moving_wallet_to_ammount']+$value['ammount']);
                            ($cur_wallets[$value['moving_wallet_to']]['balance'] + $value['ammount']);
                    }

                    // Операция расхода
                    $report[] = [
                        "ammount" => $value['ammount'],
                        "comment" => $value['comment'],
                        "created_at" => $created_at,
                        "date_in" => "",
                        "date_out" => $value['date_in'],
                        "date_time" =>
                        strtotime($value['date_in'] . date(' H:i:s', $created_at)),
                        "income" => "0",
                        "monthly" => "0",
                        "tags" => "move",
                        "type_operation" => "2",
                        "wallet_id" => $value['moving_wallet_from']
                    ];
                    // Операция дохода
                    $report[] = [
                        "ammount" => $value['ammount'],
                        "comment" => $value['comment'],
                        "created_at" => $created_at,
                        "date_in" => $value['date_in'],
                        "date_out" => "",
                        "date_time" =>
                        strtotime($value['date_in'] . date(' H:i:s', $created_at)),
                        "income" => "0",
                        "monthly" => "0",
                        "tags" => "move",
                        "type_operation" => "1",
                        "wallet_id" => $value['moving_wallet_to']
                    ];

                } // else перемещние

                $date_action = $value['date_in'];

                /*
                 * Если выравнивание счета выключено
                 * то в процессе есть кошельки
                 */
                if ($value['wallet_id']) {

                    /*
                     * Если в массиве уже есть кошелек
                     * то к сумме этого кошелька (к балансу кошелька)
                     * прибавляем сумму операции
                     * и перезаписываем значение баланса кошелька
                     */
                    if (array_key_exists($value['wallet_id'], $wallets)) {
                        $wallets[$value['wallet_id']] = (
                            $wallets[$value['wallet_id']] + $value['ammount']
                        );
                    } else {
                        /*
                         * Если в массиве "кошелька" ещё нет
                         * то добавляем новый, где значением будет
                         * текущий баланс текущего кошелька ПЛЮС сумма операции
                         */
                        $wallets[$value['wallet_id']] = (
                            $cur_wallets[$value['wallet_id']]['balance'] + $value['ammount']
                        );
                    }
                }
            }
            //            if($value['date_out'] != ''){
            else {
                // Если это расход
                $value['type_operation'] = '2';
                $date_action = $value['date_out'];

                /*
                 * Если выравнивание счета выключено
                 * то в процессе есть кошельки
                 */
                if ($value['wallet_id']) {

                    /*
                     * Если в массиве уже есть кошелек
                     * то от суммы этого кошелька (от баланса кошелька)
                     * отнимаем сумму операции
                     * и перезаписываем значение баланса кошелька
                     */
                    if (array_key_exists($value['wallet_id'], $wallets)) {
                        $wallets[$value['wallet_id']] = (
                            $wallets[$value['wallet_id']] - $value['ammount']
                        );
                    } else {
                        /*
                         * Если в массиве "кошелька" ещё нет
                         * то добавляем новый, где значением будет
                         * баланс кошелька минус сумма операции
                         */
                        $wallets[$value['wallet_id']] = (
                            //                            $value['wallet_balance'] - $value['ammount']
                            $cur_wallets[$value['wallet_id']]['balance'] - $value['ammount']
                        );
                    }
                }
            }

            // Чекбокс "Доход"
            $value['income'] = ($value['income'] != '') ? '1' : '0';

            //            if($value['income'] != ''){
//                $value['income'] = '1';
////                ?'1':'0';
//            }else{
//                $value['income'] = '0';
//            }

            //            d::pe($value['income']);

            // Текущая дата
            $value['created_at'] = Yii::$app->getFormatter()->asTimestamp(time() - 10800);
            /*
             * Дата, в которую произошло событие
             * Можно добавлять события задним числом
             */
            $value['date_time'] =
                strtotime($date_action . date(' H:i:s', $value['created_at']));

            //            unset($value['wallet_balance']);
//            unset($value['moving_accounts']);
//            unset($value['moving_wallet_from']);
//            unset($value['moving_wallet_from_ammount']);
//            unset($value['moving_wallet_to']);
//            unset($value['moving_wallet_to_ammount']);
//            unset($value['percentage']);

            ksort($value);

            $report[] = d::secureEncode($value);

        } // foreach($data[rows])

        //        d::jtd($wallets);

        $fm = new FinanceMovement();

        $fm_attrs = [];
        foreach ($fm->getAttributes() as $key => $item) {
            $fm_attrs[] = $key;
        }

        // удаляем лишние элементы из массива $report
        $report_to_db = [];
        // Перебираем основной массив
        foreach ($report as $arr) {
            // Перебираем вложенные массивы
            foreach ($arr as $key => $item) {
                /*
                 * Если в массиве атрибутов модели
                 * нет текущего ключа
                 * то из вложенного массива удаляем элемент по ключу
                 */
                if (!in_array($key, $fm_attrs))
                    unset($arr[$key]);
            }
            // Собираем массив для запии в таблицу
            $report_to_db[] = $arr;
        }

        if (count($wallets) != 0) {
            // ==========================================
            // тут собираем строку UPDATE таблицы Wallets
            // ==========================================

            $where_ws = '';
            $query_ws = "UPDATE `wallets` SET ";

            foreach ($wallets as $k => $w) {
                // собираем SQL строку
                $query_ws .= "`balance`= CASE
                        WHEN `id`='{$k}'
                        THEN '{$w}'
                        ELSE `balance` END, ";
                // собираем строку для WHERE IN
                $where_ws .= "'" . $k . "',";
            }

            // убираем с конца строки лишние символы
            $query_ws = substr($query_ws, 0, -2);
            $where_ws = substr($where_ws, 0, -1);

            // дополняем SQL строку WHERE IN
            $query_ws .= " WHERE `id` IN (" . $where_ws . ')';

            $update_ws = Yii::$app->db->createCommand($query_ws);
        }

        /*
         * Порядок взят из отсортированных вложенных массивов
         * которые в основном массиве - $report_to_db
         */
        $fields = [
            'ammount',
            'comment',
            'created_at',
            'date_in',
            'date_out',
            'date_time',
            'income',
            'monthly',
            'tags',
            'type_operation',
            'wallet_id',
        ];

        $command_finance_movement = Yii::$app->db->createCommand()->batchInsert(
            'finance_movement',
            $fields,
            $report_to_db
        );

        //        d::pe($report_to_db);

        try {
            // Записываем строки в историю отчета
            $command_finance_movement->execute();

            /*
             * Если не отмечен чекбокс "разработка"
             * то изменяем балансы кошельков
             */
            if (!$data['development']) {
                if (count($wallets) != 0) {
                    try {
                        // Обновляем таблицу кошельков
                        $update_ws->execute();
                    } catch (Exception $e) {
                        //                        $return['errors'] .= d::getMessage('UPDATE_WALLETS_ERROR').'<br>';
                        $return['errors'] .= $e->getMessage() . '<br>';
                    }
                }
            }
        } catch (Exception $e) {
            //            $return['errors'] .= d::getMessage('DATA_SAVE_ERROR');
            $return['errors'] .= $e->getMessage();
        }

        return $return;

    } // function saveReport(...)

    /**
     * Страница "История"
     * ===============================
     * Кнопка "Проверить недочет"
     */
    public static function checkShortcoming($data)
    {
        // Получаем все кошельки
        return Wallets::find()->asArray()->all();
    } // function checkShortcoming(...)

    /**
     * Страница "История"
     * ===============================
     * Кнопка "Найти"
     */
    public static function getHistory($post)
    {

        //        d::pe($post);

        $debug = [];

        /*
         * Создаем объект выборки
         * из таблицы - движение финансов
         */
        $query = FinanceMovement::find();

        // Если запрошен период отчета (от числа до числа)
        if (iconv_strlen($post['date_history']) == 2 or $post['date_range']) {

            if ($post['date_history']) {
                // Если запрошен период "за текущий год"
                if ($post['date_history'] == '00') {
                    // От даты
                    $date_from = strtotime((date('Y') . '-01-01 00:00:00'));
                    // До даты
                    $date_to = strtotime((date('Y') . '-12-31 23:59:59'));
                } else {
                    /*
                     * Если запрошен период "один месяц"
                     * =================================
                     * Берем начало запрошенного месяца, первый день - 01
                     */
                    $date_from = (date('Y') . '-' . $post['date_history'] . '-01');

                    /*
                     * Получаем от начала месяца последний день
                     * запрошенного месяца.
                     * Время ставим конец дня: 23:59:59
                     */
                    $d = new \DateTime($date_from);
                    $date_to = strtotime($d->format('Y-m-t') . '23:59:59');

                    // Преобразуем начальную дату в Time
                    $date_from = strtotime($date_from);
                }
            }
            if ($post['date_range']) {
                // От даты
                $date_from = strtotime($post['date_range']['date_from']);
                // До даты
                $date_to = strtotime($post['date_range']['date_to'] . ' 23:59:59');
            }

            /*
             * Этот код выбирает диапозон (от и до)
             * по полю date_time
             * где значения дат в time
             */
            $query = FinanceMovement::find()
                ->where(['>', 'date_time', $date_from])
                ->andWhere(['<', 'date_time', $date_to]);

            if ($post['type_operation'] == '1') {
                $query->andWhere(['type_operation' => '1']);
            } elseif ($post['type_operation'] == '2') {
                $query->andWhere(['type_operation' => '2']);
            }

        } else {

            // Если запрошен отчет за один конкретный день

            /*
             * Этот код выбирает значения по полям date_in/date_out
             * по дате в обычном формате БД - 0000-00-00
             */

            // Выбираем только "доходы"
            if ($post['type_operation'] == '1') {
                $query->where(['date_in' => $post['date_history']]);
                $query->andWhere(['type_operation' => '1']);
            } elseif ($post['type_operation'] == '2') {
                // Выбираем только "расходы"
                $query->where(['date_out' => $post['date_history']]);
                $query->andWhere(['type_operation' => '2']);
            } else {
                /*
                 * Выбираем и "доходы" и "расходы"
                 * по заданной дате
                 */
                $query->where(['date_in' => $post['date_history']]);
                $query->orWhere(['date_out' => $post['date_history']]);
            }
        }

        // Если запрошен поиск по метке
        if ($post['tags'])
            $query->andWhere(['tags' => $post['tags']]);

        // Если запрошен поиск по нескольким меткам
        if ($post['multiple_tags']) {
            $query->andWhere(['tags' => $post['multiple_tags']]);
        }

        // Если отмечены сразу два чекбокса Приход и Расход
        if ($post['monthly_in'] and $post['monthly_out']) {
            $query->andWhere(['in', 'monthly', ['1', '2']]);
        } else {
            // Если отмечен чекбокс "Ежемесячный приход (плата (приход))"
            if ($post['monthly_in'])
                $query->andWhere(['monthly' => '1']);
            // Если отмечен чекбокс "Ежемесячный расход (плата (расход))"
            if ($post['monthly_out']) {
                $debug['monthly_out'] = 'Расход';
                $query->andWhere(['monthly' => '2']);
            }
        }

        // Если отмечен чекбокс Доход(чисто заработанные)
        if ($post['income'])
            $query->andWhere(['income' => '1']);

        // Если выбран кошелек
        if ($post['wallet'])
            $query->andWhere(['wallet_id' => $post['wallet']]);

        /*
         * Строки "Перемещение" со своего счета на свои же счета
         * отображать не нужно. Там где поле "wallet_id" = 0
         * чтобы итоговая сумма совпадала с общим балансом
         */
        //        $query->andWhere(['not in', 'wallet_id', ['0']]);

        //        d::pe($query);

        //        $debug['query_string'] = $query->createCommand()->sql;

        //        d::pe($debug);

        // Производим настроенную выборку
        return $query->asArray()->all();

    } // function getHistory(...)

    /*
     * Странци "Time Трекер"
     * =====================
     * Кнопка "Старт/Стоп"
     * -------------------
     * Обновляем время задачи.
     * $json_task - Строка json
     */
    public static function updateTimeWork($post)
    {

        $working_hours = new WorkingHours();
        $return = [];

        $json_time = json_encode($post['task']['json_time']);
        $update = Yii::$app->db->createCommand()
            ->update(
                'tasks',
                ['time_work' => $json_time],
                ['id' => $post['task']['id']]
            );
        try {
            $update->execute();

            /*
             * Получаем данные из таблицы tasks
             * для заполнения счетчика общего времени задачи
             */
            $return['tasks'] = Tasks::find()
                ->where(['id' => $post['task']['id']])
                ->asArray()->one();

            // ID проекта
            $wt['project_id'] = $post['project']['id'];
            // ID задачи
            $wt['task_id'] = $post['task']['id'];
            // Время работы
            $wt['working_hours'] = $post['working_hours'];
            // Текущая дата
            $wt['created_at'] = Yii::$app->getFormatter()->asTimestamp(time() - 10800);
            // Заполняем модель данными
            $working_hours->load($wt, '');

            /*
             * Если валидация данных модели "Document" не удачна
             */
            if (!$working_hours->validate()) {
                /*
                 * данные не корректны:
                 * $errors - массив содержащий сообщения об ошибках
                 */
                $return['errors'] .=
                    d::getErrors($working_hours, $working_hours->errors) . '<br>';
            } else {
                /*
                 * Если валидация данных в модели "WorkingHours" успешна
                 * =====================================================
                 * заполняем модель данными
                 */
                foreach ($wt as $key => $val)
                    $working_hours->$key = $val;

                /*
                 * Если запись в таблицу "working_hours" успешна
                 * Получаем данные последней записи
                 * для вывода на экран
                 */
                if ($working_hours->save()) {
                    $return['working_hours'] =
                        WorkingHours::find()
                            ->orderBy('created_at DESC')
                            ->where(['id' => Yii::$app->db->getLastInsertID()])
                            ->asArray()->one();
                } else {
                    $return['errors'] = d::getMessage('ERROR_SAVE_WORKING_HOURS');
                }
            }

            // Записываем новую строку в таблицу working_hours


        } catch (Exception $e) {
            //            d::td($e->getMessage());
            $return['errors'] = d::getMessage('ERROR_TIME_UPDATE_TASK');
        }

        return $return;
    } // function updateTimeWork(...)

    /*
     * Странци "Time Трекер"
     * =====================
     * Выпадающий список "Выберите задачу"
     * -----------------------------------
     * Получаем все строки сегодняшнего дня
     */
    public static function getTodayTime($post)
    {

        $current_time = Yii::$app->getFormatter()->asTimestamp(time() - 10800);
        $date_from = strtotime(date('Y-m-d', $current_time) . ' 00:00:00');
        $date_to = strtotime(date('Y-m-d', $current_time) . ' 23:59:59');

        /*
         * Этот код выбирает диапозон (от и до)
         * по полю date_time
         * где значения дат в time
         */
        $query = WorkingHours::find()
            ->orderBy('created_at DESC')
            ->where(['>', 'created_at', $date_from])
            ->andWhere(['<', 'created_at', $date_to])
            ->andWhere([
                'project_id' => $post['project_id'],
                'task_id' => $post['task_id'],
            ]);

        // Производим настроенную выборку
        return $query->asArray()->all();

    } // function getTodayTime()

    /*
     * Странци "Статистика WebMaster"
     * ==============================
     * Кнопка "Найти"
     */
    public static function getStatisticsWebmaster($data)
    {

        //        d::ajax($data);

        //        $current_time = Yii::$app->getFormatter()->asTimestamp(time()-10800);
//        $current_time = date('Y-m-d', time());
        // По умолчанию текущий день
//        $date_from = $current_time;
//        $date_to = $current_time;

        $current_time = time();
        $date_from = '';
        $date_to = '';

        $query = WorkingHours::find();
        if (isset($data['project_id']) and $data['project_id'] != '') {
            // Получаем все секунды по проекту
            $query->where(['project_id' => $data['project_id']]);
        }

        if (isset($data['projects_ids']) and $data['projects_ids'] != '') {
            // Получаем все секунды по выбранным проектам
            $query->where(['in', 'project_id', $data['projects_ids']]);
        }

        if (isset($data['task_id']) and $data['task_id'] != '') {
            $query->andWhere(['task_id' => $data['task_id']]);
        }

        // Строим даты по заданным параметрам

        // Выборка одного дня
        if (isset($data['date_day']) and $data['date_day'] != '') {
            $date_from = strtotime($data['date_day']);
            $date_to = strtotime(date('Y-m-d', strtotime($data['date_day'])) . ' 23:59:59');
        }
        // Выборка за период
        if (isset($data['period']) and $data['period'] != '') {
            if ($data['period'] == '00') {
                // От начала текущего года
                $date_from = strtotime((date('Y', $current_time) . '-01-01 00:00:00'));
                // До конца текущего года
                $date_to = strtotime((date('Y', $current_time) . '-12-31 23:59:59'));
            } else {
                /*
                 * Если запрошен период "один месяц"
                 * =================================
                 * Берем начало запрошенного месяца, первый день - 01
                 */
                $date_from = (date('Y') . '-' . $data['period'] . '-01');

                /*
                 * Получаем от начала месяца последний день
                 * запрошенного месяца.
                 * Время ставим конец дня: 23:59:59
                 */
                $d = new \DateTime($date_from);
                $date_to = strtotime($d->format('Y-m-t') . '23:59:59');

                // Преобразуем начальную дату в Time
                $date_from = strtotime($date_from);
            }
        }
        // Выборка между указанными датами
        if (isset($data['date_from'], $data['date_to']) and $data['date_from'] != '' and $data['date_to'] != '') {
            // От даты
            $date_from = strtotime($data['date_from']);
            // До даты
            $date_to = strtotime($data['date_to']);
        }

        /*
         * Этот код выбирает диапозон (от и до)
         * по полю date_time
         * где значения дат в time
         */
        //        $d = [
//            'date_from' => date('Y-m-d H:i:s', $date_from),
//            'date_to' => date('Y-m-d H:i:s', $date_to),
//            'date_from_time' => $date_from,
//            'date_to_time' => $date_to
//        ];
//        d::ajax($d);
        if ($date_from != '' and $date_to != '') {
            $query
                ->andWhere(['>', 'created_at', $date_from])
                ->andWhere(['<', 'created_at', $date_to]);
        }

        //        d::ajax(date('Y-m-d H:i:s', $date_from) . ' - ' . date('Y-m-d H:i:s', $date_to));
//        d::ajax($date_from . ' - ' . $date_to);
//        d::ajax($query->createCommand()->sql);
        // Производим настроенную выборку
        $result = $query->asArray()->all();
        //        d::ajax($result);
        return $result;

    } // function getStatisticsWebmaster(...)

    /*
     * Страница "Проекты"
     * ==================
     * Кнопка "Добавить проект"
     * ------------------------
     * Добавление нового проекта
     */
    public static function addProject($data)
    {

        $return = [];

        $ps = new Projects();
        $ps->code = $data['project']['code'];
        $ps->name = $data['project']['name'];
        $ps->active = $data['project']['active'];

        $ws = WebmasterSettings::findOne(['code' => 'statistics']);

        $ps->rate = json_encode([
            'name' => $ws->settings['rate']['currency'],
            'value' => $ws->settings['rate']['cost'],
        ]);

        if ($ps->save()) {

            $last_insert_id = Yii::$app->db->getLastInsertID();
            $return['project_id'] = $last_insert_id;

            // Добавим проект в файл общего времени "project_time.txt"

            // Получим содержимое файла
            $time = file_get_contents(Yii::getAlias('@timetrecker') . '/' . Yii::$app->params['project_time_file']);
            // Преобразуем в массив
            $arr = d::objectToArray(json_decode($time));
            // Добавим новый проект в массив
            $arr[$data['project']['code']] = [
                'days' => '00',
                'hours' => '00',
                'minutes' => '00',
                'seconds' => '00'
            ];
            // Массив преобразуем в JSON
            $json = json_encode($arr);
            // Перепишем файл с новыми данными
            @file_put_contents(Yii::getAlias('@timetrecker') . '/' . Yii::$app->params['project_time_file'], $json);

        }

        return $return;

    } // function addProject(...)

    /*
     * Странци "Проекты"
     * =================
     * Кнопка "Добавить проект"
     * ------------------------
     * Редактирование проекта
     */
    public static function editProject($data)
    {

//        d::ajax($data);
        $return = [];

        $ps = Projects::findOne($data['project']['project_id']);

        $ps->name = $data['project']['name'];
        $ps->code = $data['project']['code'];
        $ps->active = $data['project']['active'];

        if ($ps->save()) {
            $return['project_id'] = $data['project']['project_id'];

            // Получим содержимое файла
            $time = file_get_contents(Yii::getAlias('@timetrecker') . '/' . Yii::$app->params['project_time_file']);
            // Преобразуем в массив
            $file_projects = d::objectToArray(json_decode($time));
            if(is_array($file_projects) AND count($file_projects)){
                foreach($file_projects as $project_name => $project_time){
                    if($project_name == $data['project']['code_orig']){
                        unset($file_projects[$data['project']['code_orig']]);
                        $file_projects[$data['project']['code']] = $project_time;
                    }
                }
            }

            // Массив преобразуем в JSON
            $json_projects = json_encode($file_projects);
            // Перепишем файл с новыми данными
            @file_put_contents(Yii::getAlias('@timetrecker') . '/' . Yii::$app->params['project_time_file'], $json_projects);
        }

        return $return;

    } // function editProject(...)

    /*
     * Странци "Задачи"
     * ================
     * Кнопка "Добавить задачу"
     * ------------------------
     * Добавление новой задачи
     */
    public static function addTask($data)
    {

        $tasks = new Tasks();
        $tasks->project_id = $data['task']['project_id'];
        $tasks->name = $data['task']['name'];
        $tasks->description = $data['task']['description'];
        $tasks->time_work =
            '{"days":"00","hours":"00","minutes":"00","seconds":"00"}';
        $tasks->active = $data['task']['active'];
        $tasks->created_at = Yii::$app->getFormatter()->asTimestamp(time());

        if ($tasks->save())
            return Yii::$app->db->getLastInsertID();
        else
            return false;

    } // function addTask(...)

    /*
     * Странци "Задачи"
     * ================
     * Кнопка "Добавить задачу"
     * ------------------------
     * Редактирование задачи
     */
    public static function editTask($data)
    {

        $task = Tasks::find()
            ->where([
                'id' => $data['task']['id'],
                'project_id' => $data['project']['project_id'],
            ])->one();

        //        d::pe($task);

        $task->name = $data['task']['name'];
        $task->description = $data['task']['description'];

        if (isset($data['task']['active'])) {
            $task->active = $data['task']['active'];
        }

        //        d::ajax($task);

        if ($task->save())
            return true;
        else
            return false;

    } // function editTask(...)

    /*
     * Странци "Настройки WebMaster"
     * =============================
     * Кнопка "Сохранить"
     */
    public static function saveSettings($data)
    {

        //        d::pe($data);
        $settings = [];

        $ss = WebmasterSettings::findOne(['code' => $data['setting_code']]);

        foreach ($data as $key => $data_ss) {
            if (is_array($data_ss)) {

                //                d::pri($key);

                if (isset($ss_sobject['settings']['$key'])) {
                    $current_ss = $ss_sobject['settings']['$key'];
                    foreach ($current_ss as $sg_key => $sg) {
                        //                    d::pri($sg_key);
                        $settings[$key][$sg_key] = $data[$key][$sg_key];
                    }
                } else {
                    foreach ($data[$key] as $d_key => $d_item) {
                        //                    d::pri($sg_key);
                        $settings[$key][$d_key] = $d_item;
                    }
                }
            }
        }

        //        $settings[] = $data;
        $settings_json = json_encode($settings, JSON_UNESCAPED_UNICODE);
        //        d::pe($settings_json);

        $ss->settings = $settings_json;

        if ($ss->save()) {
            return true;
            //            d::pe('Сохранено');
        } else {
            return false;
            //            d::pe('Ошибка сохранения');
        }

    } // function editTask(...)

    public static function editData($data)
    {

        $tasks = Tasks::find()
            ->where([
                'id' => '35',
                'project_id' => '8',
            ])->one();

        $tasks->description = $data['s'];

        if ($tasks->save()) {

            $tasks2 = Tasks::find()
                ->where([
                    'id' => '35',
                    'project_id' => '8',
                ])->one();

            return $tasks2->description;
        } else {
            d::pe('Ошибка обновления');
        }

    } // function editTask(...)

} // Class Ajax