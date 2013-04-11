<?php

/**
 * Bootstrap
 *
 * @category   PayIntelligent
 * @package    PigmbhRatePAY
 * @copyright  Copyright (c) 2013 PayIntelligent GmbH (http://payintelligent.de)
 */
class Shopware_Plugins_Frontend_PigmbhRatePay_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{

    /**
     * Get Info for the Pluginmanager
     *
     * @return array
     */
    public function getInfo()
    {
        return array(
            'version' => $this->getVersion(),
            'autor' => 'PayIntelligent GmbH',
            'source' => $this->getSource(),
            'support' => 'http://www.payintelligent.de',
            'link' => 'http://www.payintelligent.de',
            'copyright' => 'Copyright (c) 2013, PayIntelligent GmbH',
            'label' => 'RatePAY',
            'description' => ''
        );
    }

    /**
     * Returns the Pluginversion
     *
     * @return string
     */
    public function getVersion()
    {
        return "3.0.0";
    }

    /**
     * Installs the Plugin and its components
     *
     * @return boolean
     */
    public function install()
    {
        $this->_createPaymentmeans();
        $this->_createForm();
        $this->_createPluginConfigTranslation();
        $this->_subscribeEvents();
        $this->_createMenu();
        $this->_createDataBaseTables();
        $this->Plugin()->setActive(true);
        return true;
    }

    /**
     * Uninstalls the Plugin and its components
     *
     * @return boolean
     */
    public function uninstall()
    {
        return parent::uninstall();
    }

    /**
     * Creates the Paymentmeans
     */
    private function _createPaymentmeans()
    {
        try {
            $this->createPayment(
                    array(
                        'name' => 'pigmbhratepayinvoice',
                        'description' => 'RatePAY Rechnung',
                        'action' => 'pigmbh_ratepay',
                        'active' => 1,
                        'position' => 1,
                        'additionaldescription' => '',
                        'template' => 'RatePAYInvoice.tpl'
                    )
            );
            $this->createPayment(
                    array(
                        'name' => 'pigmbhratepayrate',
                        'description' => 'RatePAY Ratenzahlung',
                        'action' => 'pigmbh_ratepay',
                        'active' => 1,
                        'position' => 2,
                        'additionaldescription' => '',
                        'template' => 'RatePAYRate.tpl'
                    )
            );
            $this->createPayment(
                    array(
                        'name' => 'pigmbhratepaydebit',
                        'description' => 'RatePAY Lastschrift',
                        'action' => 'pigmbh_ratepay',
                        'active' => 1,
                        'position' => 3,
                        'additionaldescription' => '',
                        'template' => 'RatePAYDebit.tpl'
                    )
            );
        } catch (Exception $exception) {
            $this->uninstall();
            throw new Exception("Can not create payment." . $exception->getMessage());
        }
    }

    /**
     * Creates the Pluginconfiguration
     */
    private function _createForm()
    {
        try {
            $form = $this->Form();
            $form->setElement('text', 'RatePayProfileID', array(
                'label' => 'Profile-ID',
                'value' => ''
            ));
            $form->setElement('text', 'RatePaySecurityCode', array(
                'label' => 'Security Code',
                'value' => ''
            ));
            $form->setElement('boolean', 'RatePaySandbox', array(
                'label' => 'Sandbox',
                'value' => true
            ));
            $form->setElement('boolean', 'RatePayLogging', array(
                'label' => 'Logging',
                'value' => true
            ));
            $form->setElement('boolean', 'RatePayBankData', array(
                'label' => 'Bankdatenspeicherung aktivieren',
                'value' => true
            ));
            $form->setElement('boolean', 'RatePayDifferentShippingaddress', array(
                'label' => 'Abweichende Addressen zulassen',
                'value' => false
            ));
        } catch (Exception $exception) {
            $this->uninstall();
            throw new Exception("Can not create configelements." . $exception->getMessage());
        }
    }

    /**
     * Creates the Translation for the Pluginconfiguration
     */
    private function _createPluginConfigTranslation()
    {
        try {
            $form = $this->Form();
            $translations = array(
                'de_DE' => array(
                    'RatePayProfileID' => 'Profile-ID',
                    'RatePaySecurityCode' => 'Security Code',
                    'RatePaySandbox' => 'Sandboxmodus',
                    'RatePayLogging' => 'Logging aktivieren',
                    'RatePayBankData' => 'Bankdatenspeicherung aktivieren',
                    'RatePayDifferentShippingaddress' => 'Abweichende Addressen zulassen'
                )
            );

            $shopRepository = Shopware()->Models()->getRepository('\Shopware\Models\Shop\Locale');
            foreach ($translations as $locale => $snippets) {
                $localeModel = $shopRepository->findOneBy(array(
                    'locale' => $locale
                        ));
                foreach ($snippets as $element => $snippet) {
                    if ($localeModel === null) {
                        continue;
                    }
                    $elementModel = $form->getElement($element);
                    if ($elementModel === null) {
                        continue;
                    }
                    $translationModel = new \Shopware\Models\Config\ElementTranslation();
                    $translationModel->setLabel($snippet);
                    $translationModel->setLocale($localeModel);
                    $elementModel->addTranslation($translationModel);
                }
            }
        } catch (Exception $exception) {
            $this->uninstall();
            throw new Exception("Can not create translation." . $exception->getMessage());
        }
    }

    private function _createDataBaseTables()
    {
        $sqlLogging = "CREATE TABLE IF NOT EXISTS `pigmbh_ratepay_logging` (" .
                "`id` int(11) NOT NULL AUTO_INCREMENT," .
                "`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP," .
                "`version` varchar(10) DEFAULT 'N/A'," .
                "`operation` varchar(50) DEFAULT 'N/A'," .
                "`suboperation` varchar(50) DEFAULT 'N/A'," .
                "`transactionId` varchar(50) DEFAULT 'N/A'," .
                "`firstname` varchar(100) DEFAULT 'N/A'," .
                "`lastname` varchar(100) DEFAULT 'N/A'," .
                "`request` text," .
                "`response` text," .
                "PRIMARY KEY (`id`)" .
                ")";

        $sqlConfig = "CREATE TABLE IF NOT EXISTS `pigmbh_ratepay_config` (" .
                "`profileId` varchar(500) NOT NULL," .
                "`invoiceStatus` int(1) NOT NULL, " .
                "`debitStatus` int(1) NOT NULL, " .
                "`rateStatus` int(1) NOT NULL, " .
                "PRIMARY KEY (`profileId`)" .
                ")";

        $sqlOrderPositions = "CREATE TABLE IF NOT EXISTS `pigmbh_ratepay_order_positions` (" .
                "`s_order_details_id` int NOT NULL," .
                "`delivered` int NOT NULL DEFAULT 0, " .
                "`cancelled` int NOT NULL DEFAULT 0, " .
                "`returned` int NOT NULL DEFAULT 0, " .
                "PRIMARY KEY (`s_order_details_id`)" .
                ")";

        $sqlOrderShipping = "CREATE TABLE IF NOT EXISTS `pigmbh_ratepay_order_shipping` (" .
                "`s_order_id` int NOT NULL," .
                "`delivered` int NOT NULL DEFAULT 0, " .
                "`cancelled` int NOT NULL DEFAULT 0, " .
                "`returned` int NOT NULL DEFAULT 0, " .
                "PRIMARY KEY (`s_order_id`)" .
                ")";
        try {
            Shopware()->Db()->query($sqlLogging);
            Shopware()->Db()->query($sqlConfig);
            Shopware()->Db()->query($sqlOrderPositions);
            Shopware()->Db()->query($sqlOrderShipping);
        } catch (Exception $exception) {
            $this->uninstall();
            throw new Exception('Can not create Database.' . $exception->getMessage());
        }
    }

    /**
     * Creates the Menuentry for the RatePAY-logging
     */
    private function _createMenu()
    {
        try {
            $parent = $this->Menu()->findOneBy('label', 'logfile');
            $this->createMenuItem(array(
                'label' => 'RatePAY',
                'class' => 'sprite-cards-stack',
                'active' => 1,
                'controller' => 'PigmbhRatepayLogging',
                'action' => 'index',
                'parent' => $parent
                    )
            );
        } catch (Exception $exception) {
            $this->uninstall();
            throw new Exception("Can not create menuentry." . $exception->getMessage());
        }
    }

    /**
     * Subcribe eventslistener for the events
     *
     * @throws Exception Error: Can not create events.
     */
    private function _subscribeEvents()
    {
        try {
            $this->subscribeEvent(
                    'Enlight_Controller_Dispatcher_ControllerPath_Frontend_PigmbhRatepay', 'frontendPaymentController'
            );
            $this->subscribeEvent(
                    'Enlight_Controller_Dispatcher_ControllerPath_Backend_PigmbhRatepayLogging', 'onLoggingBackendController'
            );
            $this->subscribeEvent(
                    'Enlight_Controller_Dispatcher_ControllerPath_Backend_PigmbhRatepayOrderDetail', 'onOrderDetailBackendController'
            );
            $this->subscribeEvent(
                    'Enlight_Controller_Action_PostDispatch_Frontend_Checkout', 'preValidation'
            );
            $this->subscribeEvent(
                    'Enlight_Controller_Action_PreDispatch_Frontend_Checkout', 'onCheckoutConfirm'
            );
            $this->subscribeEvent(
                    'Shopware_Modules_Admin_GetPaymentMeans_DataFilter', 'filterPayments'
            );
            $this->subscribeEvent(
                    'Shopware_Controllers_Backend_Config::saveFormAction::before', 'beforeSavePluginConfig'
            );
            $this->subscribeEvent(
                    'Shopware_Controllers_Backend_Order::deletePositionAction::before', 'beforeDeleteOrderPosition'
            );
            $this->subscribeEvent(
                    'Shopware_Controllers_Backend_Order::deleteAction::before', 'beforeDeleteOrder'
            );
            $this->subscribeEvent(
                    'Enlight_Controller_Action_PostDispatch_Backend_Order', 'extendOrderDetailView'
            );
            $this->subscribeEvent(
                    'Shopware_Modules_Order_SaveOrder_ProcessDetails', 'insertRatepayPositions'
            );
        } catch (Exception $exception) {
            $this->uninstall();
            throw new Exception('Can not create events.' . $exception->getMessage());
        }
    }

    public function beforeSavePluginConfig(Enlight_Hook_HookArgs $arguments)
    {
        $request = $arguments->getSubject()->Request();
        $parameter = $request->getParams();

        if ($parameter['name'] !== $this->getName() || $parameter['controller'] !== 'config') {
            return;
        }

        foreach ($parameter['elements'] as $element) {
            if (in_array($element['name'], array('RatePayProfileID', 'RatePaySecurityCode')) && empty($element['values'][0]['value'])) {
                Shopware()->Log()->Warn('RatePAY: Credentials are missing!');
                return;
            }
            if ($element['name'] === 'RatePayProfileID') {
                $profileID = $element['values'][0]['value'];
            }
            if ($element['name'] === 'RatePaySecurityCode') {
                $securityCode = $element['values'][0]['value'];
            }
        }

        if ($this->getRatepayConfig($profileID, $securityCode)) {
            Shopware()->Log()->Info('RatePAY: Ruleset successfully updated.');
        }
    }

    /**
     * Eventlistener for frontendcontroller
     *
     * @param Enlight_Event_EventArgs $arguments
     * @return string
     */
    public function frontendPaymentController(Enlight_Event_EventArgs $arguments)
    {
        Shopware()->Template()->addTemplateDir($this->Path() . 'Views/');
        return $this->Path() . '/Controller/frontend/PigmbhRatepay.php';
    }

    /**
     * Loads the Backendextentions
     *
     * @param Enlight_Event_EventArgs $arguments
     */
    public function onLoggingBackendController()
    {
        Shopware()->Template()->addTemplateDir($this->Path() . 'Views/');
        return $this->Path() . "/Controller/backend/PigmbhRatepayLogging.php";
    }

    /**
     * Loads the Backendextentions
     *
     * @param Enlight_Event_EventArgs $arguments
     */
    public function onOrderDetailBackendController()
    {
        Shopware()->Template()->addTemplateDir($this->Path() . 'Views/');
        return $this->Path() . "/Controller/backend/PigmbhRatepayOrderDetail.php";
    }

    /**
     * Saves Data into the pigmbh_ratepay_order_position
     *
     * @param Enlight_Event_EventArgs $arguments
     */
    public function insertRatepayPositions(Enlight_Event_EventArgs $arguments)
    {
        $ordernumber = $arguments->getSubject()->sOrderNumber;

        try {
            $isRatePAYpaymentSQL = "SELECT COUNT(*) FROM `s_order` "
                    . "JOIN `s_core_paymentmeans` ON `s_core_paymentmeans`.`id`=`s_order`.`paymentID` "
                    . "WHERE  `s_order`.`ordernumber`=? AND`s_core_paymentmeans`.`name` LIKE 'pigmbhratepay%';";
            $isRatePAYpayment = Shopware()->Db()->fetchOne($isRatePAYpaymentSQL, array($ordernumber));
            Shopware()->Log()->Debug($isRatePAYpayment);
        } catch (Exception $exception) {
            Shopware()->Log()->Err($exception->getMessage());
            $isRatePAYpayment = 0;
        }

        if ($isRatePAYpayment != 0) {
            $sql = "SELECT `id` FROM `s_order_details` WHERE `ordernumber`=?;";
            $rows = Shopware()->Db()->fetchAll($sql, array($ordernumber));
            $values = "";
            foreach ($rows as $row) {
                $values .= "(" . $row['id'] . "),";
            }
            $values = substr($values, 0, -1);
            $sqlInsert = "INSERT INTO `pigmbh_ratepay_order_positions` "
                    . "(`s_order_details_id`) "
                    . "VALUES " . $values;
            try {
                Shopware()->Db()->query($sqlInsert);
            } catch (Exception $exception) {
                Shopware()->Log()->Err($exception->getMessage());
            }
        }
        return $ordernumber;
    }

    /**
     * validated the Userdata
     *
     * @param Enlight_Event_EventArgs $arguments
     */
    public function preValidation(Enlight_Event_EventArgs $arguments)
    {
        $request = $arguments->getSubject()->Request();
        $response = $arguments->getSubject()->Response();
        $view = $arguments->getSubject()->View();

        if (!$request->isDispatched() || $response->isException() || $request->getModuleName() != 'frontend' || !$view->hasTemplate()) {
            return;
        }

        // Check for the right Action
        if (!in_array('confirm', array($request->get('action'), $view->sTargetAction)) || $request->get('controller') !== 'checkout') {
            return;
        }

        if (empty(Shopware()->Session()->sUserId)) {
            Shopware()->Log()->Debug("RatePAY: sUserId is empty");
            return;
        }
        Shopware()->Template()->addTemplateDir(dirname(__FILE__) . '/Views/');
        $validation = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Validation();

        if ($validation->isRatePAYPayment()) {
            $view->sRegisterFinished = 'false';
            $view->ratepayValidateTelephoneNumber = $validation->isTelephoneNumberSet() ? 'true' : 'false';
            Shopware()->Log()->Debug("RatePAY: isTelephoneNumberSet->" . $view->ratepayValidateTelephoneNumber);
            $view->ratepayValidateUST = $validation->isUSTSet() ? 'true' : 'false';
            Shopware()->Log()->Debug("RatePAY: isUSTSet->" . $view->ratepayValidateUST);
            $view->ratepayValidateCompanyName = $validation->isCompanyNameSet() ? 'true' : 'false';
            Shopware()->Log()->Debug("RatePAY: isCompanyNameSet->" . $view->ratepayValidateCompanyName);
            $view->ratepayValidateIsB2B = $validation->isCompanyNameSet() || $validation->isUSTSet() ? 'true' : 'false';
            Shopware()->Log()->Debug("RatePAY: isB2B->" . $view->ratepayValidateIsB2B);
            $view->ratepayValidateIsAddressValid = $validation->isAddressValid() ? 'true' : 'false';
            Shopware()->Log()->Debug("RatePAY: isAddressValid->" . $view->ratepayValidateIsAddressValid);
            $view->ratepayValidateIsBirthdayValid = $validation->isBirthdayValid() ? 'true' : 'false';
            Shopware()->Log()->Debug("RatePAY: isBirthdayValid->" . $view->ratepayValidateIsBirthdayValid);
            $view->ratepayValidateisAgeValid = $validation->isAgeValid() ? 'true' : 'false';
            Shopware()->Log()->Debug("RatePAY: isAgeValid->" . $view->ratepayValidateisAgeValid);
            $view->ratepayValidateisDebitSet = $validation->isDebitSet() ? 'true' : 'false';
            Shopware()->Log()->Debug("RatePAY: isDebitSet->" . $view->ratepayValidateisDebitSet);
        }
    }

    /**
     * Extends the confirmationpage with an Errorbox, if there is an error.
     *
     * @param Enlight_Event_EventArgs $arguments
     * @return null
     */
    public function onCheckoutConfirm(Enlight_Event_EventArgs $arguments)
    {
        $params = $arguments->getRequest()->getParams();
        if ($arguments->getRequest()->getActionName() !== 'confirm' || $params['showError'] !== '1') {
            return;
        }
        $pigmbhErrorMessage = Shopware()->Session()->RatePAY['errorMessage'];
        $view = $arguments->getSubject()->View();
        $view->pigmbhErrorMessage = $pigmbhErrorMessage;
    }

    /**
     * Filters the shown Payments
     * RatePAY-payments will be hidden, if one of the following requirement is not given
     *  - The Customer must be over 18 years old
     *  - The Country must be germany
     *  - The Currency must be EUR
     *
     * @param Enlight_Event_EventArgs $arguments
     * @return array
     */
    public function filterPayments(Enlight_Event_EventArgs $arguments)
    {
        $return = $arguments->getReturn();
        $currency = Shopware()->Config()->get('currency');
        if (empty(Shopware()->Session()->sUserId) || empty($currency)) {
            return;
        }

        $profileId = Shopware()->Plugins()->Frontend()->PigmbhRatePay()->Config()->get('RatePayProfileID');
        $paymentStati = Shopware()->Db()->fetchRow('SELECT * FROM `pigmbh_ratepay_config` WHERE `profileId`=?', array($profileId));
        $showRate = $paymentStati['rateStatus'] == 2 ? true : false;
        $showDebit = $paymentStati['debitStatus'] == 2 ? true : false;
        $showInvoice = $paymentStati['invoiceStatus'] == 2 ? true : false;

        $validation = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Validation();
        if (!$validation->isAgeValid() || !$validation->isCountryValid() || !$validation->isCurrencyValid()) {
            $showRate = false;
            $showDebit = false;
            $showInvoice = false;
        }

        if ($validation->isCompanyNameSet() || $validation->isUSTSet()) {
            // Rate & B2B is forbidden
            $showRate = false;
        }

        $payments = array();
        foreach ($return as $payment) {
            if ($payment['name'] === 'pigmbhratepayinvoice' && !$showInvoice) {
                Shopware()->Log()->Debug("RatePAY: Filter RatePAY-Invoice");
                continue;
            }
            if ($payment['name'] === 'pigmbhratepaydebit' && !$showDebit) {
                Shopware()->Log()->Debug("RatePAY: Filter RatePAY-Debit");
                continue;
            }
            if ($payment['name'] === 'pigmbhratepayrate' && !$showRate) {
                Shopware()->Log()->Debug("RatePAY: Filter RatePAY-Rate");
                continue;
            }
            $payments[] = $payment;
        }

        return $payments;
    }

    private function getRatepayConfig($profileId, $securityCode)
    {
        $factory = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Mapper_ModelFactory();
        $profileRequestModel = $factory->getModel(new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_ProfileRequest());
        $head = $profileRequestModel->getHead();
        $head->setProfileId($profileId);
        $head->setSecurityCode($securityCode);
        $profileRequestModel->setHead($head);
        $requestService = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Service_RequestService();
        $response = $requestService->xmlRequest($profileRequestModel->toArray());

        if (Shopware_Plugins_Frontend_PigmbhRatePay_Component_Service_Util::validateResponse('PROFILE_REQUEST', $response)) {
            $sql = "REPLACE INTO `pigmbh_ratepay_config`"
                    . "(`profileId`, `invoiceStatus`,`debitStatus`,`rateStatus`) "
                    . "VALUES(?, ?, ?, ?);";
            $data = array(
                $response->getElementsByTagName('profile-id')->item(0)->nodeValue,
                $response->getElementsByTagName('activation-status-invoice')->item(0)->nodeValue,
                $response->getElementsByTagName('activation-status-elv')->item(0)->nodeValue,
                $response->getElementsByTagName('activation-status-installment')->item(0)->nodeValue
            );
            try {
                $this->clearRuleSet();
                $this->setRuleSet(
                        'pigmbhratepayinvoice', 'ORDERVALUELESS', $response->getElementsByTagName('tx-limit-invoice-min')->item(0)->nodeValue
                );
                $this->setRuleSet(
                        'pigmbhratepayinvoice', 'ORDERVALUEMORE', $response->getElementsByTagName('tx-limit-invoice-max')->item(0)->nodeValue
                );
                $this->setRuleSet(
                        'pigmbhratepaydebit', 'ORDERVALUELESS', $response->getElementsByTagName('tx-limit-elv-min')->item(0)->nodeValue
                );
                $this->setRuleSet(
                        'pigmbhratepaydebit', 'ORDERVALUEMORE', $response->getElementsByTagName('tx-limit-elv-max')->item(0)->nodeValue
                );
                $this->setRuleSet(
                        'pigmbhratepayrate', 'ORDERVALUELESS', $response->getElementsByTagName('tx-limit-installment-min')->item(0)->nodeValue
                );
                $this->setRuleSet(
                        'pigmbhratepayrate', 'ORDERVALUEMORE', $response->getElementsByTagName('tx-limit-installment-max')->item(0)->nodeValue
                );
                Shopware()->Db()->query($sql, $data);
                return true;
            } catch (Exception $exception) {
                Shopware()->Log()->Err($exception->getMessage());
                return false;
            }
        } else {
            Shopware()->Log()->Err('RatePAY: Profile_Request failed!');
            return false;
        }
    }

    private function setRuleSet($paymentName, $firstRule, $firstValue)
    {
        $payment = $this->Payments()->findOneBy(array('name' => $paymentName));
        $ruleset = new Shopware\Models\Payment\RuleSet;
        $ruleset->setPayment($payment);
        $ruleset->setRule1($firstRule);
        $ruleset->setValue1($firstValue);
        $ruleset->setRule2('');
        $ruleset->setValue2(0);
        Shopware()->Models()->persist($ruleset);
    }

    private function clearRuleSet()
    {
        $sql = "DELETE FROM `s_core_rulesets` "
                . "WHERE `paymentID` IN("
                . "SELECT `id` FROM `s_core_paymentmeans` "
                . "WHERE `name` LIKE 'pigmbhratepay%'"
                . ") AND `rule1` LIKE 'ORDERVALUE%';";
        Shopware()->Db()->query($sql);
    }

    /**
     * extends the Orderdetailview
     *
     * @param Enlight_Event_EventArgs $arguments
     */
    public function extendOrderDetailView(Enlight_Event_EventArgs $arguments)
    {
        $arguments->getSubject()->View()->addTemplateDir(
                $this->Path() . 'Views/backend/pigmbh_ratepay_orderdetail/'
        );

        if ($arguments->getRequest()->getActionName() === 'load') {
            $arguments->getSubject()->View()->extendsTemplate(
                    'backend/order/view/detail/ratepaydetailorder.js'
            );
        }

        if ($arguments->getRequest()->getActionName() === 'index') {
            $arguments->getSubject()->View()->extendsTemplate(
                    'backend/order/ratepayapp.js'
            );
        }
    }

}
