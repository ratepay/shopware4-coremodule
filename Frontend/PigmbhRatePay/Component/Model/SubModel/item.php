<?php

/**
 * Item
 *
 * @category   PayIntelligent
 * @copyright  Copyright (c) 2013 PayIntelligent GmbH (http://payintelligent.de)
 */
class Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_item
{

    /**
     * @var string
     */
    private $_articleNumber;

    /**
     * @var string
     */
    private $_articleName;

    /**
     * @var string
     */
    private $_quantity;

    /**
     * @var string
     */
    private $_taxRate;

    /**
     * @var string
     */
    private $_unitPriceGross;

    /**
     * This function returns the value of $_articleName
     *
     * @return string
     */
    public function getArticleName()
    {
        return $this->_articleName;
    }

    /**
     * This function sets the value for $_articleName
     *
     * @param string $articleName
     */
    public function setArticleName($articleName)
    {
        $this->_articleName = $articleName;
    }

    /**
     * This function returns the value of $_articleName
     *
     * @return string
     */
    public function getArticleNumber()
    {
        return $this->_articleNumber;
    }

    /**
     * This function sets the value for $_articleNumber
     *
     * @param string $articleNumber
     */
    public function setArticleNumber($articleNumber)
    {
        $this->_articleNumber = $articleNumber;
    }

    /**
     * This function returns the value of $_quantity
     *
     * @return string
     */
    public function getQuantity()
    {
        return $this->_quantity;
    }

    /**
     * This function sets the value for $_quantity
     *
     * @param string $quantity
     */
    public function setQuantity($quantity)
    {
        $this->_quantity = $quantity;
    }

    /**
     * This function returns the value of $_taxRate
     *
     * @return string
     */
    public function getTaxRate()
    {
        return $this->_taxRate;
    }

    /**
     * This function sets the value for $_taxRate
     *
     * @param string $taxRate
     */
    public function setTaxRate($taxRate)
    {
        $this->_taxRate = number_format((float)$taxRate, 2, '.', '');;
    }

    /**
     * This function returns the value of $_unitPriceGross
     *
     * @return string
     */
    public function getUnitPriceGross()
    {
        return $this->_unitPriceGross;
    }

    /**
     * This function sets the value for $_unitPriceGross
     *
     * @param string $unitPriceGross
     */
    public function setUnitPriceGross($unitPriceGross)
    {
        $this->_unitPriceGross = number_format((float)$unitPriceGross, 2, '.', '');
    }

    /**
     * This function returns all values as Array
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'item' => array(
                '@article-number' => $this->getArticleNumber(),
                '@quantity' => $this->getQuantity(),
                '@tax-rate' => $this->getTaxRate(),
                '@unit-price-gross' => $this->getUnitPriceGross(),
                $this->getArticleName()
            )
        );
    }

}
