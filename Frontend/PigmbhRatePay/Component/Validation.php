<?php

/**
 * validation
 *
 * @category   PayIntelligent
 * @package    Expression package is undefined on line 6, column 18 in Templates/Scripting/PHPClass.php.
 * @copyright  Copyright (c) 2011 PayIntelligent GmbH (http://payintelligent.de)
 */
class Shopware_Plugins_Frontend_PigmbhRatePay_Component_Validation
{

    /**
     * An Instance of the Shopware-CustomerModel
     *
     * @var Shopware\Models\Customer\Customer
     */
    private $_user;

    /**
     * Constructor
     *
     * Saves the CustomerModel and initiate the Class
     */
    public function __construct()
    {
        $this->_user = Shopware()->Models()->find('Shopware\Models\Customer\Customer', Shopware()->Session()->sUserId);
    }

    /**
     * Checks if the choosen payment is a RatePAY-payment
     *
     * @return boolean
     */
    public function isRatePAYPayment()
    {
        $payment = Shopware()->Models()->find('Shopware\Models\Payment\Payment', $this->_user->getPaymentId());
        return in_array($payment->getName(), array("pigmbhratepayinvoice", "pigmbhratepayrate", "pigmbhratepaydebit"));
    }

    /**
     * Checks the Customers Age for RatePAY payments
     *
     * @return boolean
     */
    public function isAgeValid()
    {
        $today = new DateTime("now");
        $birthday = $this->_user->getBilling()->getBirthday();
        return $birthday->diff($today)->y > 18;
    }

    /**
     * Checks the format of the birthday-value
     *
     * @return boolean
     */
    public function isBirthdayValid()
    {
        $birthday = $this->_user->getBilling()->getBirthday();
        return preg_match("/^\d{4}-\d{2}-\d{2}$/", $birthday->format('Y-m-d')) !== 0;
    }

    /**
     * Checks if the telephoneNumber is Set
     *
     * @return boolean
     */
    public function isTelephoneNumberSet()
    {
        $phone = $this->_user->getBilling()->getPhone();
        return !empty($phone);
    }

    /**
     * Checks if the VatId is set
     *
     * @return boolean
     */
    public function isUSTSet()
    {
        $ust = $this->_user->getBilling()->getVatId();
        return !empty($ust);
    }

    /**
     * Checks if the CompanyName is set
     *
     * @return boolean
     */
    public function isCompanyNameSet()
    {
        $companyName = $this->_user->getBilling()->getCompany();
        return !empty($companyName);
    }

    /**
     * Checks if the Addresses are Equal
     *
     * @return type
     */
    public function isAddressValid()
    {
        $config = Shopware()->Plugins()->Frontend()->PigmbhRatePay()->Config();
        return ($config->get('RatePayDifferentShippingaddress') && !$this->_areAddressesEqual()) || $this->_areAddressesEqual();
    }

    /**
     * Compares the Data of billing- and shippingaddress.
     *
     * @return boolean
     */
    private function _areAddressesEqual()
    {
        $billingAddress = $this->_user->getBilling();
        $shippingAddress = $this->_user->getShipping();
        $classFunctions = array(
            'getCity',
            'getCompany',
            'getCountryId',
            'getDepartment',
            'getFirstname',
            'getLastName',
            'getSalutation',
            'getStateId',
            'getStreet',
            'getStreetNumber',
            'getZipCode'
        );
        $return = true;
        foreach ($classFunctions as $function) {
            if (call_user_func(array($billingAddress, $function)) !== call_user_func(array($shippingAddress, $function))) {
                Shopware()->Log()->Debug('RatePAY: areAddressesEqual-> The value of ' . $function . " differs.");
                $return = false;
            }
        }
        return $return;
    }

    /**
     * Checks if the country is germany
     *
     * @return boolean
     */
    public function isCountryValid()
    {
        $country = Shopware()->Models()->find('Shopware\Models\Country\Country', $this->_user->getBilling()->getCountryId());
        return $country->getIso() === "DE";
    }

    /**
     * Checks if the currency is EUR
     *
     * @return boolean
     */
    public function isCurrencyValid()
    {
        return Shopware()->Shop()->getCurrency()->getCurrency() === "EUR";
    }

}
