<?php

use backend\controllers\MainController as d;
use yii\helpers\Html;
use yii\widgets\MaskedInput;

//d::pri($ss);

$tt = isset($ss['time_trecker']) ? $ss['time_trecker'] : null;
$pomodoro = isset($tt['settings']['pomodoro']) ? $tt['settings']['pomodoro'] : null;
$s_time = isset($tt['settings']['time']) ? $tt['settings']['time'] : null;
//d::pex($tt->settings);
//d::pex($pomodoro);

?>
<div data-tab="time_trecker">

    <input type="hidden" name="setting_code" value="<?=$tt->code?>" />

    <br>
    <div class="col-md-3">

        <div class="w-pomodoro">
            <label>Настройки Pomodoro</label>

            <fieldset class="formRow">
                <div class="formRow--item">
                    <label for="time_work" class="formRow--input-wrapper js-inputWrapper">
                        <?= MaskedInput::widget([
                            'name' => 'Pomodoro[time_work]',
                            'id' => 'time_work',
                            'mask' => '99:99:99',
                            'value' => $pomodoro['time_work'],
                            'definitions' => [
                                'maskSymbol' => '_'
                            ],
                            'options' => [
                                'placeholder' => 'Время работы',
                                'class' => 'formRow--input js-input'
                            ]
                        ]) ?>
                    </label>
                </div>
            </fieldset>

            <fieldset class="formRow">
                <div class="formRow--item">
                    <label for="time_relax" class="formRow--input-wrapper js-inputWrapper">
                        <?= MaskedInput::widget([
                            'name' => 'Pomodoro[time_relax]',
                            'id' => 'time_relax',
                            'mask' => '99:99:99',
                            'value' => $pomodoro['time_relax'],
                            'definitions' => [
                                'maskSymbol' => '_'
                            ],
                            'options' => [
                                'placeholder' => 'Время отдыха',
                                'class' => 'formRow--input js-input'
                            ]
                        ]) ?>
                    </label>
                </div>
            </fieldset>

            <fieldset class="formRow">
                <div class="formRow--item">
                    <label for="long_relax" class="formRow--input-wrapper js-inputWrapper">
                        <?= MaskedInput::widget([
                            'name' => 'Pomodoro[long_relax]',
                            'id' => 'long_relax',
                            'mask' => '99:99:99',
                            'value' => $pomodoro['long_relax'],
                            'definitions' => [
                                'maskSymbol' => '_'
                            ],
                            'options' => [
                                'placeholder' => 'Большой отдых',
                                'class' => 'formRow--input js-input'
                            ]
                        ]) ?>
                    </label>
                </div>
            </fieldset>
        </div>

    </div>

    <div class="col-md-12">
        <button
            type="button" class="btn btn-primary btn-sm save-settings"
            name="time_trecker"
            data-url="ajax/save-settings"
            data-type-method="post"
        >
            Сохранить
            <?=Html::img('images/animate/loading.gif',['alt'=>'','class'=>'loading'])?>
        </button>
    </div>

</div>
