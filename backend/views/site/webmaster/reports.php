<?php

use backend\controllers\MainController as d;
use yii\helpers\Url;
use backend\components\Bootstrap as bp;
//d::pex($reports);
// url для отправки отчёта ajax/change-status-payment
?>
<div class="w-reports">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Проект</th>
            <th class="th-rate">
                Детали отчёта<br>
<!--                <span class="notices">Валюта</span>-->
            </th>
            <th>Общее время работы</th>
            <th class="no-paid">
                Итоговая сумма отчёта<br>
                <span class="notices">Отчёт не отправлен / отправлен</span>
            </th>
            <th class="waiting-payment">
                Ожидание оплаты<br>
                <span class="notices">Оплата запрошена</span>
            </th>
            <th>Оплачено</th>
        </tr>
        </thead>
        <tbody>
        <?if(count($reports)){foreach($reports as $report):?>

            <?

//        d::pri($report->total_time['h_i_s']);

            ?>

            <tr
                class="p-row" data-project-id="<?=$report->id?>"
                data-url="ajax/project-change-status-payment"
                data-type-method="post"
            >
                <td>
                    <a href="<?= Url::to([
                        '/projects-list',
                        'id' => $report->id,
                        'sort' => 'inwork'
                    ]) ?>" target="_blank">
                        <?= $report->id ?>. <?= $report->title ?>
                    </a>
                </td>
                <td style="text-align:center;">
                    <a href="<?=Url::to(['site/reports', 'id' => $report->id])?>">Открыть отчёт</a>
                </td>
                <td>
                    <span data-cost="empty">
                        <?=$report->total_time['h_i_s']?>
                    </span>
                </td>
                <td class="list-group-item-warning t-nopaid" data-wh-ids="" data-p-ids="">
                        <span data-cost="empty">
                            <?=$report->total_cost?>
                        </span>
                    <button name="total_payment" data-type="payment_request" class="btn btn-primary btn-sm">
                        Отправить отчёт</button>
                </td>
                <td class="list-group-item-primary t-waitpay" data-wh-ids="" data-p-ids="">
                        <span data-cost="empty">0</span>
                    <button name="total_payment" data-type="proof_payment" class="btn btn-success btn-sm">Подтвердить оплату</button>
                </td>
                <td class="list-group-item-success t-paid">
                    <div class="w-load">
                        <img class="loading" src="/admin/images/animate/loading.gif" width="20" alt="Загрузка">
                    </div>
                    <span data-cost="empty">0</span>
                </td>
            </tr>

        <?endforeach;}?>
        </tbody>
    </table>
</div>
