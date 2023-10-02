<?php
namespace shadow;

use Yii;
use yii\base\Object;

class SParser extends Object
{
    public $pathToCookieFile='';
    protected $ch;
    public function initCurl($url,$isDom){
        if(!$this->ch){
            $this->ch = curl_init();
            curl_setopt($this->ch, CURLOPT_HEADER, 0);
//		curl_setopt($this->ch, CURLOPT_REFERER, $urldata['path']);
//		curl_setopt($this->ch, CURLOPT_URL, $urldata['host']);
            curl_setopt($this->ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 7.0" .
                "; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR" .
                " 3.0.04506.30)");
            curl_setopt($this->ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
            if ($this->pathToCookieFile) {
                curl_setopt($this->ch, CURLOPT_COOKIEFILE, $this->pathToCookieFile);
                curl_setopt($this->ch, CURLOPT_COOKIEJAR, $this->pathToCookieFile);
            }
        }

        curl_setopt($this->ch, CURLOPT_URL, $url);
        $result = curl_exec($this->ch);
        $http_code = curl_getinfo($this->ch,CURLINFO_HTTP_CODE);
        if($http_code!=200){
            $result = false;
        }
        curl_close($this->ch);
        $this->ch = null;
        if($isDom){
            if ($result) {
                $dom = new \DOMDocument();
                libxml_use_internal_errors(true);
                $dom->loadHTML($result);
                libxml_use_internal_errors(false);
                $result = $dom;
            } else {
                sleep(20);
                $result = $this->initCurl($url, $isDom);
            }
        }
        return $result;
    }
    public function createDomDocument()
    {
        $this->_dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        if ($this->_dom->loadHTML($this->_data)) {
            Yii::info(Yii::t('app', 'Create DomDocument'));
        } else {
            Yii::info(Yii::t('app', 'An error occurred when creating an object of class DOMDocument'));
        }
        libxml_use_internal_errors(false);

        return $this;
    }
    public function innerHTML(\DOMNode $element)
    {
        $innerHTML = '';
        $children  = $element->childNodes;

        foreach ($children as $child)
        {
            $innerHTML .= $element->ownerDocument->saveHTML($child);
        }

        return $innerHTML;
    }
}