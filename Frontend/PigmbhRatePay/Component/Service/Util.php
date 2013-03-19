<?php

class Shopware_Plugins_Frontend_PigmbhRatePay_Component_Service_Util
{

    /**
     *
     * @param array $model
     * @return \Application\Util\SimpleXmlExtended
     */
    public static function convertToXml($array, $root)
    {
        $xmlString = '<?xml version="1.0" encoding="UTF-8"?>'
                . '<' . $root . '/>';
        $xml = new Shopware_Plugins_Frontend_PigmbhRatePay_Component_Service_SimpleXmlExtended($xmlString);
        self::_arrayToXml($array, $xml);
        return $xml;
    }

    /**
     *
     * @TODO: Needs better testing.
     * @param array $model
     * @param \Application\Util\SimpleXmlExtended $xml
     */
    private static function _arrayToXml(array $model, &$xml)
    {
        foreach ($model as $key => $value) {
            if (!empty($value) && !(is_array($value) && count($value) === 0)
            ) {
                if (is_array($value)) {
                    if (!is_numeric($key)) {
                        $subnode = $xml->addChild("$key");
                        self::_arrayToXml($value, $subnode);
                    } else {
                        self::_arrayToXml($value, $xml);
                    }
                } else if (substr($key, 0, 1) === '@') {
                    $attributeKey = substr($key, 1);
                    $xml->addAttribute("$attributeKey", "$value");
                } else if (substr($key, 0, 1) === '%') {
                    $attributeKey = substr($key, 1);
                    $xml->addCDataChild("$attributeKey", "$value");
                } else {
                    if (is_numeric($key)) {
//                        $name = $xml->getName();
                        $xml->{0} = $value;
                    } else {
                        $xml->addChild("$key", "$value");
                    }
                }
            }
        }
    }

}