<?php
namespace shadow;

use common\components\Debugger as d;
use backend\models\Settings;
use yii\base\Component;

class SSettings extends Component
{
    const CACHE_TAG_KEY = 'SSettings_db';
    /**
     * @var integer the time in seconds that the settings can remain valid in cache.
     * Use 0 to indicate that the cached data will never expire.
     */
    public $cachingDuration = 86400;

    private $_settings = [];
    /**
     * Initializes the object.
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     */
    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
    }
    public function get($key, $default = '')
    {
        if (!$this->_settings) {
            $this->_settings = $this->loadSettings();
        }
        if (isset($this->_settings[$key])) {
            return $this->_settings[$key];
        } else {
            return $default;
        }
    }
    public function loadSettings()
    {
        /**
         * @var $settings Settings[]
         */
        $key_cache = [
            __CLASS__,
            \Yii::$app->language,
        ];
        $result = \Yii::$app->cache->get($key_cache);
        if (!is_array($result)) {
            $settings = Settings::find()->indexBy('key')->all();
            $result = [];
            foreach ($settings as $key => $val) {
                $result[$key] = $val->value;
            }
            \Yii::$app->cache->set($key_cache, $result, $this->cachingDuration, new \yii\caching\TagDependency(['tags' => self::CACHE_TAG_KEY]));
        }
        return $result;
    }
}