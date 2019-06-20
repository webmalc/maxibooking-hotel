<?php
/**
 * Date: 20.06.19
 */

namespace MBH\Bundle\BillingBundle\Service;


use MBH\Bundle\BaseBundle\Document\NotificationType;
use MBH\Bundle\BaseBundle\Service\Messenger\Notifier;
use MBH\Bundle\BillingBundle\Exception\BillingException;
use MBH\Bundle\HotelBundle\Document\Hotel;

class BillingExceptionHandler
{
    /**
     * @var Notifier
     */
    private $notifier;

    public function __construct(Notifier $notifier)
    {
        $this->notifier = $notifier;
    }

    public function sendNotifier(BillingException $exception, Hotel $hotel = null): void
    {
        $message = $this->notifier::createMessage();
        $message
            ->setText('notifier.online.city_not_found.message')
            ->setFrom('online')
            ->setSubject('notifier.online.city_not_found.message')
            ->setType('danger')
            ->setCategory('notification')
            ->setAutohide(false)
            ->setMessageType(NotificationType::ERROR);
        //send to backend
        if ($hotel !== null) {
            $message->setHotel($hotel);
        }

        try {
            $this->notifier->setMessage($message)->notify();
        } catch (\Throwable $e) {
        }
    }


}
