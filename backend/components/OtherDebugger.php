<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 15.06.2022
 * Time: 20:14
 */

namespace backend\components;

use common\components\Debugger as d;
use common\models\User;
use Yii;

class OtherDebugger
{

    /**
     * @param array $data
     * Yii::$app->params - используется из common/config/params.php
     */
    public static function onesignal($data = [])
    {
//        d::ajax($data);
        $config = new Config();
        $params = Yii::$app->params['oneSignal'];
        $config->setApplicationId($params['app_id']);
        $config->setApplicationAuthKey($params['rest_api_key']);
        $config->setUserAuthKey($params['user_auth_key']);

        $guzzle = new GuzzleClient([ // http://docs.guzzlephp.org/en/stable/quickstart.html
            // ..config
        ]);

        $client = new HttpClient(new GuzzleAdapter($guzzle), new GuzzleMessageFactory());
        $api = new OneSignal($config, $client);

        // Основная настройка параметров
        $notification_params = [
            'headings' => [
                'en' => 'Notification header',
                'ru' => 'Заголовок'
            ],
            'contents' => [
                'en' => 'Notification message',
                'ru' => 'Текст уведомления'
            ],
//    'data' => ['foo' => 'bar'],
//    'isChrome' => true,
//    'send_after' => new \DateTime('1 hour'),
//    'filters' => [
////        'channel_for_external_user_ids' => 'push',
////        [
////            'field' => 'tag',
////            'key' => 'is_vip',
////            'relation' => '!=',
////            'value' => 'true',
////        ],
////        [
////            'operator' => 'OR',
////        ],
////        [
////            'field' => 'tag',
////            'key' => 'is_admin',
////            'relation' => '=',
////            'value' => 'true',
////        ],
//    ],
            // ..other options
        ];

        /**
         * Далее идёт настройка $notification_params
         * по параметрам $data
         */

        // Заголовок уведомления
        if(isset($data['header']) AND $data['header']){
            $notification_params['headings']['ru'] = $data['header'];
        }
        // Текст уведомления
        if(isset($data['message']) AND $data['message']){
            $notification_params['contents']['ru'] = $data['message'];
        }

        /*
         * IDs пользователей, кому отправить.
         * Если нет ни одного ID, то пуши отправляются
         * всем пользователям, зарегистрированным в приложении,
         * (т.е. всем, чьи ID есть в системе OneSignal)
         */
        if(isset($data['user_ids']) AND count($data['user_ids'])){
            $user_ids = [];
            foreach($data['user_ids'] as $user_id){
                $user_ids[] = (string)$user_id;
            }
            $notification_params['include_external_user_ids'] = $user_ids;
        }else{
            $notification_params['included_segments'] = ['All'];
        }

        // Шаблон для уведомлений. (Настраивается в ЛК OneSignal)
        if(isset($data['template_id']) AND $data['template_id']){
            $notification_params['template_id'] = $data['template_id'];
        }

        // Отправка произвольных данных
        if(
            isset($data['data']) AND is_array($data['data']) AND count($data['data']) >= 2
            AND array_key_exists('type', $data['data']) AND array_key_exists('id', $data['data'])
        ){
            $notification_params['data'] = $data['data'];
        }

        d::ajax([
            'request' => 'OtherDebugger->onesignal',
            'data' => $notification_params
        ]);

        $res = $api->notifications->add($notification_params);
        d::ajax($res);

        try{
            $api->notifications->add($notification_params);
            d::ajax('Уведомление отправлено');
        }catch(\Exception $e){
            d::ajax('Ошибка отправки уведомления');
        }

    }

    public static function maxma()
    {
        $test_auth_key = Yii::$app->params['maxma']['api_key'];
        $client = new Client();
        $client->setTestAddress();
        $res = 'Ничего не произошло';

        $apiClient = ($client)
            ->setProcessingKey($test_auth_key);

        try {
//    $res = $apiClient->newClient(
//        (new NewClientRequest())
//            ->setClient(
//                (new ClientInfoQuery())
//                    ->setExternalId('193000')
//                    ->setPhoneNumber('+79234720890')
//                    ->setEmail('akvarius_90@mail.ru')
//                    ->setName('Сергей')
//                    ->setSurname('Бражников')
//                    ->setGender(1)
//            )
//        );

//            $res = $apiClient->getBalance(
//                (new ClientQuery())
//                    ->setExternalId('192999')
//            );

            $products = [
                (new CalculationQueryRow())
                    ->setProduct(
                        (new CalculationQueryRowProduct())
                            ->setSku('PO045')
                            ->setBlackPrice(156.65)
                    )
                    ->setQty(3),
                (new CalculationQueryRow())
                    ->setProduct(
                        (new CalculationQueryRowProduct())
                            ->setSku('PO057')
                            ->setBlackPrice(3512.41)
                    )
                    ->setQty(5),
                (new CalculationQueryRow())
                    ->setProduct(
                        (new CalculationQueryRowProduct())
                            ->setSku('GD045')
                            ->setBlackPrice(456.79)
                    )
                    ->setQty(1),
            ];

            // =================================
//            $res = $apiClient->setOrder(
//                (new V2SetOrderRequest())
//                    ->setOrderId('12000')
//                    ->setCalculationQuery(
//                        (new CalculationQuery())
//                            ->setClient((new ClientQuery())->setExternalId('192999'))
//                            ->setShop((new ShopQuery())->setCode('kingfisher.kz')->setName('Kingfisher'))
//                            ->setRows($products)
//                    )
//            );
            // =================================

            $res = $apiClient->calculatePurchase(
                (new V2CalculatePurchaseRequest())
                    ->setCalculationQuery(
                        (new CalculationQuery())
                            ->setShop((new ShopQuery())->setCode('kingfisher.kz')->setName('Kingfisher'))
                            ->setRows($products)
                    )
            );

        } catch (TransportException $e) {
            // Ошибка обмена с сервером
            $res = 'Ошибка обмена с сервером';
        } catch (ProcessingException $e) {
//     Ошибка обработки запроса сервером
            $res = [
                // код
                'code' => $e->getCode(),
                // описание ошибки
                'desc' => $e->getDescription(),
                // детали ошибки
                'detail' => $e->getHint()
            ];
        }

        d::ajax($res);
    }

    public static function test()
    {

        $path = $_SERVER['DOCUMENT_ROOT'];
        $path .= '/frontend/web/web';
//        d::ajax($path);
        $directory = new \RecursiveDirectoryIterator($path);
        $iterator = new \RecursiveIteratorIterator($directory);
        $files = array();
        $bad = 0;
        $ok = 0;
        $files = [];
        foreach ($iterator as $info) {
            if(preg_match('/htaccess/', $info->getPathname())) {
                unlink($info->getPathname());
//                $files[] = $info->getPathname();
            }
        }
//        array_unshift($files, count($files));
//        d::ajax($files);
        d::ajax('Файлы удалены');


    }

}//Class