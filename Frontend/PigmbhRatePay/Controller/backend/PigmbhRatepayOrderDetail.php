<?php

/**
 * PigmbhRatepayLogging
 *
 * @category   PayIntelligent
 * @copyright  Copyright (c) 2013 PayIntelligent GmbH (http://payintelligent.de)
 */
class Shopware_Controllers_Backend_PigmbhRatepayOrderDetail extends Shopware_Controllers_Backend_ExtJs
{

    private $_config;
    private $_modelFactory;
    private $_request;
    private $_history;

    /**
     * index action is called if no other action is triggered
     * @return void
     */
    public function init()
    {
        $this->_config = Shopware()->Plugins()->Frontend()->PigmbhRatePay()->Config();
        $this->_modelFactory = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Mapper_ModelFactory();
        $this->_request = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Service_RequestService($this->_config->get('RatePaySandbox'));
        $this->_history = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_History();
    }

    /**
     * Initiate the PositionDetails for the given Article in the given Order
     */
    public function initPositionsAction()
    {
        $articleNames = json_decode($this->Request()->getParam("articleNumber"));
        $orderID = $this->Request()->getParam("orderID");
        $success = false;
        $sqlSelectIDs = "SELECT `id` FROM `s_order_details` WHERE `orderID`=? AND `articleordernumber` IN (?)";
        foreach ($articleNames as $articleName) {
            $names .= $articleName . ",";
        }
        $names = substr($names, 0, -1);
        try {
            $detailIDs = Shopware()->Db()->fetchAll($sqlSelectIDs, array($orderID, $names));
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
     * Loads the History for the given Order
     */
    public function loadHistoryStoreAction()
    {
        $orderId = $this->Request()->getParam("orderId");
        $history = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_History();
        $historyData = $history->getHistory($orderId);
        $this->View()->assign(
                array(
                    "data" => $historyData,
                    "success" => true
                )
        );
    }

    /**
     * This Action loads the data from the datebase into the backendview
     */
    public function loadPositionStoreAction()
    {
        $orderId = $this->Request()->getParam("orderId");
        $zero = $this->Request()->getParam("setToZero");
        $data = $this->getFullBasket($orderId);
        $positions = array();
        if ($zero) {
            foreach ($data as $row) {
                $row['quantityDeliver'] = 0;
                $row['quantityReturn'] = 0;
                $positions[] = $row;
            }
        } else {
            $positions = $data;
        }
        $total = Shopware()->Db()->fetchOne("SELECT count(*) FROM `s_order_details` WHERE `s_order_details`.`orderId`=?;", array($orderId));

        $this->View()->assign(array(
            "data" => $positions,
            "total" => $total,
            "success" => true
                )
        );
    }

    /**
     * Delivers the given Items and assigns the result to the backend
     */
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
        $basket->setAmount($this->getRecalculatedAmount($basketItems));
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
                if ($item->quantity <= 0) {
                    continue;
                }
                $this->_history->logHistory($orderId, "Artikel wurde versand.", $item->name, $item->articlenumber, $item->quantity);
            }
        }

        $this->setNewOrderState($orderId);
        $this->View()->assign(array(
            "result" => $result,
            "success" => true
                )
        );
    }

    /**
     * Cancel the given Items and returns the result to the backend
     */
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
        $basket->setAmount($this->getRecalculatedAmount($basketItems));
        $basket->setCurrency($order['currency']);
        $basket->setItems($basketItems);

        $subtype = 'partial-cancellation';
        if ($this->isFullPaymentChange($orderId, $basketItems, 'cancel')) {
            $subtype = 'full-cancellation';
        }

        $this->_modelFactory->setTransactionId($order['transactionID']);
        $paymentChange = $this->_modelFactory->getModel(new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentChange());
        $head = $paymentChange->getHead();
        $head->setOperationSubstring($subtype);
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
                if ($item->cancelledItems <= 0) {
                    continue;
                }
                $this->_history->logHistory($orderId, "Artikel wurde storniert.", $item->name, $item->articlenumber, $item->cancelledItems);
            }
        }
        $this->setNewOrderState($orderId);
        $this->View()->assign(array(
            "result" => $result,
            "success" => true
                )
        );
    }

    /**
     * returns the given Items and returns the result to the backend
     */
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
        $basket->setAmount($this->getRecalculatedAmount($basketItems));
        $basket->setCurrency($order['currency']);
        $basket->setItems($basketItems);

        $subtype = 'partial-return';
        if ($this->isFullPaymentChange($orderId, $basketItems, 'return')) {
            $subtype = 'full-return';
        }

        $this->_modelFactory->setTransactionId($order['transactionID']);
        $paymentChange = $this->_modelFactory->getModel(new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentChange());
        $head = $paymentChange->getHead();
        $head->setOperationSubstring($subtype);
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
                if ($item->returnedItems <= 0) {
                    continue;
                }
                $this->_history->logHistory($orderId, "Artikel wurde retourniert.", $item->name, $item->articlenumber, $item->returnedItems);
            }
        }

        $this->View()->assign(array(
            "result" => $result,
            "success" => true
                )
        );
    }

    /**
     * Add the given Items to the given order
     */
    public function addAction()
    {
        $orderId = $this->Request()->getParam("orderId");
        $insertedIds = json_decode($this->Request()->getParam("insertedIds"));
        $subOperation = $this->Request()->getParam("suboperation");
        $order = Shopware()->Db()->fetchRow("SELECT * FROM `s_order` WHERE `id`=?", array($orderId));
        $orderItems = Shopware()->Db()->fetchAll("SELECT *, (`quantity` - `delivered` - `cancelled`) AS `quantityDeliver` FROM `s_order_details` "
                . "INNER JOIN `pigmbh_ratepay_order_positions` ON `s_order_details`.`id` = `pigmbh_ratepay_order_positions`.`s_order_details_id` "
                . "WHERE `orderID`=?", array($orderId));
        $basketItems = array();
        foreach ($orderItems as $row) {
            if ($row['quantityDeliver'] == 0) {
                continue;
            }
            $basketItem = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_item();
            $basketItem->setArticleName($row['name']);
            $basketItem->setArticleNumber($row['articleordernumber']);
            $basketItem->setQuantity($row['quantityDeliver']);
            $basketItem->setTaxRate($row['tax_rate']);
            $basketItem->setUnitPriceGross($row['price']);
            $basketItems[] = $basketItem;
        }
        $shippingRow = $this->getShippingFromDBAsItem($orderId);
        if (!is_null($shippingRow)) {
            $basketItem = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_item();
            $basketItem->setArticleName($shippingRow['name']);
            $basketItem->setArticleNumber($shippingRow['articleordernumber']);
            $basketItem->setQuantity($shippingRow['quantityDeliver']);
            $basketItem->setTaxRate($shippingRow['tax_rate']);
            $basketItem->setUnitPriceGross($shippingRow['price']);
            $basketItems[] = $basketItem;
        }
        $basket = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_ShoppingBasket();
        $basket->setAmount($this->getRecalculatedAmount($basketItems));
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
        if ($result) {
            $event = $subOperation === 'credit' ? 'Gutschein wurde hinzugefügt' : 'Artikel wurde hinzugefügt';
            foreach ($insertedIds as $id) {
                $newItems = Shopware()->Db()->fetchRow("SELECT * FROM `s_order_details` WHERE `id`=?", array($id));
                if ($newItems['quantity'] <= 0) {
                    continue;
                }
                $this->_history->logHistory($orderId, $event, $newItems['name'], $newItems['articleordernumber'], $newItems['quantity']);
            }
        }
        $this->setNewOrderState($orderId);
        $this->View()->assign(array(
            "result" => $result,
            "success" => true
                )
        );
    }

    /**
     * Updates the given binding for the given article
     *
     * @param string $orderID
     * @param string $articleordernumber
     * @param array $bind
     */
    private function updateItem($orderID, $articleordernumber, $bind)
    {
        if ($articleordernumber === 'shipping') {
            Shopware()->Db()->update('pigmbh_ratepay_order_shipping', $bind, '`s_order_id`=' . $orderID);
        } else {
            $positionId = Shopware()->Db()->fetchOne("SELECT `id` FROM `s_order_details` WHERE `orderID`=? AND `articleordernumber`=?", array($orderID, $articleordernumber));
            Shopware()->Db()->update('pigmbh_ratepay_order_positions', $bind, '`s_order_details_id`=' . $positionId);
        }
    }

    /**
     * Returns the article for the given id
     */
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

    /**
     * Returns the Price for the given id
     *
     * @param string $id
     * @param float $tax
     * @return float
     */
    protected function getPrices($id, $tax)
    {
        $prices = Shopware()->Models()->getRepository('Shopware\Models\Article\Article')
                ->getPricesQuery($id)
                ->getArrayResult();

        return $this->formatPricesFromNetToGross($prices, $tax);
    }

    /**
     * Converts the given data from netto to gross
     * @param float $prices
     * @param float $tax
     * @return float
     */
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

    /**
     * Returns the Shipping as item for the given order
     *
     * @param string $orderId
     * @return array
     */
    private function getShippingFromDBAsItem($orderId)
    {
        $sql = "SELECT "
                . "`invoice_shipping` AS `price`, "
                . "(1 - `delivered` - `cancelled`) AS `quantityDeliver`, "
                . "(`delivered` - `returned`) AS `quantityReturn`, "
                . "`delivered`, "
                . "`cancelled`, "
                . "`returned`, "
                . "`s_core_tax`.`tax` AS `tax_rate` "
                . "FROM `s_order` "
                . "LEFT JOIN `pigmbh_ratepay_order_shipping` ON `s_order_id`=`s_order`.`id` "
                . "LEFT JOIN `s_premium_dispatch` ON `s_order`.`dispatchID`=`s_premium_dispatch`.`id` "
                . "LEFT JOIN `s_core_tax` ON `s_premium_dispatch`.`tax_calculation`=`s_core_tax`.`id` "
                . "WHERE `s_order`.`id` = ?";
        $shippingRow = Shopware()->Db()->fetchRow($sql, array($orderId));
        if (isset($shippingRow['quantityDeliver']) && $shippingRow['quantityDeliver'] > 0) {
            if ($shippingRow['tax_rate'] == null) {
                $shippingRow['tax_rate'] = Shopware()->Db()->fetchOne("SELECT MAX(`tax`) FROM `s_core_tax`");
            }
            $shippingRow['quantity'] = 1;
            $shippingRow['articleID'] = 0;
            $shippingRow['name'] = 'shipping';
            $shippingRow['articleordernumber'] = 'shipping';
            return $shippingRow;
        }
    }

    /**
     * Recalculates the Amount for the given items
     *
     * @param array $items
     * @return float
     */
    private function getRecalculatedAmount($items)
    {
        $basket = array();
        foreach ($items as $item) {
            $detailModel = new \Shopware\Models\Order\Detail();
            $detailModel->setQuantity($item->getQuantity());
            $detailModel->setPrice($item->getUnitPriceGross());
            $detailModel->setTaxRate($item->getTaxRate());
            $detailModel->setArticleName($item->getArticleName());
            $detailModel->setArticleNumber($item->getArticleNumber());
            $basket[] = $detailModel;
        }
        $orderModel = new \Shopware\Models\Order\Order();
        $orderModel->setDetails($basket);
        $orderModel->calculateInvoiceAmount();
        return $orderModel->getInvoiceAmount();
    }

    /**
     * Checks if the Transaction is Full or Partial
     *
     * @param string $orderId
     * @param array $remainingBasket
     * @param string $type
     * @return boolean
     */
    private function isFullPaymentChange($orderId, $remainingBasket, $type)
    {
        if ($type == 'return') {
            $column = 'returned';
        } elseif ($type == 'cancel') {
            $column = 'cancelled';
        }
        $count = $this->countOpenPositions($column, $orderId);
        //No Items remaining and no partial-requests done before.
        if (count($remainingBasket) === 0 && $count == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns the whole Basket
     *
     * @param string $orderId
     * @return array
     */
    private function getFullBasket($orderId)
    {
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
        $shipping = $this->getShippingFromDBAsItem($orderId);
        if (!is_null($shipping)) {
            $data[] = $shipping;
        }
        return $data;
    }

    /**
     * Counts the open Positions
     *
     * @param string $column
     * @param string $orderId
     * @return int
     */
    private function countOpenPositions($column, $orderId)
    {
        $count = null;
        $sql = "SELECT COUNT(*)"
                . "FROM `s_order_details` AS `detail` "
                . "INNER JOIN `pigmbh_ratepay_order_positions` ON `detail`.`id` = `pigmbh_ratepay_order_positions`.`s_order_details_id` "
                . "WHERE `$column` != 0 AND `detail`.`orderID` = ?";
        $sqlShipping = "SELECT COUNT(*) "
                . "FROM `pigmbh_ratepay_order_shipping` AS `shipping` "
                . "WHERE `$column` != 0 AND `shipping`.`s_order_id` = ?";
        try {
            $count = Shopware()->Db()->fetchOne($sql, array($orderId));
            $temp = Shopware()->Db()->fetchOne($sqlShipping, array($orderId));
            $count += $temp;
        } catch (Exception $exception) {
            Shopware()->Log()->Err($exception->getMessage());
        }
        return $count;
    }

    /**
     * Sets the new Orderstate
     *
     * @param boolean $orderComplete
     */
    private function setNewOrderState($orderId)
    {
        $sql = "SELECT COUNT((`quantity` - `delivered` - `cancelled`)) AS 'itemsLeft' "
                . "FROM `s_order_details` "
                . "JOIN `pigmbh_ratepay_order_positions` ON `s_order_details`.`id` = `pigmbh_ratepay_order_positions`.`s_order_details_id` "
                . "WHERE `orderID`=? AND (`quantity` - `delivered` - `cancelled`) > 0";
        try {
            $orderComplete = Shopware()->Db()->fetchOne($sql, array($orderId));
            $newState = $orderComplete == 0 ? 7 : 6;
            Shopware()->Db()->update('s_order', array(
                'status' => $newState
                    ), '`id`=' . $orderId);
        } catch (Exception $exception) {
            Shopware()->Log()->Err($exception->getMessage());
        }
    }

}
