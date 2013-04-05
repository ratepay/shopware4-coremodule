<?php

/**
 * PigmbhRatepayLogging
 *
 * @category   PayIntelligent
 * @copyright  Copyright (c) 2011 PayIntelligent GmbH (http://payintelligent.de)
 */
class Shopware_Controllers_Backend_PigmbhRatepayOrderDetail extends Shopware_Controllers_Backend_ExtJs
{

    private $_config;
    private $_modelFactory;
    private $_request;

    /**
     * index action is called if no other action is triggered
     * @return void
     */
    public function init()
    {
        $this->_config = Shopware()->Plugins()->Frontend()->PigmbhRatePay()->Config();
        $this->_modelFactory = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Mapper_ModelFactory();
        $this->_request = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Service_RequestService($this->_config->get('RatePaySandbox'));
    }

    /**
     * This Action loads the data from the datebase into the backendview
     */
    public function loadPositionStoreAction()
    {
        $orderId = $this->Request()->getParam("orderId");

        $sql = "SELECT `articleID`, `name`, `articleordernumber`, `price`, `quantity`, (`quantity` - `delivered` - `cancelled`) AS `quantityDeliver`,(`delivered`) AS `quantityReturn`,`delivered`, `cancelled`, `returned`, `tax_rate` "
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

    public function deliverItemsAction()
    {
        $orderId = $this->Request()->getParam("orderId");
        $items = json_decode($this->Request()->getParam("items"));

        $order = Shopware()->Db()->fetchRow("SELECT * FROM `s_order` WHERE `id`=?", array($orderId));

        $basketItems = array();
        foreach ($items as $item) {
            $basketItem = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_item();
            $basketItem->setArticleName($item->name);
            $basketItem->setArticleNumber($item->articlenumber);
            $basketItem->setQuantity($item->quantity);
            $basketItem->setTaxRate($item->taxRate);
            $basketItem->setUnitPriceGross($item->price);
            $basketItems[] = $basketItem;
        }

        $basket = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_ShoppingBasket();
        $basket->setAmount($order["invoice_amount"]);
        $basket->setCurrency($order["currency"]);
        $basket->setItems($basketItems);

        $confirmationDeliveryModel = $this->_modelFactory->getModel(new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_ConfirmationDelivery());
        $confirmationDeliveryModel->setShoppingBasket($basket);
        $head = $confirmationDeliveryModel->getHead();
        $head->setTransactionId($order['transactionID']);
        $confirmationDeliveryModel->setHead($head);
        $response = $this->_request->xmlRequest($confirmationDeliveryModel->toArray());
        $result = Shopware_Plugins_Frontend_PigmbhRatePay_Component_Service_Util::validateResponse('CONFIRMATION_DELIVER', $response);
        if ($result === true) {
            foreach ($items as $item) {
                $bind = array(
                    'delivered' => $item->delivered + $item->quantity
                );
                $this->updateItem($orderId, $item->articlenumber, $bind);
            }
        }

        $this->View()->assign(array(
            "orderID" => $orderId,
            "result" => $result,
            "success" => true
                )
        );
    }

    public function cancelItemsAction()
    {
        $orderId = $this->Request()->getParam("orderId");
        $items = json_decode($this->Request()->getParam("items"));
        $order = Shopware()->Db()->fetchRow("SELECT * FROM `s_order` WHERE `id`=?", array($orderId));

        $basketItems = array();
        foreach ($items as $item) {
            $basketItem = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_item();
            if($item->quantity <= 0){
                continue;
            }
            $basketItem->setArticleName($item->name);
            $basketItem->setArticleNumber($item->articlenumber);
            $basketItem->setQuantity($item->quantity);
            $basketItem->setTaxRate($item->taxRate);
            $basketItem->setUnitPriceGross($item->price);
            $basketItems[] = $basketItem;
        }

        $basket = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_ShoppingBasket();
        $basket->setAmount($order["invoice_amount"]);
        $basket->setCurrency($order['currency']);
        $basket->setItems($basketItems);

        $this->_modelFactory->setTransactionId($order['transactionID']);
        $paymentChange = $this->_modelFactory->getModel(new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentChange());
        $head = $paymentChange->getHead();
        $head->setOperationSubstring('partial-cancellation');
        $paymentChange->setHead($head);
        $paymentChange->setShoppingBasket($basket);

        $response = $this->_request->xmlRequest($paymentChange->toArray());
        $result = Shopware_Plugins_Frontend_PigmbhRatePay_Component_Service_Util::validateResponse('PAYMENT_CHANGE', $response);
        if ($result === true) {
            foreach ($items as $item) {
                $bind = array(
                    'cancelled' => $item->cancelled + $item->cancelledItems
                );
                $this->updateItem($orderId, $item->articlenumber, $bind);
            }
        }

        if ($result === true) {
            foreach ($items as $item) {
                $bind = array(
                    'cancelled' => $item->cancelled + $item->quantity
                );
                $this->updateItem($orderId, $item->articlenumber, $bind);
            }
        }

        $this->View()->assign(array(
            "orderID" => $orderId,
            "result" => $result,
            "success" => true
                )
        );
    }

    private function updateItem($orderID, $articleordernumber, $bind)
    {
        $positionId = Shopware()->Db()->fetchOne("SELECT `id` FROM `s_order_details` WHERE `orderID`=? AND `articleordernumber`=?", array($orderID, $articleordernumber));
        Shopware()->Db()->update('pigmbh_ratepay_order_positions', $bind, '`s_order_details_id`=' . $positionId);
    }

}
