<?php

namespace MBH\Bundle\OnlineBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\OnlineBundle\Document\FormConfig;
use MBH\Bundle\OnlineBundle\Document\SiteConfig;
use MBH\Bundle\OnlineBundle\Form\SiteForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mb_site")
 * Class MBSiteController
 * @package MBH\Bundle\OnlineBundle\Controller
 */
class MBSiteController extends BaseController
{
    const DEFAULT_RESULTS_PAGE = '/results';
    const DEFAULT_BOOTSTRAP_THEME = 'cerulean';

    /**
     * @Template()
     * @Route("/", name="site_settings")
     * @param Request $request
     * @return array
     */
    public function siteSettingsAction(Request $request)
    {
        $config = $this->dm->getRepository('MBHOnlineBundle:SiteConfig')->findOneBy([]);
        $form = $this->createForm(SiteForm::class, $config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var SiteConfig $config */
            $config = $form->getData();
            $this->dm->persist($config);

            $formConfig = $this->dm->getRepository('MBHOnlineBundle:FormConfig')->findOneBy(['forMbSite' => true]);
            if (is_null($formConfig)) {
                $formConfig = (new FormConfig())
                    ->setForMbSite(true)
                    ->setIsFullWidth(true)
                    ->setIsHorizontal(true)
                    ->setTheme(FormConfig::THEMES[self::DEFAULT_BOOTSTRAP_THEME])
                    ->setResultsUrl(self::DEFAULT_RESULTS_PAGE)
                ;

                $this->dm->persist($formConfig);
            }

            $formConfig
                ->setHotels($config->getHotels())
                ->setPaymentTypes($request->get($form->getName())['paymentTypes']);

            $this->dm->flush();
            $this->addFlash('success', 'mb_site_controller.site_config_saved');
        }

        return [
            'form' => $form->createView(),
            'hotelsSettings' => $this->getHotelsSettingsInfo($config)
        ];
    }

    /**
     * @Route("hotel_settings/{id}", name="site_hotel_settings")
     * @Template()
     * @param Hotel $hotel
     * @return array
     */
    public function hotelSettingsAction(Hotel $hotel)
    {
        $config = $this->dm->getRepository('MBHOnlineBundle:SiteConfig')->findOneBy([]);

        return [
            'hotelsSettings' => $this->getHotelsSettingsInfo($config),
            'hotel' => $hotel
        ];
    }

    private function getHotelsSettingsInfo(SiteConfig $config = null)
    {
        $settingsInfo = [];
        if (!is_null($config) && $config->getIsEnabled()) {
            foreach ($config->getHotels() as $hotel) {
                $settingsInfo[] = [
                    'hotel' => $hotel,
                    'numberOfWarnings' => 2,
                ];
            }
        }

        return $settingsInfo;
    }
}