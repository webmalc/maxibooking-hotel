<?php


namespace MBH\Bundle\BaseBundle\EventListener;


use Gedmo\Translatable\Translatable;
use Gedmo\Translatable\TranslatableListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriber implements EventSubscriberInterface
{
    /** @var string  */
    private $defaultLocale;
    private $translatableListener;

    /**
     * LocaleListener constructor.
     * @param string $defaultLocale
     * @param TranslatableListener $translatableListener
     */
    public function __construct($defaultLocale = 'ru', TranslatableListener $translatableListener)
    {
        $this->defaultLocale = $defaultLocale;
        $this->translatableListener = $translatableListener;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->hasPreviousSession()) {
            return;
        }

        $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
        $this->translatableListener->setDefaultLocale($this->defaultLocale);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                [
                    'onKernelRequest', 15
                ]
            ]
        ];
    }
}