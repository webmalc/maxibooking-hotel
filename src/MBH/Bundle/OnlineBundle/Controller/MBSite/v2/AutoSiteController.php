<?php

namespace MBH\Bundle\OnlineBundle\Controller\MBSite\v2;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfig;
use MBH\Bundle\OnlineBundle\Document\SiteConfig;
use MBH\Bundle\OnlineBundle\Exception\MBSiteIsDisabledInClientConfigException;
use MBH\Bundle\OnlineBundle\Exception\NotFoundConfigMBSiteException;
use MBH\Bundle\OnlineBundle\Lib\MBSite\v2\RoomTypeDataDecorator;
use MBH\Bundle\OnlineBundle\Lib\MBSite\FormConfigDecoratorForMBSite;
use MBH\Bundle\OnlineBundle\Lib\MBSite\v2\HotelDataDecorator;
use MBH\Bundle\OnlineBundle\Services\DataForSearchForm;
use MBH\Bundle\OnlineBundle\Services\SiteManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\HotelBundle\Document\RoomType;
//use MBH\Bundle\OnlineBundle\Lib\MBSite\RoomTypeDataDecorator;
use MBH\Bundle\OnlineBundle\Services\ApiHandler;
use MBH\Bundle\OnlineBundle\Services\ApiResponseCompiler;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\Service;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

/**
 * @Route("/api/mb-site/v2")
 * Class AutoSiteController
 */
class AutoSiteController extends BaseController
{
    /**
     * @var SiteManager
     */
    private $siteManager;

    /**
     * @Route("/settings", name="api_mb_site_v2_settings")
     * @SWG\Get(
     *     path="/management/online/api/v2/settings",
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Return general setting for site"),
     * )
     * @return JsonResponse
     */
    public function getMainSettingsAction()
    {
        $siteConfig = $this->checkSiteMangerAndInitDataAndGetSiteConfig();

        $formConfig = $this->dm
            ->getRepository(FormConfig::class)
            ->getForMBSite();

        $responseCompiler = $this->get('mbh.api_response_compiler');

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
                //            'contract'             => $siteConfig->getContract(),
                'currency'             => $this->clientConfig->getCurrency(),
                'languages'            => $this->clientConfig->getLanguages(),
                'defaultLang'          => $this->getParameter('locale'),
                'colorTheme'           => $siteConfig->getColorTheme(),
                'paymentFormUrl'       => $this->generateUrl(
                    'online_payment_form_load_js',
                    ['configId' => $siteConfig->getPaymentFormId()]
                ),
                'socialNetworks'       => $siteConfig->getSocialNetworkingServices()->getValues(),
                'paymentSystems'       => $this->clientConfig->getPaymentSystems(),
                'usePaymentForm'       => $siteConfig->isUsePaymentForm(),
            ]
        );

        return $responseCompiler->getResponse();
    }

    /**
     * @Method("GET")
     * @SWG\Get(
     *     path="/management/online/api/mb-site/v2/hotels",
     *     produces={"application/json"},
     * )
     * @Route("/hotels")
     * @param Request $request
     * @return JsonResponse
     */
    public function hotelsAction(Request $request)
    {
        $this->checkSiteMangerAndInitDataAndGetSiteConfig();
        $this->setLocaleByRequest();

        $requestHandler = $this->get('mbh.api_handler');
        $responseCompiler = $this->get('mbh.api_response_compiler');
        $queryData = $request->query;

        $formConfig = $requestHandler->getFormConfig($queryData->get('onlineFormId'), $responseCompiler);

        if (!$responseCompiler->isSuccessful()) {
            return $responseCompiler->getResponse();
        }

        $responseData = [];

        /** @var Hotel $hotel */
        foreach ($formConfig->getHotels() as $hotel) {
            if ($request->get('locale')) {
                $hotel->setLocale($request->getLocale());
                $this->dm->refresh($hotel);
            }

            $hotelDataDecorator = new HotelDataDecorator(
                $hotel,
                $this->get('mbh.billing.api'),
                $queryData->get('locale'),
                $this->get('vich_uploader.templating.helper.uploader_helper'),
                $this->get('liip_imagine.cache.manager')
            );

            $hotelData = $hotelDataDecorator->getData();
            $responseData[] = $hotelData;
        }

        $responseCompiler->setData($responseData);

        return $responseCompiler->getResponse();
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
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function roomTypesAction(Request $request)
    {
        $this->checkSiteMangerAndInitDataAndGetSiteConfig();
        $this->setLocaleByRequest();

        $requestHandler = $this->get('mbh.api_handler');
        $responseCompiler = $this->get('mbh.api_response_compiler');
        $queryData = $request->query;

        $formConfig = $requestHandler->getFormConfig($queryData->get('onlineFormId'), $responseCompiler);

//        $requestHandler->checkIsArrayFields($queryData, ['hotelId'], $responseCompiler);

        if (!$responseCompiler->isSuccessful()) {
            return $responseCompiler->getResponse();
        }

        $hotelId = $queryData->get('hotelId');

        if (!$formConfig->getHotels()->exists(function ($key, Hotel $hotel) use ($hotelId) { return $hotel->getId() === $hotelId;})) {
            $responseCompiler->addErrorMessage('The hotel is not for this form.');

            return $responseCompiler->getResponse();
        }

        $roomTypesQB = $this->dm->getRepository('MBHHotelBundle:RoomType')
            ->createQueryBuilder()
            ->sort('fullTitle')
            ->field('isEnabled')->equals(true);

        $roomTypesQB->field('hotel.id')->equals($hotelId);

        $roomTypes = $roomTypesQB
            ->getQuery()
            ->execute();


        $roomTypeImageDatar = new RoomTypeDataDecorator(
            $this->get('vich_uploader.templating.helper.uploader_helper'),
            $this->get('liip_imagine.cache.manager')
        );


        $responseData['amount'] = 0;
        $responseData['list'] = [];
        /** @var RoomType $roomType */
        foreach ($roomTypes as $roomType) {
            $roomType->setLocale($request->getLocale());
            $this->dm->refresh($roomType);

            $roomTypeImageDatar->setRoomType($roomType);
            $responseData['amount']++;
            $responseData['list'][] = $roomTypeImageDatar->getJsonSerialized(true);
        }
        $responseCompiler->setData($responseData);

        return $responseCompiler->getResponse();
    }


    /**
     * @SWG\Get(
     *     path="/management/online/api/mb-site/v2/min-prices",
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Return min prices"),
     * )
     * @Route("/min-prices")
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function minPricesForRoomTypesAction(Request $request)
    {
        $this->checkSiteMangerAndInitDataAndGetSiteConfig();
        $this->setLocaleByRequest();

        $responseCompiler = $this->get('mbh.api_response_compiler');
        $requestHandler = $this->get('mbh.api_handler');
        $queryData = $request->query;

//        $responseCompiler = $requestHandler->checkMandatoryFields($queryData, ['hotelId', 'onlineFormId'], $responseCompiler);
//        if (!$responseCompiler->isSuccessful()) {
//            return $responseCompiler->getResponse();
//        }

//        $this->getFormConfigAndAddOriginHeader($queryData, $requestHandler, $responseCompiler);

        $hotel = $this->dm->find('MBHHotelBundle:Hotel', $queryData->get('hotelId'));
        $onlineTariffs = $this->dm
            ->getRepository('MBHPriceBundle:Tariff')
            ->fetch($hotel, null, true, true);
        $onlineTariffsIds = $this->helper->toIds($onlineTariffs);

        $minPrices = $this->get('mbh.calculation')
            ->getMinPricesForRooms($hotel->getRoomTypes()->toArray(), $onlineTariffsIds, $requestHandler::MIN_PRICES_PERIOD_IN_DAYS);

        $responseCompiler->setData($minPrices);

        return $responseCompiler->getResponse();
    }


    /**
     * @Method("GET")
     * @SWG\Get(
     *     path="/management/online/api/mb-site/v2/facilities-data",
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Return array of facilities data"),
     * )
     * @Route("/facilities-data")
     * @param Request $request
     * @return JsonResponse
     */
    public function getFacilitiesData(Request $request)
    {
        $this->checkSiteMangerAndInitDataAndGetSiteConfig();
        $this->setLocaleByRequest();

        $responseCompiler = $this->get('mbh.api_response_compiler');

        $responseCompiler->setData(
            $this->get('mbh.facility_repository')->getActualFacilitiesData($request->getLocale())
        );

        return $responseCompiler->getResponse();
    }

    private function checkSiteMangerAndInitDataAndGetSiteConfig(): SiteConfig
    {
        if (!$this->clientConfig->isMBSiteEnabled()) {
            throw new MBSiteIsDisabledInClientConfigException();
        }

        $this->siteManager = $this->get('mbh.site_manager');
        $siteConfig = $this->siteManager->getSiteConfig();

        if ($siteConfig === null) {
            throw new NotFoundConfigMBSiteException();
        }

        header(sprintf('Access-Control-Allow-Origin: %s', $this->siteManager->getSiteAddress()));

        return $siteConfig;
    }
}
