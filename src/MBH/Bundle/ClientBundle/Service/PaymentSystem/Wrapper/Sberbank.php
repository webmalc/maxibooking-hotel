<?php
/**
 * Created by PhpStorm.
 * Date: 30.08.18
 */

namespace MBH\Bundle\ClientBundle\Service\PaymentSystem\Wrapper;


use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\CheckResultHolder;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank\CallbackNotification;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Sberbank
 * @package MBH\Bundle\ClientBundle\Service\PaymentSystem\Wrapper
 * @property \MBH\Bundle\ClientBundle\Document\PaymentSystem\Sberbank $entity
 */
class Sberbank extends Wrapper
{
    public const SUCCESSFUL_RESPONSE = 'OK';

    public function getFormData(CashDocument $cashDocument, $url = null, $checkUrl = null)
    {
        return [
            'cashDocumentId' => $cashDocument->getId(),
        ];
    }

    public function getSignature(CashDocument $cashDocument, $url = null)
    {
        // TODO: Implement getSignature() method.
    }

    public function checkRequest(Request $request, ClientConfig $config): CheckResultHolder
    {
        $notification = CallbackNotification::parseRequest($request);

        $holder = new CheckResultHolder();

        if ($notification === null) {
            return $holder;
        }

        $intermediateOperation = [
            CallbackNotification::OPERATION_REFUNDED,
            CallbackNotification::OPERATION_REVERSED,
            CallbackNotification::OPERATION_APPROVED,
        ];

        if (in_array($notification->getOperation(), $intermediateOperation)) {
            $holder->setInterimResponse(new Response(self::SUCCESSFUL_RESPONSE));

            return $holder;
        }

        if ($notification->getOperation() !== CallbackNotification::OPERATION_DEPOSITED
            && $notification->getStatus() !== CallbackNotification::STATUS_SUCCESS
            && $notification->getChecksum() !== $notification->generateHmacSha256($this->entity)
        ) {
            return $holder;
        }

        $holder->setDoc($notification->getOrderNumber());
        $holder->setText(self::SUCCESSFUL_RESPONSE);

        return $holder;
    }

}