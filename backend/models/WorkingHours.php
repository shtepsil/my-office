<?php

namespace backend\models;

use backendold\models\Payment;
use common\components\Debugger as d;
use Yii;

/**
 * This is the model class for table "working_hours".
 *
 * @property int $id
 * @property int $project_id ID проекта таблицы projects
 * @property int $task_id ID задачи таблицы tasks
 * @property int $task_type Вид задачи (1-Верстка, 2-backend)
 * @property int $status 1-в работе/2-не оплачен/3-ожидание оплаты/4-оплачено
 * @property int $working_hours Время работы
 * Отрезок времени от нажатия на кнопку старт - до нажатия на стоп
 * @property int $created_at Дата создания записи в секундах
 * Именно по этому времени будет фильтроваться время.
 * Это поле является основой филтра.
 */
class WorkingHours extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'working_hours';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                ['project_id', 'task_id', 'working_hours', 'created_at'],
                'integer',
                'message' => 'Ошибка валидации (проверка на integer)'
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'project_id' => 'ID проекта таблицы projects',
            'task_id' => 'ID задачи таблицы tasks',
            'working_hours' => 'Время работы Отрезок времени от нажатия на кнопку старт - до нажатия на стоп',
            'status' => '1-в работе/2-не оплачен/3-ожидание оплаты/4-оплачено',
            'created_at' => 'Дата создания записи в секундах Именно по этому времени будет фильтроваться время. Это поле является основой филтра.',
        ];
    }

    /**
     * Собираем все IDs wh - в один массив и для всех строк обновляем поле reports_id
     * т.е. записываем ID отчёта
     * @param $report_id
     * @param $wh_tasks_ids
     * @return void
     */
    public static function addReportsId($report_id, $wh_tasks_ids)
    {
        $wh_ids_update = [];
        foreach($wh_tasks_ids as $tasks){
            foreach($tasks as $task_wh_ids) {
                foreach($task_wh_ids as $wh_ids){
                    $wh_ids_update[] = $wh_ids;
                }
            }
        }
        WorkingHours::updateAll(['reports_id' => $report_id], ['IN', 'id', $wh_ids_update]);
    }
}
