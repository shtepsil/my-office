<?php

namespace frontend\actions;

use common\components\Debugger as d;
use common\models\Orders;
use common\models\User as ModelUser;

class User
{

    public $post = [];

    public function run()
    {
        $this->post = d::post();
        switch($this->post['type']){
            case 'get_user':
                $this->getUser();
                break;
            case 'set_wholesale':
                $this->setWholesale();
                break;
            default:
                $this->setWholesale();
                d::ajax('User->run()->switch:default');
        }
    }

    public function getUser()
    {
        $user = ModelUser::findOne($this->post['user_id']);
        d::pe($user);
    }

    public function setWholesale()
    {
//        $user = ModelUser::findOne($this->post['user_id']);
//        $user->isWholesale = $this->post['is_wholesale'];

        $user = ModelUser::findOne(21277);
        $user->isWholesale = 0;

//        d::pe($user);
        if($user->save()){
            d::ajax('Изменено: user->isWholesale - ' . $user->isWholesale);
        }
        else{
            d::ajax($user->getErrors());
        }
    }

}//Class
