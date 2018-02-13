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
        $clientManager = $this->container->get('mbh.client_manager');
        $session = $this->container->get('session');

        if (!($this->container->getParameter('client') === 'maxibooking' || $clientManager->isClientActive())
            && $session->get(ClientManager::NOT_CONFIRMED_BECAUSE_OF_ERROR) !== true
            && !$clientManager->isRouteAccessibleForInactiveClient($event->getRequest()->get('_route'))
            && $this->container->get('security.token_storage')->getToken()
            && $this->container->get('security.token_storage')->getToken()->getUser() instanceOf User
            && $this->container->get('security.authorization_checker')->isGranted('ROLE_PAYMENTS')
        ) {
            if (!$session->getFlashBag()->has('error')) {
                $session->getFlashBag()->add('error', 'on_request_listener.mb_not_paid_error');
            }
            $url = $this->container->get('router')->generate(ClientManager::DEFAULT_ROUTE_FOR_INACTIVE_CLIENT);
            $response = new RedirectResponse($url);
            $event->setResponse($response);
        }
    }
}