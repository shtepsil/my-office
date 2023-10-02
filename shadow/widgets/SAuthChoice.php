<?php
/**
 * Created by PhpStorm.
 * Project: morkovka
 * User: lxShaDoWxl
 * Date: 20.08.15
 * Time: 15:49
 */
namespace shadow\widgets;

use shadow\assets\SAuthChoiceAsset;
use Yii;
use yii\authclient\widgets\AuthChoice;
use yii\helpers\Html;

class SAuthChoice extends AuthChoice
{
    /**
     * Initializes the widget.
     */
    public function init()
    {
        $view = Yii::$app->getView();
        if ($this->popupMode) {
            SAuthChoiceAsset::register($view);
            $view->registerJs("\$('#" . $this->getId() . "').authchoice();");
        }
        $this->options['id'] = $this->getId();
        echo Html::beginTag('ul', $this->options);
    }
    /**
     * Runs the widget.
     */
    public function run()
    {
        if ($this->autoRender) {
            $this->renderMainContent();
        }
        echo Html::endTag('ul');
    }
    /**
     * Renders the main content, which includes all external services links.
     */
    protected function renderMainContent()
    {
//        echo Html::beginTag('ul',$this->options);
        foreach ($this->getClients() as $id=> $externalService) {
            echo Html::beginTag('li',['class'=>$id]);
            $this->clientLink($externalService,'');
            echo Html::endTag('li');
        }
//        echo Html::endTag('ul');
    }
}