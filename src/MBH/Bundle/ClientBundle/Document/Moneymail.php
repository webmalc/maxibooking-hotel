<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\CheckResultHolder;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ODM\EmbeddedDocument
 */
class Moneymail implements PaymentSystemInterface
{
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $moneymailShopIDP;

    /**
     * @var string
     * @ODM\Field(type="string")
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

    /**
     * @inheritdoc
     */
    public function getFormData(CashDocument $cashDocument, $url = null , $checkUrl = null)
    {
        $payer = $cashDocument->getPayer();
        $createdAt = clone $cashDocument->getCreatedAt();
        $createdAt->modify('+30 minutes');

        return [
            'action' => 'https://cardpay.krasplat.ru/pay',
            'testAction' => 'https://testcardpay.krasplat.ru/pay',
            'shopId' => $this->getMoneymailShopIDP(),
            'total' => $cashDocument->getTotal(),
            'orderId' => $cashDocument->getId(),
            'touristId' => $cashDocument->getId(),
            'url' => $url,
            'time' => 60 * 30,
            'disabled' => $createdAt <= new \DateTime(),
            'touristEmail' => $payer ? $payer->getEmail() : null,
            'comment' => 'Order # ' . $cashDocument->getOrder()->getId(),
            'signature' => $this->getSignature($cashDocument, $url),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getSignature(CashDocument $cashDocument, $url = null)
    {
        $sig = $this->getMoneymailShopIDP() . $cashDocument->getId() . $cashDocument->getTotal();
        $sig .= $cashDocument->getId();
        $sig .= $url . $this->getMoneymailKey();

        return strtoupper(str_replace('-', '', md5($sig)));
    }

    /**
     * @inheritdoc
     */
    public function checkRequest(Request $request, ClientConfig $clientConfig): CheckResultHolder
    {
        $cashDocumentId = $request->get('Order_IDP');
        $shopId = $request->get('Shop_IDP');
        $status = $request->get('Status');
        $cyberSourceTransactionNumber = $request->get('CyberSourceTransactionNumber');
        $requestSignature = $request->get('Signature');
        $commission = $request->get('Comission');

        $holder = new CheckResultHolder();

        if (!$cashDocumentId || !$shopId || !$status || !$requestSignature || $status != 'AS000') {
            return $holder;
        }
        $signature = $cashDocumentId . $status . $shopId . $cyberSourceTransactionNumber . $commission . $this->getMoneymailKey();
        $signature = strtoupper(str_replace('-', '', md5($signature)));

        if ($signature != $requestSignature) {
            return $holder;
        }

        return $holder->parseData([
            'doc'        => $cashDocumentId,
            'commission' => $commission,
            'text'       => 'OK',
        ]);
    }
}
