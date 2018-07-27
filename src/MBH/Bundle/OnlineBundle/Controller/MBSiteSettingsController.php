<?php

namespace MBH\Bundle\OnlineBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/site_settings")
 * Class MBSiteSettingsController
 * @package MBH\Bundle\OnlineBundle\Controller
 */
class MBSiteSettingsController extends BaseController
{
    /**
     * @Route("/main", name="mb_site_main_settings")
     * @return JsonResponse
     */
    public function getMainSettingsAction()
    {
        $siteManager = $this->get('mbh.site_manager');
        $siteConfig = $siteManager->getSiteConfig();

        header('Access-Control-Allow-Origin: ' . $siteManager->getSiteAddress());

        $formConfig = $this->dm
            ->getRepository('MBHOnlineBundle:FormConfig')
            ->getForMBSite();

        return new JsonResponse([
            'hotelsIds' => $this->helper->toIds($siteConfig->getHotels()),
            'formConfigId' => $formConfig->getId(),
            'keyWords' => $siteConfig->getKeyWords(),
            'personalDataPolicies' => $siteConfig->getPersonalDataPolicies(),
            'contract' => $siteConfig->getContract(),
            'currency' => $this->clientConfig->getCurrency(),
            'languages' => $this->clientConfig->getLanguages(),
            'defaultLang' => $this->getParameter('locale'),
            'colorTheme' => $siteConfig->getColorTheme()
        ]);
    }
}