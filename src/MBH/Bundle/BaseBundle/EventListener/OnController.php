<?php

namespace MBH\Bundle\BaseBundle\EventListener;

use Doctrine\ODM\MongoDB\Query\FilterCollection;
use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\BaseBundle\Controller\DeletableControllerInterface;
use MBH\Bundle\BaseBundle\Controller\EnvironmentInterface;
use MBH\Bundle\BaseBundle\Controller\HotelableControllerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OnController
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

        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof BaseController) {
            //check messages
            $this->container->get('mbh.system.messenger')->get();
        }

        //demo version
        if ($controller[0] instanceof EnvironmentInterface && $this->container->getParameter('mbh.environment') != 'prod') {

            throw new NotFoundHttpException('Page not found');
        }
        /** @var FilterCollection $collection */
        $collection = $this->container->get('doctrine_mongodb')->getManager()->getFilterCollection();
        //remove deletable filter
        if ($controller[0] instanceof DeletableControllerInterface) {
            if ($collection->isEnabled('softdeleteable')) {
                $collection->disable('softdeleteable');
            }
        }
        if ($controller[0] instanceof HotelableControllerInterface) {
            if (!$collection->isEnabled('hotelable')) {
                $collection->enable('hotelable');
                $collection->getFilter('hotelable')->setHotelSelector($this->container->get('mbh.hotel.selector'));
            }
        }
    }

}
