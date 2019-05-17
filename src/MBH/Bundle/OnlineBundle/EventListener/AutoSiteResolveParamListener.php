<?php
/**
 * Date: 17.05.19
 */

namespace MBH\Bundle\OnlineBundle\EventListener;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfig;
use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfigManager;
use MBH\Bundle\OnlineBundle\Document\SiteConfig;
use MBH\Bundle\OnlineBundle\Exception\MBSiteException;
use MBH\Bundle\OnlineBundle\Interfaces\Controllers\AutoSiteResolveParamInterface;
use MBH\Bundle\OnlineBundle\Services\ApiResponseCompiler;
use MBH\Bundle\OnlineBundle\Services\SiteManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class AutoSiteResolveParamListener
{
    private const MAP_PARAMS = [
        ApiResponseCompiler::class => null,
        SiteConfig::class          => null,
        Hotel::class               => 'hotelId',
        FormConfig::class          => null,
    ];

    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var ApiResponseCompiler $responseCompiler
     */
    private $responseCompiler;

    /**
     * @var SiteManager
     */
    private $siteManager;

    /**
     * @var FormConfigManager
     */
    private $formConfigManager;

    public function __construct(
        DocumentManager $dm,
        ApiResponseCompiler $responseCompiler,
        SiteManager $siteManager,
        FormConfigManager $formConfigManager
    )
    {
        $this->dm = $dm;
        $this->responseCompiler = $responseCompiler;
        $this->siteManager = $siteManager;
        $this->formConfigManager = $formConfigManager;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        if (!(is_array($controller) && $controller[0] instanceof AutoSiteResolveParamInterface)) {
            return;
        }

        try {
            $this->resolveParams($controller, $event->getRequest());
        } catch (MBSiteException $exception) {
            $responseCompiler = $this->responseCompiler->addErrorMessage($exception->getMessage());

            $event->setController(function () use ($responseCompiler) {
                return $responseCompiler->getResponse();
            });
        }
    }

    private function resolveParams(array $controller, Request $request): void
    {
        $ref = new \ReflectionMethod($controller[0], $controller[1]);

        foreach ($ref->getParameters() as $parameter) {
            $class = $parameter->getClass();
            if (!array_key_exists($class->getName(), self::MAP_PARAMS)) {
                continue;
            }

            $paramKey = self::MAP_PARAMS[$class->getName()];
            $paramValue = $request->get($paramKey);

            if ($paramKey !== null && empty($paramValue)) {
                throw new MBSiteException(sprintf('Not found value parameter %s on request.', $paramKey));
            }

            $object = null;
            switch ($class->getName()) {
                case ApiResponseCompiler::class:
                    $object = $this->responseCompiler;
                    break;
                case SiteConfig::class:
                    $object = $this->siteManager->getSiteConfig();
                    break;
                case Hotel::class:
                    $object = $this->findHotel($paramValue);
                    break;
                case FormConfig::class;
                    $object = $this->formConfigManager->getForMBSite();
                    break;
            }

            if ($object === null) {
                $name = explode('\\', $class->getName());
                throw new MBSiteException(sprintf('Empty object for %s.', end($name)));
            }

            $request->attributes->set($parameter->getName(), $object);
        }
    }

    private function findHotel(string $hotelId): ?Hotel
    {
        return $this->dm->getRepository(Hotel::class)->findOneBy(['id' => $hotelId]);
    }
}
