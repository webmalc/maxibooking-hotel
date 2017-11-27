<?php

namespace MBH\Bundle\BaseBundle\EventListener;

use MBH\Bundle\ClientBundle\Service\ClientManager;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
        $timeZone = $this->container->get('mbh.helper')->getTimeZone();
        $this->container->get('twig')->getExtension('Twig_Extension_Core')->setTimezone($timeZone);

        $clientManager = $this->container->get('mbh.client_manager');

        if (!$clientManager->isClientActive()
            && !$clientManager->isRouteAccessibleForInactiveClient($event->getRequest()->get('_route'))
            && $this->container->get('security.token_storage')->getToken()
            && $this->container->get('security.token_storage')->getToken()->getUser() instanceOf User
            && $this->container->get('security.authorization_checker')->isGranted('ROLE_PAYMENTS')
        ) {
            $url = $this->container->get('router')->generate(ClientManager::DEFAULT_ROUTE_FOR_INACTIVE_CLIENT);
            $response = new RedirectResponse($url);
            $event->setResponse($response);
        }
    }
}