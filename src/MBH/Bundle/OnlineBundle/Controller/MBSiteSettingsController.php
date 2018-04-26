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
        header('Access-Control-Allow-Origin: ' . 'http://localhost:4200');
        $hotels = $this->dm->getRepository('MBHHotelBundle:Hotel')->findAll();
        //TODO: Решить как получать конфиг, либо убрать необходимость его указывать в апи запросах
        $formConfig = $this->dm->getRepository('MBHOnlineBundle:FormConfig')->findOneBy([]);

        return new JsonResponse([
            'hotelsIds' => $this->helper->toIds($hotels),
            'formConfigId' => $formConfig->getId()
        ]);
    }
}