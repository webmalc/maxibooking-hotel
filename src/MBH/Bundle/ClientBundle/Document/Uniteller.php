<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\FiscalizationTrait;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemDocument;

/**
 * @ODM\EmbeddedDocument
 */
class Uniteller extends PaymentSystemDocument
{
    use FiscalizationTrait;

    const COMMISSION = 0.035;

    const DO_CHECK_URL = 'https://wpay.uniteller.ru/api/1/iacheck';

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $unitellerShopIDP;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $unitellerPassword;
    /**
     * @var float
     * @ODM\Field(type="float")
     */
    protected $taxationRateCode;

    /**
     * @var float
     * @ODM\Field(type="float")
     */
    protected $taxationSystemCode;

    /**
     * @return float
     */
    public function getTaxationRateCode(): ?float
    {
        return $this->taxationRateCode;
    }

    /**
     * @param float $taxationRateCode
     * @return Uniteller
     */
    public function setTaxationRateCode(?float $taxationRateCode): Uniteller
    {
        $this->taxationRateCode = $taxationRateCode;

        return $this;
    }

    /**
     * @return float
     */
    public function getTaxationSystemCode(): ?float
    {
        return $this->taxationSystemCode;
    }

    /**
     * @param float $taxationSystemCode
     * @return Uniteller
     */
    public function setTaxationSystemCode(?float $taxationSystemCode): Uniteller
    {
        $this->taxationSystemCode = $taxationSystemCode;

        return $this;
    }

    /**
     * Set unitellerShopIDP
     *
     * @param string $unitellerShopIDP
     * @return self
     */
    public function setUnitellerShopIDP($unitellerShopIDP)
    {
        $this->unitellerShopIDP = $unitellerShopIDP;
        return $this;
    }

    /**
     * Get unitellerShopIDP
     *
     * @return string $unitellerShopIDP
     */
    public function getUnitellerShopIDP()
    {
        return $this->unitellerShopIDP;
    }

    /**
     * Set unitellerPassword
     *
     * @param string $unitellerPassword
     * @return self
     */
    public function setUnitellerPassword($unitellerPassword)
    {
        $this->unitellerPassword = $unitellerPassword;
        return $this;
    }

    /**
     * Get unitellerPassword
     *
     * @return string $unitellerPassword
     */
    public function getUnitellerPassword()
    {
        return $this->unitellerPassword;
    }
}
