<?php

/**
 * PaymentRequest
 *
 * @category   PayIntelligent
 * @package    Expression package is undefined on line 6, column 18 in Templates/Scripting/PHPClass.php.
 * @copyright  Copyright (c) 2013 PayIntelligent GmbH (http://payintelligent.de)
 */
class Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentRequest
{

    /**
     * @var Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_Head
     */
    private $_head;

    /**
     * @var Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_Customer
     */
    private $_customer;

    /**
     * @var Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_ShoppingBasket
     */
    private $_shoppingBasket;

    /**
     * @var Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_Payment
     */
    private $_payment;

    /**
     * This function returns the value of $_head
     *
     * @return Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_Head
     */
    public function getHead()
    {
        return $this->_head;
    }

    /**
     * This function sets the value for $_head
     *
     * @param Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_Head $head
     */
    public function setHead(Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_Head $head)
    {
        $this->_head = $head;
    }

    /**
     * This function sets the value for $_customer
     *
     * @return Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_Customer
     */
    public function getCustomer()
    {
        return $this->_customer;
    }

    /**
     * This function returns the value of $_customer
     *
     * @param Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_Customer $customer
     */
    public function setCustomer(Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_Customer $customer)
    {
        $this->_customer = $customer;
    }

    /**
     * This function returns the value of $_shoppingBasket
     *
     * @return Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_ShoppingBasket
     */
    public function getShoppingBasket()
    {
        return $this->_shoppingBasket;
    }

    /**
     * This function sets the value for $_shoppingBasket
     *
     * @param Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_ShoppingBasket $shoppingBasket
     */
    public function setShoppingBasket(Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_ShoppingBasket $shoppingBasket)
    {
        $this->_shoppingBasket = $shoppingBasket;
    }

    /**
     * This function returns the value of $_payment
     *
     * @return Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_Payment
     */
    public function getPayment()
    {
        return $this->_payment;
    }

    /**
     * This function sets the value for $_payment
     *
     * @param Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_Payment $payment
     */
    public function setPayment(Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_Payment $payment)
    {
        $this->_payment = $payment;
    }

    /**
     * This function returns all values as Array
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            '@version' => '1.0',
            '@xmlns' => "urn://www.ratepay.com/payment/1_0",
            'head' => $this->getHead()->toArray(),
            'content' => array(
                'customer' => $this->getCustomer(),
                'shopping-basket' => $this->getShoppingBasket(),
                'payment' => $this->getPayment()
            )
        );
    }

}
