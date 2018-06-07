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
        //TODO: Здест будет указан адрес из настроек
        header('Access-Control-Allow-Origin: ' . 'http://localhost:4200');

        //TODO: Решить как получать конфиг, либо убрать необходимость его указывать в апи запросах
        $formConfig = $this->dm
            ->getRepository('MBHOnlineBundle:FormConfig')
            ->getForMBSite();

        $siteConfig = $this->dm
            ->getRepository('MBHOnlineBundle:SiteConfig')
            ->findOneBy([]);

        return new JsonResponse([
            //TODO: Может быть еще фильтровать по тому насколько заполнены данные в отелях
            'hotelsIds' => $this->helper->toIds($siteConfig->getHotels()),
            'formConfigId' => $formConfig->getId(),
            'keyWords' => $siteConfig->getKeyWords(),
            'personalDataPolicies' => $siteConfig->getPersonalDataPolicies(),
            'contract' => $siteConfig->getContract()
        ]);
    }
}