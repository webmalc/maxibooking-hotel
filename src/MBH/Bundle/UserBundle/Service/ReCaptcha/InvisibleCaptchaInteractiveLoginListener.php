<?php

namespace MBH\Bundle\UserBundle\Service\ReCaptcha;


use MBH\Bundle\UserBundle\Lib\Exception\InvisibleCaptchaException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Class InvisibleCaptchaInteractiveLoginListener
 * @package MBH\Bundle\UserBundle\Service\ReCaptcha
 */
class InvisibleCaptchaInteractiveLoginListener
{

    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var InvisibleCaptcha
     */
    protected $captcha;

    /**
     * InvisibleCaptchaInteractiveLoginListener constructor.
     * @param KernelInterface $kernel
     * @param InvisibleCaptcha $captcha
     */
    public function __construct(KernelInterface $kernel, InvisibleCaptcha $captcha)
    {
        $this->captcha = $captcha;
        $this->kernel = $kernel;
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        if ($event->getAuthenticationToken() instanceof UsernamePasswordToken) {
            if ($this->kernel->getEnvironment() == 'prod') {
                $request = $event->getRequest();

                if (!$request->get('re_token', false)) {
                    throw new BadCredentialsException('The presented password is invalid.');
                }

                try {
                    $this->captcha->validate($request->get('re_token'));
                } catch (InvisibleCaptchaException $e) {
                    throw new CustomUserMessageAuthenticationException($e->getMessage());
                }
            }
        }
    }
}
