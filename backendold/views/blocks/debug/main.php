<?php

use backendold\controllers\MainController as d;
use yii\helpers\Url;
use yii\bootstrap\Html;

?>
<style>

</style>
<div class="row">
    <div class="col-md-12">
        <div class="tab<?=$tab_index?>-buttons" style="position: relative;">
            <?=Html::img('/images/animate/loading.gif', [
                'class' => 'loading'
            ])?>
            <div class="form-gorup">
                <div class="h3">Файл debug.txt</div>
                <div class="mini-form">
                    <input
                        type="text" name="file_debug_name"
                        class="form-control w120" placeholder="Имя файла debug"
                        value="debug.txt"
                    >
                    <button name="get_file_debug" class="btn btn-sm btn-primary">Получить</button>
                    &nbsp;&nbsp;&nbsp;
                    <button name="clear_file_debug" class="btn btn-sm btn-danger">Очистить</button>
                    &nbsp;&nbsp;&nbsp;
                </div>
            </div>
            <br>
            <div class="form-gorup">

                <div class="mini-form">
                    <button name="btn_push" class="btn btn-sm btn-danger">Нажать</button>
                    &nbsp;&nbsp;&nbsp;
                </div>
            </div>
            <br>
            <div class="form-gorup">

                <div class="mini-form">
                    <button name="btn_browser_notyfication" class="btn btn-sm btn-danger" onclick="sendNotification2('title', {
                        body: 'Начнём работу!',
                        dir: 'auto',
                        icon: 'images/work2.jpg'
                    })">Тест Browser Notyfication</button>
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
$action = 'debug';
$this->registerJs(<<<JS
//JS
$(function(){});
var params = {};
params['action'] = '{$action}';
tabsAjax('{$tab_index}', params, true);
JS
)
?>
