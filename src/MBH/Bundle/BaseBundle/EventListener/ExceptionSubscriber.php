<?php

namespace MBH\Bundle\BaseBundle\EventListener;

use MBH\Bundle\BaseBundle\Service\Messenger\Notifier;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ExceptionSubscriber implements EventSubscriberInterface
{
    /** @var \AppKernel  */
    private $kernel;
    private $exceptionNotifier;
    private $tokenStorage;

    public function __construct(KernelInterface $kernel, Notifier $exceptionNotifier, TokenStorage $tokenStorage)
    {
        $this->kernel = $kernel;
        $this->exceptionNotifier = $exceptionNotifier;
        $this->tokenStorage = $tokenStorage;
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
        $user = $this->tokenStorage->getToken();
        if (!$exception instanceof AccessDeniedException && !$exception instanceof NotFoundHttpException && $this->kernel->getEnvironment() === 'prod') {
            $user = $this->tokenStorage->getToken();
            $message = $this->exceptionNotifier::createMessage();
            $messageText = "Произошла ошибка у \"" . $this->kernel->getClient()
                . '. Пользователь: ' . (!empty($user) ? $user->getUsername() : 'без пользователя')
                . ". \"\n Сообщение \"" . $exception->getMessage()
                . "\".\n Стек:" . $exception->getTraceAsString();
            $message
                ->setType('danger')
                ->setText($messageText);
            $this->exceptionNotifier
                ->setMessage($message)
                ->notify();
        }
    }
}