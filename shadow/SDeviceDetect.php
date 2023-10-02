<?php
namespace shadow;

use Detection\MobileDetect;
use yii\base\Application;
use yii\base\Component;

/**
 * Class SDeviceDetect
 * @package shadow
 * @method (bool)isMobile()
 * @method (bool)isTablet()
 */
class SDeviceDetect extends Component
{
    /**
     * @var self
     */
    static protected $_instance = null;
    /**
     * @var \Mobile_Detect
     */
    protected $_mobileDetect;

    // Automatically set view parameters based on device type
    public $setParams = true;

    public function __construct($config = array())
    {
        parent::__construct($config);
    }
    public function init()
    {
        $this->_mobileDetect = new MobileDetect();
        parent::init();
        if ($this->setParams) {
            \Yii::$app->on(Application::EVENT_BEFORE_REQUEST, function ($event) {
                \Yii::$app->params['devicedetect'] = [
                    'isMobile' => (\Yii::$app->devicedetect->isMobile()&&!\Yii::$app->devicedetect->isTablet()),
                    'isTablet' => \Yii::$app->devicedetect->isTablet()
                ];
                \Yii::$app->params['devicedetect']['isDesktop'] =
                    !\Yii::$app->params['devicedetect']['isMobile'] &&
                    !\Yii::$app->params['devicedetect']['isTablet'];
            });
        }
    }

    /**
     * @return self
     */
    static public function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->_mobileDetect, $name], $arguments);
    }

    /**
     * @return bool
     */
    public function isDesktop()
    {
        if ($this->isMobile() || $this->isTablet()) {
            return false;
        }
        return true;
    }

}