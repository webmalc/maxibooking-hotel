<?php

namespace MBH\Bundle\HotelBundle\EventListener;

use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CheckHotelListener
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface 
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        /*
         * $controller passed can be either a class or a Closure. This is not usual in Symfony2 but it may happen.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof CheckHotelControllerInterface) {
            if(!$this->container->get('mbh.hotel.selector')->getSelected() instanceof Hotel) {

                $redirectUrl = $this->container->get('router')->generate('hotel_not_found');
                
                $event->setController(function() use ($redirectUrl) {
                    return new RedirectResponse($redirectUrl);
                });
            }
        }
    }

}
