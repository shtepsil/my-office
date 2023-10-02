<?php

namespace frontend\actions;

use common\components\Debugger as d;
use common\models\Orders;
use common\models\User as ModelUser;

class Debug
{

    public $post = [];

    public function run()
    {
        $this->post = d::post();
//        d::ajax($this->post);
        switch($this->post['type']){
            case 'btn_push':
                $this->test();
                break;
            case 'get_file_debug':
                $this->getFileDebug();
                break;
            case 'clear_file_debug':
                $this->setFileDebug();
                break;
            default:
                $this->test();
                d::ajax('Debug->run()->switch:default');
        }
    }

    /*
     * Кнопка "Нажать"
     */
    public function test()
    {
        d::ajax('Debug->test(site)');
    }

    public function getFileDebug()
    {
        $result = d::getDebug($this->post['file_debug_name']);
        d::ajax($result);
    }

    public function setFileDebug($data = '')
    {
        $result = d::clearDebug($this->post['file_debug_name']);
        d::ajax($result);
    }

}//Class
