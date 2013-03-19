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
    }

    /**
     *
     */
    public function indexAction()
    {
        $requestParams = $this->Request()->getParams();
        if (count(preg_grep("/^ratepay_.*$/", array_keys($requestParams))) !== 0) {
            Shopware()->Log()->Info('RatePAY: UpdateUserData');
            $this->updateUserData($requestParams);
        }

        switch ($this->getPaymentShortName()) {
            case 'pigmbhratepayinvoice':
                $this->invoice();
                break;
            case 'pigmbhratepayrate':
                $this->rate();
                break;
            case 'pigmbhratepaydebit':
                $this->debit();
                break;
            case 'pigmbhratepayprepayment':
                $this->prepayment();
                break;
            default:
                break;
        }
    }

    /**
     * Updates phone, ustid, company and the birthday for the current user.
     *
     * @param array $userData
     */
    private function updateUserData($userData)
    {
        Shopware()->Db()->update('s_user_billingaddress', array(
            'phone' => $userData['ratepay_phone'] ? : $this->_user->getPhone(),
            'ustid' => $userData['ratepay_ustid'] ? : $this->_user->getVatId(),
            'company' => $userData['ratepay_company'] ? : $this->_user->getCompany(),
            'birthday' => $userData['ratepay_birthday'] ? : $this->_user->getBirthday()->format("Y-m-d")
                ), 'userID=' . $this->_user->getCustomer()->getId()
        );
    }

    private function invoice()
    {
        $paymentInitModel = $this->_modelFactory->getModel(new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentInit());
        $result = $this->_request->xmlRequest($paymentInitModel->toArray());
        if ($this->validateResponse('PAYMENT_INIT', $result)) {
            Shopware()->Session()->RatePAY['transactionId'] = $result->getElementsByTagName('transaction-id')->item(0)->nodeValue;
            $paymentRequestModel = $this->_modelFactory->getModel(new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentRequest());
            $result = $this->_request->xmlRequest($paymentRequestModel->toArray());
            if($this->validateResponse('PAYMENT_REQUEST', $result)){
                echo "saveOrder";
            }else{
                echo "RedirectToConfirmPage";
            }
        }
        exit;
    }

    private function debit()
    {

    }

    private function rate()
    {

    }

    private function prepayment()
    {

    }

    /**
     * Validates the Response
     *
     * @param string $requestType
     * @return boolean|array
     */
    public function validateResponse($requestType = '', $response = null)
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
                    $this->error = '';
                    $return = true;
                }
                break;
            case 'CONFIRMATION_DELIVER':
                if ($statusCode == "OK" && $resultCode == "404") {
                    $this->error = '';
                    $return = true;
                }
                break;
            case 'PAYMENT_CHANGE':
                if ($statusCode == "OK" && $resultCode == "403") {
                    $this->error = '';
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
