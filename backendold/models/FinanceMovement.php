<?php

namespace backendold\models;

use Yii;

/**
 * This is the model class for table "finance_movement".
 *
 * @property string $id
 * @property double $ammount
 * @property string $comment Комментарий
 * @property int $date_in Дата прихода
 * @property int $date_out Дата расхода
 * @property int $type_operation Тип операции (приход/расход) по умолчанию - приход
 */
class FinanceMovement extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'finance_movement';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ammount', 'comment', 'date_in', 'date_out'], 'required'],
            [['ammount'], 'number'],
            [['comment'], 'string'],
            [['date_in', 'date_out', 'type_operation'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ammount' => 'Ammount',
            'comment' => 'Comment',
            'date_in' => 'Date In',
            'date_out' => 'Date Out',
            'type_operation' => 'Type Operation',
        ];
    }
}
