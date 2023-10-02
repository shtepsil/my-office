<?php

namespace backendold\models;

use backendold\controllers\MainController as d;
use common\models\Payment;
use Yii;
use shadow\helpers\StringHelper as SH;

/**
 * This is the model class for table "projects".
 *
 * @property int $id
 * @property string $code Код имени проекта (либо свое имя либо транслит имени)
 * @property string $name Наименование проекта
 */
class Projects extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'projects';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name'], 'string'],
            [['code'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Код имени проекта (либо свое имя либо транслит имени)',
            'name' => 'Наименование проекта',
        ];
    }

    public function getPayment(){
        return $this->hasMany(Payment::className(), ['project_id' => 'id'])
            ->orderBy('created_at');
    }

    public function getPaymentnopaid(){
        return $this->hasMany(Payment::className(), ['project_id' => 'id'])
            ->where(['status'=>'2'])
            ->sum('cost');
    }

    public function getPaymentpaid(){
        return $this->hasMany(Payment::className(), ['project_id' => 'id'])
            ->where(['status'=>'4'])
            ->sum('cost');
    }

    public function getPaymentwaitpay(){
        return $this->hasMany(Payment::className(), ['project_id' => 'id'])
            ->where(['status'=>'3'])
            ->sum('cost');
    }

    public function afterFind(){
        if(d::isJson($this->rate)){
            $this->rate = json_decode($this->rate, true);
        }
    }

    public function beforeSave($insert)
    {
        if(is_array($this->rate)){
            $this->rate = json_encode($this->rate, 256);
        }
        return parent::beforeSave($insert); // TODO: Change the autogenerated stub
    }

}//Class