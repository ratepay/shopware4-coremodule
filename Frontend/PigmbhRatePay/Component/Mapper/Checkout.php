<?php

/**
 * Checkout
 *
 * @category   PayIntelligent
 * @copyright  Copyright (c) 2013 PayIntelligent GmbH (http://payintelligent.de)
 */
class Shopware_Plugins_Frontend_PigmbhRatePay_Component_Mapper_Checkout
{

    /**
     * Expects an instance of a paymentmodel and fill it with shopdata
     *
     * @param ObjectToBeFilled $modelName
     * @return filledObjectGivenToTheFunction
     * @throws Exception The submitted Class is not supported!
     */
    public function getModel($modelName)
    {
        switch ($modelName) {
            case is_a($modelName, Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentInit):
                $this->fillPaymentInit($modelName);
                break;
//            case is_a($modelName, Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentInit):
//                $this->fillPaymentInit($modelName);
//                break;
            default:
                throw new Exception('The submitted Class is not supported!');
                break;
        }
        return $modelName;
    }

    /**
     * Fills an object of the class Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentInit
     * 
     * @param Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentInit $paymentInitModel
     */
    private function fillPaymentInit(Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_PaymentInit &$paymentInitModel)
    {
        $config = Shopware()->Plugins()->Frontend()->PigmbhRatePay()->Config();
        $head = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_Head();
        $head->setOperation('PAYMENT_INIT');
        $head->setProfileId($config->get('RatePayProfileID'));
        $head->setSecurityCode($config->get('RatePaySecurityCode'));
        $head->setSystemId(Shopware()->Shop()->getHost());
        $paymentInitModel->setHead($head);
    }

}
