<?php
/**
 * Created by PhpStorm.
 * Project: aitas
 * User: lxShaDoWxl
 * Date: 28.01.16
 * Time: 15:29
 */
namespace shadow;

use yii\i18n\I18N;

class SI18N extends I18N {
    public function translate($category, $message, $params, $language){
        if($language=='en' && $category=='yii'){
            $language = 'en-US';
        }
        return parent::translate($category, $message, $params, $language);
    }
}