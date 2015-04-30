<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\EmbeddedDocument
 */
class Moneymail
{
    /**
     * @var string
     * @ODM\String()
     */
    protected $moneymailShopIDP;

    /**
     * @var string
     * @ODM\String()
     */
    protected $moneymailKey;

    /**
     * Set moneymailShopIDP
     *
     * @param string $moneymailShopIDP
     * @return self
     */
    public function setMoneymailShopIDP($moneymailShopIDP)
    {
        $this->moneymailShopIDP = $moneymailShopIDP;
        return $this;
    }

    /**
     * Get moneymailShopIDP
     *
     * @return string $moneymailShopIDP
     */
    public function getMoneymailShopIDP()
    {
        return $this->moneymailShopIDP;
    }

    /**
     * Set moneymailKey
     *
     * @param string $moneymailKey
     * @return self
     */
    public function setMoneymailKey($moneymailKey)
    {
        $this->moneymailKey = $moneymailKey;
        return $this;
    }

    /**
     * Get moneymailKey
     *
     * @return string $moneymailKey
     */
    public function getMoneymailKey()
    {
        return $this->moneymailKey;
    }
}
