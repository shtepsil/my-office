<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "wallets".
 *
 * @property int $id
 * @property string $code Код кошелька (имя на латинице)
 * @property string $name Наименование кошелька
 * @property double $balance Баланс кошелька
 */
class Wallets extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'wallets';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['balance'], 'number'],
            [['code'], 'string', 'max' => 50],
            [['name'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Код кошелька (имя на латинице)',
            'name' => 'Наименование кошелька',
            'balance' => 'Баланс кошелька',
        ];
    }
}
