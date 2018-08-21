<?php

namespace MBH\Bundle\BaseBundle\EventListener;

use MBH\Bundle\BaseBundle\Service\ExceptionManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

class ExceptionSubscriber implements EventSubscriberInterface
{
    /** @var \AppKernel  */
    private $kernel;
    private $exceptionManager;

    public function __construct(KernelInterface $kernel, ExceptionManager $exceptionManager)
    {
        $this->kernel = $kernel;
        $this->exceptionManager = $exceptionManager;
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

    /**
     * @param GetResponseForExceptionEvent $event
     * @throws \Throwable
     */
    public function notifyException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if (!$exception instanceof AccessDeniedException && !$exception instanceof NotFoundHttpException && $this->kernel->getEnvironment() === 'prod') {
            $this->exceptionManager->sendExceptionNotification($exception);
        }
    }
}