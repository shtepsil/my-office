<?php

use backend\controllers\MainController as d;
use yii\helpers\Html;

$this->title = 'Расчёты';

//d::pri();

?>
<style>
    .rows-time li{
        position: relative;
        margin-bottom: 4px;
    }
    .rows-time li span.del{
        position: absolute;
        top: 4px;
        left: -24px;
        display: inline-block;
        background-color: rgba(237,33,33,.6);
        color: white;
        padding: 0px 5px;
        line-height: 16px;
        font-size: 11px;
        border-radius: 100px;
        cursor: pointer;
    }
    .wrap-time-calc{
        position: relative;
    }
    [name=one_item]{
        position: absolute;
        top: 37px;
        left: 220px;
    }
</style>
<div class="wrap-calculations" data-course="<?=$course['curse']?>" data-rate="<?=$rate?>">

    <div class="text-center h3 header"><?= Html::encode($this->title) ?></div>
    <hr>
    <br>

    <div class="row-">
        <div class="col-md-3">

            <label for="price">Расчёт времени от стоимости</label>
            <input
                type="text"
                name="price"
                id="price"
                class="form-control"
                style="width: 200px;"
                placeholder="Введите цену"
            />
            <br>
            <div class="time-res">00:00:00</div>

        </div>
        <div class="col-md-3">

            <label>Расчёт стоимости от времени</label>
            <input
                type="text"
                class="form-control time"
                style="width: 200px;"
                placeholder="Введите время"
            />
            <br>
            <div class="price-res">0.00</div>

        </div>
        <div class="col-md-5 wrap-time-calc">

            <label>Калькулятор времени</label>
            <input
                type="text"
                class="form-control time-calc"
                style="width: 200px;"
                placeholder="Введите время"
            />
            <input type="checkbox" name="one_item">
            <button class="btn btn-primary btn-sm calculate-cost">
                Расчитать стоимость</button>
            <br>
            <ul class="rows-time"></ul>
            <div class="c-price-res">0.00</div>

        </div>
    </div>
</div>
