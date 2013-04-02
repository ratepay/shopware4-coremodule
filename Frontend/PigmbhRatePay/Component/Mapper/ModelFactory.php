<?php

/**
 * Checkout
 *
 * @category   PayIntelligent
 * @copyright  Copyright (c) 2013 PayIntelligent GmbH (http://payintelligent.de)
 */
class Shopware_Plugins_Frontend_PigmbhRatePay_Component_Mapper_ModelFactory
{

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
            case is_a($modelName, 'Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentInit'):
                $this->fillPaymentInit($modelName);
                break;
            case is_a($modelName, 'Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentRequest'):
                $this->fillPaymentRequest($modelName);
                break;
            case is_a($modelName, 'Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentConfirm'):
                $this->fillPaymentConfirm($modelName);
                break;
            case is_a($modelName, 'Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_ProfileRequest'):
                $this->fillProfileRequest($modelName);
                break;
            default:
                throw new Exception('The submitted Class is not supported!');
                break;
        }
        return $modelName;
    }

    /**
     * Fills an object of the class Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentInit
     *
     * @param Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentInit $paymentInitModel
     */
    private function fillPaymentInit(Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentInit &$paymentInitModel)
    {
        $config = Shopware()->Plugins()->Frontend()->PigmbhRatePay()->Config();
        $head = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_Head();
        $head->setOperation('PAYMENT_INIT');
        $head->setProfileId($config->get('RatePayProfileID'));
        $head->setSecurityCode($config->get('RatePaySecurityCode'));
        $head->setSystemId(Shopware()->Shop()->getHost());
        $paymentInitModel->setHead($head);
    }

    /**
     * Fills an object of the class Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentRequest
     *
     * @param Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentRequest $paymentRequestModel
     */
    private function fillPaymentRequest(Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentRequest &$paymentRequestModel)
    {
        $config = Shopware()->Plugins()->Frontend()->PigmbhRatePay()->Config();
        $head = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_Head();
        $head->setTransactionId(Shopware()->Session()->RatePAY['transactionId']);
        $head->setOperation('PAYMENT_REQUEST');
        $head->setProfileId($config->get('RatePayProfileID'));
        $head->setSecurityCode($config->get('RatePaySecurityCode'));
        $head->setSystemId(Shopware()->Shop()->getHost());

        $shopUser = Shopware()->Models()->find('Shopware\Models\Customer\Customer', Shopware()->Session()->sUserId);
        $shopCountry = Shopware()->Models()->find('Shopware\Models\Country\Country', $shopUser->getBilling()->getCountryId());
        $customer = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_Customer();

        $shopBillingAddress = $shopUser->getBilling();
        $billingAddress = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_Address();
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


        $shopShippingAddress = $shopUser->getShipping() === null ? : $shopUser->getBilling();
        $shippingAddress = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_Address();
        $shippingAddress->setType('DELIVERY');
        $shippingAddress->setCountryCode($shopCountry->getIso());
        $shippingAddress->setCity($shopShippingAddress->getCity());
        $shippingAddress->setStreet($shopShippingAddress->getStreet());
        $shippingAddress->setStreetNumber($shopShippingAddress->getStreetNumber());
        $shippingAddress->setZipCode($shopShippingAddress->getZipCode());
        $customer->setShippingAddresses($shippingAddress);

        // nur bei ELV
        if (!is_null($shopUser->getDebit())) {
            $bankAccount = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_BankAccount();
            $bankAccount->setBankAccount($shopUser->getDebit()->getAccount());
            $bankAccount->setBankCode($shopUser->getDebit()->getBankCode());
            $bankAccount->setBankName($shopUser->getDebit()->getBankName());
            $bankAccount->setOwner($shopUser->getDebit()->getAccountHolder());
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

        $payment = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_Payment();
        $payment->setAmount($this->getAmount());
        $payment->setCurrency(Shopware()->Currency()->getShortName());
        $payment->setMethod($this->getPaymentMethod());
        if ($this->getPaymentMethod() === 'INSTALLMENT') {
            $payment->setDirectPayType('BANK-TRANSFER');
            $payment->setInstallmentAmount(Shopware()->Session()->RatePAY['ratenrechner']['amount']);
            $payment->setInstallmentNumber(Shopware()->Session()->RatePAY['ratenrechner']['number_of_rates']);
            $payment->setInterestRate(Shopware()->Session()->RatePAY['ratenrechner']['interest_rate']);
            $payment->setLastInstallmentAmount(Shopware()->Session()->RatePAY['ratenrechner']['last_rate']);
        }

        $basket = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_ShoppingBasket();
        $basket->setAmount($this->getAmount());
        $basket->setCurrency(Shopware()->Currency()->getShortName());
        $shopItems = Shopware()->Session()->sOrderVariables['sBasket']['content'];
        $items = array();
        foreach ($shopItems as $shopItem) {
            $item = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_item();
            $item->setArticleName($shopItem['articlename']);
            $item->setArticleNumber($shopItem['articleID']);
            $item->setQuantity($shopItem['quantity']);
            $item->setTaxRate($shopItem['tax_rate']);
            $item->setUnitPriceGross($shopItem['amountnet']);
            $items[] = $item;
        }
        $basket->setItems($items);

        $paymentRequestModel->setHead($head);
        $paymentRequestModel->setCustomer($customer);
        $paymentRequestModel->setPayment($payment);
        $paymentRequestModel->setShoppingBasket($basket);
    }

    /**
     * Fills an object of the class Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentConfirm
     *
     * @param Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentConfirm $paymentConfirmModel
     */
    private function fillPaymentConfirm(Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentConfirm &$paymentConfirmModel)
    {
        $config = Shopware()->Plugins()->Frontend()->PigmbhRatePay()->Config();
        $head = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_Head();
        $head->setOperation('PAYMENT_CONFIRM');
        $head->setProfileId($config->get('RatePayProfileID'));
        $head->setSecurityCode($config->get('RatePaySecurityCode'));
        $head->setSystemId(Shopware()->Shop()->getHost());
        $head->setTransactionId(Shopware()->Session()->RatePAY['transactionId']);
        $paymentConfirmModel->setHead($head);
    }

    /**
     * Fills an object of the class Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_ProfileRequest
     *
     * @param Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_ProfileRequest $profileRequestModel
     */
    private function fillProfileRequest(Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_ProfileRequest &$profileRequestModel)
    {
        $config = Shopware()->Plugins()->Frontend()->PigmbhRatePay()->Config();
        $head = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_Head();
        $head->setOperation('PROFILE_REQUEST');
        $head->setProfileId($config->get('RatePayProfileID'));
        $head->setSecurityCode($config->get('RatePaySecurityCode'));
        $head->setSystemId(Shopware()->Db()->fetchOne("SELECT `host` FROM `s_core_shops` WHERE `default`=1"));
        $profileRequestModel->setHead($head);
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
     * Return the methodname for RatePAY
     *
     * @return string
     */
    public function getPaymentMethod()
    {
        $payment = Shopware()->Session()->sOrderVariables['sUserData']['additional']['payment']['name'];
        switch ($payment) {
            case 'pigmbhratepayinvoice':
                return 'INVOICE';
                break;
            case 'pigmbhratepayrate':
                return 'INSTALLMENT';
                break;
            case 'pigmbhratepaydebit':
                return 'ELV';
                break;
            case 'pigmbhratepayprepayment':
            default:
                return 'PREPAYMENT';
                break;
        }
    }

}
