<?php // todo строка tr страница "История"

use backendold\controllers\MainController as d;
$number_sign = '';
if($type_operation != '3' AND $tags != 'move'){
    if ($date_in == '0000-00-00') $text_color = 'warning';
    if ($date_out == '0000-00-00') $text_color = 'success';
}else{
    $text_color = 'info';
    if($type_operation == '1'){
        $number_sign = '+';
        $color = 'text-success';
    }
    if($type_operation == '2'){
        $number_sign = '-';
        $color = 'text-warning';
    }
}

?>
<tr data-type="<?=$type_operation?>" class="<?=$text_color?> <?=$color?>">
    <td><?=($date_in != '0000-00-00')?d::changeDate($date_in,'rus'):''?></td>
    <td><?=($date_out != '0000-00-00')?d::changeDate($date_out,'rus'):''?></td>
    <td><?=$comment?></td>
    <td class="ammount"><?=$ammount?></td>
    <td><?=$tags?> <?=$number_sign?></td>
</tr>