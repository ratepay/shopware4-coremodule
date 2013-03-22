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
        return "2.2.0";
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
            $this->createPayment(
                    array(
                        'name' => 'pigmbhratepayprepayment',
                        'description' => 'RatePAY Vorkasse',
                        'action' => 'pigmbh_ratepay',
                        'active' => 1,
                        'position' => 4,
                        'additionaldescription' => '',
                        'template' => 'RatePAYPrepayment.tpl'
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
                'controller' => 'PigmbhRatePay',
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
                    'Enlight_Controller_Dispatcher_ControllerPath_Backend_PigmbhRatepay', 'onBackendController'
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

// save profile_request into DB
        if (!$this->getRatepayConfig($profileID, $securityCode)) {
            Shopware()->Log()->Err('RatePAY: Profile_Request failed!');
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
        Shopware()->Template()->addTemplateDir($this->Path() . 'View/');
        return $this->Path() . 'Controller/frontend/PigmbhRatepay.php';
    }

    /**
     * Loads the Backendextentions
     *
     * @param Enlight_Event_EventArgs $arguments
     */
    public function onBackendController()
    {
        Shopware()->Template()->addTemplateDir($this->Path() . 'View/');
        return $this->Path() . "Controller/backend/";
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
        Shopware()->Template()->addTemplateDir(dirname(__FILE__) . '/View/');
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
        if ($arguments->getRequest()->getActionName() !== 'confirm' || $params['showError'] != 1) {
            return;
        }
        $pigmbhErrorMessage = Shopware()->Session()->RatePAY['errorMessage'];
        $view = $arguments->getSubject()->View();
        $content = '{if $pigmbhErrorMessage}' .
                '<div class="grid_20">' .
                '<div class="error">' .
                '<div class="center">' .
                '<strong>' .
                '{$pigmbhErrorMessage}' .
                '</strong>' .
                '</div>' .
                '</div>' .
                '</div>' .
                '{/if}';
        $view->extendsBlock("frontend_index_content_top", $content, "append");
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
        if (empty(Shopware()->Session()->sUserId) || Shopware()->Config()->get('currency')) {
            return;
        }

        $payments = $return;
        $validation = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Validation();
        if (!$validation->isAgeValid() || !$validation->isCountryValid() || !$validation->isCurrencyValid()) {
            Shopware()->Log()->Debug("RatePAY: Filter RatePAY-payments");
            $payments = array();
            foreach ($return as $payment) {
                if (!in_array($payment['name'], array('pigmbhratepayinvoice', 'pigmbhratepaydebit', 'pigmbhratepayrate', 'pigmbhratepayprepayment'))) {
                    $payments[] = $payment;
                }
            }
        }
        return $payments;
    }

    private function getRatepayConfig($profileId, $securityCode)
    {
        $xmlstr = '<response version="1.0" xmlns="urn://www.ratepay.com/payment/1_0">
 <head>
 <system-id>test.mypayment.de</system-id>
 <operation>CONFIGURATION_REQUEST</operation>
 <response-type>CONFIGURATION_SETTINGS</response-type>
 <processing>
 <timestamp>2012-04-30T12:27:39.234</timestamp>
 <status code="OK">Successful</status>
 <reason code="306">Calculation configuration read successful</reason>
 <result code="500">Calculation configuration processed</result>
 </processing>
 </head>
 <content>
 <installment-configuration-result name="Standardconfig" type="DEFAULT">
 <interestrate-min>5.90</interestrate-min>
 <interestrate-default>9.90</interestrate-default>
 <interestrate-max>12.90</interestrate-max>
 <interest-rate-merchant-towards-bank>12.90</interest-rate-merchant-towards-bank>
 <month-number-min>3</month-number-min>
 <month-number-max>36</month-number-max>
 <month-longrun>25</month-longrun>
 <amount-min-longrun>1000</amount-min-longrun>
 <month-allowed>3,4,5,6,9,12,15,18,24,36</month-allowed>
 <valid-payment-firstdays>1,15,28</valid-payment-firstdays>
 <payment-firstday>28</payment-firstday>
 <payment-amount>200.00</payment-amount>
 <payment-lastrate>4.00</payment-lastrate>
 <rate-min-normal>20.00</rate-min-normal>
 <rate-min-longrun>10.00</rate-min-longrun>
 <service-charge>3.95</service-charge>
 <min-difference-dueday>28</min-difference-dueday>
 </installment-configuration-result>
 </content>
</response>';
        $response = new SimpleXMLElement($xmlstr);
        $status = (string) $response->children()->head->children()->processing->children()->status->attributes();
        $return = false;
        if ($status === "OK") {
            $content = (array) $response->children()->content->children()->children();
            $sql = 'REPLACE INTO `pigmbh_ratepay_configprofile` ('
                    . '`profileId`, `interestrateMin`, `interestrateDefault`, `interestrateMax`, `interestrateMerchantTowardsBank`'
                    . ',`monthNumberMin`, `monthNumberMax`, `monthLongrun`, `amountMinLongrun`, `monthAllowed`'
                    . ',`validPaymentFirstdays`, `paymentFirstday`, `paymentAmount`, `paymentLastrate`, `rateMinNormal`, `rateMinLongrun`'
                    . ',`serviceCharge`, `minDifferenceDueday`'
                    . ') VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
            $configData = array(
                $profileId,
                $content['interestrate-min'],
                $content['interestrate-default'],
                $content['interestrate-max'],
                $content['interest-rate-merchant-towards-bank'],
                $content['month-number-min'],
                $content['month-number-max'],
                $content['month-longrun'],
                $content['amount-min-longrun'],
                $content['month-allowed'],
                $content['valid-payment-firstdays'],
                $content['payment-firstday'],
                $content['payment-amount'],
                $content['payment-lastrate'],
                $content['rate-min-normal'],
                $content['rate-min-longrun'],
                $content['service-charge'],
                $content['min-difference-dueday']
            );
            try {
                Shopware()->Db()->query($sql, $configData);
                $return = true;
            } catch (Exception $exception) {
                Shopware()->Log()->Err($exception->getMessage());
            }
        }
        return $return;
    }

}