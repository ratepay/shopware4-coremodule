<?php


class Shopware_Plugins_Frontend_PigmbhRatePay_Component_Model_SubModel_Payment {
    /**
     * @var string
     */
    private $_method;
    /**
     * @var string
     */
    private $_currency;
    /**
     * @var float
     */
    private $_amount;
    /**
     * @var integer
     */
    private $_installmentNumber;
    /**
     * @var float
     */
    private $_installmentAmount;
    /**
     * @var float
     */
    private $_lastInstallmentAmount;
    /**
     * @var float
     */
    private $_interestRate;
    /**
     * @var integer
     */
    private $_paymentFirstday;
    /**
     * @var string
     */
    private $_directPayType;

    public function getMethod() {
        return $this->_method;
    }

    public function setMethod($method) {
        $this->_method = $method;
    }

    public function getCurrency() {
        return $this->_currency;
    }

    public function setCurrency($currency) {
        $this->_currency = $currency;
    }

    public function getAmount() {
        return $this->_amount;
    }

    public function setAmount($amount) {
        $this->_amount = $amount;
    }

    public function getInstallmentNumber() {
        return $this->_installmentNumber;
    }

    public function setInstallmentNumber($installmentNumber) {
        $this->_installmentNumber = $installmentNumber;
    }

    public function getInstallmentAmount() {
        return $this->_installmentAmount;
    }

    public function setInstallmentAmount($installmentAmount) {
        $this->_installmentAmount = $installmentAmount;
    }

    public function getLastInstallmentAmount() {
        return $this->_lastInstallmentAmount;
    }

    public function setLastInstallmentAmount($lastInstallmentAmount) {
        $this->_lastInstallmentAmount = $lastInstallmentAmount;
    }

    public function getInterestRate() {
        return $this->_interestRate;
    }

    public function setInterestRate($interestRate) {
        $this->_interestRate = $interestRate;
    }

    public function getPaymentFirstday() {
        return $this->_paymentFirstday;
    }

    public function setPaymentFirstday($paymentFirstday) {
        $this->_paymentFirstday = $paymentFirstday;
    }

    public function getDirectPayType() {
        return $this->_directPayType;
    }

    public function setDirectPayType($directPayType) {
        $this->_directPayType = $directPayType;
    }

    public function toArray(){
        $return = array(
            '@method' => $this->getMethod(),
            '@currency' => $this->getCurrency(),
            'amount' => $this->getAmount()
        );
        if($return['@method'] === 'INSTALLMENT'){
            $return['installment-details'] = array(
                'installment-number' => $this->getInstallmentNumber(),
                'installment-amount' => $this->getInstallmentAmount(),
                'last-installment-amount' => $this->getLastInstallmentAmount(),
                'interest-rate' => $this->getInterestRate(),
                'payment-firstday' => $this->getPaymentFirstday()
            );
            $return['debit-pay-type'] = $this->getDirectPayType();
        }
        return $return;

    }

}
