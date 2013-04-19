<?php

require_once 'EncryptionAbstract.php';

class Shopware_Plugins_Frontend_PigmbhRatePay_Component_Encryption_ShopwareEncryption extends Pi_Util_Encryption_EncryptionAbstract
{

    /**
     * Executes the given SQL
     * @param string $insertSql
     */
    protected function _insertBankdataToDatabase($insertSql)
    {
        Shopware()->Db()->query($insertSql);
    }

    /**
     * Executes the given SQL and returns the result
     * @param string $selectSql
     */
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

    /**
     * Executes the given SQL and returns the UserID
     * @param string $userSql
     */
    protected function _selectUserIdFromDatabase($userSql)
    {
        $userID = Shopware()->Db()->fetchOne($userSql);
        return (string) $userID;
    }

}
