<?php

use backendold\controllers\MainController as d;
use yii\helpers\Html;

$this->title = 'Отправка Eamil';


/*
 * Класс eml, это сокращение: email
*/

//d::pre(date('Y-m-d',1548149584));

?>

<div class="wrap eml">
    <div class="text-center h3 header"><?= Html::encode($this->title) ?></div>

    <div class="row">

        <div class="col-md-1">
            <button class="btn btn-primary btn-xs send-mail"
                    name="serebros"
                    action="ajax/send-mail"
                    method="post">
                <?=Html::img('@web/images/animate/loading.gif',['alt'=>'Загрузка','width'=>'20'])?>
                Отправить Email
            </button>
        </div>
    </div><!-- row -->
    <br>

    <?=$alerts?>
    <!--    <div class="res">result</div>-->



</div>