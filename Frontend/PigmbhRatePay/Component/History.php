<?php

/**
 * History
 *
 * @category   PayIntelligent
 * @copyright  Copyright (c) 2013 PayIntelligent GmbH (http://payintelligent.de)
 */
class Shopware_Plugins_Frontend_PigmbhRatePay_Component_History
{

    public function logHistory($orderId, $event, $name = '', $articlenumber = '', $quantity = '')
    {
        $sql = "INSERT INTO `pigmbh_ratepay_order_history` "
                . "(`orderId`, `event`, `articlename`, `articlenumber`, `quantity`) "
                . "VALUES(?, ?, ?, ?, ?)";
        Shopware()->Db()->query($sql, array($orderId, $event, $name, $articlenumber, $quantity));
    }

    public function getHistory($orderId)
    {
        $sql = "SELECT * FROM `pigmbh_ratepay_order_history`"
                . " WHERE `orderId`=? "
                . "ORDER BY `id` DESC";
        $history = Shopware()->Db()->fetchAll($sql,array($orderId));
        return $history;
    }

}
