<?php

namespace backendold\models;

use backendold\models\Tasks;
use backendold\models\WorkingHours;
use backendold\controllers\MainController as d;
use backendold\helpers\StringHelper;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "reports".
 *
 * @property int $id
 * @property int $project_id ID проекта
 * @property string $title Заголовок (не большое описание отчёта)
 * @property string $ids_tasks_working_hours IDs времени работы пример ({task_id: {ids_wh(23,56,57,78,89)}) ...
 * @property int $status Статус отчёта (Оплата не запрошена 0/запрошена 1/оплачено 2)
 * @property int $updated_at Дата обновления
 * @property int $created_at Дата создания
 */
class Reports extends \yii\db\ActiveRecord
{

    public $ids_tasks_working_hours_old = NULL;

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
            [['ids_tasks_working_hours'], 'string'],
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
            'ids_tasks_working_hours' => 'IDs времени работы пример ({task_id: {ids_wh(23,56,57,78,89)}) ...',
            'status' => 'Статус отчёта (Оплата не запрошена 0/запрошена 1/оплачено 2)',
            'updated_at' => 'Дата обновления',
            'created_at' => 'Дата создания',
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
        if($this->ids_tasks_working_hours AND StringHelper::isJson($this->ids_tasks_working_hours)){
            $this->ids_tasks_working_hours_old = $this->ids_tasks_working_hours;
            $this->ids_tasks_working_hours = json_decode($this->ids_tasks_working_hours, true);
            $tasks = [];
            // Если у текущего отчёта ids_tasks_working_hours массив не пустой
            if(count($this->ids_tasks_working_hours)){
                foreach($this->ids_tasks_working_hours as $task_id => $ids_wh){
                    // Получаем задачу по ID
                    $task = Tasks::findOne($task_id);
                    // Если задача найден, если $ids_wh массив и он не пуст
                    if($task AND (is_array($ids_wh) AND count($ids_wh))){
                        // Если найдены какие-то время-часы для текущей задачи
                        $wh = WorkingHours::find()->where(['task_id' => $task_id, 'status' => 3])->all();
                        d::pri($wh);
                        if($wh){ $task->wh = $wh; }
                    }
                    $tasks[] = $task;
                }
            }

            if($tasks[0]){
                d::pri($tasks[0]->wh);
            }else{
                d::pri('Ничего нет');
            }

        }
//        exit();
    }
}
