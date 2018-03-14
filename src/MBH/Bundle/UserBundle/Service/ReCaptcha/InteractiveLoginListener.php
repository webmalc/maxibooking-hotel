<?php

namespace MBH\Bundle\UserBundle\Service\ReCaptcha;

use MBH\Bundle\BillingBundle\Lib\Model\ClientAuth;
use MBH\Bundle\BillingBundle\Service\BillingApi;
use MBH\Bundle\ClientBundle\Service\ClientManager;
use \ReCaptcha\ReCaptcha;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Translation\TranslatorInterface;

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
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $request = $event->getRequest();

        $reCaptcha = new ReCaptcha($this->params['secret']);
        $isAuthorizedByToken = $event->getAuthenticationToken() instanceof PreAuthenticatedToken;
        $this->session->set(ClientManager::IS_AUTHORIZED_BY_TOKEN, $isAuthorizedByToken);

        if ($event->getAuthenticationToken() instanceof UsernamePasswordToken) {
            if ($this->kernel->getEnvironment() == 'prod'){
                if (!$reCaptcha->verify($request->get('g-recaptcha-response'), $request->getClientIp())->isSuccess()) {
                    throw new BadCredentialsException('Captcha is invalid');
                }
            }

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
                    $serverData = $event->getRequest()->server;

                    $this->billingApi->senClientAuthMessage($serverData->get("REMOTE_ADDR"), $serverData->get("HTTP_USER_AGENT"));
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