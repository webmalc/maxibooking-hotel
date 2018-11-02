<?php

namespace MBH\Bundle\ClientBundle\Document\PaymentSystem;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemDocument;

/**
 * @ODM\EmbeddedDocument
 */
class Paypal extends PaymentSystemDocument
{
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $paypalLogin;

    /**
     * @return string
     */
    public function getPaypalLogin()
    {
        return $this->paypalLogin;
    }

    /**
     * @param string $PayPalLogin
     */
    public function setPaypalLogin(string $paypalLogin)
    {
        $this->paypalLogin = $paypalLogin;
        return $this;
    }

}
