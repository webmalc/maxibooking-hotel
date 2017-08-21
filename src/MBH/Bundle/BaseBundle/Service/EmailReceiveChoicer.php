<?php


namespace MBH\Bundle\BaseBundle\Service;


use MBH\Bundle\BaseBundle\Document\NotificationConfig;
use MBH\Bundle\BaseBundle\Document\NotificationConfigRepository;
use MBH\Bundle\BaseBundle\Lib\ChoicerInterface;
use MBH\Bundle\BaseBundle\Lib\NotifierChoicerException;

class EmailReceiveChoicer
{
    /** @var  NotificationConfig */
    private $notificationConfig;

    /**
     * EmailReceiveChoicer constructor.
     * @param NotificationConfigRepository $notificationConfigRepository
     */
    public function __construct(NotificationConfigRepository $notificationConfigRepository)
    {
        $this->notificationConfig = $notificationConfigRepository->fetchConfig();
    }


    public function makeChoice(ChoicerInterface $message): bool
    {
        $choice = true;
        if ($message->getReceiverGroup() && $message->getMessageType()) {
            $receiverGroup = $message->getReceiverGroup();
            $messageType = $message->getMessageType();
            $method = 'getEmail'.ucfirst($receiverGroup);
            try {
                $allowType = $this->notificationConfig->$method();
                $choice = in_array($messageType, $allowType);
            } catch (\Throwable $e) {
                $message = sprintf('No method %s in %s.', $method, get_class($this->notificationConfig));
                throw new NotifierChoicerException($message.$e->getMessage());
            }
        }

        return $choice;
    }
}