<?php

namespace MBH\Bundle\OnlineBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\BillingBundle\Lib\Model\Result;
use MBH\Bundle\BillingBundle\Lib\Model\WebSite;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\OnlineBundle\Document\SiteConfig;
use MBH\Bundle\OnlineBundle\Form\SiteForm;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
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
     * @throws \Exception
     */
    public function siteSettingsAction(Request $request)
    {
        $siteManager = $this->get('mbh.site_manager');
        $siteConfig = $siteManager->getSiteConfig();
        $formConfig = $siteManager->fetchFormConfig();

        $clientManager = $this->get('mbh.client_manager');
        $clientSite = $clientManager->getClientSite();

        $form = $this->createForm(SiteForm::class, $siteConfig);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                /** @var SiteConfig $siteConfig */
                $siteConfig = $form->getData();
                $this->dm->persist($siteConfig);

                $client = $clientManager->getClient();
                $newSiteAddress = $siteManager->compileSiteAddress($siteConfig->getSiteDomain());

                if (is_null($clientSite)) {
                    $clientSite = (new WebSite());
                }

                $isSuccess = true;
                if ($clientSite->getUrl() !== $newSiteAddress) {
                    $clientSite
                        ->setUrl($newSiteAddress)
                        ->setClient($client->getLogin());
                    $result = $clientManager->addOrUpdateSite($clientSite);

                    if (!$result->isSuccessful()) {
                        $isSuccess = false;
                        if (isset($result->getErrors()['url'])) {
                            foreach ($result->getErrors()['url'] as $error) {
                                $form->get('siteDomain')->addError(new FormError($error));
                            }
                        } else {
                            throw new \UnexpectedValueException('Incorrect errors from billing: ' . json_encode($result->getErrors()));
                        }
                    }
                }
                if ($isSuccess) {
                    $siteManager->updateSiteFormConfig($siteConfig, $formConfig, $request->get($form->getName())['paymentTypes']);
                    $this->dm->flush();
                    $this->addFlash('success', 'mb_site_controller.site_config_saved');
                }
            }
        } else {
            $form->get('paymentTypes')->setData($formConfig->getPaymentTypes());
        }

        return [
            'form' => $form->createView(),
            'hotelsSettings' => $siteManager->getHotelsSettingsInfo($siteConfig)
        ];
    }

    /**
     * @Route("/hotel_settings/{id}", name="site_hotel_settings")
     * @Template()
     * @param Hotel $hotel
     * @return array
     * @throws \Exception
     */
    public function hotelSettingsAction(Hotel $hotel)
    {
        $siteManager = $this->get('mbh.site_manager');
        $config = $siteManager->getSiteConfig();

        $roomTypesWarnings = array_map(function (RoomType $roomType) use ($siteManager) {
            return $siteManager->getDocumentFieldsCorrectnessTypesByRoutesNames($roomType);
        }, $hotel->getRoomTypes()->toArray());
        $warningsCompiler = $this->get('mbh.warnings_compiler');

        return [
            'hotelsSettings' => $siteManager->getHotelsSettingsInfo($config),
            'hotel' => $hotel,
            'hotelWarnings' => $siteManager->getDocumentFieldsCorrectnessTypesByRoutesNames($hotel),
            'roomTypesWarnings' => $roomTypesWarnings,
            'emptyPriceCaches' => $warningsCompiler->getEmptyPriceCachePeriods(),
            'emptyRoomCaches' => $warningsCompiler->getEmptyRoomCachePeriods()
        ];
    }
}