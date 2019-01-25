<?php
namespace MBH\Bundle\UserBundle\Service\ReCaptcha;

use \ReCaptcha\ReCaptcha;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class InteractiveLoginListener
{
    /**
     * @var array
     */
    protected $params;

    /**
     * InteractiveLoginListener constructor.
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->params = $params;
    }

    /**
     * Listen for successful login events
     * @param \Symfony\Component\Security\Http\Event\InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $request = $event->getRequest();

        $reCaptcha = new ReCaptcha($this->params['secret']);
//        if (!$reCaptcha->verify($request->get('g-recaptcha-response'), $request->getClientIp())->isSuccess()) {
//            throw new BadCredentialsException('Captcha is invalid');
//        }
    }

}