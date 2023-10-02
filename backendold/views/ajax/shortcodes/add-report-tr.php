<?php // todo строка tr страница "Добавление отчета"

use backendold\controllers\MainController as d;

if($date_in){
    $text_color = 'success';
    $type_operation = '1';
}else{
    $text_color = 'warning';
    $type_operation = '2';
}
if($moving_accounts == '1'){
    $text_color = 'info';
}
?>
<tr data-type="<?=$type?>" class="<?=$text_color?>">
    <td class="income"><?=($income == 'on')?'Доход':''?></td>
    <td class="date-in">
        <input type="hidden" name="date_in" value="<?=($date_in)?$date_in:''?>" />
        <?=($date_in)?d::changeDate($date_in,'rus'):''?>
    </td>
    <td class="date-out">
        <input type="hidden" name="date_out" value="<?=($date_out)?$date_out:''?>" />
        <?=($date_out)?d::changeDate($date_out,'rus'):''?>
    </td>
    <td class="comment"><?=$comment?></td>
    <td class="ammount"><?=($ammount)?$ammount:'0'?></td>
    <td class="wallet"><?=$wallet_name?></td>
    <td class="tags">
        <input
            type="hidden"
            name="type_operation"
            value="<?=$type_operation?>" />
        <input
            type="hidden"
            name="wallet"
            value="<?=$wallet?>"
            data-balance="<?=$wallet_balance?>"
        >
        <input
            type="hidden"
            name="monthly_row"
            value="<?=$monthly_row?>"
        >
        <input
            type="hidden"
            name="income"
            value="<?=$income?>"
        >
        <input
            type="hidden"
            name="tags"
            value="<?=($tags)?$tags:'other'?>"
        >
        <input
            type="hidden"
            name="moving_accounts"
            data-wallet-from="<?=$moving_wallet_from?>"
            data-wallet-from-ammount="<?=$moving_wallet_from_ammount?>"
            data-wallet-to="<?=$moving_wallet_to?>"
            data-wallet-to-ammount="<?=$moving_wallet_to_ammount?>"
            data-percentage="<?=$percentage?>"
            value="<?=($moving_accounts == '1')?$moving_accounts:'0'?>"
        >
        <span><?=($tags != 'other')?$tags_name:'Прочее'?></span>
    </td>
    <td class="actions">
        <div class="delete">
            <span class="glyphicon glyphicon-remove" onclick="deleteRow(this)"></span>
        </div>
        <div class="edit">
            <span class="glyphicon glyphicon-pencil" onclick="editRow(this)"></span>
        </div>
    </td>
</tr>