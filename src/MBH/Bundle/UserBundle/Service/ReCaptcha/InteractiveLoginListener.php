<?php
namespace MBH\Bundle\UserBundle\Service\ReCaptcha;

use MBH\Bundle\BillingBundle\Service\BillingApi;
use \ReCaptcha\ReCaptcha;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
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

    /**
     * InteractiveLoginListener constructor.
     * @param array $params
     * @param BillingApi $billingApi
     */
    public function __construct(array $params, BillingApi $billingApi)
    {
        $this->params = $params;
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
        if ($event->getAuthenticationToken() instanceof UsernamePasswordToken
            && !$reCaptcha->verify($request->get('g-recaptcha-response'), $request->getClientIp())->isSuccess()) {
            throw new BadCredentialsException('Captcha is invalid');
        }

        if ($event->getAuthenticationToken() instanceof PreAuthenticatedToken) {
            $this->billingApi->confirmClientEmail();
        }
    }

}