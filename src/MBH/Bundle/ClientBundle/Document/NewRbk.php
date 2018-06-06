<?php
/**
 * Created by PhpStorm.
 * Date: 06.06.18
 */

namespace MBH\Bundle\ClientBundle\Document;


use MBH\Bundle\CashBundle\Document\CashDocument;
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
    public function getLifetimeInvoice(): int
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


    public function getFormData(CashDocument $cashDocument, $url = null, $checkUrl = null)
    {
        // TODO: Implement getFormData() method.
    }

    public function checkRequest(Request $request)
    {
        // TODO: Implement checkRequest() method.
    }

    public function getSignature(CashDocument $cashDocument, $url = null)
    {
        // TODO: Implement getSignature() method.
    }
}