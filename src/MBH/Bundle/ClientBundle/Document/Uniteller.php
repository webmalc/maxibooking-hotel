<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ODM\EmbeddedDocument
 */
class Uniteller implements PaymentSystemInterface
{

    const COMMISSION = 0.035;

    /**
     * @var string
     * @ODM\String()
     */
    protected $unitellerShopIDP;

    /**
     * @var string
     * @ODM\String()
     */
    protected $unitellerPassword;

    /**
     * Set unitellerShopIDP
     *
     * @param string $unitellerShopIDP
     * @return self
     */
    public function setUnitellerShopIDP($unitellerShopIDP)
    {
        $this->unitellerShopIDP = $unitellerShopIDP;
        return $this;
    }

    /**
     * Get unitellerShopIDP
     *
     * @return string $unitellerShopIDP
     */
    public function getUnitellerShopIDP()
    {
        return $this->unitellerShopIDP;
    }

    /**
     * Set unitellerPassword
     *
     * @param string $unitellerPassword
     * @return self
     */
    public function setUnitellerPassword($unitellerPassword)
    {
        $this->unitellerPassword = $unitellerPassword;
        return $this;
    }

    /**
     * Get unitellerPassword
     *
     * @return string $unitellerPassword
     */
    public function getUnitellerPassword()
    {
        return $this->unitellerPassword;
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
            'action' => 'https://wpay.uniteller.ru/pay/',
            'testAction' => 'https://test.wpay.uniteller.ru/pay/',
            'shopId' => $this->getUnitellerShopIDP(),
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
        return strtoupper(
            md5(
                md5($this->getUnitellerShopIDP()) . "&" .                // $Shop_IDP
                md5($cashDocument->getId()) . "&" .                      // $Order_IDP
                md5($cashDocument->getTotal()) . "&" . // $Subtotal_P
                md5('') . "&" .                                          // $MeanType
                md5('') . "&" .                                          // $EMoneyType
                md5('') . "&" .                                          // $Lifetime
                md5($cashDocument->getOrder()->getId()) . "&" .          // $Customer_IDP
                md5('') . "&" .                                          // $Card_IDP
                md5('') . "&" .                                          // $IData
                md5('') . "&" .                                          // $PT_Code
                md5($this->getUnitellerPassword())                       // $password
            )
        );
    }

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
        $signature = $cashDocumentId . $status . $this->getUnitellerPassword();
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
