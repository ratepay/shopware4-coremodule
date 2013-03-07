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

    private $_user;

    public function __construct()
    {
        $this->_user = Shopware()->Models()->find('Shopware\Models\Customer\Customer', Shopware()->Session()->sUserId);
    }

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
        return ($birthday->diff($today)->y > 18) && $this->_isBirthdayValid() && !$this->_isBirthdaySetToDefault();
    }

    /**
     * Checks the birthday to its defaultvalue after registration
     *
     * @return boolean
     */
    private function _isBirthdaySetToDefault()
    {
        $birthday = $this->_user->getBilling()->getBirthday();
        return $birthday->format('Y-m-d') === '-0001-11-30';
    }

    /**
     * Checks the birthday-format
     *
     * @return boolean
     */
    private function _isBirthdayValid()
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

    public function isUSTSet()
    {
        $ust = $this->_user->getBilling()->getVatId();
        return !empty($ust);
    }

    public function isCompanyNameSet()
    {
        $companyName = $this->_user->getBilling()->getCompany();
        return !empty($companyName);
    }

}
