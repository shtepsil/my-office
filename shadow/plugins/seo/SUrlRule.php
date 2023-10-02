<?php
namespace shadow\plugins\seo;

use backend\models\Module;
use backend\modules\seo\models\SSeoRedirects;
use backend\modules\seo\models\SSeoUrls;
use Yii;
use yii\base\Object;
use yii\caching\Cache;
use yii\caching\TagDependency;
use yii\db\ActiveQuery;
use yii\web\Request;
use yii\web\UrlManager;
use yii\web\UrlRuleInterface;

class SUrlRule extends Object implements UrlRuleInterface
{
    /**
     * Prefix which would be used when generating cache key.
     */
    const CACHE_KEY_TAG = 'SUrlRule';
    /**
     * @inheritdoc
     */
    public $suffix;
    /**
     * @var string resource_id from table
     */
    public $keyGet = 'id';
    /**
     * @var Cache|string the cache object or the application component ID of the cache object.
     * Compiled URL rules will be cached through this cache object, if it is available.
     *
     * After the UrlManager object is created, if you want to change this property,
     * you should only assign it with a cache object.
     * Set this property to `false` if you do not want to cache the URL rules.
     */
    public $cache = 'cache';

    protected $cacheKey = __CLASS__;

    private $_regexCache;
    private $_createUrlCache=[];

    public function init()
    {
        parent::init();
        if (is_string($this->cache)) {
            $this->cache = Yii::$app->get($this->cache, false);
        }
    }
    /**
     * @inheritdoc
     */
    public function parseRequest($manager, $request)
    {
        $pathInfo = $request->getPathInfo();
        if ($pathInfo === '') {
            return false;
        }
        /** @var SSeoRedirects $redirect */
        $redirect = SSeoRedirects::find()
            ->andWhere(['old_url' => $pathInfo])
            ->one();
        if ($redirect) {
            $url = '/' . $redirect->new_url;
//            if (strpos($url, 'http') === false) {
//                $url = [$url];
//            }
            Yii::$app->response->redirect($url, $redirect->type);
            Yii::$app->end();
        }
        $suffix = (string)($this->suffix === null ? $manager->suffix : $this->suffix);
        if ($suffix !== '' && $pathInfo !== '') {
            $n = strlen($suffix);
            if (substr_compare($pathInfo, $suffix, -$n, $n) === 0) {
                $pathInfo = substr($pathInfo, 0, -$n);
                if ($pathInfo === '') {
                    // suffix alone is not allowed
                    return false;
                }
            } else {
                return false;
            }
        }
        /** @var SSeoUrls $item */
        $item = SSeoUrls::find()
            ->andWhere(['path' => $pathInfo])
            ->one();
        $return = false;
        $params = [];
        if ($item) {
            $route = $item->controller . '/' . $item->action;
            $params[$this->keyGet] = $item->resource_id;
            $return = [$route, $params];
        } else {
            /** @var Module $item */
            $item = Module::find()->andWhere(['path' => $pathInfo])->one();
            if ($item) {
                $return = [$item->action,$params];
            }
        }
        if ($return) {
            return $return;
        }
        //TODO Надо проверить работу проверки через регулярные выражения
        if ($redirect_regex = $this->checkRegex($pathInfo)) {
            $url = '/' . $redirect_regex['new_url'];
            Yii::$app->response->redirect($url, $redirect_regex['type']);
            Yii::$app->end();
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function createUrl($manager, $route, $params)
    {
        $route = trim($route, '/');
        $url = false;
        if (isset($params[$this->keyGet])) {
            if (strpos($route, '/') !== false) {
                list($controller, $action) = explode('/', $route);
            } else {
                $controller = Yii::$app->controller->id;
                $action = $route;
            }
            $resource_id = $params[$this->keyGet];
            unset($params[$this->keyGet]);
            if($this->_createUrlCache===[]){
                $this->_createUrlCache = $this->cache->get($this->cacheKey . '_createUrl');
                if($this->_createUrlCache===false){
                    $this->_createUrlCache = [];
                }
            }
            $key_cache = $this->cacheKey . '_' . $resource_id . '_' . $controller . '_' . $action;
            if(isset($this->_createUrlCache[$key_cache])){
                $url = $this->_createUrlCache[$key_cache];
                if($url===''){
                    $url = false;
                }
            }else{
                $q = new ActiveQuery(SSeoUrls::className());
                $q->andWhere([
                    'resource_id' => $resource_id,
                    'controller' => $controller,
                    'action' => $action
                ]);
                /** @var SSeoUrls $item */
                $item = $q->one();
                if ($item) {
                    $url = $item->path;
                    $this->_createUrlCache[$key_cache] = $url;
                }else{
                    $this->_createUrlCache[$key_cache] = '';
                }
                $this->cache->set($this->cacheKey . '_createUrl', $this->_createUrlCache, 86400, new TagDependency(['tags' => self::CACHE_KEY_TAG]));

            }


        } else {
            /** @var Module[] $modules */
            $modules = Module::getDb()->cache(
                function ($db) {
                    return Module::find()->indexBy('action')->all($db);
                },
                86400,
                new TagDependency(['tags' => 'db_caching_module'])
            );
            if (isset($modules[$route])) {
                $url = $modules[$route]->path;
            }
        }
        if (strpos($url, '//') !== false) {
            $url = preg_replace('#/+#', '/', $url);
        }
        if ($url !== false && $url !== '') {
            $url .= ($this->suffix === null ? $manager->suffix : $this->suffix);
        }
        if ($url !== false && !empty($params) && ($query = http_build_query($params)) !== '') {
            $url .= '?' . $query;
        }
        return $url;
    }
    protected function checkRegex($url)
    {
        if ($this->cache instanceof Cache) {
            $cacheKey = $this->cacheKey;
            if (($data = $this->cache->get($cacheKey)) !== false) {
                $this->_regexCache = $data;
            } else {
                $this->_regexCache = $this->buildRegex();
                $this->cache->set($cacheKey, $this->_regexCache, 86400, new TagDependency(['tags' => self::CACHE_KEY_TAG]));
            }
        } else {
            $this->_regexCache = $this->buildRegex();
        }
        $result = false;
        if (isset($this->_regexCache['placeholders']) && isset($this->_regexCache['urls'])) {
            foreach ($this->_regexCache['urls'] as $redirect) {
                if (preg_match($redirect['old'], $url, $matches)) {
                    $matches = $this->substitutePlaceholderNames($matches, $this->_regexCache['placeholders']);
                    $tr = [];
                    foreach ($matches as $name => $value) {
                        if (isset($redirect['params'][$name])) {
                            $tr[$redirect['params'][$name]] = $value;
                        }
                    }
                    $result = [
                        'new_url' => strtr($redirect['new'], $tr),
                        'type' => $redirect['type'],
                    ];
                    break;
                }
            }
        }
        return $result;
    }
    protected function buildRegex()
    {
        /** @var SSeoRedirects[] $regex_redirects */
        $regex_redirects = SSeoRedirects::find()->andWhere(['isRegex' => 1])->all();
        $placeholders = [];
        $urls = [];
        foreach ($regex_redirects as $regex_redirect) {
            $pattern_url = $regex_redirect->old_url;
            $tr = [
                '.' => '\\.',
                '*' => '\\*',
                '$' => '\\$',
                '[' => '\\[',
                ']' => '\\]',
                '(' => '\\(',
                ')' => '\\)',
            ];
            $tr2 = [];
            $params = [];
            if (preg_match_all('/<([\w._-]+):?([^>]+)?>/', $pattern_url, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $name = $match[1][0];
                    $pattern = isset($match[2][0]) ? $match[2][0] : '[^\/]+';
                    $placeholder = 'a' . hash('crc32b', $name); // placeholder must begin with a letter
                    $placeholders[$placeholder] = $name;
                    $tr["<$name>"] = "(?P<$placeholder>$pattern)";
                    $tr2["<$name>"] = "(?P<$placeholder>$pattern)";
                    $params[$name] = $name;
                }
            }
            $template = preg_replace('/<([\w._-]+):?([^>]+)?>/', '<$1>', $pattern_url);
            $pattern_url = '#^' . trim(strtr($template, $tr), '/') . '$#u';
            $urls[] = [
                'old' => $pattern_url,
                'new' => $regex_redirect->new_url,
                'type' => $regex_redirect->type,
                'params' => $params
            ];
        }
        return [
            'placeholders' => $placeholders,
            'urls' => $urls
        ];
    }

    protected function substitutePlaceholderNames(array $matches, array $placeholders)
    {
        foreach ($placeholders as $placeholder => $name) {
            if (isset($matches[$placeholder])) {
                $matches[$name] = $matches[$placeholder];
                unset($matches[$placeholder]);
            }
        }
        return $matches;
    }
}