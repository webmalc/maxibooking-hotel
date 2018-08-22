<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemCommonDocument;

/**
 * @ODM\EmbeddedDocument
 */
class Paypal extends PaymentSystemCommonDocument
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
