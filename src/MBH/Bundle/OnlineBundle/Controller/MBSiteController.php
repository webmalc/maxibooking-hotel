<?php

namespace MBH\Bundle\OnlineBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\BillingBundle\Lib\Model\WebSite;
use MBH\Bundle\ClientBundle\Form\ClientPaymentSystemType;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\OnlineBundle\Document\SiteConfig;
use MBH\Bundle\OnlineBundle\Form\SiteForm;
use MBH\Bundle\OnlineBundle\Form\SitePersonalDataPoliciesType;
use MBH\Bundle\OnlineBundle\Form\SocialNetworking\SiteSocialNetworkingServicesType;
use MBH\Bundle\OnlineBundle\Exception\NotFoundConfigMBSiteException;
use MBH\Bundle\OnlineBundle\Lib\SocialNetworking\HolderSNSs;
use MBH\Bundle\OnlineBundle\Services\AvailableSocialNetworkingServices;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
     * @Security("is_granted('ROLE_MB_SITE')")
     * @throws \Exception
     */
    public function siteSettingsAction(Request $request)
    {
        $siteManager = $this->get('mbh.site_manager');
        $siteConfig = $siteManager->getSiteConfig();
        $formConfig = $siteManager->fetchFormConfig();

        $clientManager = $this->get('mbh.client_manager');
        $clientSite = $clientManager->getClientSite();

        $form = $this->createForm(
            SiteForm::class,
            $siteConfig,
            [
                'disabledSettingPaymentForm' => $this->clientConfig->getPaymentSystems() === [],
                'urlToCreatePaymentSystem'   => $this->generateUrl('site_hotel_payment_systems'),
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                /** @var SiteConfig $siteConfig */
                $siteConfig = $form->getData();
                $this->dm->persist($siteConfig);

                $client = $clientManager->getClient();
                $newSiteAddress = $siteManager->compileSiteAddress($siteConfig);

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
                    $paymentForm = $siteManager->fetchPaymentFormConfig();

                    if ($siteConfig->isUsePaymentForm()) {
                        $paymentForm->setHotels($siteConfig->getHotels()->toArray());
                        $this->dm->flush();
                        $siteConfig->setPaymentFormId($paymentForm->getId());
                    } else {
                        $siteConfig->setPaymentFormId(null);
                        $this->dm->remove($paymentForm);
                    }

                    $siteManager->updateSiteFormConfig($siteConfig, $formConfig,
                        $request->get($form->getName())['paymentTypes']);
                    $this->dm->flush();
                    $this->addFlash('success', 'mb_site_controller.site_config_saved');
                }
            }
        } else {
            $form->get('paymentTypes')->setData($formConfig->getPaymentTypes());
        }

        return [
            'form'                  => $form->createView(),
            'siteConfig'            => $siteConfig,
            'hotelsSettings'        => $siteManager->getHotelsSettingsInfo($siteConfig),
            'isSetUpPaymentSystems' => $this->clientConfig->getPaymentSystems() !== [],
        ];
    }

    /**
     * @Route("/personal_data_policies", name="site_hotel_personal_data_policies")
     * @param Request $request
     * @return array
     * @Template()
     * @Security("is_granted('ROLE_MB_SITE')")
     */
    public function personalDataPoliciesAction(Request $request)
    {
        $siteManager = $this->get('mbh.site_manager');
        $siteConfig = $siteManager->getSiteConfig();

        $form = $this->createForm(SitePersonalDataPoliciesType::class, $siteConfig);
        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var SiteConfig $siteConfig */
            $siteConfig = $form->getData();
            $this->dm->flush($siteConfig);
            $this->addFlash('success', 'mb_site_controller.site_config_saved');
        }

        return [
            'form'                  => $form->createView(),
            'siteConfig'            => $siteConfig,
            'hotelsSettings'        => $siteManager->getHotelsSettingsInfo($siteConfig),
            'isSetUpPaymentSystems' => $this->clientConfig->getPaymentSystems() !== [],
        ];
    }

    /**
     * @Route("/hotel_settings/{id}", name="site_hotel_settings")
     * @Template()
     * @param Hotel $hotel
     * @return array
     * @throws \Exception
     * @Security("is_granted('ROLE_MB_SITE')")
     */
    public function hotelSettingsAction(Hotel $hotel)
    {
        $siteManager = $this->get('mbh.site_manager');
        $siteConfig = $siteManager->getSiteConfig();

        $roomTypesWarnings = array_map(function (RoomType $roomType) use ($siteManager) {
            return $siteManager->getDocumentFieldsCorrectnessTypesByRoutesNames($roomType);
        }, $hotel->getRoomTypes()->toArray());
        $warningsCompiler = $this->get('mbh.warnings_compiler');

        return [
            'hotelsSettings'        => $siteManager->getHotelsSettingsInfo($siteConfig),
            'siteConfig'            => $siteConfig,
            'hotel'                 => $hotel,
            'hotelWarnings'         => $siteManager->getDocumentFieldsCorrectnessTypesByRoutesNames($hotel),
            'roomTypesWarnings'     => $roomTypesWarnings,
            'emptyPriceCaches'      => $warningsCompiler->getEmptyPriceCachePeriods(),
            'emptyRoomCaches'       => $warningsCompiler->getEmptyRoomCachePeriods(),
            'isSetUpPaymentSystems' => $this->clientConfig->getPaymentSystems() !== [],
        ];
    }

    /**
     * @Route("/payment_system", name="site_hotel_payment_systems")
     * @Template()
     * @Security("is_granted('ROLE_MB_SITE')")
     */
    public function paymentSystemsAction(Request $request)
    {
        $paymentSystems = $this->clientConfig->getPaymentSystems();
        $isUsedForm = true;

        if (empty($paymentSystems)) {
            $paymentSystemName = null;
        } elseif (count($paymentSystems) > 1) {
            $paymentSystemName = true;
            $isUsedForm = false;
        } else {
            $paymentSystemName = $paymentSystems[0];
        }

        $siteManager = $this->get('mbh.site_manager');
        $siteConfig = $siteManager->getSiteConfig();

        if ($isUsedForm) {

            $paymentSystemName = $paymentSystemName ?? $request->get(ClientPaymentSystemType::FORM_NAME)['paymentSystem'] ?? null;
            $form = $this->createForm(ClientPaymentSystemType::class, $this->clientConfig, [
                'paymentSystemName' => $paymentSystemName,
            ]);

            if ($request->isMethod('POST')) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $this->clientConfig->addPaymentSystemFromForm($form, $paymentSystemName);
                    $this->dm->flush($this->clientConfig);

                    $this->addFlash('success', 'controller.clientConfig.params_success_save');

                    /** для корректого отображении формы */
                    return $this->redirectToRoute('site_hotel_payment_systems');
                }
            }
        }

        $response = [
            'siteConfig'            => $siteConfig,
            'hotelsSettings'        => $siteManager->getHotelsSettingsInfo($siteConfig),
            'isSetUpPaymentSystems' => $paymentSystemName !== null,
            'paymentSystemName'     => $paymentSystemName,
            'isUsedForm'            => $isUsedForm,
        ];

        if ($isUsedForm) {
            $response['form'] = $form->createView();
        }

        return $response;
    }

    /**
     * @Route("/payment_system/remove/{paymentSystemName}", name="site_hotel_remove_payment_system")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Security("is_granted('ROLE_MB_SITE')")
     */
    public function removePaymentSystemAction($paymentSystemName)
    {
        $this->clientConfig->removePaymentSystem($paymentSystemName);
        $this->dm->flush();

        return $this->redirectToRoute('site_hotel_payment_systems');
    }

    /**
     * @Route("/social_networking_services", name="site_config_social_networking_services")
     * @Security("is_granted('ROLE_MB_SITE')")
     * @Template()
     */
    public function socialNetworkingServicesAction(Request $request)
    {
        $siteManager = $this->get('mbh.site_manager');
        $siteConfig = $siteManager->getSiteConfig();

        if ($siteConfig === null) {
            throw new NotFoundConfigMBSiteException();
        }

        $holder = $this->get(AvailableSocialNetworkingServices::class)->createHolder($siteConfig);

        $form = $this->createForm(SiteSocialNetworkingServicesType::class, $holder);

        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {
            /** @var HolderSNSs $data */
            $data = $form->getData();
            $data->deleteEmptyUrl();
            $siteConfig->setSocialNetworkingServices($data->getSnss());

            $this->dm->flush($siteConfig);

            $this->addFlash('success', 'mb_site_controller.site_config_saved');
        }

        return [
            'form'                  => $form->createView(),
            'siteConfig'            => $siteConfig,
            'hotelsSettings'        => $siteManager->getHotelsSettingsInfo($siteConfig),
            'isSetUpPaymentSystems' => $this->clientConfig->getPaymentSystems() !== [],
        ];
    }
}