<?php
/**
 * Date: 16.05.19
 */

namespace MBH\Bundle\OnlineBundle\EventListener;


use MBH\Bundle\ClientBundle\Service\ClientConfigManager;
use MBH\Bundle\OnlineBundle\Exception\MBSiteIsDisabledInClientConfigException;
use MBH\Bundle\OnlineBundle\Exception\NotFoundConfigMBSiteException;
use MBH\Bundle\OnlineBundle\Interfaces\Controllers\CheckSiteManagerInterface;
use MBH\Bundle\OnlineBundle\Interfaces\Controllers\ResponseViaApiResponseCompilerInterface;
use MBH\Bundle\OnlineBundle\Services\ApiResponseCompiler;
use MBH\Bundle\OnlineBundle\Services\SiteManager;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class CheckSiteMangerListener
{
    /**
     * @var ClientConfigManager
     */
    private $clientConfigManager;

    /**
     * @var SiteManager
     */
    private $siteManager;

    /**
     * @var ApiResponseCompiler
     */
    private $responseCompiler;

    public function __construct(
        ClientConfigManager $clientConfigManager,
        SiteManager $siteManager,
        ApiResponseCompiler $responseCompiler)
    {
        $this->clientConfigManager = $clientConfigManager;
        $this->siteManager = $siteManager;
        $this->responseCompiler = $responseCompiler;
    }

    public function checkSiteManger(FilterControllerEvent $event): void
    {
        $controller = $event->getController();

        if (!(is_array($controller) && $controller[0] instanceof CheckSiteManagerInterface)) {
            return;
        }

        if (!$this->clientConfigManager->fetchConfig()->isMBSiteEnabled()) {
            throw new MBSiteIsDisabledInClientConfigException();
        }

        if ($this->siteManager->getSiteConfig() === null) {
            throw new NotFoundConfigMBSiteException();
        }

        $headerKey = 'Access-Control-Allow-Origin';
        $headerValue = $this->siteManager->getSiteAddress();

        if ($controller[0] instanceof ResponseViaApiResponseCompilerInterface) {
            $this->responseCompiler->addHeader($headerKey, $headerValue);
        } else {
            header(sprintf('%s: %s', $headerKey, $headerValue));
        }
    }

}
