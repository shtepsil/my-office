<?php

use backend\controllers\MainController as d;

// todo просто список задач, для отправки

$pn = '';

?>
<?if($tasks):?>

    <?foreach($tasks as $t):?>
        <?php

//        if($pn != $t->project->name){
            $pn = $t->project->name;
//        }else $pn = '';

        ?>
        <b><?=$pn?></b>

        <div class="t-name">
            <b><?=$t->id?> <?=$t->name?></b>
        </div>
        <div class="t-desc">
            <?=$t->description?>
        </div>
        <br>
        <br>
    <?endforeach?>
<?else:?>
    <div class="p-no">
        Не введено ID проекта
    </div>
<?endif?>
