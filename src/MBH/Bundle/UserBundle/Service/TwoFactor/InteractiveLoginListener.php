<?php
namespace MBH\Bundle\UserBundle\Service\TwoFactor;

use MBH\Bundle\UserBundle\Document\User;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class InteractiveLoginListener
{
    /**
     * @var HelperInterface $helper
     */
    private $helper;

    /**
     * @var bool $helper
     */
    private $type = null;

    /**
     * Construct a listener, which is handling successful authentication
     * @param HelperInterface $helper
     * @param string $type
     */
    public function __construct(HelperInterface $helper, string $type)
    {
        $this->helper = $helper;
        $this->type = $type;
    }

    /**
     * Listen for successful login events
     * @param \Symfony\Component\Security\Http\Event\InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        /*$recaptcha = new \ReCaptcha\ReCaptcha('6Lev6AYUAAAAAIk51IMnRdGS4Im_-1h3pk-cM87a');
        $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
        dump($resp->isSuccess());
        exit();*/

        $session = $event->getRequest()->getSession();

        if (!$event->getAuthenticationToken() instanceof UsernamePasswordToken)
        {
            return;
        }

        //Check if user can do two-factor authentication
        $token = $event->getAuthenticationToken();
        $user = $token->getUser();

        if (!$user instanceof User || $user->getTwoFactorAuthentication() != $this->type)
        {
            return;
        }

        //Set flag in the session
        $session->set($this->helper->getSessionKey($token), null);
        if ($session->has('_security.main.target_path')) {
            $session->set('_two_factor_path', $session->get('_security.main.target_path'));
        }

        //Generate and send a new security code
        $this->helper->generateAndSend($user);
    }

}