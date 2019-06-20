<?php

namespace MBH\Bundle\OnlineBundle\Controller\MBSite\v2;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\BillingBundle\Exception\EmptyResponseBillingException;
use MBH\Bundle\BillingBundle\Service\BillingExceptionHandler;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfig;
use MBH\Bundle\OnlineBundle\Document\SiteConfig;
use MBH\Bundle\OnlineBundle\Interfaces\Controllers\AutoSiteHandlerInterface;
use MBH\Bundle\OnlineBundle\Interfaces\Controllers\CheckSiteManagerInterface;
use MBH\Bundle\OnlineBundle\Services\ApiHandler;
use MBH\Bundle\OnlineBundle\Services\ApiResponseCompiler;
use MBH\Bundle\OnlineBundle\Services\MBSite\v2\CollectRoomTypeData;
use MBH\Bundle\OnlineBundle\Services\MBSite\v2\CollectHotelData;
use MBH\Bundle\OnlineBundle\Lib\MBSite\FormConfigDecoratorForMBSite;
use MBH\Bundle\OnlineBundle\Services\DataForSearchForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Swagger\Annotations as SWG;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/api/mb-site/v2")
 * Class AutoSiteController
 */
class AutoSiteController extends BaseController implements CheckSiteManagerInterface, AutoSiteHandlerInterface
{
    /**
     * @Route("/settings", name="mb_site_api_v2_settings")
     * @SWG\Get(
     *     path="/management/online/api/v2/settings",
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Return general setting for site"),
     * )
     */
    public function settingsAction(ApiResponseCompiler $responseCompiler, SiteConfig $siteConfig, FormConfig $formConfig)
    {
        return $responseCompiler->setData(
            [
                'hotels'               => [
                    'amount' => $siteConfig->getHotels()->count(),
                    'list'   => $siteConfig->getHotels()->map(
                        function (Hotel $hotel) { return ['id' => $hotel->getId()];}
                        )->getValues()
                ],
                'formConfig'           => new FormConfigDecoratorForMBSite($formConfig, $this->get(DataForSearchForm::class)),
                'keyWords'             => $siteConfig->getKeyWords(),
                'currency'             => $this->get('mbh.currency')->info(true),
                'languages'            => $this->clientConfig->getLanguages(),
                'defaultLang'          => $this->getParameter('locale'),
                'colorTheme'           => $siteConfig->getThemeColors(),
                'paymentFormUrl'       => $this->generateUrl(
                    'online_payment_form_load_js',
                    ['configId' => $siteConfig->getPaymentFormId()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'paymentSystems'       => $this->clientConfig->getPaymentSystems(),
                'usePaymentForm'       => $siteConfig->isUsePaymentForm(),
            ]
        );
    }

    /**
     * @Route("/additional-content/{hotelId}", name="mb_site_api_v2_additional_content")
     * @SWG\Get(
     *     path="/management/online/api/v2/additional-content/{hotelId}",
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Return link for social networking services, aggregator serveces"),
     * )
     */
    public function additionalContentAction(ApiResponseCompiler $responseCompiler, SiteConfig $siteConfig, Hotel $hotel)
    {
        $siteContent = $siteConfig->getContentForHotel($hotel);

        $this->setLocaleByRequest();

        return $responseCompiler->setData(
            [
                'useBanner'          => $siteContent->isUseBanner(),
                'socialServices'     => $siteContent->getSocialNetworkingServices()->getValues(),
                'aggregatorServices' => $siteContent->getAggregatorServices()->getValues(),
            ]
        );
    }

    /**
     * @Route("/personal-data-policies", name="mb_site_api_v2_pers_data_policies")
     * @SWG\Get(
     *     path="/management/online/api/v2/personal-data-policies",
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Return personal data policies"),
     * )
     */
    public function personalDataPoliciesAction(ApiResponseCompiler $responseCompiler, SiteConfig $siteConfig)
    {
        $this->setLocaleByRequest();
        return $responseCompiler->setData(
            [
                'text' => $siteConfig->getPersonalDataPolicies(),
            ]
        );
    }

    /**
     * @Method("GET")
     * @SWG\Get(
     *     path="/management/online/api/mb-site/v2/hotels",
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="return data hotels")
     * )
     * @Route("/hotels", name="mb_site_api_v2_hotels")
     */
    public function hotelsAction(Request $request, ApiResponseCompiler $responseCompiler, FormConfig $formConfig)
    {
        $this->setLocaleByRequest();

        $responseData = [];

        /** @var CollectHotelData $collectHotelData */
        $collectHotelData = $this->get(CollectHotelData::class);
        $collectHotelData->setLocale($request->getLocale());

        /** @var Hotel $hotel */
        foreach ($formConfig->getHotels() as $hotel) {
            if ($request->get('locale')) {
                $hotel->setLocale($request->getLocale());
                $this->dm->refresh($hotel);
            }

            $collectHotelData->setHotel($hotel);
            $hotelData = $collectHotelData->getPreparedData();
            $responseData[] = $hotelData;
        }

        return $responseCompiler->setData($responseData);
    }

    /**
     * @Method("GET")
     * @SWG\Get(
     *     path="/management/online/api/mb-site/organization-by-hotel/{hotelId}",
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Return organization data"),
     * )
     * @Route("/organization-by-hotel/{hotelId}" , name="mb_site_api_v2_organization_by_hotel")
     * @param Request $request
     */
    public function organizationByHotelAction(ApiResponseCompiler $responseCompiler, Hotel $hotel)
    {
        $this->setLocaleByRequest();

        $organization = $hotel->getOrganization();

        if ($organization === null ) {
            return $responseCompiler->addErrorMessage('Hotel organization not found.');
        }

        $organizationData = [
            'name'    => $organization->getName(),
            'inn'     => $organization->getInn(),
            'index'   => $organization->getIndex(),
            'street'  => $organization->getStreet(),
            'house'   => $organization->getHouse(),
            'phone'   => $organization->getPhone(),
            'email'   => $organization->getEmail(),
            'city'    => null,
            'country' => null,
        ];

        if (!empty($organization->getCityId()) && !empty($organization->getCountryTld())) {
            $billingApi = $this->get('mbh.billing.api');

            try {
                $organizationData['city'] = $billingApi->getCityById($organization->getCityId())->getName();
                $organizationData['country'] = $billingApi->getCountryByTld($organization->getCountryTld())->getName();
            } catch (EmptyResponseBillingException $e) {
                $this->get(BillingExceptionHandler::class)->sendNotifier($e, $hotel);
            }
        }

        return  $responseCompiler->setData([$organizationData]);
    }

    /**
     * @Method("GET")
     * @Cache(expires="tomorrow", public=true)
     * @SWG\Get(
     *     path="/management/online/api/mb-site/v2/room-types",
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Return array of room types"),
     * )
     * @Route("/room-types" , name="mb_site_api_v2_room_types")
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function roomTypesAction(Request $request, ApiResponseCompiler $responseCompiler, Hotel $hotel, FormConfig $formConfig)
    {
        $this->setLocaleByRequest();

        $hotelNotExists = !$formConfig->getHotels()->exists(function ($key, Hotel $hotelInside) use ($hotel) {
            return $hotelInside->getId() === $hotel->getId();}
        );
        if ($hotelNotExists) {
            $responseCompiler->addErrorMessage('The hotel is not for this form.');

            return $responseCompiler;
        }

        $roomTypesQB = $this->dm->getRepository('MBHHotelBundle:RoomType')
            ->createQueryBuilder()
            ->sort('fullTitle')
            ->field('isEnabled')->equals(true);

        $roomTypesQB->field('hotel.id')->equals($hotel->getId());

        $roomTypes = $roomTypesQB
            ->getQuery()
            ->execute();

        /** @var CollectRoomTypeData $roomTypeImageData */
        $roomTypeImageData = $this->get(CollectRoomTypeData::class);

        $responseData['amount'] = 0;
        $responseData['list'] = [];
        /** @var RoomType $roomType */
        foreach ($roomTypes as $roomType) {
            $roomType->setLocale($request->getLocale());
            $this->dm->refresh($roomType);

            $roomTypeImageData->setRoomType($roomType);
            $responseData['amount']++;
            $responseData['list'][] = $roomTypeImageData->getPreparedData();
        }

        return $responseCompiler->setData($responseData);
    }

    /**
     * @SWG\Get(
     *     path="/management/online/api/mb-site/v2/min-prices",
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Return min prices"),
     * )
     * @Route("/min-prices", name="mb_site_api_v2_min_prices")
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function minPricesForRoomTypesAction(ApiResponseCompiler $responseCompiler, Hotel $hotel)
    {
        $this->setLocaleByRequest();

        $onlineTariffs = $this->dm
            ->getRepository('MBHPriceBundle:Tariff')
            ->fetch($hotel, null, true, true);
        $onlineTariffsIds = $this->helper->toIds($onlineTariffs);

        $minPrices = $this->get('mbh.calculation')
            ->getMinPricesForRooms(
                $hotel->getRoomTypes()->toArray(),
                $onlineTariffsIds,
                ApiHandler::MIN_PRICES_PERIOD_IN_DAYS
            );

        return $responseCompiler->setData($minPrices);
    }

    /**
     * @Method("GET")
     * @SWG\Get(
     *     path="/management/online/api/mb-site/v2/facilities-data",
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Return array of facilities data"),
     * )
     * @Route("/facilities-data", name="mb_site_api_v2_facilities_data")
     */
    public function getFacilitiesData(Request $request, ApiResponseCompiler $responseCompiler)
    {
        $this->setLocaleByRequest();

        return $responseCompiler->setData(
            $this->get('mbh.facility_repository')->getActualFacilitiesData($request->getLocale())
        );
    }
}
