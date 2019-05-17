<?php
/**
 * Date: 17.05.19
 */

namespace MBH\Bundle\OnlineBundle\EventListener;


use MBH\Bundle\OnlineBundle\Services\ApiResponseCompiler;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

class ApiResponseCompilerViewListener
{
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        /** @var ApiResponseCompiler $value */
        $value = $event->getControllerResult();

        if (!($value instanceof ApiResponseCompiler)) {
            return;
        }

        $event->setResponse($value->getResponse());
    }
}
