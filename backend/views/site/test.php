<?php

use backend\controllers\MainController as d;
use yii\helpers\Html;

$this->title = 'Тест';


/*
 * Класс eml, это сокращение: email
*/

//d::pre(date('Y-m-d',1548149584));

?>
<div class="site-index">

    <div class="text-center">
        <p>
            <?php
            $options = [
                'name'=>'cc',
                'class'       => 'form-control',
                'rows'        => '3',
                'cols'        => '2',
//            'disabled'    => 'disabled',
            ];
            echo Html::textarea('cc','',$options)?>
        </p>
        <p>
            <button
                type="button"
                class="btn test2"
                data-url="ajax/test"
                data-c=">"
            >

                <?=Html::img('@web/images/animate/loading.gif',['alt'=>'Загрузка','width'=>'20','class'=>'loading'])?>
                нажать
            </button>
        </p>
        <div class="res">result</div>
        <div class="div"></div>
        <?php
        $options = [
            'placeholder' => 'Описание задачи',
            'class'       => 'form-control',
            'rows'        => '3',
            'cols'        => '2',
//            'disabled'    => 'disabled',
        ];
        echo Html::textarea('ttt','',$options)?>
    </div>
</div>