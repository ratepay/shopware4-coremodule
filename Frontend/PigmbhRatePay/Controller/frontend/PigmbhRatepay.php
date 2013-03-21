<?php

/**
 * PigmbhRatepay
 *
 * @category   PayIntelligent
 * @copyright  Copyright (c) 2013 PayIntelligent GmbH (http://payintelligent.de)
 */
class Shopware_Controllers_Frontend_PigmbhRatepay extends Shopware_Controllers_Frontend_Payment
{

    /**
     * Stores an Instance of the Shopware\Models\Customer\Billing model
     *
     * @var Shopware\Models\Customer\Billing
     */
    private $_config;
    private $_user;
    private $_request;
    private $_modelFactory;

    public function init()
    {
        $this->_config = Shopware()->Plugins()->Frontend()->PigmbhRatePay()->Config();
        $this->_user = Shopware()->Models()->find('Shopware\Models\Customer\Billing', Shopware()->Session()->sUserId);
        $this->_request = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Service_RequestService($this->_config->get('RatePaySandbox'));
        $this->_modelFactory = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Mapper_Checkout();
        unset(Shopware()->Session()->RatePAY['errorMessage']);
    }

    /**
     *
     */
    public function indexAction()
    {
        if (preg_match("/^pigmbhratepay(invoice|rate|debit|prepayment)$/", $this->getPaymentShortName())) {
            $this->_proceedPayment();
        } else {
            $this->_error('Die Zahlart ' . $this->getPaymentShortName() . ' wird nicht unterst&uuml;tzt!');
        }
    }

    /**
     * Updates phone, ustid, company and the birthday for the current user.
     *
     *
     */
    public function saveUserDataAction()
    {
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
        $requestParameter = $this->Request()->getParams();
        $user = Shopware()->Models()->find('Shopware\Models\Customer\Billing', $requestParameter['userid']);
        $updateData = array();
        if(!is_null($user)){
            $updateData['phone'] = $requestParameter['ratepay_phone'] ? : $user->getPhone();
            $updateData['ustid'] = $requestParameter['ratepay_ustid'] ? : $user->getVatId();
            $updateData['company'] = $requestParameter['ratepay_company'] ? : $user->getCompany();
            $updateData['birthday'] = $requestParameter['ratepay_birthday'] ? : $user->getBirthday()->format("Y-m-d");
        }

        try{
            Shopware()->Db()->update('s_user_billingaddress', $updateData, 'userID=' . $requestParameter['userid']);
            echo "OK";
        }  catch (Exception $exception){
            Shopware()->Log()->Err('Fehler beim Updaten der UserDaten: ' . $exception->getMessage());
            echo "NOK";
        }
    }

    private function _proceedPayment()
    {
        $paymentInitModel = $this->_modelFactory->getModel(new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentInit());
        $result = $this->_request->xmlRequest($paymentInitModel->toArray());
        if ($this->_validateResponse('PAYMENT_INIT', $result)) {
            Shopware()->Session()->RatePAY['transactionId'] = $result->getElementsByTagName('transaction-id')->item(0)->nodeValue;
            $paymentRequestModel = $this->_modelFactory->getModel(new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentRequest());
            $result = $this->_request->xmlRequest($paymentRequestModel->toArray());
            if ($this->_validateResponse('PAYMENT_REQUEST', $result)) {
                //TODO: saveOrder & PAYMENT_CONFIRM
                echo 'finishOrder';
            } else {
                $this->_error(); //TODO Error nachtragen
            }
        }

        var_dump($result);
        echo (string) $result->getElementsByTagName('status')->item(0)->nodeValue."<br>";
        echo (string) $result->getElementsByTagName('result')->item(0)->nodeValue."<br>";
        echo (string) $result->getElementsByTagName('reason')->item(0)->nodeValue."<br>";
        exit;
    }

    private function _error($message = 'Ein Fehler ist aufgetreten.')
    {
        Shopware()->Session()->RatePAY['errorMessage'] = $message;
        $this->redirect(Shopware()->Front()->Router()->assemble(array(
                    'controller' => 'checkout',
                    'action' => 'confirm',
                    'showError' => true
                )));
    }

    /**
     * Validates the Response
     *
     * @param string $requestType
     * @return boolean
     */
    private function _validateResponse($requestType = '', $response = null)
    {
        $return = false;
        $statusCode = '';
        $resultCode = '';
        $reasonCode = '';
        if ($response != null) {
            $statusCode = (string) $response->getElementsByTagName('status')->item(0)->getAttribute('code');
            $resultCode = (string) $response->getElementsByTagName('result')->item(0)->getAttribute('code');
            $reasonCode = (string) $response->getElementsByTagName('reason')->item(0)->getAttribute('code');
        }
        switch ($requestType) {
            case 'PAYMENT_INIT':
                if ($statusCode == "OK" && $resultCode == "350") {
                    $return = true;
                }
                break;
            case 'PAYMENT_REQUEST':
                if ($statusCode == "OK" && $resultCode == "402") {
                    $return = true;
                }
                break;
            case 'PAYMENT_CONFIRM':
                if ($statusCode == "OK" && $resultCode == "400") {
                    $return = true;
                }
                break;
            case 'CONFIRMATION_DELIVER':
                if ($statusCode == "OK" && $resultCode == "404") {
                    $return = true;
                }
                break;
            case 'PAYMENT_CHANGE':
                if ($statusCode == "OK" && $resultCode == "403") {
                    $return = true;
                }
                break;
            case 'CONFIGURATION_REQUEST':
                if ($statusCode == "OK" && $resultCode == "500") {
                    $return = true;
                }
                break;
            case 'CALCULATION_REQUEST':
                $successCodes = array('603', '671', '688', '689', '695', '696', '697', '698', '699');
                if ($statusCode == "OK" && in_array($reasonCode, $successCodes) && $resultCode == "502") {
                    $return = true;
                }
                break;
        }
        return $return;
    }

}
