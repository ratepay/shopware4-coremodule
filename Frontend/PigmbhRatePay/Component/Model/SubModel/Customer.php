<?php

/**
 * Customer
 *
 * @category   PayIntelligent
 * @copyright  Copyright (c) 2013 PayIntelligent GmbH (http://payintelligent.de)
 */
class Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_Customer
{

    /**
     * @var string
     */
    private $_firstName;

    /**
     * @var string
     */
    private $_lastName;

    /**
     * @var string
     */
    private $_salutaion;

    /**
     * @var string
     */
    private $_title;

    /**
     * @var string
     */
    private $_gender;

    /**
     * @var string
     */
    private $_dateOfBirth;

    /**
     * @var string
     */
    private $_ipAddress;

    /**
     * @var string
     */
    private $_companyName = null;

    /**
     *
     * @var string
     */
    private $_vatId;

    /**
     * @var string
     */
    private $_email;

    /**
     * @var string
     */
    private $_phone;

    /**
     * @var type
     */
    private $_billingAddresses;

    /**
     * @var type
     */
    private $_shippingAddresses;

    /**
     * @var Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_BankAccount
     */
    private $_bankAccount = null;

    /**
     * @var string
     */
    private $_nationality;

    /**
     * This function returns the value of $_firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->_firstName;
    }

    /**
     * This function sets the value for $_firstName
     *
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->_firstName = $firstName;
    }

    /**
     * This function returns the value of $_lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->_lastName;
    }

    /**
     * This function sets the value for $_lastName
     *
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->_lastName = $lastName;
    }

    /**
     * This function returns the value of $_salutaion
     *
     * @return string
     */
    public function getSalutaion()
    {
        return $this->_salutaion;
    }

    /**
     * This function sets the value for $_salutaion
     *
     * @param string $salutaion
     */
    public function setSalutaion($salutaion)
    {
        $this->_salutaion = $salutaion;
    }

    /**
     * This function returns the value of $_title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * This function sets the value for $_title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->_title = $title;
    }

    /**
     * This function returns the value of $_gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->_gender;
    }

    /**
     * This function sets the value for $_gender
     *
     * @param string $gender
     */
    public function setGender($gender)
    {
        $this->_gender = $gender;
    }

    /**
     * This function returns the value of $_dateOfBirth
     *
     * @return string
     */
    public function getDateOfBirth()
    {
        return $this->_dateOfBirth;
    }

    /**
     * This function sets the value for $_dateOfBirth
     *
     * @param string $dateOfBirth
     */
    public function setDateOfBirth($dateOfBirth)
    {
        $this->_dateOfBirth = $dateOfBirth;
    }

    /**
     * This function returns the value of $_ipAddress
     *
     * @return string
     */
    public function getIpAddress()
    {
        return $this->_ipAddress;
    }

    /**
     * This function sets the value for $_ipAddress
     *
     * @param string $ipAddress
     */
    public function setIpAddress($ipAddress)
    {
        $this->_ipAddress = $ipAddress;
    }

    /**
     * This function returns the value of $_companyName
     *
     * @return string
     */
    public function getCompanyName()
    {
        return $this->_companyName;
    }

    /**
     * This function sets the value for $_companyName
     *
     * @param string $companyName
     */
    public function setCompanyName($companyName)
    {
        $this->_companyName = $companyName;
    }

    /**
     * This function returns the value of $_email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->_email;
    }

    /**
     * This function sets the value for $_email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->_email = $email;
    }

    /**
     * This function returns the value of $_phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->_phone;
    }

    /**
     * This function sets the value for $_phone
     *
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->_phone = $phone;
    }

    /**
     * This function returns the value of $_billingAddresses
     *
     * @return string
     */
    public function getBillingAddresses()
    {
        return $this->_billingAddresses;
    }

    /**
     * This function sets the value for $_billingAddresses
     *
     * @param type $billingAddresses
     */
    public function setBillingAddresses($billingAddresses)
    {
        $this->_billingAddresses = $billingAddresses;
    }

    /**
     * This function returns the value of $_shippingAddresses
     *
     * @return string
     */
    public function getShippingAddresses()
    {
        return $this->_shippingAddresses;
    }

    /**
     * This function sets the value for $_shippingAddresses
     *
     * @param type $shippingAddresses
     */
    public function setShippingAddresses($shippingAddresses)
    {
        $this->_shippingAddresses = $shippingAddresses;
    }

    /**
     * This function returns the value of $_bankAccount
     *
     * @return Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_BankAccount
     */
    public function getBankAccount()
    {
        return $this->_bankAccount;
    }

    /**
     * This function sets the value for $_bankAccount
     *
     * @param Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_BankAccount $bankAccount
     */
    public function setBankAccount(Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_BankAccount $bankAccount)
    {
        $this->_bankAccount = $bankAccount;
    }

    /**
     * This function returns the value of $_nationality
     *
     * @return string
     */
    public function getNationality()
    {
        return $this->_nationality;
    }

    /**
     * This function sets the value for $_nationality
     *
     * @param string $nationality
     */
    public function setNationality($nationality)
    {
        $this->_nationality = $nationality;
    }

    /**
     * This function returns the value of $_vatId
     *
     * @return string
     */
    public function getVatId()
    {
        return $this->_vatId;
    }

    /**
     * This function sets the value for $_vatId
     *
     * @param string $vatId
     */
    public function setVatId($vatId)
    {
        $this->_vatId = $vatId;
    }

    /**
     * This function returns all values as Array
     *
     * @return array
     */
    public function toArray()
    {
        $return = array(
            'first-name' => $this->getFirstName(),
            'last-name' => $this->getLastName(),
            'salutation' => $this->getSalutaion(),
            'title' => $this->getTitle(),
            'gender' => $this->getGender(),
            'date-of-birth' => $this->getDateOfBirth(),
            'ip-address' => $this->getIpAddress(),
            'contacts' => array(
                'email' => $this->getEmail(),
                'phone' => array(
                    'direct-dial' => $this->getPhone()
                )
            ),
            'addresses' => array(
                'address' => array(
                    $this->getBillingAddresses(),
                    $this->getShippingAddresses()
                )
            )
        );

        if ($this->_companyName != null && $this->_vatId != null) {
            $return['company-name'] = $this->getCompanyName();
            $return['vat-id'] = $this->getVatId();
        }
        if ($this->_bankAccount != null) {
            $return['bank-account'] = $this->getCompanyName();
        }
        return $return;
    }

}
