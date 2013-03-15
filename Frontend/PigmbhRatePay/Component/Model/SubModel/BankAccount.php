<?php

/**
 * BankAccount
 *
 * @category   PayIntelligent
 * @package    Expression package is undefined on line 6, column 18 in Templates/Scripting/PHPClass.php.
 * @copyright  Copyright (c) 2011 PayIntelligent GmbH (http://payintelligent.de)
 */
class Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_BankAccount
{

    /**
     * @var string
     */
    private $_owner;

    /**
     * @var string
     */
    private $_bankAccount;

    /**
     * @var string
     */
    private $_bankCode;

    /**
     * @var string
     */
    private $_bankName;

    /**
     * This function returns the value of $_owner
     *
     * @return string
     */
    public function getOwner()
    {
        return $this->_owner;
    }

    /**
     * This function sets the value for $_owner
     *
     * @param string $owner
     */
    public function setOwner($owner)
    {
        $this->_owner = $owner;
    }

    /**
     * This function returns the value of $_bankAccount
     *
     * @return string
     */
    public function getBankAccount()
    {
        return $this->_bankAccount;
    }

    /**
     * This function sets the value for $_bankAccount
     *
     * @param string $bankAccount
     */
    public function setBankAccount($bankAccount)
    {
        $this->_bankAccount = $bankAccount;
    }

    /**
     * This function returns the value of $_bankCode
     *
     * @return string
     */
    public function getBankCode()
    {
        return $this->_bankCode;
    }

    /**
     * This function sets the value for $_bankCode
     *
     * @param string $bankCode
     */
    public function setBankCode($bankCode)
    {
        $this->_bankCode = $bankCode;
    }

    /**
     * This function returns the value of $_bankName
     *
     * @return string
     */
    public function getBankName()
    {
        return $this->_bankName;
    }

    /**
     * This function sets the value for $_bankName
     *
     * @param string $bankName
     */
    public function setBankName($bankName)
    {
        $this->_bankName = $bankName;
    }

    /**
     * This function returns all values as Array
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'owner' => $this->getOwner(),
            'bank-account' => $this->getBankAccount(),
            'bank-code' => $this->getBankCode(),
            'bank-name' => $this->getBankName()
        );
    }

}
