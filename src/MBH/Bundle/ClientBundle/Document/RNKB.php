<?php

namespace MBH\Bundle\ClientBundle\Document;

use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\EmbeddedDocument
 */
class RNKB implements PaymentSystemInterface
{
    const COMMISSION = 0.035;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $rnkbShopIDP;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $key;

    /**
     * @param string $rnkbShopIDP
     * @return self
     */
    public function setRnkbShopIDP($rnkbShopIDP)
    {
        $this->rnkbShopIDP = $rnkbShopIDP;
        return $this;
    }

    /**
     * @return string
     */
    public function getRnkbShopIDP()
    {
        return $this->rnkbShopIDP;
    }

    /**
     * @param string $key
     * @return self
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    public function getCheckPaymentData(CashDocument $cashDocument)
    {
        $order = $cashDocument->getOrder();
        $card = $order->getCreditCard();

        if (!$order || !$card || !$card->getCvc()) {
            throw new Exception('Invalid order document or card document.');
        }

        $data =  [
            'PAN' => $card->getNumber(),
            'ExpYear' => $card->getYear(),
            'ExpMonth' => $card->getMonth(),
            'Subtotal' => (string) $cashDocument->getTotal(),
            'CVV' => $card->getCvc(),
            'ShopID' => $this->getRnkbShopIDP(),
            'OrderID' => $cashDocument->getId()
        ];

        //Signature
        $params = $data;
        $params['Password'] = $this->getKey();
        foreach ($params as $key => $value) {
            $params[$key] = md5($value);
        }

        $data['Signature'] = mb_strtoupper(md5(implode('', $params)));

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getFormData(CashDocument $cashDocument, $url = null, $checkUrl = null)
    {
        $payer = $cashDocument->getPayer();
        $createdAt = clone $cashDocument->getCreatedAt();
        $createdAt->modify('+30 minutes');

        return [
            'action' => 'https://pay.crim-hotel.ru/pay.aspx',
            'testAction' => 'https://pay.crim-hotel.ru/pay.aspx',
            'shopId' => $this->getRnkbShopIDP(),
            'total' => $cashDocument->getTotal(),
            'orderId' => $cashDocument->getId(),
            'touristId' => $cashDocument->getOrder()->getId(),
            'cardId' => $cashDocument->getOrder()->getId(),
            'url' => $url,
            'time' => 60 * 30,
            'disabled' => $createdAt <= new \DateTime(),
            'touristEmail' => $payer ? $payer->getEmail() : null,
            'touristPhone' => $payer ? $payer->getPhone(true) : null,
            'comment' => 'Order # ' . $cashDocument->getOrder()->getId() . '. CashDocument #' . $cashDocument->getId(),
            'signature' => $this->getSignature($cashDocument, $url),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getSignature(CashDocument $cashDocument, $url = null)
    {
        return mb_convert_encoding(strtoupper(
            md5(
                md5($this->getRnkbShopIDP()) . "&" .                 // $Shop_IDP
                md5($cashDocument->getId()) . "&" .                      // $Order_IDP
                md5($cashDocument->getTotal()) . "&" .                   // $Subtotal_P
                md5($this->getKey())                            // key
            )
        ), 'UTF-8');
    }
//(UTF-8) upper(md5(md5(Shop_IDP)  + ‘&’ +  md5(Order_IDP)  +  ‘&’ + md5(Subtotal_P) + ‘&’ +  md5(KEY)))

    /**
     * @inheritdoc
     */
    public function checkRequest(Request $request)
    {
        $cashDocumentId = $request->get('Order_ID');
        $status = $request->get('Status');
        $requestSignature = $request->get('Signature');

        if (!$cashDocumentId || !$status || !$requestSignature || !in_array($status, ['authorized', 'paid'])) {
            return false;
        }
        $signature = $cashDocumentId . $status . $this->getKey();
        $signature = strtoupper(md5($signature));

        if ($signature != $requestSignature) {
            return false;
        }

        return [
            'doc' => $cashDocumentId,
            'commission' => self::COMMISSION,
            'commissionPercent' => true,
            'text' => 'OK'
        ];
    }
}