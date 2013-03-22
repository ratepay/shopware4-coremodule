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
        $debitUser = Shopware()->Models()->find('Shopware\Models\Customer\Debit', $requestParameter['userid']);

        $return = 'OK';

        $updateData = array();
        if (!is_null($user)) {
            $updateData['phone'] = $requestParameter['ratepay_phone'] ? : $user->getPhone();
            $updateData['ustid'] = $requestParameter['ratepay_ustid'] ? : $user->getVatId();
            $updateData['company'] = $requestParameter['ratepay_company'] ? : $user->getCompany();
            $updateData['birthday'] = $requestParameter['ratepay_birthday'] ? : $user->getBirthday()->format("Y-m-d");
            try {
                Shopware()->Db()->update('s_user_billingaddress', $updateData, 'userID=' . $requestParameter['userid']);
                Shopware()->Log()->Info('Kundendaten aktualisiert.');
            } catch (Exception $exception) {
                Shopware()->Log()->Err('Fehler beim Updaten der Userdaten: ' . $exception->getMessage());
                $return = 'NOK';
            }
        }
        $updateData = array();
        if (!is_null($debitUser)) {
            $updateData = array(
                $requestParameter['ratepay_debit_accountnumber'] ? : $debitUser->getAccount(),
                $requestParameter['ratepay_debit_bankcode'] ? : $debitUser->getBankCode(),
                $requestParameter['ratepay_debit_bankname'] ? : $debitUser->getBankName(),
                $requestParameter['ratepay_debit_accountholder'] ? : $debitUser->getAccountHolder(),
                $requestParameter['userid']
            );
            try {
                $sql = "REPLACE INTO `s_user_debit` " .
                        "(`account`, `bankcode`, `bankname`, `bankholder`, `userID`)" .
                        "VALUES(?, ?, ?, ?, ?) ";
                Shopware()->Db()->query($sql, $updateData);
                Shopware()->Log()->Info('Bankdaten aktualisiert.');
            } catch (Exception $exception) {
                Shopware()->Log()->Err('Fehler beim Updaten der Bankdaten: ' . $exception->getMessage());
                Shopware()->Log()->Debug($updateData);
                $return = 'NOK';
            }
        }
        echo $return;
    }

    private function _proceedPayment()
    {
        $paymentInitModel = $this->_modelFactory->getModel(new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentInit());
        $result = $this->_request->xmlRequest($paymentInitModel->toArray());
        if (Shopware_Plugins_Frontend_PigmbhRatePay_Component_Service_Util::validateResponse('PAYMENT_INIT', $result)) {
            Shopware()->Session()->RatePAY['transactionId'] = $result->getElementsByTagName('transaction-id')->item(0)->nodeValue;
            $paymentRequestModel = $this->_modelFactory->getModel(new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentRequest());
            $result = $this->_request->xmlRequest($paymentRequestModel->toArray());
            if (Shopware_Plugins_Frontend_PigmbhRatePay_Component_Service_Util::validateResponse('PAYMENT_REQUEST', $result)) {
                //TODO: saveOrder & PAYMENT_CONFIRM
                echo 'finishOrder';
            } else {
                $this->_error(); //TODO Error nachtragen
            }
        }

        echo (string) $result->getElementsByTagName('status')->item(0)->nodeValue . "<br>";
        echo (string) $result->getElementsByTagName('result')->item(0)->nodeValue . "<br>";
        echo (string) $result->getElementsByTagName('reason')->item(0)->nodeValue . "<br>";
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

}
