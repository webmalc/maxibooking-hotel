<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ODM\EmbeddedDocument
 */
class Stripe implements PaymentSystemInterface
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

    /**
     * @param CashDocument $cashDocument
     * @param string $url
     * @param string $checkUrl
     * @return array
     */
    public function getFormData(CashDocument $cashDocument, $url = null, $checkUrl = null)
    {
        return [
            'token' => $this->getPublishableToken(),
            'amount' => $cashDocument->getTotal(),
            'email' => $cashDocument->getPayer()->getEmail(),
            'returnUrl' => $url,
            'description' => '',
            'signature' => $this->getSignature($cashDocument, $url),
            'orderId' => $cashDocument->getId()
        ];
    }

    /**
     * @param CashDocument $cashDocument
     * @param string $url
     * @return string
     */
    public function getSignature(CashDocument $cashDocument, $url = null)
    {
        return $this->calcSignature([
            'secretKey' => $this->getSecretKey(),
            'pubToken' => $this->getPublishableToken(),
            'amount' => $cashDocument->getTotal(),
            'email' => $cashDocument->getPayer()->getEmail(),
            'orderId' => $cashDocument->getId()
        ]);
    }

    /**
     * @param array $data
     * @return string
     */
    private function calcSignature(array $data)
    {
        return strtolower(md5(implode('::', $data)));
    }

    /**
     * @param Request $request
     * @return array|bool
     */
    public function checkRequest(Request $request)
    {
        $requestSignature = $request->get('signature');

        $orderId = $request->get('orderId');
        $signature = $this->calcSignature([
            'secretKey' => $this->getSecretKey(),
            'pubToken' => $this->getPublishableToken(),
            'amount' => $request->get('amount'),
            'email' => $request->get('email'),
            'orderId' => $request->get('orderId'),
        ]);

        if ($requestSignature != $signature) {
            return false;
        }

        return [
            'doc' => $orderId,
            'commission' => $this->getCommissionInPercents() ? $this->getCommissionInPercents() : null,
            'commissionPercent' => true,
            'text' => 'OK'
        ];
    }
}