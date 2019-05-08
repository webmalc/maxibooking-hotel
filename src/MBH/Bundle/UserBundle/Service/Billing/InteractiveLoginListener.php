<?php

namespace MBH\Bundle\UserBundle\Service\Billing;

use MBH\Bundle\BillingBundle\Service\BillingApi;
use MBH\Bundle\ClientBundle\Service\ClientManager;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class InteractiveLoginListener
 * @package MBH\Bundle\UserBundle\Service\Billing
 */
class InteractiveLoginListener
{
    /** @var array */
    protected $params;
    /** @var  BillingApi */
    protected $billingApi;
    protected $clientManager;
    protected $session;
    protected $supportEmail;
    protected $translator;
    /** @var \AppKernel */
    protected $kernel;

    /**
     * InteractiveLoginListener constructor.
     * @param array $params
     * @param ClientManager $clientManager
     * @param Session $session
     * @param BillingApi $billingApi
     * @param TranslatorInterface $translator
     * @param $supportInfo
     * @param KernelInterface $kernel
     */
    public function __construct(
        array $params,
        ClientManager $clientManager,
        Session $session,
        BillingApi $billingApi,
        TranslatorInterface $translator,
        $supportInfo,
        KernelInterface $kernel
    )
    {
        $this->params = $params;
        $this->clientManager = $clientManager;
        $this->session = $session;
        $this->billingApi = $billingApi;
        $this->translator = $translator;
        $this->supportEmail = $supportInfo['email'];
        $this->kernel = $kernel;
    }

    /**
     * Listen for successful login events
     * @param \Symfony\Component\Security\Http\Event\InteractiveLoginEvent $event
     * @throws \Exception
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $isAuthorizedByToken = $event->getAuthenticationToken() instanceof PreAuthenticatedToken;
        $this->session->set(ClientManager::IS_AUTHORIZED_BY_TOKEN, $isAuthorizedByToken);

        if ($event->getAuthenticationToken() instanceof UsernamePasswordToken) {
            if (!$this->clientManager->isDefaultClient()) {

                $client = $this->clientManager->getClient();

                if ($client->getStatus() === 'not_confirmed') {
                    try {
                        $result = $this->clientManager->confirmClient($client);
                        if (!$result->isSuccessful()) {
                            $this->handleIncorrectConfirmationRequest();
                        }
                    } catch (\Exception $exception) {
                        $this->handleIncorrectConfirmationRequest();
                    }
                } else {
                    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                        $ip = $_SERVER['HTTP_CLIENT_IP'];
                    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                    } else {
                        $ip = $_SERVER['REMOTE_ADDR'];
                    }
                    $this->billingApi->sendClientAuthMessage($ip, $event->getRequest()->server->get("HTTP_USER_AGENT"));
                }
            }
        }
    }

    private function handleIncorrectConfirmationRequest()
    {
        $this->session->set(ClientManager::NOT_CONFIRMED_BECAUSE_OF_ERROR, true);
        $this->session->getFlashBag()->add('error',
            $this->translator->trans('interactive_login_listener.error_by_client_confirmation', [
                '%supportEmail%' => $this->supportEmail
            ]));
    }
}