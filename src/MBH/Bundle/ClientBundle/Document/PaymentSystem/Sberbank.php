<?php
/**
 * Created by PhpStorm.
 * Date: 29.08.18
 */

namespace MBH\Bundle\ClientBundle\Document\PaymentSystem;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\FiscalizationInterface;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\FiscalizationTrait;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\TaxMapInterface;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemDocument;

/**
 * Class Sberbank
 * @ODM\EmbeddedDocument()
 * @package MBH\Bundle\ClientBundle\Document\PaymentSystem
 */
class Sberbank extends PaymentSystemDocument implements TaxMapInterface, FiscalizationInterface
{
    use FiscalizationTrait;

    private const DEFAULT_TAX_SYSTEM = 0;
    private const DEFAULT_TAX_RATE = 3;


    private const TAX_SYSTEM_MAP = [
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
    ];

    private const TAX_RATE_MAP = [
        -1  => 0,
        0   => 1,
        10  => 2,
        18  => 3,
        110 => 4,
        118 => 5,
    ];

    /**
     * @var null|string
     * @ODM\Field(type="string")
     */
    private $userName;

    /**
     * @var null|string
     * @ODM\Field(type="string")
     */
    private $password;

    /**
     * @var int
     * @ODM\Field(type="int")
     */
    private $taxationRateCode = self::DEFAULT_TAX_RATE;

    /**
     * @var integer
     * @ODM\Field(type="int")
     */
    private $taxationSystemCode = self::DEFAULT_TAX_SYSTEM;

    /**
     * @return null|string
     */
    public function getUserName(): ?string
    {
        return $this->userName;
    }

    /**
     * @param null|string $userName
     */
    public function setUserName(?string $userName): void
    {
        $this->userName = $userName;
    }

    /**
     * @return null|string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param null|string $password
     */
    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return int
     */
    public function getTaxationRateCode(): int
    {
        return $this->taxationRateCode;
    }

    /**
     * @param int $taxationRateCode
     */
    public function setTaxationRateCode(int $taxationRateCode): void
    {
        $this->taxationRateCode = $taxationRateCode;
    }

    /**
     * @return int
     */
    public function getTaxationSystemCode(): int
    {
        return $this->taxationSystemCode;
    }

    /**
     * @param int $taxationSystemCode
     */
    public function setTaxationSystemCode(int $taxationSystemCode): void
    {
        $this->taxationSystemCode = $taxationSystemCode;
    }

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
}