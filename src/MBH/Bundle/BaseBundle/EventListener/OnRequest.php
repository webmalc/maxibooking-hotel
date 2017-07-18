<?php

namespace MBH\Bundle\BaseBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;


class OnRequest
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface 
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public function onKernelRequest(GetResponseEvent $event)
    {
        //set default timezone
        $tz = $this->container->getParameter('mbh.timezone');
        if (!empty($tz) && $tz != 'default') {
            $this->container->get('twig')->getExtension('core')->setTimezone($tz);
        }

    }
}
