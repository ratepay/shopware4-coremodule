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
    private $_logging;

    public function init()
    {
        $this->_config = Shopware()->Plugins()->Frontend()->PigmbhRatePay()->Config();
        $this->_user = Shopware()->Models()->find('Shopware\Models\Customer\Billing', Shopware()->Session()->sUserId);
        $this->_request = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Service_RequestService($this->_config->get('RatePaySandbox'));
        $this->_modelFactory = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Mapper_ModelFactory();
        $this->_logging = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Logging();
    }

    /**
     *
     */
    public function indexAction()
    {
        unset(Shopware()->Session()->RatePAY['errorMessage']);
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
                'account' => $requestParameter['ratepay_debit_accountnumber'] ? : $debitUser->getAccount(),
                'bankcode' => $requestParameter['ratepay_debit_bankcode'] ? : $debitUser->getBankCode(),
                'bankname' => $requestParameter['ratepay_debit_bankname'] ? : $debitUser->getBankName(),
                'bankholder' => $requestParameter['ratepay_debit_accountholder'] ? : $debitUser->getAccountHolder()
            );
            try {
                Shopware()->Db()->update('s_user_debit', $updateData, "`userID`=" . $requestParameter['userid']);
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
                $this->saveOrder(Shopware()->Session()->RatePAY['transactionId'], $this->createPaymentUniqueId(), 17);
                $paymentConfirmModel = $this->_modelFactory->getModel(new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentConfirm());
                $result = $this->_request->xmlRequest($paymentConfirmModel->toArray());
                if (Shopware_Plugins_Frontend_PigmbhRatePay_Component_Service_Util::validateResponse('PAYMENT_CONFIRM', $result)) {
                    $this->_logging->updatePaymentLoggings(Shopware()->Session()->RatePAY['transactionId'], array(
                        'lastname' => Shopware()->Session()->sUserData['billingaddress']['lastname'],
                        'firstname' => Shopware()->Session()->sUserData['billingaddress']['firstname']
                    ));
                    $this->redirect(Shopware()->Front()->Router()->assemble(array(
                                'controller' => 'checkout',
                                'action' => 'finish'
                            ))
                    );
                } else {
                    $this->_error('Order konnte nicht validiert werden.');
                }
            } else {
                $this->_error('Order wurde nicht erfolgreich &uuml;bermittelt.');
            }
        } else {
            $this->_error('Bezahlvorgang konnte nicht initialisiert werden.');
        }
    }

    private function _error($message = 'Ein Fehler ist aufgetreten.')
    {
        Shopware()->Session()->RatePAY['errorMessage'] = $message;
        $this->redirect(Shopware()->Front()->Router()->assemble(array(
                    'controller' => 'checkout',
                    'action' => 'confirm',
                    'showError' => true
                ))
        );
    }

    public function calcDesignAction()
    {
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
        $calcPath = realpath(dirname(__FILE__) . '/../../Views/frontend/Ratenrechner/php/');
        require_once $calcPath . '/PiRatepayRateCalc.php';
        require_once $calcPath . '/path.php';
        require_once $calcPath . '/PiRatepayRateCalcDesign.php';
    }

    public function calcRequestAction()
    {
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
        $calcPath = realpath(dirname(__FILE__) . '/../../Views/frontend/Ratenrechner/php/');
        require_once $calcPath . '/PiRatepayRateCalc.php';
        require_once $calcPath . '/path.php';
        require_once $calcPath . '/PiRatepayRateCalcRequest.php';
    }

}
