<?php

namespace MBH\Bundle\ClientBundle\Document;

use MBH\Bundle\ClientBundle\Lib\PaymentSystemDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\EmbeddedDocument
 */
class RNKB extends PaymentSystemDocument
{
//    const COMMISSION = 0.035;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $rnkbShopIDP;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $key;

    /**
     * @param string $rnkbShopIDP
     * @return self
     */
    public function setRnkbShopIDP($rnkbShopIDP)
    {
        $this->rnkbShopIDP = $rnkbShopIDP;
        return $this;
    }

    /**
     * @return string
     */
    public function getRnkbShopIDP()
    {
        return $this->rnkbShopIDP;
    }

    /**
     * @param string $key
     * @return self
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }
}