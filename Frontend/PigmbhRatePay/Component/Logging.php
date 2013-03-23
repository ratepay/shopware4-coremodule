<?php

class Shopware_Plugins_Frontend_PigmbhRatePay_Component_Logging {
    
    public function logRequest($requestXml, $responseXml){
        $version = Shopware()->Plugins()->Frontend()->PigmbhRatePay()->getVersion();
        
        preg_match("/<operation>(.*)<\/operation>/", $requestXml, $operationMatches);
        $operation = $operationMatches[1];
        
        preg_match("/<transaction-id>(.*)<\/transaction-id>/", $requestXml, $matches);
        $transactionId = $matches[1] ?: 'N/A' ;
        
        $bind = array(
            'version' => $version,
            'operation' => $operation,
            'transactionId' => $transactionId,
            'request' => $requestXml,
            'response' => $responseXml
        );
        try{
            Shopware()->Db()->insert('pigmbh_ratepay_logging', $bind);
        }catch(Exception $exception){
            Shopware()->Log()->Err('Fehler beim Loggen: ' . $exception->getMessage());
        }
        
    }
}
