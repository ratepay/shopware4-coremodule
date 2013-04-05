<?php

/**
 * PigmbhRatepayLogging
 *
 * @category   PayIntelligent
 * @copyright  Copyright (c) 2011 PayIntelligent GmbH (http://payintelligent.de)
 */
class Shopware_Controllers_Backend_PigmbhRatepayOrderDetail extends Shopware_Controllers_Backend_ExtJs
{

    /**
     * index action is called if no other action is triggered
     * @return void
     */
    public function init()
    {

    }

    /**
     * This Action loads the data from the datebase into the backendview
     */
    public function loadPositionStoreAction()
    {
        $orderId = $this->Request()->getParam("orderId");

        $sql = "SELECT `articleID`, `name`, `articleordernumber`, `price`, `quantity`,`delivered`, `cancelled`, `returned` "
                . "FROM `s_order_details` AS detail "
                . "INNER JOIN `pigmbh_ratepay_order_positions` AS ratepay ON detail.`id`=ratepay.`s_order_details_id` "
                . "WHERE detail.`orderId`=? "
                . "ORDER BY detail.`id`;";

        $data = Shopware()->Db()->fetchAll($sql, array($orderId));
        $total = Shopware()->Db()->fetchOne("SELECT count(*) FROM `s_order_details` WHERE `s_order_details`.`orderId`=?;", array($orderId));

        $this->View()->assign(array(
            "data" => $data,
            "total" => $total,
            "success" => true
                )
        );
    }

}
