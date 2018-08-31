<?php

namespace MBH\Bundle\ClientBundle\Document\PaymentSystem;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemDocument;

/**
 * @ODM\EmbeddedDocument
 */
class Payanyway extends PaymentSystemDocument
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
}
