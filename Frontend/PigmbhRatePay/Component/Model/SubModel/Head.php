<?php

/**
 * Head
 *
 * @category   PayIntelligent
 * @copyright  Copyright (c) 2013 PayIntelligent GmbH (http://payintelligent.de)
 */
class Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_Head
{

    /**
     * @var string
     */
    private $_systemId;

    /**
     * @var string
     */
    private $_transactionId = null;

    /**
     * @var string
     */
    private $_profileId;

    /**
     * @var string
     */
    private $_securityCode;

    /**
     * @var string
     */
    private $_operation;

    /**
     * @var string
     */
    private $_operationSubstring = null;

    /**
     * This function returns the value of $_systemId
     *
     * @return string
     */
    public function getSystemId()
    {
        return $this->_systemId;
    }

    /**
     * This function sets the value for $_head
     *
     * @param string $systemId
     */
    public function setSystemId($systemId)
    {
        $this->_systemId = $systemId;
    }

    /**
     * This function returns the value of $_transactionId
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->_transactionId;
    }

    /**
     * This function sets the value for $_transactionId
     *
     * @param string $transactionId
     */
    public function setTransactionId($transactionId)
    {
        $this->_transactionId = $transactionId;
    }

    /**
     * This function returns the value of $_profileId
     *
     * @return string
     */
    public function getProfileId()
    {
        return $this->_profileId;
    }

    /**
     * This function sets the value for $_profileId
     *
     * @param string $profileId
     */
    public function setProfileId($profileId)
    {
        $this->_profileId = $profileId;
    }

    /**
     * This function returns the value of $_securityCode
     *
     * @return string
     */
    public function getSecurityCode()
    {
        return $this->_securityCode;
    }

    /**
     * This function sets the value for $_securityCode
     *
     * @param string $securityCode
     */
    public function setSecurityCode($securityCode)
    {
        $this->_securityCode = $securityCode;
    }

    /**
     * This function returns the value of $_operation
     *
     * @return string
     */
    public function getOperation()
    {
        return $this->_operation;
    }

    /**
     * This function sets the value for $_operation
     *
     * @param string $operation
     */
    public function setOperation($operation)
    {
        $this->_operation = $operation;
    }

    /**
     * This function returns the value of $_operationSubstring
     *
     * @return string
     */
    public function getOperationSubstring()
    {
        return $this->_operationSubstring;
    }

    /**
     * This function sets the value for $_operationSubstring
     *
     * @param string $operationSubstring
     */
    public function setOperationSubstring($operationSubstring)
    {
        $this->_operationSubstring = $operationSubstring;
    }

    /**
     * This function returns all values as Array
     *
     * @return array
     */
    public function toArray()
    {
        $return = array(
            'system-id' => $this->getSystemId(),
            'operation' => $this->getOperation(),
            'credential' => array(
                'profile-id' => $this->getProfileId(),
                'securitycode' => $this->getSecurityCode()
            )
        );
        if ($this->_transactionId != null) {
            $return['transaction-id'] = $this->getTransactionId();
        }
        if ($this->_operationSubstring != null) {
            unset($return['operation']);
            $return['$operation'] = array(
                $this->getOperation(),
                'substring',
                $this->getOperationSubstring()
            );
        }
        return $return;
    }

}
