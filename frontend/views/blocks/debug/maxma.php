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
                            type="text" name="data_input"
                            class="form-control w120" placeholder="ID ордера"
                            value=""
                    >
                    <button name="send_data" class="btn_debug blue">Отправить запрос</button>
                    &nbsp;&nbsp;&nbsp;
                </div>

                <button name="send_maxma" class="btn_debug blue">Send request</button>
                &nbsp;&nbsp;&nbsp;
            </div>
            <br>
        </div>
        <?=d::res(false, 'res-tab' . $tab_index);?>
    </div>
</div>
<br><br>
<?php
$action = 'maxma';
$this->registerJs(<<<JS
//JS
$(function(){});
var params = {};
params['action'] = '{$action}';
tabsAjax('{$tab_index}', params);
JS
)
?>
