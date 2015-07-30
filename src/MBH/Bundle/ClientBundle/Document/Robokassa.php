<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemInterface;
use MBH\Bundle\CashBundle\Document\CashDocument;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ODM\EmbeddedDocument
 */
class Robokassa  implements PaymentSystemInterface
{
    /**
     * @var string
     * @ODM\String()
     */
    protected $robokassaMerchantLogin;

    /**
     * @var string
     * @ODM\String()
     */
    protected $robokassaMerchantPass1;

    /**
     * @var string
     * @ODM\String()
     */
    protected $robokassaMerchantPass2;

    /**
     * Set robokassaMerchantLogin
     *
     * @param string $robokassaMerchantLogin
     * @return self
     */
    public function setRobokassaMerchantLogin($robokassaMerchantLogin)
    {
        $this->robokassaMerchantLogin = $robokassaMerchantLogin;
        return $this;
    }

    /**
     * Get robokassaMerchantLogin
     *
     * @return string $robokassaMerchantLogin
     */
    public function getRobokassaMerchantLogin()
    {
        return $this->robokassaMerchantLogin;
    }

    /**
     * Set robokassaMerchantPass1
     *
     * @param string $robokassaMerchantPass1
     * @return self
     */
    public function setRobokassaMerchantPass1($robokassaMerchantPass1)
    {
        $this->robokassaMerchantPass1 = $robokassaMerchantPass1;
        return $this;
    }

    /**
     * Get robokassaMerchantPass1
     *
     * @return string $robokassaMerchantPass1
     */
    public function getRobokassaMerchantPass1()
    {
        return $this->robokassaMerchantPass1;
    }

    /**
     * Set robokassaMerchantPass2
     *
     * @param string $robokassaMerchantPass2
     * @return self
     */
    public function setRobokassaMerchantPass2($robokassaMerchantPass2)
    {
        $this->robokassaMerchantPass2 = $robokassaMerchantPass2;
        return $this;
    }

    /**
     * Get robokassaMerchantPass2
     *
     * @return string $robokassaMerchantPass2
     */
    public function getRobokassaMerchantPass2()
    {
        return $this->robokassaMerchantPass2;
    }

    public function getFormData(CashDocument $cashDocument, $url = null , $checkUrl = null)
    {

    }

    /**
     * @inheritdoc
     */
    public function getSignature(CashDocument $cashDocument, $url = null)
    {
        return '';
    }

    public function checkRequest(Request $request)
    {

    }
}
