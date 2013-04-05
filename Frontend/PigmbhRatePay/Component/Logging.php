<?php

class Shopware_Plugins_Frontend_PigmbhRatePay_Component_Logging
{

    public function logRequest($requestXml, $responseXml)
    {
        $version = Shopware()->Plugins()->Frontend()->PigmbhRatePay()->getVersion();

        preg_match("/<operation>(.*)<\/operation>/", $requestXml, $operationMatches);
        $operation = $operationMatches[1];

        preg_match("/<operation subtype=\"(.*)\">/", $requestXml, $operationSubtypeMatches);
        $operationSubtype = $operationSubtypeMatches[1] ?: 'N/A';

        preg_match("/<transaction-id>(.*)<\/transaction-id>/", $requestXml, $transactionMatches);
        $transactionId = $transactionMatches[1] ? : 'N/A';

        preg_match("/<transaction-id>(.*)<\/transaction-id>/", $responseXml, $transactionMatchesResponse);
        $transactionId = $transactionId == 'N/A' && $transactionMatchesResponse[1] ? $transactionMatchesResponse[1] : $transactionId;

        $bind = array(
            'version' => $version,
            'operation' => $operation,
            'suboperation' => $operationSubtype,
            'transactionId' => $transactionId,
            'request' => $requestXml,
            'response' => $responseXml
        );
        try {
            Shopware()->Db()->insert('pigmbh_ratepay_logging', $bind);
        } catch (Exception $exception) {
            Shopware()->Log()->Err('Fehler beim Loggen: ' . $exception->getMessage());
        }
    }

}
