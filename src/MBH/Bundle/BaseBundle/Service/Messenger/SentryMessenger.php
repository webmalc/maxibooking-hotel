<?php

namespace MBH\Bundle\BaseBundle\Service\Messenger;

use Symfony\Bridge\Monolog\Logger;

class SentryMessenger implements \SplObserver
{
    /** @var Logger */
    private $sentryLogger;

    public function __construct(Logger $sentryLogger) {
        $this->sentryLogger = $sentryLogger;
    }

    public function update(\SplSubject $notifier)
    {
        /** @var NotifierMessage $message */
        /** @var Notifier $notifier */
        $message = $notifier->getMessage();
        $messageTypeCode = $message->getType() === 'danger' ? Logger::CRITICAL : Logger::INFO;

        $this->sentryLogger->addRecord($messageTypeCode, $message->getText());
    }
}