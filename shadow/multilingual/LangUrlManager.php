<?php
namespace shadow\multilingual;

use yii\web\UrlManager;

class LangUrlManager extends UrlManager
{
    public function createUrl($params)
    {
        $lang_prefix = '';
        if (\Yii::$app->language != \Yii::$app->params['defaultLanguage']) {
            $lang_prefix .= '/' . \Yii::$app->language;
        }
        //Получаем сформированный URL(без префикса идентификатора языка)
        $url = parent::createUrl($params);
        //Добавляем к URL префикс - буквенный идентификатор языка
        if ($url == '/') {
            if ($lang_prefix) {
                return $lang_prefix;
            } else {
                return '/';
            }
        } else {
            return $lang_prefix . $url;
        }
    }
}