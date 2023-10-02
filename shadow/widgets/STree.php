<?php
/**
 * Created by PhpStorm.
 * Project: yii2-cms
 * User: lxShaDoWxl
 * Date: 04.05.15
 * Time: 17:30
 */
namespace shadow\widgets;

use common\models\Structure;
use Yii;
use yii\bootstrap\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

class STree extends Widget
{

    public $items = [];
    public $activeClass = 'item-expanded';
    public $columns = [];
    public $controls = [];
    public $ulOptions = ['class' => 'list-unstyled',];
    public $columnsOptions = ['class' => 'col-xs-2 text-right'];
    public $editOptions = [
        'class' => 'label label-default editable-status editable editable-click'
    ];
    private $_level = 0;
    public function run()
    {
        return $this->renderItems($this->items);
    }
    public function renderItems($items)
    {
        $result = '';
        $options = $this->ulOptions;
        if ($this->_level == 0) {
            $this->_level = 1;
            $options['class'] .= ' tree-items';
        }
        foreach ($items as $item) {
            $result .= $this->renderItem($item);
        }
        return Html::tag('ul', $result, $options);
    }
    public function renderItem($item)
    {
        $link = $this->renderLink($item['link']);
        $columns = '';
        if ($this->columns) {
            $columns = $this->renderColumns($item['model']);
        }
        $control = '';
        if ($this->controls) {
            $control = $this->renderControl($item['model']);
        }
        $control .= '<div class="clearfix"></div>';
        $content_li = Html::tag('div', $link . $columns . $control, ['class' => 'tree-item']);
        if (isset($item['items']) && $item['items']) {
            $content_li .= $this->renderItems($item['items']);
        }
        $result = Html::tag('li', $content_li);
        return $result;
    }
    public function renderControl($item)
    {
        $a_default = [
            'class' => 'btn'
        ];
        $result = Html::beginTag('div', ['class' => 'actions col-xs-2 text-right']);
        $result .= Html::beginTag('div', ['class' => 'btn-group']);
        foreach ($this->controls as $control) {
            if ($control instanceof \Closure) {
                $result .= call_user_func($control, $item);
            } else {
                $icons_option = [
                    'class' => 'fa fa-' . $control['icon']
                ];
                if (isset($control['url'])) {
                    foreach ($control['url'] as $key => $value) {
                        $control['url'][$key] = strtr($value, ['{id}' => $item->id]);
                    }
                }
                $content = Html::tag('i', '', $icons_option);
                if (isset($control['options']) && isset($control['options']['class'])) {
                    $a_options = $control['options'];
                    $a_options['class'] = ($a_options['class'] . ' ' . $a_default['class']);
                } else {
                    $a_options = [];
                    $a_options['class'] = $a_default['class'];
                }
                $result .= Html::a($content, $control['url'], $a_options);
            }
        }
        $result .= Html::endTag('div');
        $result .= Html::endTag('div');
        return $result;
    }
    public function renderLink($link)
    {
        $content_a = Html::tag('i', '', ['class' => 'fa fa-file-o fa-fw']) . ' ' . $link['title'];
        $content = Html::a($content_a, $link['url']);
        if (isset($link['prev'])) {
            $content_a = '<span class="label label-info"><i class="fa fa-globe"></i> Просмотреть страницу</span>';
            $content .= Html::a($content_a, Yii::$app->urlManagerFrontEnd->createUrl($link['prev']), ['class' => 'item-preview', 'target' => '_blank']);
        }
        $result = Html::tag('div', $content, ['class' => 'title col-xs-8']);
        return $result;
    }
    public function renderColumns($item)
    {
        /**
         * @var $item \backend\models\FooterMenu
         */
        $result = '';
        foreach ($this->columns as $key => $column) {
            if (!isset($column['options'])) {
                $options = $this->columnsOptions;
            } else {
                $options = $column['options'];
                $class = ArrayHelper::getValue($options, 'class');
                if ($class) {
                    $options['class'] .= ' ' . $this->columnsOptions['class'];
                }
            }
//            $options = ArrayHelper::merge($this->columnsOptions, $column['options']);
            if (isset($column['function']) && $column['function'] instanceof \Closure) {
                $content = call_user_func($column['function'], $item);
            } else {
                if (is_array($column)) {
                    $content = $item->getAttribute($key);
                } else {
                    $content = $item->getAttribute($column);
                }
            }
            $result .= Html::tag('div', $content, $options);
        }
        return $result;
    }

}