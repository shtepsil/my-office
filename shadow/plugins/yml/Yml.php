<?php
namespace shadow\plugins\yml;

use backend\modules\catalog\models\Category;
use backend\modules\catalog\models\Items;
use shadow\helpers\SArrayHelper;
use shadow\helpers\SFileHelper;
use XMLWriter;
use yii\base\Component;

class Yml extends Component
{
    public $shopOptions = [
        'name' => 'Trendme',
        'company' => 'Trendme',
        'url' => 'http://trendme.kz',
        'platform' => '',
        'version' => '',
        'agency' => '',
        'email' => ''
    ];
    /**
     * An array of element names used to create an offer according to YML standart
     * @var array
     */
    public $offerElements = [
        'url',
        'price',
        'oldprice',
        'currencyId',
        'categoryId',
        'market_category',
        'picture',
        'store',
        'pickup',
        'delivery',
        'local_delivery_cost',
        'typePrefix',
        'vendor',
        'vendorCode',
        'name',
        'model',
        'description',
        'sales_notes',
        'manufacturer_warranty',
        'seller_warranty',
        'country_of_origin',
        'downloadable',
        'age',
        'barcode',
        'cpa',
        'rec',
        'expiry',
        'weight',
        'dimensions',
        'param'
    ];
    /**
     * @var array Категории которые не надо экспортировать
     */
    public $no_cats = [];
    public $filePath = '@web_frontend/uploads/yml/catalog.yml';
    public $fileUrl = '/uploads/yml/catalog.yml';
    public $encoding = 'UTF-8';
    /**
     * Indent string in xml file. False or null means no indent;
     * @var string
     */
    public $indentString = "\t";
    /**
     * @var string Способ запуска
     *  console - в консоли с разбиеним на несколько файлов для большого каталога
     *  normal - обычный запуск внутри компонента
     */
    public $typeLaunch = 'console';

    public $debug = false;
    protected $_exclude_cats = [];
    protected $_dir;
    protected $_file_name;
    protected $_tmp_dir;
    protected $_engine;
    protected $_tmpFile;
    protected $_hostInfo;
    protected function initDir()
    {
        $this->_hostInfo = rtrim($this->shopOptions['url'], '/');
        \Yii::$app->urlManagerFrontEnd->hostInfo = $this->_hostInfo;
        $this->filePath = \Yii::getAlias($this->filePath);
        $path_info = pathinfo($this->filePath);
        $this->_dir = SArrayHelper::getValue($path_info, 'dirname');
        $this->_file_name = SArrayHelper::getValue($path_info, 'filename');
        $this->_tmp_dir = $this->_dir . DIRECTORY_SEPARATOR . 'tmp';
        if (!is_dir($this->_tmp_dir)) {
            SFileHelper::createDirectory($this->_tmp_dir);
        }
    }
    /**
     * Initializes the object.
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     */
    public function init()
    {
        parent::init();
        $this->initDir();
    }

    public function start()
    {
        if (!is_file($this->_dir . DIRECTORY_SEPARATOR . $this->_file_name . '.lock') || $this->debug) {
            $this->shop();
        } else {
            $lock = file_get_contents($this->_dir . DIRECTORY_SEPARATOR . $this->_file_name . '.lock');
            if ($lock) {
                if ((time() - $lock) > 1800) {
                    $this->shop();
                }else{
                    return false;
                }
            } else {
                unlink($this->_dir . DIRECTORY_SEPARATOR . $this->_file_name . '.lock');
                $this->shop();
            }
        }
        return true;
    }
    public function shop()
    {
        SFileHelper::removeDirectory($this->_tmp_dir);
        SFileHelper::createDirectory($this->_tmp_dir);
        file_put_contents($this->_dir . DIRECTORY_SEPARATOR . $this->_file_name . '.lock', time());
        if ($this->no_cats) {
            $this->no_cats = array_filter($this->no_cats, function (&$val) {
                $val = intval($val);
                return $val;
            });
            $this->_exclude_cats = $this->no_cats;
        }
        $shop = new Shop($this->shopOptions);
        $this->beforeWrite(true);
        $engine = $this->getEngine();
        foreach ($shop->attributes as $elm => $text) {
            if ($text) {
                $engine->writeElement($elm, $text);
            }
        }
        $engine->startElement('currencies');
        $this->addCurrency('KZT', 1);
        $engine->fullEndElement();
        $this->writeCategories();
        $engine->startElement('offers');
        $engine->text('{{offers}}');
        $engine->fullEndElement();
        $this->afterWrite();
        $this->_engine = null;
        if ($this->typeLaunch == 'console') {
            $yii = \Yii::getAlias('@app/..') . DIRECTORY_SEPARATOR . 'yii';
            $config = __DIR__.'/console/config/main.php';
            $command = $yii.' yml/start';
            if($this->_exclude_cats){
                $command .= ' 500 ' . implode(',', $this->_exclude_cats);
            }
            if($this->debug){
                $output=shell_exec("$command --appconfig={$config} 2>&1");
            }else{
                exec("$command --appconfig={$config} > /dev/null &");
            }

        } else {
            $this->offers(0, 0, $this->_exclude_cats);
            $this->end();
        }
    }
    public function end()
    {
        $files = SFileHelper::findFiles($this->_tmp_dir, [
            'only' => [
                '*.yml'
            ]
        ]);
        $offers = '';
        foreach ($files as $file) {
            if ($content_file = file_get_contents($file)) {
                $content_file = preg_replace('/\<\?xml .*\?\>{1}/', '', $content_file);
                $offers .= $content_file . "\n";
            }
        }
        if ($offers) {
            $shop_yml = file_get_contents($this->_dir . DIRECTORY_SEPARATOR . 'shop.yml');
            if ($shop_yml) {
                $shop_yml = str_replace('{{offers}}', "\n" . $offers, $shop_yml);
                file_put_contents($this->filePath, $shop_yml);
            }
        }
        SFileHelper::removeDirectory($this->_tmp_dir);
        unlink($this->_dir . DIRECTORY_SEPARATOR . 'shop.yml');
        unlink($this->_dir . DIRECTORY_SEPARATOR . 'catalog.lock');
    }
    public function offers($limit = 0, $offset = 0, $no_cats = [])
    {
        $q = Items::find();
        $q->andWhere(['isVisible' => 1, 'isDeleted' => 0]);
        $q->with([
            'itemOptionsValues.option',
            'itemOptionsValues.optionValue'
        ]);
        if ($no_cats) {
            $q->andWhere(['not in', 'cid', $no_cats]);
        }
        if ($limit > 0) {
            $q->limit($limit);
            $q->offset($offset);
        }
        /** @var Items[] $items */
        $items = $q->all();
        if ($items) {
            $engine = $this->getEngine();
            $this->_tmpFile = $this->_dir . DIRECTORY_SEPARATOR . md5($offset);
            $engine->openURI($this->_tmpFile);
            $engine->startDocument('1.0', $this->encoding);
            if ($this->indentString) {
                $engine->setIndentString($this->indentString);
                $engine->setIndent(true);
            }
            /** @var \yii\web\urlManager $manager */
            $manager = \Yii::$app->urlManagerFrontEnd;
            foreach ($items as $item) {
                $params = [];
                $data = [
                    'url' => $manager->createAbsoluteUrl(['site/item', 'id' => $item->id]),
                    'price' => floatval($item->real_price()),
                    'picture' => $this->_hostInfo . $item->img(false),
                    'delivery' => 'true',
                    'currencyId' => 'KZT',
                    'categoryId' => $item->cid,
                    'vendorCode' => $item->vendor_code,
                    'description' => $item->body,
                    'name' => $item->name
                ];
                if ($item->old_price && $item->old_price > $item->price) {
                    $data['oldprice'] = $item->old_price;
                }
                //array(NAME,UNIT,VALUE)
                if ($options = $item->itemOptionsValues) {
                    $add_options = [];
                    foreach ($options as $option) {
                        if (!isset($add_options[$option->option_id])) {
                            $add_options[$option->option_id] = array(
                                'name' => $option->option->name,
                                'values' => [],
                                'measure' => $option->option->measure
                            );
                        }
                        if ($option->option->type == 'value') {
                            $add_options[$option->option_id]['values'][] = $option->value;
                        } elseif ($option->option->type == 'multi_select' || $option->option->type == 'one_select') {
                            $add_options[$option->option_id]['values'][] = $option->optionValue->value;
                        } elseif ($option->option->type == 'range') {
                            $add_options[$option->option_id]['values'][] = $option->value . '-' . $option->max_value;
                        }
                    }
                    if ($add_options) {
                        foreach ($add_options as $add_option) {
                            if ($add_option['values']) {
                                $value = trim(implode(',', $add_option['values']));
                                if (mb_strlen($value) < 200 && $value) {
                                    $params[] = [$add_option['name'], $add_option['measure'], $value];
                                }
                            }
                        }
                    }
                }
                if ($item->brand_id) {
                    $data['vendor'] = $item->brand->name;
                }
                $available = true;
                if ($item->status == 0) {
                    $available = false;
                }
                $this->addOffer($item->id, $data, $params, $available, false);
            }
            $engine->endDocument();
            rename($this->_tmpFile, $this->_tmp_dir . DIRECTORY_SEPARATOR . $offset . '.yml');
        }
        return count($items);
    }
    /**
     * массив атрибутов offer например group_id
     * @link https://support.deal.by/documents/603
     * @var array
     */
    public $offerAttributes = ['group_id'];
    /**
     * Adds <offer> element. (See http://help.yandex.ru/partnermarket/offers.xml)
     * @param int $id "id" attribute
     * @param array $data array of subelements as elementName=>value pairs
     * @param array $params array of <param> elements. Every element is an array: array(NAME,UNIT,VALUE) (See http://help.yandex.ru/partnermarket/param.xml)
     * @param boolean $available "available" attribute
     * @param string $type "type" attribute
     * @param int $bid "bid" attribute
     * @param int $cbid "cbid" attribute
     */
    protected function addOffer($id, $data, $params = array(), $available = true, $type = 'vendor.model', $bid = null, $cbid = null)
    {
        $engine = $this->getEngine();
        $engine->startElement('offer');
        $engine->writeAttribute('id', $id);
        if ($type) {
            $engine->writeAttribute('type', $type);
        }
        $engine->writeAttribute('available', $available ? 'true' : 'false');
        if ($bid) {
            $engine->writeAttribute('bid', $bid);
            if ($cbid) {
                $engine->writeAttribute('cbid', $cbid);
            }
        }
        foreach ($data as $elm => $val) {
            if (in_array($elm, $this->offerAttributes)) {
                $engine->writeAttribute($elm, $val);
            }
        }
        foreach ($data as $elm => $val) {
            if (in_array($elm, $this->offerElements)) {
                if (!is_array($val)) {
                    $val = array($val);
                }
                foreach ($val as $value) {
                    $engine->writeElement($elm, $value);
                }
            }
        }
        foreach ($params as $param) {
            $engine->startElement('param');
            $engine->writeAttribute('name', $param[0]);
            if ($param[1]) {
                $engine->writeAttribute('unit', $param[1]);
            }
            $engine->text($param[2]);
            $engine->endElement();
        }
        $engine->fullEndElement();
    }
    protected function writeCategories()
    {
        $engine = $this->getEngine();
        $engine->startElement('categories');
        $this->categories();
        $engine->fullEndElement();
    }
    protected function categories($parent_id = false)
    {
        $q = Category::find();
        $q->andWhere(['isVisible' => 1]);
        $q->orderBy(['sort' => SORT_ASC]);
        if ($parent_id) {
            $q->andWhere(['parent_id' => $parent_id]);
        } else {
            $q->andWhere('parent_id is NULL');
        }
        /** @var Category[] $models */
        $models = $q->all();
        $engine = $this->getEngine();
        foreach ($models as $model) {
            if (!in_array($model->id, $this->no_cats)) {
                $engine->startElement('category');
                $engine->writeAttribute('id', $model->id);
                if ($model->parent_id) {
                    $engine->writeAttribute('parentId', $model->parent_id);
                }
                $engine->text($model->name);
                $engine->fullEndElement();
                if ($model->type == 'cats') {
                    $this->categories($model->id);
                }
            } else {
                if ($model->type == 'cats') {
                    $this->_exclude_cats = SArrayHelper::merge($this->_exclude_cats, $model->getAllSubItemCats());
                } else {
                    $this->_exclude_cats[] = $model->id;
                }
            }
        }
    }
    protected function addCurrency($id, $rate = 1)
    {
        $engine = $this->getEngine();
        $engine->startElement('currency');
        $engine->writeAttribute('id', $id);
        $engine->writeAttribute('rate', $rate);
        $engine->endElement();
    }
    protected function getEngine()
    {
        if (null === $this->_engine) {
            $this->_engine = new XMLWriter();
        }
        return $this->_engine;
    }
    protected function beforeWrite($full = false)
    {
        $this->_tmpFile = $this->_dir . DIRECTORY_SEPARATOR . md5($this->_file_name);
        $engine = $this->getEngine();
        $engine->openURI($this->_tmpFile);
        if ($this->indentString) {
            $engine->setIndentString($this->indentString);
            $engine->setIndent(true);
        }
        if ($full) {
            $engine->startDocument('1.0', $this->encoding);
            $engine->startElement('yml_catalog');
            $engine->writeAttribute('date', date('Y-m-d H:i'));
            $engine->startElement('shop');
        }
    }
    protected function afterWrite()
    {
        $engine = $this->getEngine();
        $engine->fullEndElement();
        $engine->fullEndElement();
        $engine->endDocument();
        rename($this->_tmpFile, $this->_dir . DIRECTORY_SEPARATOR . 'shop.yml');
    }
}