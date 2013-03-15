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
        $result = $this->_request->init($paymentInitModel->toArray());
        $status = $result->{'head'}->{'processing'}->{'status'}->attributes();
        if ($status['code'] === 'OK') {
            $transactionId = $result->{'head'}->{'transaction-id'};
        }



        
    }

    private function debit()
    {

    }

    private function rate()
    {

    }

}
