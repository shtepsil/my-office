<?php

namespace frontend\actions;

use common\components\Debugger as d;
use frontend\widgets\ActiveForm;
use yii\base\Action;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use Yii;

class TabsAjaxActions extends Action
{

    public $actions = [];
    public function run($a)
    {
        $post = Yii::$app->request->post();
//        d::ajax($post);
        $actions = $this->actions;
        $result = [];
        if (isset($actions[$a])) {
            $form = Yii::createObject('frontend\actions\\' . $actions[$a]);
//            d::ajax($form);
            if (Yii::$app->request->isAjax) {
                return $form->run();
            } else {
                throw new BadRequestHttpException('not found', 404);
            }
        } else {
            throw new BadRequestHttpException('not found', 404);
        }
    }

}//Class