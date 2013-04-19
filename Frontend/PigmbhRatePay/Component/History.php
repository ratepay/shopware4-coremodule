<?php

/**
 * History
 *
 * @category   PayIntelligent
 * @copyright  Copyright (c) 2013 PayIntelligent GmbH (http://payintelligent.de)
 */
class Shopware_Plugins_Frontend_PigmbhRatePay_Component_History
{

    /**
     * Logs the History for an Request
     *
     * @param string $orderId
     * @param string $event
     * @param string $name
     * @param string $articlenumber
     * @param string $quantity
     */
    public function logHistory($orderId, $event, $name = '', $articlenumber = '', $quantity = '')
    {
        $sql = "INSERT INTO `pigmbh_ratepay_order_history` "
                . "(`orderId`, `event`, `articlename`, `articlenumber`, `quantity`) "
                . "VALUES(?, ?, ?, ?, ?)";
        Shopware()->Db()->query($sql, array($orderId, $event, $name, $articlenumber, $quantity));
    }

    /**
     * Returns the stored History for the given Order
     *
     * @param string $orderId
     * @return array
     */
    public function getHistory($orderId)
    {
        $sql = "SELECT * FROM `pigmbh_ratepay_order_history`"
                . " WHERE `orderId`=? "
                . "ORDER BY `id` DESC";
        $history = Shopware()->Db()->fetchAll($sql,array($orderId));
        return $history;
    }

}
