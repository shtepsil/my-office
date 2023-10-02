<?php

use backendold\controllers\MainController as d;
use yii\helpers\Url;
use backendold\components\Bootstrap as bp;

?>
<table class="table table-bordered">
    <thead>
    <tr>
        <th>Проект</th>
        <th class="th-rate">
            Тариф<br>
            <span class="notices">Валюта <?=$ws->settings['rate']['currency']?></span>
        </th>
        <th class="no-paid">
            Не оплачено всего по проекту<br>
            <span class="notices">Выполнено, но оплата не запрошена</span>
        </th>
        <th class="waiting-payment">
            Ожидание оплаты<br>
            <span class="notices">Оплата запрошена</span>
        </th>
        <th>Оплачено</th>
    </tr>
    </thead>
    <tbody>
    <?if($projects){foreach($projects as $pt):?>
        <?if ($pt->active == $active):?>
            <?php

//            d::pri($pt);
//            continue;

            ?>

            <tr
                class="p-row" data-project-id="<?=$pt->id?>"
                data-url="ajax/project-change-status-payment"
                data-type-method="post"
            >
                <td>
                    <a href="<?= Url::to([
                        '/projects-list',
                        'id' => $pt->id,
                        'sort' => 'inwork'
                    ]) ?>" target="_blank">
                        <?= $pt->id ?>. <?= $pt->name ?>
                    </a>
                </td>
                <td>
                    <div class="wrap-rate-project">
                        <div class="go-edit"></div>
                        <span><?=(isset($pt->rate['value']) ? $pt->rate['value'] : 0)?></span>
                        <div class="w-edit-rate dn" data-pid="<?=$pt->id?>">
                            <img class="loading" src="/admin/images/animate/loading.gif" width="20" alt="Загрузка">
                            <input
                                type="text"
                                class="form-control rate-project"
                                data-rate="<?=(isset($pt->rate['value']) ? $pt->rate['value'] : 0)?>"
                                value="<?=(isset($pt->rate['value']) ? $pt->rate['value'] : 0)?>"
                                data-url="ajax/edit-rate"
                                data-type-method="post"
                            />
                            <?=bp::gi('check_square','er-save')?>
                            <?=bp::gi('x_square','er-close')?>
                        </div>
                    </div>
                </td>
                <td class="list-group-item-warning t-nopaid" data-wh-ids="" data-p-ids="">
                        <span data-cost="<?=$pt->paymentnopaid?>">
                            <?=($pt->paymentnopaid)?number_format($pt->paymentnopaid,2,'.',' '):''?>
                        </span>
                        <button name="total_payment" data-type="payment_request" class="btn btn-primary btn-sm <?=($pt->paymentnopaid)?'':'dn'?>">Запрос оплаты</button>
                </td>
                <td class="list-group-item-primary t-waitpay" data-wh-ids="" data-p-ids="">
                        <span data-cost="<?=$pt->paymentwaitpay?>">
                            <?=($pt->paymentwaitpay)?number_format($pt->paymentwaitpay,2,'.',' '):''?>
                        </span>
                        <button name="total_payment" data-type="proof_payment" class="btn btn-success btn-sm <?=($pt->paymentwaitpay)?'':'dn'?>">Подтвердить оплату</button>
                </td>
                <td class="list-group-item-success t-paid">
                    <div class="w-load">
                        <img class="loading" src="/admin/images/animate/loading.gif" width="20" alt="Загрузка">
                    </div>
                    <span data-cost="<?=$pt->paymentpaid?>">
                        <?=($pt->paymentpaid)?number_format($pt->paymentpaid,2,'.',' '):''?>
                    </span>
                </td>
            </tr>
        <?endif?>
    <?endforeach;}?>
    </tbody>
</table>
