<?php


namespace MBH\Bundle\UserBundle\EventListener;


use MBH\Bundle\PackageBundle\Lib\LocaleInterface;
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
    public function onInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();
        if ($user instanceof LocaleInterface && null !== $user->getLocale()) {
            $this->session->set('_locale', $user->getLocale());
        }

    }
}