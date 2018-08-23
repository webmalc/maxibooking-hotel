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
 * Class Rbk
 * @package MBH\Bundle\ClientBundle\Service\PaymentSystem\Wrapper
 *
 * @property \MBH\Bundle\ClientBundle\Document\Rbk $entity
 */
class Rbk extends Wrapper
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
            'action'       => 'https://rbkmoney.ru/acceptpurchase.aspx',
            'testAction'   => 'https://rbkmoney.ru/acceptpurchase.aspx',
            'shopId'       => $this->entity->getRbkEshopId(),
            'total'        => $cashDocument->getTotal(),
            'orderId'      => $cashDocument->getId(),
            'touristId'    => $cashDocument->getOrder()->getId(),
            'cardId'       => $cashDocument->getOrder()->getId(),
            'url'          => $url,
            'time'         => 60 * 30,
            'disabled'     => $createdAt <= new \DateTime(),
            'touristEmail' => $payer ? $payer->getEmail() : null,
            'touristPhone' => $payer ? $payer->getPhone(true) : null,
            'comment'      => 'Order # ' . $cashDocument->getOrder()->getId() . '. CashDocument #' . $cashDocument->getId(),
            'signature'    => $this->getSignature($cashDocument, $url),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getSignature(CashDocument $cashDocument, $url = null)
    {
        $payer = $cashDocument->getPayer();

        return $this->calcSignature([
            $this->entity->getRbkEshopId(),
            $cashDocument->getTotal(),
            'RUR',
            $payer ? $payer->getEmail() : '',
            'Order # ' . $cashDocument->getOrder()->getId() . '. CashDocument #' . $cashDocument->getId(),
            $cashDocument->getId(),
            $this->entity->getRbkSecretKey(),
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
     * @inheritdoc
     */
    public function checkRequest(Request $request, ClientConfig $clientConfig): CheckResultHolder
    {
        $eshopId = $request->get('eshopId');
        $orderId = $request->get('orderId');
        $serviceName = $request->get('serviceName');
        $eshopAccount = $request->get('eshopAccount');
        $recipientAmount = $request->get('recipientAmount');
        //$paymentAmount = $request->get('paymentAmount');
        $recipientCurrency = $request->get('recipientCurrency');
        //$paymentCurrency = $request->get('paymentCurrency');
        $paymentStatus = $request->get('paymentStatus');
        $userName = $request->get('userName');
        $userEmail = $request->get('userEmail');
        $paymentData = $request->get('paymentData');

        $requestSignature = $request->get('hash');

        $holder = new CheckResultHolder();

        if (!$eshopId || !$paymentStatus || !$requestSignature || $paymentStatus != 5) {
            return $holder;
        }
        $signature = $this->calcSignature([
            $eshopId,
            $orderId,
            $serviceName,
            $eshopAccount,
            $recipientAmount,
            //$paymentAmount,
            $recipientCurrency,
            //$paymentCurrency,
            $paymentStatus,
            $userName,
            $userEmail,
            $paymentData,
            $this->entity->getRbkSecretKey(),
        ]);

        if ($signature != $requestSignature) {
            return $holder;
        }

        return $holder->parseData([
            'doc'               => $orderId,
            'commission'        => $this->entity::COMMISSION,
            'commissionPercent' => true,
            'text'              => 'OK',
        ]);
    }
}