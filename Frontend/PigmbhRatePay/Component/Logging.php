<?php

/**
 * Logging
 *
 * @category   PayIntelligent
 * @copyright  Copyright (c) 2013 PayIntelligent GmbH (http://payintelligent.de)
 */
class Shopware_Plugins_Frontend_PigmbhRatePay_Component_Logging
{

    /**
     * Logs the Request and Response
     *
     * @param string $requestXml
     * @param string $responseXml
     */
    public function logRequest($requestXml, $responseXml)
    {
        $config = Shopware()->Plugins()->Frontend()->PigmbhRatePay()->Config();
        $version = Shopware()->Plugins()->Frontend()->PigmbhRatePay()->getVersion();
        if ($config->get('RatePayLogging') === true) {
            preg_match("/<operation.*>(.*)<\/operation>/", $requestXml, $operationMatches);
            $operation = $operationMatches[1];

            preg_match('/<operation subtype=\"(.*)">(.*)<\/operation>/', $requestXml, $operationSubtypeMatches);
            $operationSubtype = $operationSubtypeMatches[1] ? : 'N/A';

            preg_match("/<transaction-id>(.*)<\/transaction-id>/", $requestXml, $transactionMatches);
            $transactionId = $transactionMatches[1] ? : 'N/A';

            preg_match("/<transaction-id>(.*)<\/transaction-id>/", $responseXml, $transactionMatchesResponse);
            $transactionId = $transactionId == 'N/A' && $transactionMatchesResponse[1] ? $transactionMatchesResponse[1] : $transactionId;

            $requestXml = preg_replace("/<owner>(.*)<\/owner>/", "<owner>xxxxxxxx</owner>", $requestXml);
            $requestXml = preg_replace("/<bank-account-number>(.*)<\/bank-account-number>/", "<bank-account-number>xxxxxxxx</bank-account-number>", $requestXml);
            $requestXml = preg_replace("/<bank-code>(.*)<\/bank-code>/", "<bank-code>xxxxxxxx</bank-code>", $requestXml);
            $requestXml = preg_replace("/<bank-name>(.*)<\/bank-name>/", "<bank-name>xxxxxxxx</bank-name>", $requestXml);

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

}
