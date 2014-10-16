<?php

    /**
     * This program is free software; you can redistribute it and/or modify it under the terms of
     * the GNU General Public License as published by the Free Software Foundation; either
     * version 3 of the License, or (at your option) any later version.
     *
     * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
     * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
     * See the GNU General Public License for more details.
     *
     * You should have received a copy of the GNU General Public License along with this program;
     * if not, see <http://www.gnu.org/licenses/>.
     *
     * Bootstrap
     *
     * @category   RatePAY
     * @package    RpayRatePAY
     * @copyright  Copyright (c) 2013 RatePAY GmbH (http://www.ratepay.com)
     */
    class Shopware_Plugins_Frontend_RpayRatePay_Bootstrap extends Shopware_Components_Plugin_Bootstrap
    {

        /**
         * Get Info for the Pluginmanager
         *
         * @return array
         */
        public function getInfo()
        {
            return array(
                'version'     => $this->getVersion(),
                'autor'       => 'RatePay GmbH',
                'source'      => $this->getSource(),
                'support'     => 'http://www.ratepay.com/support',
                'link'        => 'http://www.ratepay.com/',
                'copyright'   => 'Copyright (c) 2013, RatePAY GmbH',
                'description' => 'RatePay Payment Module.',
                'label'       => 'RatePay Payment'
            );
        }

        /**
         * Returns all allowed actions
         *
         * @return array
         */
        public function getCapabilities()
        {
            return array(
                'install' => true,
                'update'  => true,
                'enable'  => true
            );
        }

        /**
         * Returns the Label of the Plugin
         *
         * @return string
         */
        public function getLabel()
        {
            return 'RatePay Payment';
        }


        /**
         * Returns the Pluginversion
         *
         * @return string
         */
        public function getVersion()
        {
            return "3.2.1";
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

            return array('success' => true, 'invalidateCache' => array('frontend', 'backend'));
        }

        /**
         * Updates the Plugin and its components
         *
         * @param string $oldversion
         */
        public function update($oldversion)
        {
            $this->_subscribeEvents();

            return array('success' => true, 'invalidateCache' => array('frontend', 'backend'));
        }


        /**
         * Uninstalls the Plugin and its components
         *
         * @return boolean
         */
        public function uninstall()
        {
            $this->disable();

            return parent::uninstall();
        }

        /**
         * Deactivates the Plugin and its components
         *
         * @return boolean
         */
        public function disable()
        {
            $sql = "UPDATE `s_core_paymentmeans` SET `active` =0 WHERE `name` LIKE 'rpayratepay%'";
            Shopware()->Db()->query($sql);

            return true;
        }

        /**
         * Creates the Paymentmeans
         */
        private function _createPaymentmeans()
        {
            try {
                $this->createPayment(
                    array(
                        'name'                  => 'rpayratepayinvoice',
                        'description'           => 'RatePAY Rechnung',
                        'action'                => 'rpay_ratepay',
                        'active'                => 0,
                        'position'              => 1,
                        'additionaldescription' => 'Kauf auf Rechnung',
                        'template'              => 'RatePAYInvoice.tpl'
                    )
                );
                $this->createPayment(
                    array(
                        'name'                  => 'rpayratepayrate',
                        'description'           => 'RatePAY Ratenzahlung',
                        'action'                => 'rpay_ratepay',
                        'active'                => 0,
                        'position'              => 2,
                        'additionaldescription' => 'Kauf mit Ratenzahlung',
                        'template'              => 'RatePAYRate.tpl'
                    )
                );
                $this->createPayment(
                    array(
                        'name'                  => 'rpayratepaydebit',
                        'description'           => 'RatePAY SEPA-Lastschrift',
                        'action'                => 'rpay_ratepay',
                        'active'                => 0,
                        'position'              => 3,
                        'additionaldescription' => 'Kauf mit SEPA Lastschrift',
                        'template'              => 'RatePAYDebit.tpl'
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
                $form->setElement('checkbox', 'RatePaySandbox', array(
                    'label' => 'Sandbox'
                ));
                $form->setElement('checkbox', 'RatePayLogging', array(
                    'label' => 'Logging'
                ));
                /*$form->setElement('checkbox', 'RatePayBankData', array(
                    'label' => 'Bankdatenspeicherung aktivieren'
                ));*/
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
                        'RatePayProfileID'    => 'Profile-ID',
                        'RatePaySecurityCode' => 'Security Code',
                        'RatePaySandbox'      => 'Sandboxmodus',
                        'RatePayLogging'      => 'Logging aktivieren',
                        //'RatePayBankData' => 'Bankdatenspeicherung aktivieren'
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
         * Creates the Databasetables
         *
         * @throws Exception SQL-Error
         */
        private function _createDataBaseTables()
        {
            $sqlLogging = "CREATE TABLE IF NOT EXISTS `rpay_ratepay_logging` (" .
                "`id` int(11) NOT NULL AUTO_INCREMENT," .
                "`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP," .
                "`version` varchar(10) DEFAULT 'N/A'," .
                "`operation` varchar(255) DEFAULT 'N/A'," .
                "`suboperation` varchar(255) DEFAULT 'N/A'," .
                "`transactionId` varchar(255) DEFAULT 'N/A'," .
                "`firstname` varchar(255) DEFAULT 'N/A'," .
                "`lastname` varchar(255) DEFAULT 'N/A'," .
                "`request` text," .
                "`response` text," .
                "PRIMARY KEY (`id`)" .
                ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

            $sqlBankdata = "CREATE TABLE IF NOT EXISTS `rpay_ratepay_user_bankdata` (" .
                "`userID` int(11) NOT NULL AUTO_INCREMENT," .
                "`bankname` varchar(255) NOT NULL," .
                "`bankcode` varchar(255) NOT NULL," .
                "`bankholder` varchar(255) NOT NULL," .
                "`account` varchar(255) NOT NULL," .
                "`iban` varchar(255) NOT NULL," .
                "`bic` varchar(255) NOT NULL," .
                "PRIMARY KEY (`userID`)" .
                ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

            $sqlConfig = "CREATE TABLE IF NOT EXISTS `rpay_ratepay_config` (" .
                "`profileId` varchar(255) NOT NULL," .
                "`invoiceStatus` int(1) NOT NULL, " .
                "`debitStatus` int(1) NOT NULL, " .
                "`rateStatus` int(1) NOT NULL, " .
                "`b2b-invoice` varchar(3) NOT NULL, " .
                "`b2b-debit` varchar(3) NOT NULL, " .
                "`b2b-rate` varchar(3) NOT NULL, " .
                "`address-invoice` varchar(3) NOT NULL, " .
                "`address-debit` varchar(3) NOT NULL, " .
                "`address-rate` varchar(3) NOT NULL, " .
                "`limit-invoice-min` int(5) NOT NULL, " .
                "`limit-debit-min` int(5) NOT NULL, " .
                "`limit-rate-min` int(5) NOT NULL, " .
                "`limit-invoice-max` int(5) NOT NULL, " .
                "`limit-debit-max` int(5) NOT NULL, " .
                "`limit-rate-max` int(5) NOT NULL, " .
                "PRIMARY KEY (`profileId`)" .
                ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

            $sqlOrderPositions = "CREATE TABLE IF NOT EXISTS `rpay_ratepay_order_positions` (" .
                "`s_order_details_id` int(11) NOT NULL," .
                "`delivered` int NOT NULL DEFAULT 0, " .
                "`cancelled` int NOT NULL DEFAULT 0, " .
                "`returned` int NOT NULL DEFAULT 0, " .
                "PRIMARY KEY (`s_order_details_id`)" .
                ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

            $sqlOrderShipping = "CREATE TABLE IF NOT EXISTS `rpay_ratepay_order_shipping` (" .
                "`s_order_id` int(11) NOT NULL," .
                "`delivered` int NOT NULL DEFAULT 0, " .
                "`cancelled` int NOT NULL DEFAULT 0, " .
                "`returned` int NOT NULL DEFAULT 0, " .
                "PRIMARY KEY (`s_order_id`)" .
                ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

            $sqlOrderHistory = "CREATE TABLE IF NOT EXISTS `rpay_ratepay_order_history` (" .
                "`id` int(11) NOT NULL AUTO_INCREMENT," .
                "`orderId` varchar(50) ," .
                "`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, " .
                "`event` varchar(100), " .
                "`articlename` varchar(100), " .
                "`articlenumber` varchar(50), " .
                "`quantity` varchar(50), " .
                "PRIMARY KEY (`id`)" .
                ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
            try {
                Shopware()->Db()->query($sqlBankdata);
                Shopware()->Db()->query($sqlLogging);
                Shopware()->Db()->query($sqlConfig);
                Shopware()->Db()->query($sqlOrderPositions);
                Shopware()->Db()->query($sqlOrderShipping);
                Shopware()->Db()->query($sqlOrderHistory);
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
                        'label'      => 'RatePAY',
                        'class'      => 'sprite-cards-stack',
                        'active'     => 1,
                        'controller' => 'RpayRatepayLogging',
                        'action'     => 'index',
                        'parent'     => $parent
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
                //Hook into backend order controller
                $this->subscribeEvent(
                    'Shopware_Controllers_Backend_Order::saveAction::before', 'beforeSaveOrderInBackend'
                );
                $this->subscribeEvent(
                    'Enlight_Controller_Dispatcher_ControllerPath_Frontend_RpayRatepay', 'frontendPaymentController'
                );
                $this->subscribeEvent(
                    'Enlight_Controller_Dispatcher_ControllerPath_Backend_RpayRatepayLogging', 'onLoggingBackendController'
                );
                $this->subscribeEvent(
                    'Enlight_Controller_Dispatcher_ControllerPath_Backend_RpayRatepayOrderDetail', 'onOrderDetailBackendController'
                );
                $this->subscribeEvent(
                    'Enlight_Controller_Action_PostDispatch_Frontend_Checkout', 'preValidation'
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

        /**
         * Checks if the payment method is a ratepay method. If it is a ratepay method, throw an exception
         * and forbit to change the payment method
         */
        public function beforeSaveOrderInBackend(Enlight_Hook_HookArgs $arguments)
        {
            $request = $arguments->getSubject()->Request();
            $order = Shopware()->Models()->find('Shopware\Models\Order\Order', $request->getParam('id'));
            $newPaymentMethod = Shopware()->Models()->find('Shopware\Models\Payment\Payment', $request->getParam('paymentId'));

            if ((!in_array($order->getPayment()->getName(), array('rpayratepayinvoice', 'rpayratepayrate', 'rpayratepaydebit')) && in_array($newPaymentMethod->getName(), array('rpayratepayinvoice', 'rpayratepayrate', 'rpayratepaydebit')))
                || (in_array($order->getPayment()->getName(), array('rpayratepayinvoice', 'rpayratepayrate', 'rpayratepaydebit')) && $newPaymentMethod->getName() != $order->getPayment()->getName())
            ) {
                Shopware()->Pluginlogger()->addNotice('RatePAY', 'Bestellungen k&ouml;nnen nicht nachtr&auml;glich auf RatePay Zahlungsmethoden ge&auml;ndert werden und RatePay Bestellungen k&ouml;nnen nicht nachtr&auml;glich auf andere Zahlarten ge&auml;ndert werden.');
                $arguments->stop();
                throw new \Symfony\Component\Config\Definition\Exception\Exception('Bestellungen k&ouml;nnen nicht nachtr&auml;glich auf RatePay Zahlungsmethoden ge&auml;ndert werden und RatePay Bestellungen k&ouml;nnen nicht nachtr&auml;glich auf andere Zahlarten ge&auml;ndert werden.');
            }

            return false;

        }


        /**
         * Checks if credentials are set and gets the configuration via profile_request
         *
         * @param Enlight_Hook_HookArgs $arguments
         *
         * @return null
         */
        public function beforeSavePluginConfig(Enlight_Hook_HookArgs $arguments)
        {
            $request = $arguments->getSubject()->Request();
            $parameter = $request->getParams();

            if ($parameter['name'] !== $this->getName() || $parameter['controller'] !== 'config') {
                return;
            }

            foreach ($parameter['elements'] as $element) {
                if (in_array($element['name'], array('RatePayProfileID', 'RatePaySecurityCode')) && empty($element['values'][0]['value'])) {
                    Shopware()->Pluginlogger()->addNotice('RatePAY', 'RatePAY: Credentials are missing!');

                    return;
                }
                if ($element['name'] === 'RatePayProfileID') {
                    $profileID = $element['values'][0]['value'];
                }
                if ($element['name'] === 'RatePaySecurityCode') {
                    $securityCode = $element['values'][0]['value'];
                }
                if ($element['name'] === 'RatePaySandbox') {
                    $sandbox = $element['values'][0]['value'];
                }
            }

            if ($this->getRatepayConfig($profileID, $securityCode, $sandbox)) {
                Shopware()->Pluginlogger()->addNotice('RatePAY', 'RatePAY: Ruleset successfully updated.');
            }
        }

        /**
         * Stops Orderdeletation, when its not permitted
         *
         * @param Enlight_Hook_HookArgs $arguments
         *
         * @return true
         */
        public function beforeDeleteOrderPosition(Enlight_Hook_HookArgs $arguments)
        {
            $request = $arguments->getSubject()->Request();
            $parameter = $request->getParams();
            $order = Shopware()->Models()->find('Shopware\Models\Order\Order', $parameter['orderID']);
            if ($parameter['valid'] != true && in_array($order->getPayment()->getName(), array("rpayratepayinvoice", "rpayratepayrate", "rpayratepaydebit"))) {
                Shopware()->Pluginlogger()->addNotice('RatePAY', 'Positionen einer RatePAY-Bestellung k&ouml;nnen nicht gelöscht werden. Bitte Stornieren Sie die Artikel in der Artikelverwaltung.');
                $arguments->stop();
            }

            return true;
        }

        /**
         * Stops Orderdeletation, when any article has been send
         *
         * @param Enlight_Hook_HookArgs $arguments
         */
        public function beforeDeleteOrder(Enlight_Hook_HookArgs $arguments)
        {
            $request = $arguments->getSubject()->Request();
            $parameter = $request->getParams();
            if (!in_array($parameter['payment'][0]['name'], array("rpayratepayinvoice", "rpayratepayrate", "rpayratepaydebit"))) {
                return false;
            }
            $sql = "SELECT COUNT(*) FROM `s_order_details` AS `detail` "
                . "INNER JOIN `rpay_ratepay_order_positions` AS `position` "
                . "ON `position`.`s_order_details_id` = `detail`.`id` "
                . "WHERE `detail`.`orderID`=? AND "
                . "(`position`.`delivered` > 0 OR `position`.`cancelled` > 0 OR `position`.`returned` > 0)";
            $count = Shopware()->Db()->fetchOne($sql, array($parameter['id']));
            if ($count > 0) {
                Shopware()->Pluginlogger()->addNotice('RatePAY', 'RatePAY-Bestellung k&ouml;nnen nicht gelöscht werden, wenn sie bereits bearbeitet worden sind.');
                $arguments->stop();
            }
            else {
                $config = Shopware()->Plugins()->Frontend()->RpayRatePay()->Config();
                $request = new Shopware_Plugins_Frontend_RpayRatePay_Component_Service_RequestService($config->get('RatePaySandbox'));

                $modelFactory = new Shopware_Plugins_Frontend_RpayRatePay_Component_Mapper_ModelFactory();
                $modelFactory->setTransactionId($parameter['transactionId']);
                $paymentChange = $modelFactory->getModel(new Shopware_Plugins_Frontend_RpayRatePay_Component_Model_PaymentChange());
                $head = $paymentChange->getHead();
                $head->setOperationSubstring('full-cancellation');
                $paymentChange->setHead($head);
                $basket = new Shopware_Plugins_Frontend_RpayRatePay_Component_Model_SubModel_ShoppingBasket();
                $basket->setAmount(0);
                $basket->setCurrency($parameter['currency']);
                $paymentChange->setShoppingBasket($basket);
                $response = $request->xmlRequest($paymentChange->toArray());
                $result = Shopware_Plugins_Frontend_RpayRatePay_Component_Service_Util::validateResponse('PAYMENT_CHANGE', $response);
                if (!$result) {
                    Shopware()->Pluginlogger()->addNotice('RatePAY', 'Bestellung k&ouml;nnte nicht gelöscht werden, da die Stornierung bei RatePAY fehlgeschlagen ist.');
                    $arguments->stop();
                }
            }
        }

        /**
         * Eventlistener for frontendcontroller
         *
         * @param Enlight_Event_EventArgs $arguments
         *
         * @return string
         */
        public function frontendPaymentController(Enlight_Event_EventArgs $arguments)
        {
            Shopware()->Template()->addTemplateDir($this->Path() . 'Views/');

            return $this->Path() . '/Controller/frontend/RpayRatepay.php';
        }

        /**
         * Loads the Backendextentions
         *
         * @param Enlight_Event_EventArgs $arguments
         */
        public function onLoggingBackendController()
        {
            Shopware()->Template()->addTemplateDir($this->Path() . 'Views/');

            return $this->Path() . "/Controller/backend/RpayRatepayLogging.php";
        }

        /**
         * Loads the Backendextentions
         */
        public function onOrderDetailBackendController()
        {
            Shopware()->Template()->addTemplateDir($this->Path() . 'Views/');

            return $this->Path() . "/Controller/backend/RpayRatepayOrderDetail.php";
        }

        /**
         * Saves Data into the rpay_ratepay_order_position
         *
         * @param Enlight_Event_EventArgs $arguments
         */
        public function insertRatepayPositions(Enlight_Event_EventArgs $arguments)
        {
            $ordernumber = $arguments->getSubject()->sOrderNumber;

            try {
                $isRatePAYpaymentSQL = "SELECT COUNT(*) FROM `s_order` "
                    . "JOIN `s_core_paymentmeans` ON `s_core_paymentmeans`.`id`=`s_order`.`paymentID` "
                    . "WHERE  `s_order`.`ordernumber`=? AND`s_core_paymentmeans`.`name` LIKE 'rpayratepay%';";
                $isRatePAYpayment = Shopware()->Db()->fetchOne($isRatePAYpaymentSQL, array($ordernumber));
                Shopware()->Pluginlogger()->addNotice('RatePAY', $isRatePAYpayment);
            } catch (Exception $exception) {
                Shopware()->Pluginlogger()->addNotice('RatePAY', $exception->getMessage());
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
                $sqlInsert = "INSERT INTO `rpay_ratepay_order_positions` "
                    . "(`s_order_details_id`) "
                    . "VALUES " . $values;
                try {
                    Shopware()->Db()->query($sqlInsert);
                } catch (Exception $exception) {
                    Shopware()->Pluginlogger()->addNotice('RatePAY', $exception->getMessage());
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
                Shopware()->Pluginlogger()->addNotice('RatePAY', "RatePAY: sUserId is empty");

                return;
            }
            Shopware()->Template()->addTemplateDir(dirname(__FILE__) . '/Views/');
            $validation = new Shopware_Plugins_Frontend_RpayRatePay_Component_Validation();

            if ($validation->isRatePAYPayment()) {
                $view->sRegisterFinished = 'false';

                /*
                $view->ratepayValidateTelephoneNumber = $validation->isTelephoneNumberSet() ? 'true' : 'false';
                Shopware()->Pluginlogger()->addNotice('RatePAY', "RatePAY: isTelephoneNumberSet->" . $view->ratepayValidateTelephoneNumber);
                */
                $view->ratepayValidateUST = $validation->isUSTSet() ? 'true' : 'false';
                Shopware()->Pluginlogger()->addNotice('RatePAY', "RatePAY: isUSTSet->" . $view->ratepayValidateUST);
                $view->ratepayValidateCompanyName = $validation->isCompanyNameSet() ? 'true' : 'false';
                Shopware()->Pluginlogger()->addNotice('RatePAY', "RatePAY: isCompanyNameSet->" . $view->ratepayValidateCompanyName);
                $view->ratepayValidateIsB2B = $validation->isCompanyNameSet() || $validation->isUSTSet() ? 'true' : 'false';
                Shopware()->Pluginlogger()->addNotice('RatePAY', "RatePAY: isB2B->" . $view->ratepayValidateIsB2B);
                $view->ratepayValidateIsAddressValid = $validation->isAddressValid() ? 'true' : 'false';
                Shopware()->Pluginlogger()->addNotice('RatePAY', "RatePAY: isAddressValid->" . $view->ratepayValidateIsAddressValid);

                /*
                $view->ratepayValidateIsBirthdayValid = $validation->isBirthdayValid() ? 'true' : 'false';
                Shopware()->Pluginlogger()->addNotice('RatePAY', "RatePAY: isBirthdayValid->" . $view->ratepayValidateIsBirthdayValid);
                $view->ratepayValidateisAgeValid = $validation->isAgeValid() ? 'true' : 'false';
                Shopware()->Pluginlogger()->addNotice('RatePAY', "RatePAY: isAgeValid->" . $view->ratepayValidateisAgeValid);
                */
                $view->ratepayValidateIsBirthdayValid = true;
                $view->ratepayValidateisAgeValid = true;

                $view->ratepayValidateisDebitSet = $validation->isDebitSet() ? 'true' : 'false';
                Shopware()->Pluginlogger()->addNotice('RatePAY', "RatePAY: isDebitSet->" . $view->ratepayValidateisDebitSet);
                $view->ratepayErrorRatenrechner = Shopware()->Session()->ratepayErrorRatenrechner ? 'true' : 'false';
            }
        }

        /**
         * Filters the shown Payments
         * RatePAY-payments will be hidden, if one of the following requirement is not given
         *  - Delivery Address is the same as Billing Address
         *  - The Customer must be over 18 years old
         *  - The Country must be germany
         *  - The Currency must be EUR
         *
         * @param Enlight_Event_EventArgs $arguments
         *
         * @return array
         */
        public function filterPayments(Enlight_Event_EventArgs $arguments)
        {
            $return = $arguments->getReturn();
            $currency = Shopware()->Config()->get('currency');
            if (empty(Shopware()->Session()->sUserId) || empty($currency)) {
                return;
            }

            $profileId = Shopware()->Plugins()->Frontend()->RpayRatePay()->Config()->get('RatePayProfileID');
            $paymentStati = Shopware()->Db()->fetchRow('SELECT * FROM `rpay_ratepay_config` WHERE `profileId`=?', array($profileId));
            $showRate = $paymentStati['rateStatus'] == 2 ? true : false;
            $showDebit = $paymentStati['debitStatus'] == 2 ? true : false;
            $showInvoice = $paymentStati['invoiceStatus'] == 2 ? true : false;


            $validation = new Shopware_Plugins_Frontend_RpayRatePay_Component_Validation();
            if (!$validation->isCountryValid()) {
                $showRate = false;
                $showDebit = false;
                $showInvoice = false;
            }

            if ($validation->isCompanyNameSet() || $validation->isUSTSet()) {
                $showRate = $paymentStati['b2b-rate'] == 'yes' && $showRate ? : false;
                $showDebit = $paymentStati['b2b-debit'] == 'yes' && $showDebit ? : false;
                $showInvoice = $paymentStati['b2b-invoice'] == 'yes' && $showInvoice ? : false;
            }

            if (!$validation->isAddressValid()) {
                Shopware()->Pluginlogger()->addNotice('RatePAY', $paymentStati['address-debit'] == 'yes');
                Shopware()->Pluginlogger()->addNotice('RatePAY', $validation->isCountryValid());
                Shopware()->Pluginlogger()->addNotice('RatePAY', $showDebit);
                Shopware()->Pluginlogger()->addNotice('RatePAY', $paymentStati);


                $showRate = $paymentStati['address-rate'] == 'yes' && $validation->isCountryValid() && $showRate ? : false;
                $showDebit = $paymentStati['address-debit'] == 'yes' && $validation->isCountryValid() && $showDebit ? : false;
                $showInvoice = $paymentStati['address-invoice'] == 'yes' && $validation->isCountryValid() && $showInvoice ? : false;
            }

            if (Shopware()->Session()->RatePAY['hidePayment'] === true) {
                $showRate = false;
                $showDebit = false;
                $showInvoice = false;
            }

            if (Shopware()->Modules()->Basket()) {
                $basket = Shopware()->Modules()->Basket()->sGetAmount();
                $basket = $basket['totalAmount'];
                Shopware()->Pluginlogger()->addNotice('RatePAY', "BasketAmount: $basket");
                if ($basket < $paymentStati['limit-invoice-min'] || $basket > $paymentStati['limit-invoice-max']) {
                    $showInvoice = false;
                }
                if ($basket < $paymentStati['limit-debit-min'] || $basket > $paymentStati['limit-debit-max']) {
                    $showDebit = false;
                }
                if ($basket < $paymentStati['limit-rate-min'] || $basket > $paymentStati['limit-rate-max']) {
                    $showRate = false;
                }
            }

            $user = Shopware()->Models()->find('Shopware\Models\Customer\Customer', Shopware()->Session()->sUserId);
            $paymentModel = Shopware()->Models()->find('Shopware\Models\Payment\Payment', $user->getPaymentId());
            $setToDefaultPayment = false;
            $payments = array();
            foreach ($return as $payment) {
                if ($payment['name'] === 'rpayratepayinvoice' && !$showInvoice) {
                    Shopware()->Pluginlogger()->addNotice('RatePAY', "RatePAY: Filter RatePAY-Invoice");
                    $setToDefaultPayment = $paymentModel->getName() === "rpayratepayinvoice" ? : $setToDefaultPayment;
                    continue;
                }
                if ($payment['name'] === 'rpayratepaydebit' && !$showDebit) {
                    Shopware()->Pluginlogger()->addNotice('RatePAY', "RatePAY: Filter RatePAY-Debit");
                    $setToDefaultPayment = $paymentModel->getName() === "rpayratepaydebit" ? : $setToDefaultPayment;
                    continue;
                }
                if ($payment['name'] === 'rpayratepayrate' && !$showRate) {
                    Shopware()->Pluginlogger()->addNotice('RatePAY', "RatePAY: Filter RatePAY-Rate");
                    $setToDefaultPayment = $paymentModel->getName() === "rpayratepayrate" ? : $setToDefaultPayment;
                    continue;
                }
                $payments[] = $payment;
            }

            if ($setToDefaultPayment) {
                Shopware()->Pluginlogger()->addNotice('RatePAY', $user->getPaymentId());
                $user->setPaymentId(Shopware()->Config()->get('paymentdefault'));
                Shopware()->Models()->persist($user);
                Shopware()->Models()->flush();
                Shopware()->Models()->refresh($user);
                Shopware()->Pluginlogger()->addNotice('RatePAY', $user->getPaymentId());
            }

            return $payments;
        }

        /**
         * Sends a Profile_request and saves the data into the Database
         *
         * @param string $profileId
         * @param string $securityCode
         *
         * @return boolean
         */
        private function getRatepayConfig($profileId, $securityCode, $sandbox)
        {
            $factory = new Shopware_Plugins_Frontend_RpayRatePay_Component_Mapper_ModelFactory();
            $profileRequestModel = $factory->getModel(new Shopware_Plugins_Frontend_RpayRatePay_Component_Model_ProfileRequest());
            $head = $profileRequestModel->getHead();
            $head->setProfileId($profileId);
            $head->setSecurityCode($securityCode);
            $profileRequestModel->setHead($head);
            $requestService = new Shopware_Plugins_Frontend_RpayRatePay_Component_Service_RequestService($sandbox);
            $response = $requestService->xmlRequest($profileRequestModel->toArray());

            if (Shopware_Plugins_Frontend_RpayRatePay_Component_Service_Util::validateResponse('PROFILE_REQUEST', $response)) {
                $data = array(
                    $response->getElementsByTagName('profile-id')->item(0)->nodeValue,
                    $response->getElementsByTagName('activation-status-invoice')->item(0)->nodeValue,
                    $response->getElementsByTagName('activation-status-elv')->item(0)->nodeValue,
                    $response->getElementsByTagName('activation-status-installment')->item(0)->nodeValue,
                    $response->getElementsByTagName('b2b-invoice')->item(0)->nodeValue ? : 'no',
                    $response->getElementsByTagName('b2b-elv')->item(0)->nodeValue ? : 'no',
                    $response->getElementsByTagName('b2b-installment')->item(0)->nodeValue ? : 'no',
                    $response->getElementsByTagName('delivery-address-invoice')->item(0)->nodeValue ? : 'no',
                    $response->getElementsByTagName('delivery-address-elv')->item(0)->nodeValue ? : 'no',
                    $response->getElementsByTagName('delivery-address-installment')->item(0)->nodeValue ? : 'no',
                    $response->getElementsByTagName('tx-limit-invoice-min')->item(0)->nodeValue,
                    $response->getElementsByTagName('tx-limit-elv-min')->item(0)->nodeValue,
                    $response->getElementsByTagName('tx-limit-installment-min')->item(0)->nodeValue,
                    $response->getElementsByTagName('tx-limit-invoice-max')->item(0)->nodeValue,
                    $response->getElementsByTagName('tx-limit-elv-max')->item(0)->nodeValue,
                    $response->getElementsByTagName('tx-limit-installment-max')->item(0)->nodeValue
                );

                $activePayments = '';
                if ($response->getElementsByTagName('activation-status-invoice')->item(0)->nodeValue == 2) {
                    $activePayments = $activePayments == '' ? '"rpayratepayinvoice"' : $activePayments . ', "rpayratepayinvoice"';
                }
                if ($response->getElementsByTagName('activation-status-elv')->item(0)->nodeValue == 2) {
                    $activePayments = $activePayments == '' ? '"rpayratepaydebit"' : $activePayments . ', "rpayratepaydebit"';
                }
                if ($response->getElementsByTagName('activation-status-installment')->item(0)->nodeValue == 2) {
                    $activePayments = $activePayments == '' ? '"rpayratepayrate"' : $activePayments . ', "rpayratepayrate"';
                }

                $updatesql = "UPDATE `s_core_paymentmeans` SET `active` = 1 WHERE `name` in($activePayments)";
                $sql = "REPLACE INTO `rpay_ratepay_config`"
                    . "(`profileId`, `invoiceStatus`,`debitStatus`,`rateStatus`, "
                    . "`b2b-invoice`, `b2b-debit`, `b2b-rate`, "
                    . "`address-invoice`, `address-debit`, `address-rate`, "
                    . "`limit-invoice-min`, `limit-debit-min`, `limit-rate-min`, "
                    . "`limit-invoice-max`, `limit-debit-max`, `limit-rate-max`) "
                    . "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";

                try {
                    $this->clearRuleSet();
                    $this->setRuleSet(
                        'rpayratepayinvoice', 'CURRENCIESISOISNOT', 'EUR'
                    );
                    $this->setRuleSet(
                        'rpayratepaydebit', 'CURRENCIESISOISNOT', 'EUR'
                    );
                    $this->setRuleSet(
                        'rpayratepayrate', 'CURRENCIESISOISNOT', 'EUR'
                    );
                    Shopware()->Db()->query($sql, $data);
                    Shopware()->Db()->query($updatesql);

                    return true;
                } catch (Exception $exception) {
                    Shopware()->Pluginlogger()->addNotice('RatePAY', $exception->getMessage());
                    Shopware()->Db()->query("UPDATE `s_core_paymentmeans` SET `active` =0 WHERE `name` LIKE 'rpayratepay%'");

                    return false;
                }
            }
            else {
                Shopware()->Pluginlogger()->addNotice('RatePAY', 'RatePAY: Profile_Request failed!');
                Shopware()->Db()->query("UPDATE `s_core_paymentmeans` SET `active` =0 WHERE `name` LIKE 'rpayratepay%'");

                return false;
            }
        }

        /**
         * Sets the Ruleset for the given Payment
         *
         * @param string $paymentName
         * @param string $firstRule
         * @param string $firstValue
         */
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

        /**
         * Clears the Ruleset for all RatePAY-Payments
         */
        private function clearRuleSet()
        {
            $sql = "DELETE FROM `s_core_rulesets` "
                . "WHERE `paymentID` IN("
                . "SELECT `id` FROM `s_core_paymentmeans` "
                . "WHERE `name` LIKE 'rpayratepay%'"
                . ") AND `rule1` LIKE 'ORDERVALUE%' OR `rule1` = 'CURRENCIESISOISNOT';";
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
                $this->Path() . 'Views/backend/rpay_ratepay_orderdetail/'
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
