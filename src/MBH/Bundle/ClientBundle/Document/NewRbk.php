<?php
/**
 * Created by PhpStorm.
 * Date: 06.06.18
 */

namespace MBH\Bundle\ClientBundle\Document;


use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\CheckResultHolder;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\NewRbk\CheckWebhook;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\NewRbk\Webhook;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\NewRbk\InvoiceFromWebhook;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class NewRbk
 * @package MBH\Bundle\ClientBundle\Document\PaymentSystem
 * @ODM\EmbeddedDocument()
 */
class NewRbk implements PaymentSystemInterface
{
    private const LIFETIME_INVOICE = 1;

    /**
     * @var int
     * @ODM\Field(type="integer")
     * @Assert\GreaterThanOrEqual(1)
     */
    protected $lifetimeInvoice = self::LIFETIME_INVOICE;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $apiKey;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\Range(min="1", max="40")
     */
    protected $shopId;

    /**
     * @var float
     * @ODM\Field(type="float")
     */
    protected $taxationRateCode = 18;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $webhookKey;

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
     * @return float
     */
    public function getTaxationRateCode(): ?float
    {
        return $this->taxationRateCode;
    }

    /**
     * @param float $taxationRateCode
     */
    public function setTaxationRateCode(?float $taxationRateCode = 18): void
    {
        $this->taxationRateCode = $taxationRateCode;
    }

    public function getFormData(CashDocument $cashDocument, $url = null, $checkUrl = null)
    {
        return [];
    }

    public function checkRequest(Request $request, ClientConfig $clientConfig): CheckResultHolder
    {
        $check = new CheckWebhook($request,$clientConfig);

        $holder = new CheckResultHolder();

        if (!$check->verifySignature()) {
            $holder->setIndividualErrorResponse($check->getErrorResponse());
            return $holder;
        }

        $webhook = Webhook::parseAndCreate($check->getContent());

        $a = $webhook->getEventType() != Webhook::PAYMENT_CAPTURED;
        $b = $webhook->getTopic() != Webhook::INVOICES_TOPIC;


        if ($webhook->getEventType() != Webhook::PAYMENT_CAPTURED ||
            $webhook->getTopic() != Webhook::INVOICES_TOPIC) {
            return $holder;
        }

        $invoice = $webhook->getInvoice();

        if ($invoice === null) {
            return $holder;
        }

        $holder->setDoc($invoice->getCashDocumentId());
        $holder->setText('Ok');

        return $holder;
    }

    public function getSignature(CashDocument $cashDocument, $url = null)
    {
        // TODO: Implement getSignature() method.
    }
}