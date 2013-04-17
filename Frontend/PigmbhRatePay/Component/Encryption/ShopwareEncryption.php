<?php

require_once 'EncryptionAbstract.php';

class Shopware_Plugins_Frontend_PigmbhRatePay_Component_Encryption_ShopwareEncryption extends Pi_Util_Encryption_EncryptionAbstract
{
    protected function _insertBankdataToDatabase($insertSql)
    {
        Shopware()->Db()->query($insertSql);
    }

    protected function _selectBankdataFromDatabase($selectSql)
    {
        $result = Shopware()->Db()->fetchRow($selectSql);
        return array(
            'bankname' => $this->_convertHexToBinary($result['decrypt_bankname']),
            'bankcode' => $this->_convertHexToBinary($result['decrypt_bankcode']),
            'bankholder' => $this->_convertHexToBinary($result['decrypt_bankholder']),
            'account' => $this->_convertHexToBinary($result['decrypt_account']),
        );
    }

    protected function _selectUserIdFromDatabase($userSql)
    {
        $userID = Shopware()->Db()->fetchOne($userSql);
        return (string)$userID;
    }
}
