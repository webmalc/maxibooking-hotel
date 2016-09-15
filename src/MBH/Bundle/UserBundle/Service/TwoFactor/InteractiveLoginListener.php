<?php
namespace MBH\Bundle\UserBundle\Service\TwoFactor;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use MBH\Bundle\UserBundle\Document\User;

class InteractiveLoginListener
{
    /**
     * @var HelperInterface $helper
     */
    private $helper;

    /**
     * @var bool $helper
     */
    private $enabled = false;

    /**
     * Construct a listener, which is handling successful authentication
     * @param HelperInterface $helper
     * @param bool $enabled
     */
    public function __construct(HelperInterface $helper, bool $enabled = false)
    {
        $this->helper = $helper;
        $this->enabled = $enabled;
    }

    /**
     * Listen for successful login events
     * @param \Symfony\Component\Security\Http\Event\InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        if (!$this->enabled) {
            return;
        }

        $session = $event->getRequest()->getSession();

        if (!$event->getAuthenticationToken() instanceof UsernamePasswordToken)
        {
            return;
        }

        //Check if user can do two-factor authentication
        $token = $event->getAuthenticationToken();
        $user = $token->getUser();
        if (!$user instanceof User)
        {
            return;
        }

        if (!$user->isTwoFactorAuthentication())
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