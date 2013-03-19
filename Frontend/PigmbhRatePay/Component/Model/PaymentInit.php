<?php

/**
 * paymentInitModel
 *
 * @category   PayIntelligent
 * @copyright  Copyright (c) 2013 PayIntelligent GmbH (http://payintelligent.de)
 */
class Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentInit
{

    /**
     * @var Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_Head
     */
    private $_head;

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
     * This function returns all values as Array
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            '@version' => '1.0',
            '@xmlns' => "urn://www.ratepay.com/payment/1_0",
            'head' => $this->getHead()->toArray()
        );
    }

}
