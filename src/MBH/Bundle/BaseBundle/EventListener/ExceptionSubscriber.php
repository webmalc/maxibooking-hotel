<?php
/**
 * Created by PhpStorm.
 * User: danya
 * Date: 10.06.17
 * Time: 15:12
 */

namespace MBH\Bundle\BaseBundle\EventListener;

use MBH\Bundle\BaseBundle\Service\Messenger\Notifier;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{
    /** @var $slackNotifier  Notifier */
    private $slackNotifier;
    private $domain;
    private $environment;

    public function __construct(Notifier $slackNotifier, $domain, $environment)
    {
        $this->slackNotifier = $slackNotifier;
        $this->domain = $domain;
        $this->environment = $environment;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => [
                ['notifyException', 0]
            ]
        ];
    }

    public function notifyException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if (!$exception instanceof AccessDeniedException && $this->environment === 'prod') {
            $message = $this->slackNotifier::createMessage();
            $messageText = "Произошла ошибка у \"" . $this->domain
                . ". \"\n Сообщение \"" . $exception->getMessage()
                . "\".\n Стек:" . $exception->getTraceAsString();
            $message->setText($messageText);
            $this->slackNotifier->setMessage($message)->notify();
        }
    }
}