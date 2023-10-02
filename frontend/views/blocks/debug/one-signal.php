<?php

use common\components\Debugger as d;
use yii\helpers\Url;
use yii\bootstrap\Html;
?>
<style>

</style>
<div class="row">
    <div class="col-md-12">
        <div class="tab<?=$tab_index?>-buttons" style="position: relative;">
            <?=Html::img($context->AppAsset->baseUrl . '/images/animate/loading.gif', [
                'class' => 'loading'
            ])?>
            <div class="form-gorup">

                <div class="mini-form dn">
                    <input
                        type="text" name="order_id"
                        class="form-control w120" placeholder="ID ордера"
                        value=""
                    >
                    <button name="delete_order" class="btn_debug blue">Удалить ордер</button>
                    &nbsp;&nbsp;&nbsp;
                </div>
                <div class="mini-form">
                    <input
                            type="text" name="header"
                            class="form-control w250" placeholder="Заголовок уведомления"
                            value=""
                    >
                    <br>
			   		<textarea
                        name="message" id=""
                        placeholder="PUSH Сообщение" class="h90 w250"></textarea>
                   	<br>
					<input
						type="text" name="user_id"
						class="form-control w250" placeholder="ID пользователя"
						value=""
					>
                   	<br>
                    <button name="onesignal_send_notification" class="btn_debug blue">Отправить уведомление</button>
                &nbsp;&nbsp;&nbsp;
                </div>
            </div>
            <br>
        </div>
        <?=d::res(false, 'res-tab' . $tab_index);?>
    </div>
</div>
<br><br>
<?php
$action = 'one-signal';
$this->registerJs(<<<JS
//JS
$(function(){});
var params = {};
params['action'] = '{$action}';
tabsAjax('{$tab_index}', params);
JS
)
?>
