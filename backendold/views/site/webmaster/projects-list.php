<?php

use backendold\controllers\MainController as d;
use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'Проекты/задачи';

?>

<div class="pts">

    <?=d::res()?>

    <div class="text-center h3 header"><?= Html::encode($this->title) ?></div>

    <ul class="nav nav-tabs">
        <li class="active"><a data-toggle="tab" href="#p_on">Включенные</a></li>
        <li><a data-toggle="tab" href="#p_off">Выключенные</a></li>
    </ul>

    <div class="tab-content">
        <div id="p_on" class="tab-pane fade in active">

            <?=$this->render('//site/shortcodes/projects-list-table',[
                'ws'=>$ws,
                'projects'=>$projects,
                'active'=>1,
            ])?>

        </div>
        <div id="p_off" class="tab-pane fade">

            <?=$this->render('//site/shortcodes/projects-list-table',[
                'ws'=>$ws,
                'projects'=>$projects,
                'active'=>0,
            ])?>

        </div>
    </div>

    <?d::res()?>

</div>
