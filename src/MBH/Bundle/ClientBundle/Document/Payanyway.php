<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\CheckResultHolder;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ODM\EmbeddedDocument
 */
class Payanyway implements PaymentSystemInterface
{
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $payanywayMntId;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $payanywayKey;

    /**
     * Set payanywayMntId
     *
     * @param string $payanywayMntId
     * @return self
     */
    public function setPayanywayMntId($payanywayMntId)
    {
        $this->payanywayMntId = $payanywayMntId;
        return $this;
    }

    /**
     * Get payanywayMntId
     *
     * @return string $payanywayMntId
     */
    public function getPayanywayMntId()
    {
        return $this->payanywayMntId;
    }

    /**
     * Set payanywayKey
     *
     * @param string $payanywayKey
     * @return self
     */
    public function setPayanywayKey($payanywayKey)
    {
        $this->payanywayKey = $payanywayKey;
        return $this;
    }

    /**
     * Get payanywayKey
     *
     * @return string $payanywayKey
     */
    public function getPayanywayKey()
    {
        return $this->payanywayKey;
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

    public function checkRequest(Request $request, ClientConfig $clientConfig): CheckResultHolder
    {
        return new CheckResultHolder();
    }
}
