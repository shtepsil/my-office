<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 20.05.2022
 * Time: 11:54
 */

namespace backend\components;

use backend\controllers\MainController as d;
use backend\models\WebmasterSettings;

class WebSettings
{

    public $data = [];

    public function __construct()
    {
        $this->data = WebmasterSettings::find()->indexBy('code')->all();
    }

    public function getItem($code){
        return $this->data[$code];
    }

    public function getRate($item = ''){
        $result = '';
        if($item != ''){
            $result = $this->data['statistics']->settings['rate'][$item];
        }
        return $result;
    }

}//Class