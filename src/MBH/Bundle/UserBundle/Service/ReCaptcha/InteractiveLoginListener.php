<?php
namespace MBH\Bundle\UserBundle\Service\ReCaptcha;

use \ReCaptcha\ReCaptcha;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class InteractiveLoginListener
{
    /**
     * @var array
     */
    protected $params;

    /** @var Kernel  */
    protected $kernel;

    /**
     * InteractiveLoginListener constructor.
     * @param array $params
     * @param KernelInterface $kernel
     */
    public function __construct(array $params, KernelInterface $kernel)
    {
        $this->params = $params;
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
        if ($this->kernel->getEnvironment() !== 'dev'
            && !$reCaptcha->verify($request->get('g-recaptcha-response'), $request->getClientIp())->isSuccess()) {
            throw new BadCredentialsException('Captcha is invalid');
        }
    }

}