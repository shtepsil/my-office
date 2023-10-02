<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "balance_begin_month".
 *
 * @property int $id
 * @property string $wallets JSON строка в формате
 {"wallet_id":"0.00"}
 * @property int $date_time Дата создания
 Выбирается пользователем на странице
 * @property string $created_at Дата создания строки
 */
class BalanceBeginMonth extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'balance_begin_month';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['wallets'], 'string'],
            [['date_time'], 'integer'],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'wallets' => 'JSON строка в формате
{"wallet_id":"0.00"}',
            'date_time' => 'Дата создания
Выбирается пользователем на странице',
            'created_at' => 'Дата создания строки',
        ];
    }
}
