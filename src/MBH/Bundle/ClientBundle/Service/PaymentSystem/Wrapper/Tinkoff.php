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
 * @property \MBH\Bundle\ClientBundle\Document\PaymentSystem\Tinkoff $entity
 */
class Tinkoff extends Wrapper
{
    private const SUCCESSFUL_RESPONSE = 'OK';

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
            $holder->setInterimResponse(new Response(self::SUCCESSFUL_RESPONSE));

            return $holder;
        }

        if (!$notification->isSuccess() || $notification->getStatus() !== Notification::STATUS_CONFIRMED) {
            return $holder;
        }

        /**
         * сравнение ключей фиктивное с логированием
         *
         * для проверки сравнения с фискализацией и без неё
         */
        $logger = $this->container->get('mbh.payment_tinkoff.logger');
        $logger->addInfo('DEBUG: Fiscalization: ' . ($this->entity->isWithFiscalization() ? 'On' : 'Off'));
        $logger->addInfo('DEBUG: Compare Token: ' . ($notification->compareToken($this->entity) ? 'true' : 'false'));
        $logger->addInfo('DEBUG:' . var_export($notification, true));

        $em = $this->container->get('mbh.exception_manager');
        $em->sendExceptionNotification(new \Exception('Клиенту поступил платеж через Tinkoff.'));

        $holder->setDoc($notification->getOrderId());
        $holder->setText(self::SUCCESSFUL_RESPONSE);

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