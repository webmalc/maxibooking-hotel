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
    private $translator;
    private $languages;

    /**
     * LocaleListener constructor.
     * @param string $defaultLocale
     * @param TranslatorInterface $translator
     * @param array $languages
     */
    public function __construct($defaultLocale = 'ru', TranslatorInterface $translator, array $languages)
    {
        $this->defaultLocale = $defaultLocale;
        $this->translator = $translator;
        $this->languages = $languages;
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

        $locale = $request->get('locale') && in_array($request->getLocale(), $this->languages)
            ? $request->get('locale')
            : $request->getSession()->get('_locale', $this->defaultLocale);
        $request->setLocale($locale);
        $this->translator->setLocale($locale);
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