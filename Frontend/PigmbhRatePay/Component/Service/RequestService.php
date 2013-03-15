<?php

/**
 * RequestService
 *
 * @category   PayIntelligent
 * @copyright  Copyright (c) 2013 PayIntelligent GmbH (http://payintelligent.de)
 */
class Shopware_Plugins_Frontend_PigmbhRatePay_Component_Service_RequestService
{

    private $_zendHttpClient;

    public function __construct($sandbox = true)
    {
        $uri = $sandbox ? 'https://webservices-int.eos-payment.com/custom/ratepay/xml/1_0' : '';
        $this->_zendHttpClient = new Zend_Http_Client($uri);
    }

    public function init(Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentInit $initModel)
    {
        $xml = Shopware_Plugins_Frontend_PigmbhRatePay_Component_Service_Util::convertToXml($initModel, 'payment');
        $this->_zendHttpClient->setRawData($xml->asXML());
        $result = $this->_zendHttpClient->request('POST');
        return new SimpleXMLElement($result->getBody());
    }

    public function getLastResponse()
    {
        return $this->_zendHttpClient->getLastResponse();
    }

    public function getLastRequest()
    {
        return $this->_zendHttpClient->getLastRequest();
    }

}
