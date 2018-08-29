<?php
/**
 * Created by PhpStorm.
 * Date: 01.08.18
 */

namespace MBH\Bundle\ClientBundle\Document\PaymentSystem;


use MBH\Bundle\ClientBundle\Lib\PaymentSystem\FiscalizationTrait;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\TaxMapInterface;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Tinkoff
 * @ODM\EmbeddedDocument()
 */
class Tinkoff extends PaymentSystemDocument implements TaxMapInterface
{
    use FiscalizationTrait;

    public const URL_API = 'https://securepay.tinkoff.ru/v2';

    private const DEFAULT_TAX_RATE = 'vat18';
    private const DEFAULT_TAX_SYSTEM = 'osn';

    private const TAX_SYSTEM_MAP = [
        0 => self::DEFAULT_TAX_SYSTEM,
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
        18  => self::DEFAULT_TAX_RATE,
        110 => 'vat110',
        118 => 'vat118',
    ];

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\NotBlank()
     */
    private $terminalKey;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    private $language = 'ru';

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\NotBlank()
     */
    private $secretKey;

    /**
     * @var int
     * @ODM\Field(type="integer")
     */
    private $redirectDueDate = 24;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\NotBlank()
     */
    protected $taxationRateCode = self::DEFAULT_TAX_RATE;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\NotBlank()
     */
    protected $taxationSystemCode = self::DEFAULT_TAX_SYSTEM;

    /**
     * @return int
     */
    public function getRedirectDueDate(): int
    {
        return $this->redirectDueDate;
    }

    /**
     * @param int $redirectDueDate
     */
    public function setRedirectDueDate(?int $redirectDueDate): void
    {
        $this->redirectDueDate = $redirectDueDate;
    }

    /**
     * @return null|string
     */
    public function getSecretKey(): ?string
    {
        return $this->secretKey;
    }

    /**
     * @param string $secretKey
     */
    public function setSecretKey(?string $secretKey): void
    {
        $this->secretKey = $secretKey;
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @param string $language
     */
    public function setLanguage(?string $language): void
    {
        $this->language = $language;
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
    public function setTaxationRateCode(?string $taxationRateCode): self
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
    public function setTaxationSystemCode(?string $taxationSystemCode): self
    {
        $this->taxationSystemCode = $taxationSystemCode;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTerminalKey(): ?string
    {
        return $this->terminalKey;
    }

    /**
     * @param string $terminalKey
     */
    public function setTerminalKey(?string $terminalKey): void
    {
        $this->terminalKey = $terminalKey;
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