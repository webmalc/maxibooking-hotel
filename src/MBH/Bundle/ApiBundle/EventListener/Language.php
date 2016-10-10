<?php

namespace MBH\Bundle\ApiBundle\EventListener;


use MBH\Bundle\ApiBundle\Lib\Controller\LanguageInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Translation\DataCollectorTranslator;

class Language
{
    /**
     * @var ManagerRegistry
     */
    protected $dm;

    /**
     * @var IdentityTranslator
     */
    protected $translator;

    public function __construct(ManagerRegistry $dm, DataCollectorTranslator $translator)
    {
        $this->dm = $dm;
        $this->translator = $translator;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        $request = $event->getRequest();

        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof LanguageInterface && $request->get('locale')) {
            $locale = $request->get('locale');
            if ($request->get('id')) {
                $config = $this->dm->getRepository('MBHOnlineBundle:FormConfig')->find($request->get('id'));
                $locale = $config ? $config->getLanguage() : $locale;
            }
            $request->setLocale($locale);
            $this->translator->setLocale($locale);
        }
    }

}
