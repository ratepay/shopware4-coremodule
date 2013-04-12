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

    public function initPositionsAction()
    {
        $ids = json_decode($this->Request()->getParam("ids"));
        $orderID = $this->Request()->getParam("orderID");
        $success = false;
        $sqlSelectIDs = "SELECT `id` FROM `s_order_details` WHERE `orderID`=? AND `articleID` IN (?)";
        foreach ($ids as $id) {
            $articleIDs .= $id . ",";
        }
        $articleIDs = substr($articleIDs, 0, -1);
        try {
            $detailIDs = Shopware()->Db()->fetchAll($sqlSelectIDs, array($orderID, $articleIDs));
            foreach ($detailIDs as $row) {
                $values .= "(" . $row['id'] . "),";
            }
            $values = substr($values, 0, -1);
            $sqlInsert = "INSERT INTO `pigmbh_ratepay_order_positions` "
                    . "(`s_order_details_id`) "
                    . "VALUES " . $values;
            Shopware()->Db()->query($sqlInsert);
            $success = true;
        } catch (Exception $exception) {
            Shopware()->Log()->Err("Exception:" . $exception->getMessage());
        }
        $this->View()->assign(
                array(
                    "success" => $success
                )
        );
    }

    /**
     * This Action loads the data from the datebase into the backendview
     */
    public function loadPositionStoreAction()
    {
        $orderId = $this->Request()->getParam("orderId");

        $sql = "SELECT "
                . "`articleID`, "
                . "`name`, "
                . "`articleordernumber`, "
                . "`price`, "
                . "`quantity`, "
                . "(`quantity` - `delivered` - `cancelled`) AS `quantityDeliver`, "
                . "(`delivered` - `returned`) AS `quantityReturn`, "
                . "`delivered`, "
                . "`cancelled`, "
                . "`returned`, "
                . "`tax_rate` "
                . "FROM `s_order_details` AS detail "
                . "INNER JOIN `pigmbh_ratepay_order_positions` AS ratepay ON detail.`id`=ratepay.`s_order_details_id` "
                . "WHERE detail.`orderId`=? "
                . "ORDER BY detail.`id`;";

        $data = Shopware()->Db()->fetchAll($sql, array($orderId));
        $data[] = $this->getShippingFromDBAsItem($orderId);
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
            if ($item->quantity == 0) {
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
                    'delivered' => $item->delivered + $item->deliveredItems
                );
                $this->updateItem($orderId, $item->articlenumber, $bind);
            }
        }

        $this->View()->assign(array(
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
            if ($item->quantity <= 0) {
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

        $this->View()->assign(array(
            "result" => $result,
            "success" => true
                )
        );
    }

    public function returnItemsAction()
    {
        $orderId = $this->Request()->getParam("orderId");
        $items = json_decode($this->Request()->getParam("items"));
        $order = Shopware()->Db()->fetchRow("SELECT * FROM `s_order` WHERE `id`=?", array($orderId));

        $basketItems = array();
        foreach ($items as $item) {
            $basketItem = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_item();
            if ($item->quantity <= 0) {
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
        $head->setOperationSubstring('partial-return');
        $paymentChange->setHead($head);
        $paymentChange->setShoppingBasket($basket);

        $response = $this->_request->xmlRequest($paymentChange->toArray());
        $result = Shopware_Plugins_Frontend_PigmbhRatePay_Component_Service_Util::validateResponse('PAYMENT_CHANGE', $response);
        if ($result === true) {
            foreach ($items as $item) {
                $bind = array(
                    'returned' => $item->returned + $item->returnedItems
                );
                $this->updateItem($orderId, $item->articlenumber, $bind);
            }
        }

        $this->View()->assign(array(
            "result" => $result,
            "success" => true
                )
        );
    }

    public function addAction()
    {
        $orderId = $this->Request()->getParam("orderId");
        $subOperation = $this->Request()->getParam("suboperation");
        $order = Shopware()->Db()->fetchRow("SELECT * FROM `s_order` WHERE `id`=?", array($orderId));
        $orderItems = Shopware()->Db()->fetchAll("SELECT * FROM `s_order_details` WHERE `orderID`=?", array($orderId));
        $basketItems = array();
        foreach ($orderItems as $row) {
            $basketItem = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_item();
            $basketItem->setArticleName($row['name']);
            $basketItem->setArticleNumber($row['articleordernumber']);
            $basketItem->setQuantity($row['quantity']);
            $basketItem->setTaxRate($row['tax_rate']);
            $basketItem->setUnitPriceGross($row['price']);
            $basketItems[] = $basketItem;
        }
        $shippingRow = $this->getShippingFromDBAsItem($orderId);
        $basketItem = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_item();
        $basketItem->setArticleName($shippingRow['name']);
        $basketItem->setArticleNumber($shippingRow['articleordernumber']);
        $basketItem->setQuantity($shippingRow['quantity']);
        $basketItem->setTaxRate($shippingRow['tax_rate']);
        $basketItem->setUnitPriceGross($shippingRow['price']);
        $basketItems[] = $basketItem;

        $basket = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_ShoppingBasket();
        $basket->setAmount($order["invoice_amount"]);
        $basket->setCurrency($order['currency']);
        $basket->setItems($basketItems);

        $this->_modelFactory->setTransactionId($order['transactionID']);
        $paymentChange = $this->_modelFactory->getModel(new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentChange());
        $head = $paymentChange->getHead();
        $head->setOperationSubstring($subOperation);
        $paymentChange->setHead($head);
        $paymentChange->setShoppingBasket($basket);

        $response = $this->_request->xmlRequest($paymentChange->toArray());
        $result = Shopware_Plugins_Frontend_PigmbhRatePay_Component_Service_Util::validateResponse('PAYMENT_CHANGE', $response);
        $this->View()->assign(array(
            "result" => $result,
            "success" => true
                )
        );
    }

    private function updateItem($orderID, $articleordernumber, $bind)
    {
        if ($articleordernumber === 'shipping') {
            Shopware()->Db()->update('pigmbh_ratepay_order_shipping', $bind, '`s_order_id`=' . $orderID);
        } else {
            $positionId = Shopware()->Db()->fetchOne("SELECT `id` FROM `s_order_details` WHERE `orderID`=? AND `articleordernumber`=?", array($orderID, $articleordernumber));
            Shopware()->Db()->update('pigmbh_ratepay_order_positions', $bind, '`s_order_details_id`=' . $positionId);
        }
    }

    public function getArticleAction()
    {
        $id = $this->Request()->getParam('id');
        $data = Shopware()->Models()->getRepository('Shopware\Models\Article\Article')->getArticleBaseDataQuery($id)->getArrayResult();
        $data[0]['mainPrices'] = $this->getPrices($data[0]['mainDetail']['id'], $data[0]['tax']);
        $this->View()->assign(
                array(
                    "data" => $data[0],
                    "success" => true
                )
        );
    }

    protected function getPrices($id, $tax)
    {
        $prices = Shopware()->Models()->getRepository('Shopware\Models\Article\Article')
                ->getPricesQuery($id)
                ->getArrayResult();

        return $this->formatPricesFromNetToGross($prices, $tax);
    }

    protected function formatPricesFromNetToGross($prices, $tax)
    {
        foreach ($prices as $key => $price) {
            $customerGroup = $price['customerGroup'];
            if ($customerGroup['taxInput']) {
                $price['price'] = $price['price'] / 100 * (100 + $tax['tax']);
                $price['pseudoPrice'] = $price['pseudoPrice'] / 100 * (100 + $tax['tax']);
            }
            $prices[$key] = $price;
        }
        return $prices;
    }

    private function getShippingFromDBAsItem($orderId)
    {
        $sql = "SELECT "
                . "`invoice_shipping` AS `price`, "
                . "(1 - `delivered` - `cancelled`) AS `quantityDeliver`, "
                . "(`delivered` - `returned`) AS `quantityReturn`, "
                . "`delivered`, "
                . "`cancelled`, "
                . "`returned` "
                . "FROM `s_order` "
                . "INNER JOIN `pigmbh_ratepay_order_shipping` ON `s_order_id`=`s_order`.`id` "
                . "WHERE `id` = ?";
        $shippingRow = Shopware()->Db()->fetchRow($sql, array($orderId));
        $shippingRow['quantity'] = 1;
        $shippingRow['articleID'] = 0;
        $shippingRow['name'] = 'shipping';
        $shippingRow['articleordernumber'] = 'shipping';
        $shippingRow['tax_rate'] = "0";
        return $shippingRow;
    }

}
