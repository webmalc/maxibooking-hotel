<?php

namespace MBH\Bundle\ClientBundle\Document\PaymentSystem;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\FiscalizationTrait;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\TaxMapInterface;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemDocument;

/**
 * @ODM\EmbeddedDocument
 */
class Robokassa extends PaymentSystemDocument implements TaxMapInterface
{
    use FiscalizationTrait;

    private const TAX_SYSTEM_MAP = [
        0 => 'osn',
        1 => 'usn_income',
        2 => 'usn_income_outcome',
        3 => 'envd',
        4 => 'esn',
        5 => 'patent',
    ];

    private const TAX_RATE_MAP = [
        -1  => 'none',
        0   => 'vat0',
        10  => 'vat10',
        18  => 'vat18',
        110 => 'vat110',
        118 => 'vat118',
    ];

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $robokassaMerchantLogin;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $robokassaMerchantPass1;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $robokassaMerchantPass2;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $taxationRateCode;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $taxationSystemCode;

    /**
     * @return array
     */
    public function getTaxRateMap(): array
    {
        return self::TAX_RATE_MAP;
    }

    /**
     * @return array
     */
    public function getTaxSystemMap(): array
    {
        return self::TAX_SYSTEM_MAP;
    }

    /**
     * @return null|string
     */
    public function getTaxationRateCode(): ?string
    {
        return $this->taxationRateCode;
    }

    /**
     * @param string $taxationRateCode
     */
    public function setTaxationRateCode(string $taxationRateCode): self
    {
        $this->taxationRateCode = $taxationRateCode;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTaxationSystemCode(): ?string
    {
        return $this->taxationSystemCode;
    }

    /**
     * @param string $taxationSystemCode
     */
    public function setTaxationSystemCode(string $taxationSystemCode): self
    {
        $this->taxationSystemCode = $taxationSystemCode;

        return $this;
    }

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
}
