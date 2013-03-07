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
                        'active' => 0,
                        'position' => 0,
                        'additionaldescription' => ''
                    )
            );
            $this->createPayment(
                    array(
                        'name' => 'pigmbhratepayrate',
                        'description' => 'RatePAY Ratenzahlung',
                        'action' => 'pigmbh_ratepay',
                        'active' => 0,
                        'position' => 0,
                        'additionaldescription' => ''
                    )
            );
            $this->createPayment(
                    array(
                        'name' => 'pigmbhratepaydebit',
                        'description' => 'RatePAY Lastschrift',
                        'action' => 'pigmbh_ratepay',
                        'active' => 0,
                        'position' => 0,
                        'additionaldescription' => ''
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
                'value' => false
            ));
            $form->setElement('boolean', 'RatePayBankData', array(
                'label' => 'Bankdatenspeicherung aktivieren',
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
                    'RatePaySandbox' => 'Sandbox',
                    'RatePayLogging' => 'Logging',
                    'RatePayBankData' => 'Bankdatenspeicherung aktivieren'
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
     * Create eventslistener for the plugin
     *
     * @throws Exception Error: Cant create events(_subscribeEvents)
     */
    private function _subscribeEvents()
    {
        try {
            $this->subscribeEvent(
                    'Enlight_Controller_Dispatcher_ControllerPath_Frontend_PigmbhRatePay', 'frontendPaymentController'
            );
            $this->subscribeEvent(
                    'Enlight_Controller_Dispatcher_ControllerPath_Backend_PigmbhRatePay', 'onBackendController'
            );
            $this->subscribeEvent(
                    'Enlight_Controller_Action_PostDispatch_Frontend_Checkout', 'preValidation'
            );
        } catch (Exception $exception) {
            $this->uninstall();
            throw new Exception('Can not create events.' . $exception->getMessage());
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
        return $this->Path() . 'Controller/Frontend/';
    }

    /**
     * Loads the Backendextentions
     *
     * @param Enlight_Event_EventArgs $arguments
     */
    public function onBackendController()
    {
        Shopware()->Template()->addTemplateDir($this->Path() . 'Views/');
        return $this->Path() . "/Controller/Backend/";
    }

    /**
     * validated the User
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

        if(empty(Shopware()->Session()->sUserId)){
           Shopware()->Log()->Debug("RatePAY: sUserId is empty");
           return;
        }
        $validation = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Validation();
        if ($validation->isRatePAYPayment()) {
            $view->sRegisterFinished = "false";
            $view->ratepayValidateIsBirthdayValid = $validation->isBirthdayValid() ? 'true': 'false';
            Shopware()->Log()->Debug("RatePAY: isBirthdayValid->" . $view->ratepayValidateIsBirthdayValid);
            $view->ratepayValidateAgeValid = $validation->isAgeValid() ? 'true': 'false';
            Shopware()->Log()->Debug("RatePAY: isAgeValid->" . $view->ratepayValidateAgeValid);
            $view->ratepayValidateTelephoneNumber = $validation->isTelephoneNumberSet() ? 'true': 'false';
            Shopware()->Log()->Debug("RatePAY: isTelephoneNumberSet->" . $view->ratepayValidateTelephoneNumber);
            $view->ratepayValidateUST = $validation->isUSTSet() ? 'true': 'false';
            Shopware()->Log()->Debug("RatePAY: isUSTSet->" . $view->ratepayValidateUST);
            $view->ratepayValidateCompanyName = $validation->isCompanyNameSet() ? 'true': 'false';
            Shopware()->Log()->Debug("RatePAY: isCompanyNameSet->" . $view->ratepayValidateCompanyName);
            $view->ratepayValidateIsB2B = $validation->isCompanyNameSet() || $validation->isUSTSet() ? 'true': 'false';
            Shopware()->Log()->Debug("RatePAY: isB2B->" . $view->ratepayValidateIsB2B);
        }
    }



}