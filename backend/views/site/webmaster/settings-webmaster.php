<?php

use backend\controllers\MainController as d;
use yii\helpers\Html;

$this->title = 'Настройки WebMaster';

// для функции number_format
$zeroz = Yii::getAlias('@zero,');

/*
 * Класс segs, это сокращение: settings
*/

//d::pre(date('Y-m-d',1548149584));

?>
<style type="text/css">

</style>
<div class="segs">
    <div class="text-center h3 header"><?= Html::encode($this->title) ?></div>

    <ul class="nav nav-tabs">
        <li class="active"><a data-toggle="tab" href="#statistics">Статистика</a></li>
        <li><a data-toggle="tab" href="#time_trecker">Time Трекер</a></li>
    </ul>

    <div class="tab-content">

        <!-- tab -->
        <div id="statistics" class="tab-pane fade in active">

            <?=$this->render('//site/shortcodes/settings/statistics',[
                'ss'=>$ss,
            ])?>

        </div>

        <!-- tab -->
        <div id="time_trecker" class="tab-pane fade">

            <?=$this->render('//site/shortcodes/settings/time-trecker',[
                'ss'=>$ss,
            ])?>

        </div>

    </div>

    <?=d::res()?>

</div>