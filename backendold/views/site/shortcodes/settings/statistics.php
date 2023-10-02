<?php

use backendold\controllers\MainController as d;
use yii\helpers\Html;

//d::pex($ss);

$stcs = $ss['statistics'];
$rate = $stcs['settings']['rate'];

//d::pex($rate);

?>
<div data-tab="statistics">

    <input type="hidden" name="setting_code" value="<?=$stcs['code']?>" />

    <br>
    <div class="col-md-3">

        <div class="w-rate">
            <label>Настройка тарифа</label>

            <fieldset class="formRow">
                <div class="formRow--item">
                    <label for="currency" class="formRow--input-wrapper js-inputWrapper">
                        <input type="text" name="Rate[currency]" value="<?=$rate['currency']?>" class="formRow--input js-input" id="currency" placeholder="Тип валюты">
                    </label>
                </div>
            </fieldset>

            <fieldset class="formRow">
                <div class="formRow--item">
                    <label for="cost" class="formRow--input-wrapper js-inputWrapper">
                        <input type="text" name="Rate[cost]" value="<?=$rate['cost']?>" class="formRow--input js-input" id="cost" placeholder="Тариф за час">
                    </label>
                </div>
            </fieldset>

            <fieldset class="formRow">
                <div class="formRow--item">
                    <label for="state_course" class="formRow--input-wrapper js-inputWrapper">
                        <input type="text" name="Rate[state_course]" value="<?=$rate['state_course']?>" class="formRow--input js-input" id="state_course" placeholder="Курс системы">
                    </label>
                </div>
            </fieldset>
        </div>

    </div>

    <div class="col-md-12">
        <button
                type="button" class="btn btn-primary btn-sm save-settings"
                name="statistics"
                data-url="ajax/save-settings"
                data-type-method="post"
        >
            Сохранить
            <?=Html::img('images/animate/loading.gif',['alt'=>'','class'=>'loading'])?>
        </button>
    </div>

    <br><br><br><br><br><br>

    <div class="col-md-12">
        <button
                type="button" class="btn btn-primary btn-sm get-rate"
                name="get_rate"
                data-url="ajax/get-rate"
                data-type-method="post"
        >
            Получить курс доллара
            <?=Html::img('images/animate/loading.gif',['alt'=>'','class'=>'loading'])?>
        </button><br><br>
        <div class="current-curse" style="font-weight: 600;">
            USD: <span style="color: red;">0.00</span>
        </div>
    </div>

</div>