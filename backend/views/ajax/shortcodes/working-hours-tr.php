<?php
use backend\controllers\MainController as d;

$ss = $working_hours;
$m = '0';
$h = '00';
// Считаем количество минут
if ($ss > 59) {
    $m = floor($ss / 60);
    // Получаем остаток секунд (должно быть меньше 60)
    $s = $ss - ($m * 60);
} else $s = $ss;
// Считаем количество часов
if ($m > 59) {
    $h = floor($m / 60);
    if ($h < 10) $h = '0' . $h;
    // Получаем остаток минут (должно быть меньше 60)
    $m = $m - ($h * 60);
}

if ($s < 10) $s = '0' . $s;
if ($m < 10) $m = '0' . $m;
?>
<tr>
    <td><?=(d::changeDate(date('Y-m-d', $created_at), 'rus'))?></td>
    <td
        class="work-time"
        data-hs="<?= $h?>"
        data-ms="<?= $m?>"
        data-ss="<?= $s?>"
    ><?=$h . ':' . $m . ':' . $s . ''?></td>
    <td><?= date('Y-m-d H:i:s', $created_at)?></td>
</tr>