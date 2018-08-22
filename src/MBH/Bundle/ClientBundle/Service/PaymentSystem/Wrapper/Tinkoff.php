<?php
/**
 * Created by PhpStorm.
 * Date: 21.08.18
 */

namespace MBH\Bundle\ClientBundle\Service\PaymentSystem\Wrapper;

use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\CheckResultHolder;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\Tinkoff\Notification;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Tinkoff
 * @package MBH\Bundle\ClientBundle\Service\PaymentSystem\Wrapper
 *
 * @property \MBH\Bundle\ClientBundle\Document\Tinkoff $entity
 */
class Tinkoff extends CommonWrapper
{
    public function checkRequest(Request $request, ClientConfig $config): CheckResultHolder
    {
        $notification = Notification::parseRequest($request);

        $holder = new CheckResultHolder();

        if ($notification === null) {

            return $holder;
        }

        /** для прохождения тест-кейсов */
        $statusForTestCase = [
            Notification::STATUS_AUTHORIZED,
            Notification::STATUS_REJECTED,
            Notification::STATUS_REFUNDED,
        ];
        if (in_array($notification->getStatus(), $statusForTestCase)) {
            $holder->setInterimResponse(new Response('OK'));

            return $holder;
        }

        /**
         * для сравнения ключей c фискализацией
         */

        if (!$notification->compareToken($this->entity)
            || !$notification->isSuccess()
            || $notification->getStatus() !== Notification::STATUS_CONFIRMED) {

            return $holder;
        }

        $holder->setDoc($notification->getOrderId());
        $holder->setText('OK');

        return $holder;
    }

    public function getFormData(CashDocument $cashDocument, $url = null, $checkUrl = null)
    {
        return [
            'cashDocumentId' => $cashDocument->getId(),
        ];
    }

    /**
     * Используются две подписи:
     *  одна для инициализации: src/MBH/Bundle/ClientBundle/Lib/PaymentSystem/Tinkoff/InitRequest.php
     *  вторая для нотификации src/MBH/Bundle/ClientBundle/Lib/PaymentSystem/Tinkoff/Notification.php
     *
     * @param CashDocument $cashDocument
     * @param null $url
     * @return void
     */
    public function getSignature(CashDocument $cashDocument, $url = null)
    {
    }
}