<?php
/**
 * Created by PhpStorm.
 * Date: 06.06.18
 */

namespace MBH\Bundle\ClientBundle\Document\PaymentSystem;


use MBH\Bundle\ClientBundle\Lib\PaymentSystem\FiscalizationInterface;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\FiscalizationTrait;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\TaxMapInterface;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class NewRbk
 * @package MBH\Bundle\ClientBundle\Document\PaymentSystem
 * @ODM\EmbeddedDocument()
 */
class NewRbk extends PaymentSystemDocument implements TaxMapInterface, FiscalizationInterface
{
    use FiscalizationTrait;

    public const URL_FOR_CHECKOUT_JS = 'https://checkout.rbk.money/checkout.js';
    public const TYPE_POST_MSG = 'mbh-payment-newRbk';
    public const WITHOUT_TAX_RATE = 'none';

    private const LIFETIME_INVOICE = 1;
    private const DEFAULT_TAX_RATE = '18%';

    private const TAX_RATE_MAP = [
        -1  => self::WITHOUT_TAX_RATE,
        0   => '0%',
        10  => '10%',
        18  => self::DEFAULT_TAX_RATE,
        110 => '10/110',
        118 => '18/118',
    ];

    /**
     * @var int
     * @ODM\Field(type="integer")
     * @Assert\GreaterThanOrEqual(1)
     */
    protected $lifetimeInvoice = self::LIFETIME_INVOICE;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\NotBlank()
     */
    protected $apiKey;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\Range(min="1", max="40")
     * @Assert\NotBlank()
     */
    protected $shopId;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $taxationRateCode = self::DEFAULT_TAX_RATE;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\NotBlank()
     */
    protected $webhookKey;

    public function getTaxRateMap(): array
    {
        return self::TAX_RATE_MAP;
    }

    public function getTaxSystemMap(): array
    {
        return [];
    }

    /**
     * @return string
     */
    public function getWebhookKey(): ?string
    {
        return $this->webhookKey;
    }

    /**
     * @param string $webhookKey
     */
    public function setWebhookKey(string $webhookKey): void
    {
        $this->webhookKey = $webhookKey;
    }

    /**
     * @return string
     */
    public function getShopId(): ?string
    {
        return $this->shopId;
    }

    /**
     * @param string $shopId
     */
    public function setShopId(string $shopId): void
    {
        $this->shopId = $shopId;
    }

    /**
     * @return string
     */
    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @return int
     */
    public function getLifetimeInvoice(): ?int
    {
        return $this->lifetimeInvoice;
    }

    /**
     * @param int $lifetimeInvoice
     */
    public function setLifetimeInvoice(int $lifetimeInvoice): void
    {
        $this->lifetimeInvoice = $lifetimeInvoice;
    }

    /**
     * @return string
     */
    public function getTaxationRateCode(): ?string
    {
        return $this->taxationRateCode;
    }

    /**
     * @param float $taxationRateCode
     */
    public function setTaxationRateCode(string $taxationRateCode): void
    {
        $this->taxationRateCode = $taxationRateCode;
    }

    public function getTaxationSystemCode()
    {

    }
}