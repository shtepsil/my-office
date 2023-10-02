<?php

use backendold\controllers\MainController as d;
use yii\helpers\Url;
use backendold\components\Bootstrap as bp;

?>
<div class="w-reports">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Проект</th>
            <th class="th-rate">
                Задачи<br>
<!--                <span class="notices">Валюта</span>-->
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
        <?if(count($reports)){foreach($reports as $rt):?>

            <tr
                class="p-row" data-project-id="<?=$rt->id?>"
                data-url="ajax/project-change-status-payment"
                data-type-method="post"
            >
                <td>
                    <a href="<?= Url::to([
                        '/projects-list',
                        'id' => $rt->id,
                        'sort' => 'inwork'
                    ]) ?>" target="_blank">
                        <?= $rt->id ?>. <?= $rt->title ?>
                    </a>
                </td>
                <td>
                    <div class="wrap-rate-project">

                        <div class="go-edit"></div>
                        <span>empty</span>
                        <div class="w-edit-rate dn" data-pid="<?=$rt->id?>">
                            <img class="loading" src="/admin/images/animate/loading.gif" width="20" alt="Загрузка">
                            <input
                                type="text"
                                class="form-control rate-project"
                                data-rate="empty"
                                value="empty"
                                data-url="ajax/edit-rate"
                                data-type-method="post"
                            />
                            <?=bp::gi('check_square','er-save')?>
                            <?=bp::gi('x_square','er-close')?>
                        </div>
                    </div>
                </td>
                <td class="list-group-item-warning t-nopaid" data-wh-ids="" data-p-ids="">
                        <span data-cost="empty">
                            empty
                        </span>
                    <button name="total_payment" data-type="payment_request" class="btn btn-primary btn-sm">Запрос оплаты</button>
                </td>
                <td class="list-group-item-primary t-waitpay" data-wh-ids="" data-p-ids="">
                        <span data-cost="empty">
                            empty
                        </span>
                    <button name="total_payment" data-type="proof_payment" class="btn btn-success btn-sm">Подтвердить оплату</button>
                </td>
                <td class="list-group-item-success t-paid">
                    <div class="w-load">
                        <img class="loading" src="/admin/images/animate/loading.gif" width="20" alt="Загрузка">
                    </div>
                    <span data-cost="empty">
                        empty
                    </span>
                </td>
            </tr>

        <?endforeach;}?>
        </tbody>
    </table>
</div>
