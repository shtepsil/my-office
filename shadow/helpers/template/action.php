<?php
/**
 * This is the template for generating the action class of a specified table.
 */

/* @var $this yii\web\View */
/* @var $className string class name */
/* @var $template string name template */

echo "<?php\n";
?>

namespace frontend\components\actions;


use yii\base\Action;

class <?=$className?> extends Action {
    /**
     * Runs the action.
     */
    public function run()
    {
        \Yii::$app->controller->render('<?=$template?>');
    }
}