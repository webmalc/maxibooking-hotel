<?php

namespace MBH\Bundle\BaseBundle\EventListener;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\BaseBundle\Controller\EnvironmentInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use MBH\Bundle\BaseBundle\Controller\DeletableControllerInterface;

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
        //remove deletable filter
        if ($controller[0] instanceof DeletableControllerInterface) {

            $collection = $this->container->get('doctrine_mongodb')->getManager()->getFilterCollection();

            if ($collection->isEnabled('softdeleteable')) {
                $collection->disable('softdeleteable');
            }
        }
    }

}
