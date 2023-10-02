<?php

namespace shadow\plugins\seo;

use yii\web\UrlNormalizer;
use yii\web\UrlNormalizerRedirectException;

/**
 * Class SUrlNormalizer
 * @package shadow\plugins\seo
 * TODO требуеться проверка данных функций
 */
class SUrlNormalizer extends UrlNormalizer
{
    private $seo_check = false;
    /**
     * @inheritdoc
     */
    public function normalizePathInfo($pathInfo, $suffix, &$normalized = false)
    {
        $result = parent::normalizePathInfo($pathInfo, $suffix, $normalized);
        if ($normalized) {
            $this->seo_check = false;
        } else {
            $this->seo_check = $pathInfo;
            $normalized = true;
        }
        return $result;
    }
    /**
     * @inheritdoc
     */
    public function normalizeRoute($route)
    {
        if ($this->seo_check !== false) {
            $rule = new SUrlRule();
            $url = $rule->createUrl(\Yii::$app->urlManager, $route[0], $route[1] + \Yii::$app->request->getQueryParams());
            $path = parse_url($url, PHP_URL_PATH);
            if ($url && $path !== $this->seo_check) {
                throw new UrlNormalizerRedirectException($path, $this->action);
            } else {
                return $route;
            }
        } else {
            return parent::normalizeRoute($route);
        }
    }
//    private function
}