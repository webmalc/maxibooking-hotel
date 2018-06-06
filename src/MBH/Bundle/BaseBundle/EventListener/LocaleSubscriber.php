<?php

namespace MBH\Bundle\BaseBundle\EventListener;

use Gedmo\Translatable\TranslatableListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Translation\TranslatorInterface;

class LocaleSubscriber implements EventSubscriberInterface
{
    /** @var string  */
    private $defaultLocale;
    private $translatableListener;
    private $translator;

    /**
     * LocaleListener constructor.
     * @param string $defaultLocale
     * @param TranslatableListener $translatableListener
     * @param TranslatorInterface $translator
     */
    public function __construct($defaultLocale = 'ru', TranslatableListener $translatableListener, TranslatorInterface $translator)
    {
        $this->defaultLocale = $defaultLocale;
        $this->translatableListener = $translatableListener;
        $this->translator = $translator;
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

        $locale = $request->getSession()->get('_locale', $this->defaultLocale);
        $request->setLocale($locale);
        $this->translator->setLocale($locale);
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