<?php

namespace MBH\Bundle\BaseBundle\EventListener;

use MBH\Bundle\ClientBundle\Service\ClientManager;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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

        //temp fix
        if ($event->getRequest()->getPathInfo() === '/user/login_check' && $event->getRequest()->getMethod() === 'GET') {
            $response = new RedirectResponse('/user/login');
            $event->setResponse($response);
        }

        if (!$this->container->get('kernel')->isDefaultClient()) {
            $client = $clientManager->getClient();
            if (!$client->getTrial_activated() && $this->isRequestedByAuthUser()) {
                $client = $this->container->get('mbh.billing.api')->getClient();
                if (!$client->getTrial_activated()) {
                    $url = $clientManager->isRussianClient() ? ClientManager::INSTALLATION_PAGE_RU : ClientManager::INSTALLATION_PAGE_COM;
                    $url .= '?redirectedForTrial=true&client=' . $client->getLogin();
                    $response = new RedirectResponse($url);
                    $event->setResponse($response);
                } else {
                    $this->container->get('mbh.client_manager')->updateSessionClientData($client, new \DateTime());
                }
            } elseif (!$clientManager->isClientActive()
                && $session->get(ClientManager::NOT_CONFIRMED_BECAUSE_OF_ERROR) !== true
                && !$clientManager->isRouteAccessibleForInactiveClient($event->getRequest()->get('_route'))
                && $this->isRequestedByAuthUser()
                && !$this->isRequestByMbUser()
            ) {
                if ($this->container->get('security.authorization_checker')->isGranted('ROLE_PAYMENTS')) {
                    if (!$session->getFlashBag()->has('error')) {
                        $session->getFlashBag()->add('error', 'on_request_listener.mb_not_paid_error');
                    }
                    $url = $this->container->get('router')->generate(ClientManager::DEFAULT_ROUTE_FOR_INACTIVE_CLIENT);
                    $response = new RedirectResponse($url);
                    $event->setResponse($response);
                } else {
                    throw new AccessDeniedException('The payment is in arrears and user hasn\'t rights to pay.');
                }
            }
        }
    }

    /**
     * @return bool
     */
    private function isRequestedByAuthUser()
    {
        $token = $this->getSecurityToken();

        return $token && $token->getUser() instanceOf User;
    }

    private function isRequestByMbUser()
    {
        return $this->getSecurityToken()->getUser()->getUsername() === 'mb';
    }

    /**
     * @return null|\Symfony\Component\Security\Core\Authentication\Token\TokenInterface
     */
    private function getSecurityToken()
    {
        return $this->container->get('security.token_storage')->getToken();
    }
}