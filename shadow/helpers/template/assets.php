<?php
/**
 * This is the template for generating the assets class of a specified table.
 */

/* @var $className string class name */
/* @var $patch string class name */
/* @var $js string[] list of js */
/* @var $css string[] list of css */

echo "<?php\n";
?>

namespace frontend\assets;


use yii\web\AssetBundle;

class <?=$className?> extends AssetBundle {
    /**
     * @inheritdoc
     */
    public $sourcePath = '<?=$patch?>';
<?php if (isset($js)&&$js): ?>
    /**
     * @inheritdoc
     */
    public $js = [
    <?php foreach ($js as $name): ?>
        <?= "'" . $name. "',\n" ?>
    <?php endforeach; ?>
    ];
<?php endif; ?>
<?php if (isset($css)&&$css): ?>
    /**
    * @inheritdoc
    */
    public $css = [
    <?php foreach ($css as $name): ?>
        <?= "'" . $name. "',\n" ?>
    <?php endforeach; ?>
    ];
<?php endif; ?>
<?php if (isset($depends)&&$depends): ?>
    /**
    * @inheritdoc
    */
    public $depends = [
    <?php foreach ($depends as $name): ?>
        <?= "'" . $name. "',\n" ?>
    <?php endforeach; ?>
    ];
<?php endif; ?>
}