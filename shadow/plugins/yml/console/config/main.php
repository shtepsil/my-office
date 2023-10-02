<?php
/**
 * @var $config array
 */
return yii\helpers\ArrayHelper::merge(
    $config,
    [
        'aliases' => [
            '@yml_config' => __DIR__,
        ],
        'controllerMap' => [
            'yml'=>'shadow\plugins\yml\console\controllers\YmlController'
        ],
    ]
);