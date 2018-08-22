<?php
/**
 * Created by PhpStorm.
 * Date: 22.08.18
 */

namespace MBH\Bundle\ClientBundle\Service\PaymentSystem\Wrapper;

use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\CheckResultHolder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Moneymail
 * @package MBH\Bundle\ClientBundle\Service\PaymentSystem\Wrapper
 *
 * @property \MBH\Bundle\ClientBundle\Document\Moneymail $entity
 */
class Moneymail extends Wrapper
{
    /**
     * @inheritdoc
     */
    public function getFormData(CashDocument $cashDocument, $url = null, $checkUrl = null)
    {
        $payer = $cashDocument->getPayer();
        $createdAt = clone $cashDocument->getCreatedAt();
        $createdAt->modify('+30 minutes');

        return [
            'action'       => 'https://cardpay.krasplat.ru/pay',
            'testAction'   => 'https://testcardpay.krasplat.ru/pay',
            'shopId'       => $this->entity->getMoneymailShopIDP(),
            'total'        => $cashDocument->getTotal(),
            'orderId'      => $cashDocument->getId(),
            'touristId'    => $cashDocument->getId(),
            'url'          => $url,
            'time'         => 60 * 30,
            'disabled'     => $createdAt <= new \DateTime(),
            'touristEmail' => $payer ? $payer->getEmail() : null,
            'comment'      => 'Order # ' . $cashDocument->getOrder()->getId(),
            'signature'    => $this->getSignature($cashDocument, $url),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getSignature(CashDocument $cashDocument, $url = null)
    {
        $sig = $this->entity->getMoneymailShopIDP() . $cashDocument->getId() . $cashDocument->getTotal();
        $sig .= $cashDocument->getId();
        $sig .= $url . $this->entity->getMoneymailKey();

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
        $signature = $cashDocumentId . $status . $shopId . $cyberSourceTransactionNumber . $commission . $this->entity->getMoneymailKey();
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