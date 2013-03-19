<?php
/**
 * {@inheritdoc}
 *
 * Extends SimpleXMLElement with a method to easyily add CData Child to XML.
 *
 * @package PayIntelligent_RatePAY
 * @extends SimpleXMLElement
 */
class Shopware_Plugins_Frontend_PigmbhRatePay_Component_Service_SimpleXmlExtended extends SimpleXMLElement
{

    /**
     * create CData child
     *
     * @param string $sName
     * @param string $sValue
     * @param bool $utfMode
     * @return SimpleXMLElement
     */
     public function addCDataChild($sName, $sValue)
    {
        $oNodeOld = dom_import_simplexml($this);
        $oNodeNew = new DOMNode();
        $oDom = new DOMDocument();
        $oDataNode = $oDom->appendChild($oDom->createElement($sName));
        $oDataNode->appendChild($oDom->createCDATASection($sValue));
        $oNodeTarget = $oNodeOld->ownerDocument->importNode($oDataNode, true);
        $oNodeOld->appendChild($oNodeTarget);
        return simplexml_import_dom($oNodeTarget);
    }

}