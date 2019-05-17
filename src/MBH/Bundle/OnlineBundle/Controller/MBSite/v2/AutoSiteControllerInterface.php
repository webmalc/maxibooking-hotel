<?php

namespace MBH\Bundle\OnlineBundle\Controller\MBSite\v2;

use MBH\Bundle\BaseBundle\Controller\BaseController;
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

/**
 * @Route("/api/mb-site/v2")
 * Class AutoSiteController
 */
class AutoSiteControllerInterface extends BaseController implements CheckSiteManagerInterface, AutoSiteHandlerInterface
{
    /**
     * @Route("/settings", name="api_mb_site_v2_settings")
     * @SWG\Get(
     *     path="/management/online/api/v2/settings",
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Return general setting for site"),
     * )
     */
    public function getMainSettingsAction(ApiResponseCompiler $responseCompiler, SiteConfig $siteConfig, FormConfig $formConfig)
    {
        $responseCompiler->setData(
            [
                'hotels'               => [
                    'amount' => $siteConfig->getHotels()->count(),
                    'list'   => $siteConfig->getHotels()->map(
                        function (Hotel $hotel) { return ['id' => $hotel->getId()];}
                        )->getValues()
                ],
                'formConfig'           => new FormConfigDecoratorForMBSite($formConfig, $this->get(DataForSearchForm::class)),
                'keyWords'             => $siteConfig->getKeyWords(),
                'personalDataPolicies' => $siteConfig->getPersonalDataPolicies(),
                'currency'             => $this->clientConfig->getCurrency(),
                'languages'            => $this->clientConfig->getLanguages(),
                'defaultLang'          => $this->getParameter('locale'),
                'colorTheme'           => $siteConfig->getThemeColors(),
                'paymentFormUrl'       => $this->generateUrl(
                    'online_payment_form_load_js',
                    ['configId' => $siteConfig->getPaymentFormId()]
                ),
                'paymentSystems'       => $this->clientConfig->getPaymentSystems(),
                'usePaymentForm'       => $siteConfig->isUsePaymentForm(),
            ]
        );

        return $responseCompiler;
    }

    /**
     * @Route("/additional-content/{hotelId}", name="api_mb_site_v2_additional_contant")
     * @SWG\Get(
     *     path="/management/online/api/v2/additional-content",
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Return link for social networking services, aggregator serveces"),
     * )
     */
    public function additionalContentAction(ApiResponseCompiler $responseCompiler, SiteConfig $siteConfig, Hotel $hotel)
    {
        $siteContent = $siteConfig->getContentForHotel($hotel);

        $this->setLocaleByRequest();

        $responseCompiler->setData(
            [
                'useBanner'          => $siteContent->isUseBanner(),
                'socialServices'     => $siteContent->getSocialNetworkingServices()->getValues(),
                'aggregatorServices' => $siteContent->getAggregatorServices()->getValues(),
            ]
        );

        return $responseCompiler;
    }

    /**
     * @Method("GET")
     * @SWG\Get(
     *     path="/management/online/api/mb-site/v2/hotels",
     *     produces={"application/json"},
     * )
     * @Route("/hotels")
     */
    public function hotelsAction(Request $request, ApiResponseCompiler $responseCompiler, FormConfig $formConfig)
    {
        $this->setLocaleByRequest();

        $queryData = $request->query;

        $responseData = [];

        /** @var CollectHotelData $collectHotelData */
        $collectHotelData = $this->get(CollectHotelData::class);
        $collectHotelData
            ->setBillingApi($this->get('mbh.billing.api'))
            ->setLocale($queryData->get('locale'));

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

        $responseCompiler->setData($responseData);

        return $responseCompiler;
    }

    /**
     * @Method("GET")
     * @Cache(expires="tomorrow", public=true)
     * @SWG\Get(
     *     path="/management/online/api/mb-site/v2/room-types",
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Return array of room types"),
     * )
     * @Route("/room-types")
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
        $responseCompiler->setData($responseData);

        return $responseCompiler;
    }

    /**
     * @SWG\Get(
     *     path="/management/online/api/mb-site/v2/min-prices",
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Return min prices"),
     * )
     * @Route("/min-prices")
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

        $responseCompiler->setData($minPrices);

        return $responseCompiler;
    }

    /**
     * @Method("GET")
     * @SWG\Get(
     *     path="/management/online/api/mb-site/v2/facilities-data",
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Return array of facilities data"),
     * )
     * @Route("/facilities-data")
     */
    public function getFacilitiesData(Request $request, ApiResponseCompiler $responseCompiler)
    {
        $this->setLocaleByRequest();

        $responseCompiler->setData(
            $this->get('mbh.facility_repository')->getActualFacilitiesData($request->getLocale())
        );

        return $responseCompiler;
    }
}
