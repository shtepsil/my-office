<?php

namespace backend\models;

use backend\models\Tasks;
use backend\models\WorkingHours;
use backend\controllers\MainController as d;
use shadow\helpers\SNumberHelper;
use shadow\helpers\StringHelper;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "reports".
 *
 * @property int $id
 * @property int $project_id ID проекта
 * @property string $title Заголовок (не большое описание отчёта)
 * @property string $ids_projects_tasks_working_hours IDs времени работы в формате json:
 * {
 *    project_id: {
 *      task_id: {
 *        ids_wh: [23,56,57,78,89]
 *      },
 *      task_id: {
 *        ids_wh: [23,56,57,78,89]
 *      },
 *      ...
 *    },
 *    project_id: { ... }, ...
 * }
 * @property int $ids_payment IDs из таблицы payments, по которым нужно будет изменять статус оплаты
 * @property int $status Статус отчёта (Оплата не запрошена 0/запрошена 1/оплачено 2)
 * @property int $updated_at Дата обновления
 * @property int $created_at Дата создания
 */
class Reports extends \yii\db\ActiveRecord
{

    public $report = NULL;
    public $total_time = 0;
    public $total_cost = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reports';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['project_id', 'status', 'updated_at', 'created_at'], 'integer'],
            [['ids_projects_tasks_working_hours', 'ids_payment'], 'string'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'project_id' => 'ID проекта',
            'title' => 'Заголовок (не большое описание отчёта)',
            'ids_projects_tasks_working_hours' => 'IDs времени работы в формате json: {project_id:{task_id: {ids_wh[23,56,57,78,89],task_id: {ids_wh[23,56,57,78,89],},project_id:{...}}',
            'ids_payment' => 'IDs из таблицы payments, по которым нужно будет изменять статус оплаты',
            'status' => 'Статус отчёта (Оплата не запрошена 0/запрошена 1/оплачено 2)',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    public function afterFind()
    {
        parent::afterFind(); // TODO: Change the autogenerated stub
        if($this->ids_projects_tasks_working_hours AND StringHelper::isJson($this->ids_projects_tasks_working_hours)){
            $this->ids_projects_tasks_working_hours = $this->ids_projects_tasks_working_hours;
            $ids_projects_tasks_working_hours = json_decode($this->ids_projects_tasks_working_hours, true);
            $projects = $tasks_objects = [];
            $total_time = 0;
            $total_cost = 0;
            // Если у текущего отчёта ids_projects_tasks_working_hours массив не пустой
            if(count($ids_projects_tasks_working_hours)){
                // Перебираем проекты в json
                foreach($ids_projects_tasks_working_hours as $project_id => $tasks) {
                    // Перебираем задачи в проекте
                    foreach ($tasks as $task_id => $ids_wh) {
                        // Получаем задачу по ID
                        $task = Tasks::findOne($task_id);
                        // Если задача найдена, если $ids_wh является массивом и он не пуст
                        if ($task and (is_array($ids_wh) and count($ids_wh))) {
                            // Время работы текущей задачи, выбирается по статусу 2-не оплачен.
                            $wh_sum = WorkingHours::find()
                                ->where(['task_id' => $task_id, 'status' => 2])
                                ->sum('working_hours');
                            if ($wh_sum) {
                                $total_time += $wh_sum;
                                $total_cost += $task->getCost($wh_sum);
                                $task->wh = [
                                    'time' => $wh_sum,
                                    'cost' => $task->getCost($wh_sum),
                                    'hms' => SNumberHelper::whSecToHMS($wh_sum),
                                ];
                            }
                        }
                        $tasks_objects[] = $task;
                    }
                    $projects[$project_id] = $tasks_objects;
                }
            }
//            d::pex('stop');
            $this->total_time = SNumberHelper::whSecToHMS($total_time);
            $this->total_cost = $total_cost;
            $this->report = $projects;
        }
    }
}
