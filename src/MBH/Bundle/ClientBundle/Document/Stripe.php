<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\CheckResultHolder;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemCommonDocument;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemInterface;
use Stripe\Charge;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @ODM\EmbeddedDocument
 */
class Stripe extends PaymentSystemCommonDocument
{
    const NAME = 'stripe';

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $publishableToken;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $secretKey;

    /**
     * @var float
     * @ODM\Field(type="float")
     */
    protected $commissionInPercents;

    /**
     * @return float
     */
    public function getCommissionInPercents(): ?float
    {
        return $this->commissionInPercents;
    }

    /**
     * @param float $commissionInPercents
     * @return Stripe
     */
    public function setCommissionInPercents(float $commissionInPercents): Stripe
    {
        $this->commissionInPercents = $commissionInPercents;

        return $this;
    }

    /**
     * @return string
     */
    public function getSecretKey(): ?string
    {
        return $this->secretKey;
    }

    /**
     * @param string $secretKey
     * @return Stripe
     */
    public function setSecretKey(string $secretKey): Stripe
    {
        $this->secretKey = $secretKey;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPublishableToken()
    {
        return $this->publishableToken;
    }

    /**
     * @param mixed $publishableToken
     * @return Stripe
     */
    public function setPublishableToken($publishableToken)
    {
        $this->publishableToken = $publishableToken;

        return $this;
    }
}