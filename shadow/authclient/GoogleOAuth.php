<?php

namespace shadow\authclient;

use yii\authclient\clients\Google;

class GoogleOAuth extends Google
{
    public $apiBaseUrl = 'https://www.googleapis.com/oauth2/v2';

    /**
     * @inheritdoc
     */
    protected function initUserAttributes()
    {
        return $this->api('userinfo', 'GET');
    }
}