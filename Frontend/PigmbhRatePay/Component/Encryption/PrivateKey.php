<?php

class Pi_Util_Encryption_PrivateKey
{
    private $_keyPath;

    /**
     *
     * @param string $pathModifier
     */
    public function __construct($pathModifier = '/')
    {
        $pathModifier = isset($pathModifier)? $pathModifier : '';
        $this->_keyPath = dirname(__FILE__) . $pathModifier . 'piPrivateKey.php';
    }

    /**
     * Gets private key from piPrivateKey.php
     *
     * @return  String PI_PRIVATE_KEY     private key
     */
    public function getPrivateKey()
    {
        if (!file_exists($this->_keyPath)) {
            $this->createPrivateKey();
        }
        require $this->_keyPath;
        return PI_PRIVATE_KEY;
    }

    /**
     * Creates file with random private key
     */
    private function createPrivateKey()
    {
        $datei = fopen($this->_keyPath, "w");
        fwrite($datei, '<?php DEFINE ("PI_PRIVATE_KEY", "'
            . $this->createRandomString(15, true)
            . $this->createRandomString(15, false)
            . $this->createRandomString(15, true) . '");'
            .' ?>');
    }

    /**
     * Generate a random string with variable length and optional
     * number and/or special characters
     * @param int $length
     * @param bool $useNumbers
     * @return string
     */
    public function createRandomString($length, $useNumbers = false)
    {
        $secret  = '';
        $key     = 0;
        $numbers = range (0, 9);
        $chars   = range ('a', 'z');
        if ($useNumbers) {
            $randomize = array_merge($numbers, $chars);
        } else {
            $randomize = $chars;
        }
        shuffle($randomize);
        for ($index = 1; $index <= (int) $length; $index++) {
            $key = array_rand($randomize, 1);
            if (0 == ($key % 2)) {
                $secret .= $randomize[$key];
            } else {
                $secret .= strtoupper($randomize[$key]);
            }
        }
        return (string) $secret;
    }
}
