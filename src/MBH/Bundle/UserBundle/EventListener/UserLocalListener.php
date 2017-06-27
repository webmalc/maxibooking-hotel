<?php


namespace MBH\Bundle\UserBundle\EventListener;


use MBH\Bundle\UserBundle\Document\User;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class UserLocalListener
{
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        /** @var User $user */
        if (null !== $user->getLocale()) {
            if ($user->getName() == 'danya-odmin') {
                $this->session->set('_locale', 'qwe');
            } else {
                $this->session->set('_locale', $user->getLocale());
            }
        }
    }
}