<?php
/**
 * @var $actions array
 * @var $this yii\web\View
 * @var $generator shadow\helpers\GeneratorHelper
 */

echo "<?php\n";
echo "return [\n";
?>
<?php foreach ($actions as $key=>$value): ?>
<?=$generator->echoArray($key,$value,1)?>
<?php endforeach; ?>
<?="\n];";