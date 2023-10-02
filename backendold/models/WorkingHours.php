<?php

namespace backendold\models;

use Yii;

/**
 * This is the model class for table "working_hours".
 *
 * @property int $id
 * @property int $project_id ID проекта таблицы projects
 * @property int $task_id ID задачи таблицы tasks
 * @property int $task_type Вид задачи (1-Верстка, 2-backend)
 * @property int $working_hours Время работы
 Отрезок времени от нажатия на кнопку старт
 - до нажатия на стоп
 * @property int $created_at Дата создания записи в секундах
 Именно по этому времени будет фильтроваться
 время. Это поле является основой филтра.
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
            'working_hours' => 'Время работы
Отрезок времени от нажатия на кнопку старт
- до нажатия на стоп',
            'created_at' => 'Дата создания записи в секундах
Именно по этому времени будет фильтроваться
время. Это поле является основой филтра.',
        ];
    }
}
