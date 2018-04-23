<?php

namespace MBH\Bundle\OnlineBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
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
        $siteManager = $this->get('mbh.site_manager');
        $formConfig = $siteManager->fetchFormConfig();

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                /** @var SiteConfig $config */
                $config = $form->getData();
                $this->dm->persist($config);

                $formConfig
                    ->setHotels(new ArrayCollection($config->getHotels()->toArray()))
                    ->setPaymentTypes($request->get($form->getName())['paymentTypes']);

                $this->dm->flush();
                $this->addFlash('success', 'mb_site_controller.site_config_saved');
            }
        } else {
            $form->get('paymentTypes')->setData($formConfig->getPaymentTypes());
        }

        return [
            'form' => $form->createView(),
            'hotelsSettings' => $siteManager->getHotelsSettingsInfo($config)
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
        $siteManager = $this->get('mbh.site_manager');
        $config = $this->dm->getRepository('MBHOnlineBundle:SiteConfig')->findOneBy([]);
        $roomTypesWarnings = array_map(function (RoomType $roomType) use ($siteManager) {
            return $siteManager->getDocumentFieldsCorrectnessTypesByRoutesNames($roomType);
        }, $hotel->getRoomTypes()->toArray());

        return [
            'hotelsSettings' => $siteManager->getHotelsSettingsInfo($config),
            'hotel' => $hotel,
            'hotelWarnings' => $siteManager->getDocumentFieldsCorrectnessTypesByRoutesNames($hotel),
            'roomTypesWarnings' => $roomTypesWarnings
        ];
    }
}