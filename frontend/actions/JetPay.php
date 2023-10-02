<?php
/**
 * Токен
 * cQV-k4YZhwzm2PWPfEoEcqt6Ro5_Gq
 * Создано 23.01.2023
 * ------------------------------
 * Секретный ключ
 * 8DwywjvX
 *
 * --------------
 * Документация по PHP SDK
 * https://developers.jetpay.kz/ru/ru_sdk_php.html
 *
 */

namespace frontend\actions;

use common\components\Debugger as d;
use common\components\api\Api;
use jetpay\Payment;
use jetpay\Gate;

class JetPay
{

    public $post = [];

    public function run()
    {
        $this->post = d::post();
//        d::ajax($this->post);
        switch($this->post['type']){
            case 'btn_push':
                $this->test();
                break;
            default:
                d::ajax('Debug->run()->switch:default');
        }
    }

    /*
     * Кнопка "Нажать"
     */
    public function test()
    {
        // Секретный ключ проекта, полученный при интеграции
//        $gate = new Gate('8DwywjvX');
//        $gate = new Gate('12345');
//        // Идентификатор проекта и идентификатор платежа, уникальный в рамках проекта
//        $payment = new Payment('105361', '123456');
//        // Сумма (в дробных единицах валюты) и код валюты (в формате ISO-4217 alpha-3)
//        $payment->setPaymentAmount(1000)->setPaymentCurrency('RUS');
//        // Описание платежа
//        $payment->setPaymentDescription('Test payment');
//        // Дата и время, до которых платёж должен быть завершён
//        $payment->setBestBefore(new \DateTime('2050-01-01 00:00:00 +0000'));
//        // Код языка, на котором Payment Page открывается пользователю
//        $payment->setLanguageCode('en');
//        // Готовый запрос с подписью
//        $url = $gate->getPurchasePaymentPageUrl($payment);
//        d::ajax($url);








        $payment = new Payment('105361', '5556667778889');
        $payment->setPaymentAmount(1000)->setPaymentCurrency('KZT');
        // Необязательный параметр
        $payment->setPaymentDescription('Тестовый платёж');

        // Секретный ключ проекта, полученный при интеграции от Jetpay
        $gate = new Gate('8DwywjvX');

        $url = $gate->getPurchasePaymentPageUrl($payment);

        d::ajax($url);

        d::ajax('JetPay->test()');
    }

}//Class