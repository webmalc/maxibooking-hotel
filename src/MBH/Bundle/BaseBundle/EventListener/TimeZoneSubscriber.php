<?php

namespace MBH\Bundle\BaseBundle\EventListener;

use MBH\Bundle\BaseBundle\Service\Helper;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class TimeZoneSubscriber implements EventSubscriberInterface
{
    private $helper;

    public function __construct(Helper $helper)
    {
        $this->helper = $helper;
    }

    public function onKernelRequest()
    {
        $timeZone = $this->helper->getTimeZone();
        date_default_timezone_set($timeZone);
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
            ConsoleEvents::COMMAND => [[
                'onKernelRequest'
            ]],
            KernelEvents::REQUEST => [[
                'onKernelRequest'
            ]]
        ];
    }
}