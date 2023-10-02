<?php
/**
 * @link https://github.com/himiklab/yii2-recaptcha-widget
 * @copyright Copyright (c) 2014 HimikLab
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace shadow\widgets\ReCaptcha;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\widgets\InputWidget;

/**
 *
 * TODO необходимо разделить JS функцию для проверки и обновления чтобы была одна функция на все капчи, а не для каждой новая функция
 *
 * Yii2 Google reCAPTCHA widget.
 *
 * For example:
 *
 * ```php
 * <?= $form->field($model, 'reCaptcha')->widget(
 *  ReCaptcha::className(),
 *  ['siteKey' => 'your siteKey']
 * ) ?>
 * ```
 *
 * or
 *
 * ```php
 * <?= ReCaptcha::widget([
 *  'name' => 'reCaptcha',
 *  'siteKey' => 'your siteKey',
 *  'widgetOptions' => ['class' => 'col-sm-offset-3']
 * ]) ?>
 * ```
 *
 * @see https://developers.google.com/recaptcha
 * @author HimikLab
 * @package himiklab\yii2\recaptcha
 */
class ReCaptcha extends InputWidget
{
    const JS_API_URL = 'https://www.google.com/recaptcha/api.js?render=explicit';

    const THEME_LIGHT = 'light';
    const THEME_DARK = 'dark';

    const TYPE_IMAGE = 'image';
    const TYPE_AUDIO = 'audio';

    /** @var string Your sitekey. */
    public $siteKey;

    /** @var string Your secret. */
    public $secret;

    /** @var string The color theme of the widget. [[THEME_LIGHT]] (default) or [[THEME_DARK]] */
    public $theme;

    /** @var string The type of CAPTCHA to serve. [[TYPE_IMAGE]] (default) or [[TYPE_AUDIO]] */
    public $type;

    /** @var string Your JS callback function that's executed when the user submits a successful CAPTCHA response. */
    public $jsCallback;
    /** @var string Your JS callback function that's executed when the user submits a successful CAPTCHA response. */
    public $jsExpCallback;
    /** @var array Additional html widget options, such as `class`. */
    public $widgetOptions = [];

    public function init()
    {
        parent::init();

        if (empty($this->siteKey)) {
            if (!empty(Yii::$app->reCaptcha->siteKey)) {
                $this->siteKey = Yii::$app->reCaptcha->siteKey;
            } else {
                throw new InvalidConfigException('Required `siteKey` param isn\'t set.');
            }
        }

        $view = $this->view;
        $view->registerJs(<<<JS
var recaptchaInstances={};
JS
,$view::POS_HEAD);
//        $view->registerJs(<<<JS
//var onloadCallback = function() {
//    // Renders the HTML element with id 'example1' as a reCAPTCHA widget.
//    // The id of the reCAPTCHA widget is assigned to 'widgetId1'.
//    widgetId1 = grecaptcha.render('example1', {
//        'sitekey' : 'your_site_key',
//        'theme' : 'light'
//    });
//    widgetId2 = grecaptcha.render(document.getElementById('example2'), {
//        'sitekey' : 'your_site_key'
//    });
//    grecaptcha.render('example3', {
//        'sitekey' : 'your_site_key',
//        'callback' : verifyCallback,
//        'theme' : 'dark'
//    });
//};
//JS
//)
        $view->registerJsFile(
            self::JS_API_URL,
            ['position' => $view::POS_HEAD]
        );
    }

    public function run()
    {
        $this->customFieldPrepare();
        $js_options = [
            'sitekey' => $this->siteKey,
            'hl'=>$this->getLanguageSuffix()
        ];
        $divOptions = [
            'class' => 'g-recaptcha',
            'data-sitekey' => $this->siteKey,
            'id'=>'recaptcha_'.$this->id
        ];
        if (!empty($this->jsExpCallback)) {
            $divOptions['data-expired-callback'] = $this->jsExpCallback;
            $js_options['expired-callback'] = Json::encode($this->jsExpCallback);
            $js_options['expired-callback'] = new JsExpression($this->jsExpCallback);
        }
        if (!empty($this->jsCallback)) {
            $divOptions['data-callback'] = $this->jsCallback;
            $js_options['callback'] = new JsExpression($this->jsCallback);
        }
        if (!empty($this->theme)) {
            $divOptions['data-theme'] = $this->theme;
            $js_options['theme'] = $this->theme;
        }
        if (!empty($this->type)) {
            $divOptions['data-type'] = $this->type;
            $js_options['type'] = $this->type;
        }
        $divOptions['data-size'] = 'compact';
        $js_options['size'] = 'compact';

        if (isset($this->widgetOptions['class'])) {
            $divOptions['class'] = "{$divOptions['class']} {$this->widgetOptions['class']}";
        }
        $divOptions['data-id'] = new JsExpression("recaptcha_{$this->id}");
        $js_options = Json::encode($js_options);
        $js=<<<JS
recaptchaInstances.recaptcha_{$this->id}= grecaptcha.render('recaptcha_{$this->id}', {$js_options});
JS;
        $view = $this->view;

        $view->registerJs($js,$view::POS_LOAD);
        $divOptions = $divOptions + $this->widgetOptions;
        echo Html::tag('div', '', $divOptions);
    }

    protected function getLanguageSuffix()
    {
        $currentAppLanguage = Yii::$app->language;
        $langsExceptions = ['zh-CN', 'zh-TW', 'zh-TW'];

        if (strpos($currentAppLanguage, '-') === false) {
            return $currentAppLanguage;
        }

        if (in_array($currentAppLanguage, $langsExceptions)) {
            return $currentAppLanguage;
        } else {
            return substr($currentAppLanguage, 0, strpos($currentAppLanguage, '-'));
        }
    }

    protected function customFieldPrepare()
    {
        $view = $this->view;
        if ($this->hasModel()) {
            $inputName = Html::getInputName($this->model, $this->attribute);
            $inputId = Html::getInputId($this->model, $this->attribute);
        } else {
            $inputName = $this->name;
            $inputId = 'recaptcha-' . $this->name;
        }

        if (empty($this->jsCallback)) {
            $jsCode = <<<JS
var Callback_{$this->id} = function(response){jQuery('#{$inputId}').val(response);};
var expCallback_{$this->id} = function(){
grecaptcha.reset(recaptchaInstances.recaptcha_{$this->id});
};
JS
;
        } else {
            $jsCode = <<<JS
var Callback_{$this->id} = function(response){jQuery('#{$inputId}').val(response);};
var expCallback_{$this->id} = function(){grecaptcha.reset(recaptchaInstances.recaptcha_{$this->id});};
JS
            ;
//            $jsCode = "var recaptchaCallback = function(response){jQuery('#{$inputId}').val(response); {$this->jsCallback}(response);};";
        }
        $this->jsExpCallback = 'expCallback_' . $this->id;
        $this->jsCallback = 'Callback_'.$this->id;

        $view->registerJs($jsCode, $view::POS_HEAD);
        echo Html::input('hidden', $inputName, null, ['id' => $inputId]);
    }
}
