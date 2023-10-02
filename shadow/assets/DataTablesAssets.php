<?php
/**
 * Created by PhpStorm.
 * Project: morkovka
 * User: lxShaDoWxl
 * Date: 09.09.15
 * Time: 10:03
 */
namespace shadow\assets;

use yii\web\AssetBundle;

class DataTablesAssets extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@shadow/js_css_lib/datatables';
    /**
     * @inheritdoc
     * Не добавлять никакие больше скрипты кроме основного все остальные прекреплять через
     * $this->registerJsFile(
        $context->AppAsset->baseUrl . '/js/plugins/goods_slide.js',
        [
            'depends' => [
                '\shadow\assets\DataTablesAssets'
            ]
        ]
    );
     */
    public $js = [
        'datatables.js',
        'custom_datatables.js',
        'jquery.dataTables.columnFilter.js',
    ];
    public $depends = [
        'backend\assets\AdminAsset',
    ];
}