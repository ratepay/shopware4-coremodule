<?php

/**
 * PigmbhRatepayLogging
 *
 * @category   PayIntelligent
 * @copyright  Copyright (c) 2011 PayIntelligent GmbH (http://payintelligent.de)
 */
class Shopware_Controllers_Backend_PigmbhRatepayLogging extends Shopware_Controllers_Backend_ExtJs
{

    /**
     * index action is called if no other action is triggered
     * @return void
     */
    public function indexAction()
    {
        $this->View()->loadTemplate("backend/pigmbh_ratepay_logging/app.js");
        $this->View()->assign("title", "RatePAY-Logging");
    }

    /**
     * This Action loads the loggingdata from the datebase into the backendview
     */
    public function loadStoreAction()
    {
        $start = intval($this->Request()->getParam("start"));
        $limit = intval($this->Request()->getParam("limit"));

        $data = Shopware()->Db()->select()->from("pigmbh_ratepay_logging")->limit($limit, $start)->query();

        $store = array();
        foreach ($data as $row) {
            $row['request'] = preg_replace("/>(\/*)</", ">\n<", $row['request']);
            $row['response'] = preg_replace("/>(\/*)</", ">\n<", $row['response']);
            $store[] = $row;
        }
        $total = Shopware()->Db()->fetchOne("SELECT count(*) FROM `pigmbh_ratepay_logging`");
        $this->View()->assign(array(
            "data" => $store,
            "total" => $total,
            "success" => true
                )
        );
    }

}
