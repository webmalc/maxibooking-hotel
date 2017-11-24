<?php
namespace MBH\Bundle\UserBundle\Service\ReCaptcha;

use MBH\Bundle\BillingBundle\Service\BillingApi;
use MBH\Bundle\BillingBundle\Lib\Model\Client;
use MBH\Bundle\BillingBundle\Service\BillingApi;
use MBH\Bundle\ClientBundle\Service\ClientManager;
use \ReCaptcha\ReCaptcha;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class InteractiveLoginListener
{
    /**
     * @var array
     */
    protected $params;
    /** @var  BillingApi */
    protected $billingApi;
    protected $clientManager;
    protected $session;

    /**
     * InteractiveLoginListener constructor.
     * @param array $params
     * @param ClientManager $clientManager
     */
    public function __construct(array $params, ClientManager $clientManager, Session $session)
    {
        $this->params = $params;
        $this->clientManager = $clientManager;
        $this->session = $session;
        $this->billingApi = $billingApi;
    }

    /**
     * Listen for successful login events
     * @param \Symfony\Component\Security\Http\Event\InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $request = $event->getRequest();

        $reCaptcha = new ReCaptcha($this->params['secret']);
        if ($event->getAuthenticationToken() instanceof UsernamePasswordToken) {
            if (!$reCaptcha->verify($request->get('g-recaptcha-response'), $request->getClientIp())->isSuccess()) {
                throw new BadCredentialsException('Captcha is invalid');
            }

            $client = $this->clientManager->getClient();
            if ($client->getStatus() === 'not_confirmed') {
                try {
                    $this->clientManager->confirmClient($client);
                } catch (\Exception $exception) {
                    $this->session->getFlashBag()->add($type, $message);
                }
            }
        }
        if ($event->getAuthenticationToken() instanceof UsernamePasswordToken
            && !$reCaptcha->verify($request->get('g-recaptcha-response'), $request->getClientIp())->isSuccess()) {
            throw new BadCredentialsException('Captcha is invalid');
        }

        if ($event->getAuthenticationToken() instanceof PreAuthenticatedToken) {
            $this->billingApi->confirmClientEmail();
        }
    }

}