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
 * Checkout
 *
 * @category   RatePAY
 * @copyright  Copyright (c) 2013 PayIntelligent GmbH (http://payintelligent.de)
 */
class Shopware_Plugins_Frontend_RpayRatePay_Component_Mapper_ModelFactory
{

    private $_transactionId;

    /**
     * Gets the TransactionId for Requests
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->_transactionId;
    }

    /**
     * Sets the TransactionId for Requests
     *
     * @param string $transactionId
     */
    public function setTransactionId($transactionId)
    {
        $this->_transactionId = $transactionId;
    }

    /**
     * Expects an instance of a paymentmodel and fill it with shopdata
     *
     * @param ObjectToBeFilled $modelName
     * @return filledObjectGivenToTheFunction
     * @throws Exception The submitted Class is not supported!
     */
    public function getModel($modelName)
    {
        switch ($modelName) {
            case is_a($modelName, 'Shopware_Plugins_Frontend_RpayRatePay_Component_Model_PaymentInit'):
                $this->fillPaymentInit($modelName);
                break;
            case is_a($modelName, 'Shopware_Plugins_Frontend_RpayRatePay_Component_Model_PaymentRequest'):
                $this->fillPaymentRequest($modelName);
                break;
            case is_a($modelName, 'Shopware_Plugins_Frontend_RpayRatePay_Component_Model_PaymentConfirm'):
                $this->fillPaymentConfirm($modelName);
                break;
            case is_a($modelName, 'Shopware_Plugins_Frontend_RpayRatePay_Component_Model_ProfileRequest'):
                $this->fillProfileRequest($modelName);
                break;
            case is_a($modelName, 'Shopware_Plugins_Frontend_RpayRatePay_Component_Model_ConfirmationDelivery'):
                $this->fillConfirmationDelivery($modelName);
                break;
            case is_a($modelName, 'Shopware_Plugins_Frontend_RpayRatePay_Component_Model_PaymentChange'):
                $this->fillPaymentChange($modelName);
                break;
            default:
                throw new Exception('The submitted Class is not supported!');
                break;
        }
        return $modelName;
    }

    /**
     * Fills an object of the class Shopware_Plugins_Frontend_RpayRatePay_Component_Model_PaymentInit
     *
     * @param Shopware_Plugins_Frontend_RpayRatePay_Component_Model_PaymentInit $paymentInitModel
     */
    private function fillPaymentInit(Shopware_Plugins_Frontend_RpayRatePay_Component_Model_PaymentInit &$paymentInitModel)
    {
        $config = Shopware()->Plugins()->Frontend()->RpayRatePay()->Config();
        $head = new Shopware_Plugins_Frontend_RpayRatePay_Component_Model_SubModel_Head();
        $head->setOperation('PAYMENT_INIT');
        $head->setProfileId($config->get('RatePayProfileID'));
        $head->setSecurityCode($config->get('RatePaySecurityCode'));
        $head->setSystemId(Shopware()->Shop()->getHost());
        $paymentInitModel->setHead($head);
    }

    /**
     * Fills an object of the class Shopware_Plugins_Frontend_RpayRatePay_Component_Model_PaymentRequest
     *
     * @param Shopware_Plugins_Frontend_RpayRatePay_Component_Model_PaymentRequest $paymentRequestModel
     */
    private function fillPaymentRequest(Shopware_Plugins_Frontend_RpayRatePay_Component_Model_PaymentRequest &$paymentRequestModel)
    {
        $config = Shopware()->Plugins()->Frontend()->RpayRatePay()->Config();
        $method = Shopware_Plugins_Frontend_RpayRatePay_Component_Service_Util::getPaymentMethod(Shopware()->Session()->sOrderVariables['sUserData']['additional']['payment']['name']);
        $encryption = new Shopware_Plugins_Frontend_RpayRatePay_Component_Encryption_ShopwareEncryption();


        $head = new Shopware_Plugins_Frontend_RpayRatePay_Component_Model_SubModel_Head();
        $head->setTransactionId(Shopware()->Session()->RatePAY['transactionId']);
        $head->setOperation('PAYMENT_REQUEST');
        $head->setProfileId($config->get('RatePayProfileID'));
        $head->setSecurityCode($config->get('RatePaySecurityCode'));
        $head->setSystemId(Shopware()->Shop()->getHost());

        $shopUser = Shopware()->Models()->find('Shopware\Models\Customer\Customer', Shopware()->Session()->sUserId);
        $shopCountry = Shopware()->Models()->find('Shopware\Models\Country\Country', $shopUser->getBilling()->getCountryId());
        $customer = new Shopware_Plugins_Frontend_RpayRatePay_Component_Model_SubModel_Customer();

        $shopBillingAddress = $shopUser->getBilling();
        $billingAddress = new Shopware_Plugins_Frontend_RpayRatePay_Component_Model_SubModel_Address();
        $billingAddress->setFirstName($shopBillingAddress->getFirstName());
        $billingAddress->setLastName($shopBillingAddress->getLastName());
        $billingAddress->setSalutation($shopBillingAddress->getSalutation());
        $billingAddress->setCompany($shopBillingAddress->getCompany());
        $billingAddress->setType('BILLING');
        $billingAddress->setCountryCode($shopCountry->getIso());
        $billingAddress->setCity($shopBillingAddress->getCity());
        $billingAddress->setStreet($shopBillingAddress->getStreet());
        $billingAddress->setStreetNumber($shopBillingAddress->getStreetNumber());
        $billingAddress->setZipCode($shopBillingAddress->getZipCode());
        $customer->setBillingAddresses($billingAddress);


        $shopShippingAddress = $shopUser->getShipping() !== null ? $shopUser->getShipping() : $shopUser->getBilling();
        $shippingAddress = new Shopware_Plugins_Frontend_RpayRatePay_Component_Model_SubModel_Address();
        $shippingAddress->setType('DELIVERY');
        $shippingAddress->setCountryCode($shopCountry->getIso());
        $shippingAddress->setCity($shopShippingAddress->getCity());
        $shippingAddress->setStreet($shopShippingAddress->getStreet());
        $shippingAddress->setStreetNumber($shopShippingAddress->getStreetNumber());
        $shippingAddress->setZipCode($shopShippingAddress->getZipCode());
        $customer->setShippingAddresses($shippingAddress);

        // nur bei ELV
        if ($method === 'ELV') {
            $bankAccount = new Shopware_Plugins_Frontend_RpayRatePay_Component_Model_SubModel_BankAccount();
            if ($config->get('RatePayBankData') == true) {
                $bankdata = $encryption->loadBankdata(Shopware()->Session()->sUserId);
                $bankAccount->setBankAccount($bankdata['account']);
                $bankAccount->setBankCode($bankdata['bankcode']);
                $bankAccount->setBankName($bankdata['bankname']);
                $bankAccount->setOwner($bankdata['bankholder']);
            } else {
                $bankAccount->setBankAccount(Shopware()->Session()->RatePAY['bankdata']['account']);
                $bankAccount->setBankCode(Shopware()->Session()->RatePAY['bankdata']['bankcode']);
                $bankAccount->setBankName(Shopware()->Session()->RatePAY['bankdata']['bankname']);
                $bankAccount->setOwner(Shopware()->Session()->RatePAY['bankdata']['bankholder']);
            }
            $customer->setBankAccount($bankAccount);
        }
        $customer->setCompanyName($shopBillingAddress->getCompany());
        $customer->setVatId($shopBillingAddress->getVatId());
        $customer->setDateOfBirth($shopBillingAddress->getBirthday()->format('Y-m-d'));
        $customer->setEmail($shopUser->getEmail());
        $customer->setFirstName($shopBillingAddress->getFirstName());
        $customer->setLastName($shopBillingAddress->getLastName());
        $gender = 'U';
        if ($shopBillingAddress->getSalutation() === 'mr') {
            $gender = 'M';
        } else if ($shopBillingAddress->getSalutation() === 'ms.') {
            $gender = 'F';
        }
        $customer->setGender($gender);
        $customer->setSalutaion($shopBillingAddress->getSalutation());
        $customer->setPhone($shopBillingAddress->getPhone());
        $customer->setNationality($shopCountry->getIso());

        $payment = new Shopware_Plugins_Frontend_RpayRatePay_Component_Model_SubModel_Payment();
        $payment->setAmount($this->getAmount());
        $payment->setCurrency(Shopware()->Currency()->getShortName());
        $payment->setMethod($method);
        if ($method === 'INSTALLMENT') {
            $payment->setAmount(Shopware()->Session()->RatePAY['ratenrechner']['total_amount']);
            $payment->setDirectPayType('BANK-TRANSFER');
            $payment->setInstallmentAmount(Shopware()->Session()->RatePAY['ratenrechner']['rate']);
            $payment->setInstallmentNumber(Shopware()->Session()->RatePAY['ratenrechner']['number_of_rates']);
            $payment->setInterestRate(Shopware()->Session()->RatePAY['ratenrechner']['interest_rate']);
            $payment->setLastInstallmentAmount(Shopware()->Session()->RatePAY['ratenrechner']['last_rate']);
        }

        $basket = new Shopware_Plugins_Frontend_RpayRatePay_Component_Model_SubModel_ShoppingBasket();
        $basket->setAmount($this->getAmount());
        $basket->setCurrency(Shopware()->Currency()->getShortName());
        $shopItems = Shopware()->Session()->sOrderVariables['sBasket']['content'];
        $items = array();
        foreach ($shopItems as $shopItem) {
            $item = new Shopware_Plugins_Frontend_RpayRatePay_Component_Model_SubModel_item();
            $item->setArticleName($shopItem['articlename']);
            $item->setArticleNumber($shopItem['ordernumber']);
            $item->setQuantity($shopItem['quantity']);
            $item->setTaxRate($shopItem['tax_rate']);
            $item->setUnitPriceGross($shopItem['priceNumeric']);
            $items[] = $item;
        }
        if (Shopware()->Session()->sOrderVariables['sBasket']['sShippingcosts'] > 0) {
            $items[] = $this->getShippingAsItem(
                    Shopware()->Session()->sOrderVariables['sBasket']['sShippingcosts'], Shopware()->Session()->sOrderVariables['sBasket']['sShippingcostsTax']
            );
        }
        $basket->setItems($items);

        $paymentRequestModel->setHead($head);
        $paymentRequestModel->setCustomer($customer);
        $paymentRequestModel->setPayment($payment);
        $paymentRequestModel->setShoppingBasket($basket);
    }

    /**
     * Fills an object of the class Shopware_Plugins_Frontend_RpayRatePay_Component_Model_PaymentConfirm
     *
     * @param Shopware_Plugins_Frontend_RpayRatePay_Component_Model_PaymentConfirm $paymentConfirmModel
     */
    private function fillPaymentConfirm(Shopware_Plugins_Frontend_RpayRatePay_Component_Model_PaymentConfirm &$paymentConfirmModel)
    {
        $config = Shopware()->Plugins()->Frontend()->RpayRatePay()->Config();
        $head = new Shopware_Plugins_Frontend_RpayRatePay_Component_Model_SubModel_Head();
        $head->setOperation('PAYMENT_CONFIRM');
        $head->setProfileId($config->get('RatePayProfileID'));
        $head->setSecurityCode($config->get('RatePaySecurityCode'));
        $head->setSystemId(Shopware()->Shop()->getHost());
        $head->setTransactionId(Shopware()->Session()->RatePAY['transactionId']);
        $paymentConfirmModel->setHead($head);
    }

    /**
     * Fills an object of the class Shopware_Plugins_Frontend_RpayRatePay_Component_Model_ProfileRequest
     *
     * @param Shopware_Plugins_Frontend_RpayRatePay_Component_Model_ProfileRequest $profileRequestModel
     */
    private function fillProfileRequest(Shopware_Plugins_Frontend_RpayRatePay_Component_Model_ProfileRequest &$profileRequestModel)
    {
        $config = Shopware()->Plugins()->Frontend()->RpayRatePay()->Config();
        $head = new Shopware_Plugins_Frontend_RpayRatePay_Component_Model_SubModel_Head();
        $head->setOperation('PROFILE_REQUEST');
        $head->setProfileId($config->get('RatePayProfileID'));
        $head->setSecurityCode($config->get('RatePaySecurityCode'));
        $head->setSystemId(Shopware()->Db()->fetchOne("SELECT `host` FROM `s_core_shops` WHERE `default`=1"));
        $profileRequestModel->setHead($head);
    }

    /**
     * Fills an object of the class Shopware_Plugins_Frontend_RpayRatePay_Component_Model_ConfirmationDelivery
     *
     * @param Shopware_Plugins_Frontend_RpayRatePay_Component_Model_ConfirmationDelivery $confirmationDeliveryModel
     */
    private function fillConfirmationDelivery(Shopware_Plugins_Frontend_RpayRatePay_Component_Model_ConfirmationDelivery &$confirmationDeliveryModel)
    {
        $config = Shopware()->Plugins()->Frontend()->RpayRatePay()->Config();
        $head = new Shopware_Plugins_Frontend_RpayRatePay_Component_Model_SubModel_Head();
        $head->setOperation('CONFIRMATION_DELIVER');
        $head->setProfileId($config->get('RatePayProfileID'));
        $head->setSecurityCode($config->get('RatePaySecurityCode'));
        $head->setSystemId(Shopware()->Db()->fetchOne("SELECT `host` FROM `s_core_shops` WHERE `default`=1"));
        $confirmationDeliveryModel->setHead($head);
    }

    /**
     * Fills an object of the class Shopware_Plugins_Frontend_RpayRatePay_Component_Model_ConfirmationDelivery
     *
     * @param Shopware_Plugins_Frontend_RpayRatePay_Component_Model_PaymentChange $paymentChangeModel
     */
    private function fillPaymentChange(Shopware_Plugins_Frontend_RpayRatePay_Component_Model_PaymentChange &$paymentChangeModel)
    {
        $config = Shopware()->Plugins()->Frontend()->RpayRatePay()->Config();
        $encryption = new Shopware_Plugins_Frontend_RpayRatePay_Component_Encryption_ShopwareEncryption();
        $head = new Shopware_Plugins_Frontend_RpayRatePay_Component_Model_SubModel_Head();
        $head->setOperation('PAYMENT_CHANGE');
        $head->setTransactionId($this->_transactionId);
        $head->setProfileId($config->get('RatePayProfileID'));
        $head->setSecurityCode($config->get('RatePaySecurityCode'));
        $head->setSystemId(Shopware()->Db()->fetchOne("SELECT `host` FROM `s_core_shops` WHERE `default`=1"));

        $order = Shopware()->Db()->fetchRow("SELECT * FROM `s_order` WHERE `transactionID`=?", array($this->_transactionId));

        $shopUser = Shopware()->Models()->find('Shopware\Models\Customer\Customer', $order['userID']);
        $shopCountry = Shopware()->Models()->find('Shopware\Models\Country\Country', $shopUser->getBilling()->getCountryId());
        $customer = new Shopware_Plugins_Frontend_RpayRatePay_Component_Model_SubModel_Customer();

        $shopBillingAddress = $shopUser->getBilling();
        $billingAddress = new Shopware_Plugins_Frontend_RpayRatePay_Component_Model_SubModel_Address();
        $billingAddress->setFirstName($shopBillingAddress->getFirstName());
        $billingAddress->setLastName($shopBillingAddress->getLastName());
        $billingAddress->setSalutation($shopBillingAddress->getSalutation());
        $billingAddress->setCompany($shopBillingAddress->getCompany());
        $billingAddress->setType('BILLING');
        $billingAddress->setCountryCode($shopCountry->getIso());
        $billingAddress->setCity($shopBillingAddress->getCity());
        $billingAddress->setStreet($shopBillingAddress->getStreet());
        $billingAddress->setStreetNumber($shopBillingAddress->getStreetNumber());
        $billingAddress->setZipCode($shopBillingAddress->getZipCode());
        $customer->setBillingAddresses($billingAddress);


        $shopShippingAddress = $shopUser->getShipping() === null ? $shopUser->getBilling() : $shopUser->getShipping();
        $shippingAddress = new Shopware_Plugins_Frontend_RpayRatePay_Component_Model_SubModel_Address();
        $shippingAddress->setType('DELIVERY');
        $shippingAddress->setCountryCode($shopCountry->getIso());
        $shippingAddress->setCity($shopShippingAddress->getCity());
        $shippingAddress->setStreet($shopShippingAddress->getStreet());
        $shippingAddress->setStreetNumber($shopShippingAddress->getStreetNumber());
        $shippingAddress->setZipCode($shopShippingAddress->getZipCode());
        $customer->setShippingAddresses($shippingAddress);

        // nur bei ELV
        if ($encryption->isBankdataSetForUser($order['userID'])) {
            $bankdata = $encryption->loadBankdata($order['userID']);
            $bankAccount = new Shopware_Plugins_Frontend_RpayRatePay_Component_Model_SubModel_BankAccount();
            $bankAccount->setBankAccount($bankdata['account']);
            $bankAccount->setBankCode($bankdata['bankcode']);
            $bankAccount->setBankName($bankdata['bankname']);
            $bankAccount->setOwner($bankdata['bankholder']);
            $customer->setBankAccount($bankAccount);
        }
        $customer->setCompanyName($shopBillingAddress->getCompany());
        $customer->setVatId($shopBillingAddress->getVatId());
        $customer->setDateOfBirth($shopBillingAddress->getBirthday()->format('Y-m-d'));
        $customer->setEmail($shopUser->getEmail());
        $customer->setFirstName($shopBillingAddress->getFirstName());
        $customer->setLastName($shopBillingAddress->getLastName());
        $gender = 'U';
        if ($shopBillingAddress->getSalutation() === 'mr') {
            $gender = 'M';
        } else if ($shopBillingAddress->getSalutation() === 'ms.') {
            $gender = 'F';
        }
        $customer->setGender($gender);
        $customer->setSalutaion($shopBillingAddress->getSalutation());
        $customer->setPhone($shopBillingAddress->getPhone());
        $customer->setNationality($shopCountry->getIso());

        $order = Shopware()->Db()->fetchRow("SELECT `name`,`currency` FROM `s_order` "
                . "INNER JOIN `s_core_paymentmeans` ON `s_core_paymentmeans`.`id` = `s_order`.`paymentID` "
                . "WHERE `s_order`.`transactionID`=?;", array($this->_transactionId));

        $payment = new Shopware_Plugins_Frontend_RpayRatePay_Component_Model_SubModel_Payment();
        $payment->setMethod(Shopware_Plugins_Frontend_RpayRatePay_Component_Service_Util::getPaymentMethod($order['name']));
        $payment->setCurrency($order['currency']);

        $paymentChangeModel->setPayment($payment);
        $paymentChangeModel->setHead($head);
        $paymentChangeModel->setCustomer($customer);
    }

    /**
     * Return the full amount to pay.
     *
     * @return float
     */
    public function getAmount()
    {
        $user = Shopware()->Session()->sOrderVariables['sUserData'];
        $basket = Shopware()->Session()->sOrderVariables['sBasket'];
        if (!empty($user['additional']['charge_vat'])) {
            return empty($basket['AmountWithTaxNumeric']) ? $basket['AmountNumeric'] : $basket['AmountWithTaxNumeric'];
        } else {
            return $basket['AmountNetNumeric'];
        }
    }

    /**
     * Returns the Shippingcosts as Item
     *
     * @param string $amount
     * @param string $tax
     * @return \Shopware_Plugins_Frontend_RpayRatePay_Component_Model_SubModel_item
     */
    private function getShippingAsItem($amount, $tax)
    {
        $item = new Shopware_Plugins_Frontend_RpayRatePay_Component_Model_SubModel_item();
        $item->setArticleName('Shipping');
        $item->setArticleNumber('Shipping');
        $item->setQuantity(1);
        $item->setTaxRate($tax);
        $item->setUnitPriceGross($amount);
        return $item;
    }

}
