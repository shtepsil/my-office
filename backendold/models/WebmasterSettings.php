<?php

namespace backendold\models;
use backendold\controllers\MainController as d;
use Yii;
use shadow\helpers\StringHelper as SH;

/**
 * This is the model class for table "webmaster_settings".
 *
 * @property int $id
 * @property string $code Код страницы. Наименвание латиницей
 * @property string $name Наименование страницы
 * @property string $settings Настройки страницы в json
 */
class WebmasterSettings extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'webmaster_settings';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['settings'], 'string'],
            [['code', 'name'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Код страницы. Наименвание латиницей',
            'name' => 'Наименование страницы',
            'settings' => 'Настройки страницы в json',
        ];
    }

    public function afterFind(){
        if(d::isJson($this->settings)){
            $this->settings = json_decode($this->settings, true);
        }
    }

}//Class
