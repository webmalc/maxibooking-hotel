<?php

namespace MBH\Bundle\ApiBundle\Controller;

use MBH\Bundle\BaseBundle\Service\MBHSerializer;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\OnlineBundle\Document\FormConfig;
use MBH\Bundle\OnlineBundle\Services\ApiHandler;
use MBH\Bundle\OnlineBundle\Services\ApiResponseCompiler;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\Service;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;

/**
 * Class ExternalApiController
 * @package MBH\Bundle\OnlineBundle\Controller
 * @Route("/")
 */
class HotelApiController extends BaseApiController
{
    /**
     * @Cache(expires="+1 hour", public=true)
     * @Method("GET")
     * @SWG\Get(
     *     path="/api/v1/roomTypes",
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Return array of room types"),
     *     @SWG\Parameter(name="onlineFormId", in="query", type="string", required=true, description="Id of the online form"),
     *     @SWG\Parameter(name="ids", in="query", type="array", required=false, @SWG\Items(type="string"), description="List of room type ids"),
     *     @SWG\Parameter(name="hotelIds", in="query", type="array", required=false, @SWG\Items(type="string"), description="List of hotel ids"),
     *     @SWG\Parameter(name="isEnabled", in="query", type="boolean", required=false, description="Show enabled room types only?"),
     *     @SWG\Parameter(name="locale", in="query", type="string", required=false, description="Response language"),
     * )
     * @Route("/roomTypes")
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     * @throws \MBH\Bundle\BaseBundle\Lib\Normalization\NormalizationException
     */
    public function getRoomTypesAction(Request $request)
    {
        $this->setLocaleByRequest();
        $formConfig = $this->getFormConfigAndAddOriginHeader($request->query);
        $roomTypes = $this->apiManager->getDocuments($request->query, RoomType::class);

        if ($this->responseCompiler->isSuccessful()) {
            $responseData = [];
            /** @var RoomType $roomType */
            foreach ($roomTypes as $roomType) {
                if (is_null($formConfig) || $formConfig->isRoomTypeContainsInConfig($roomType)) {
                    $this->refreshDocumentByLocale($request, $roomType);
                    $responseData[] = $this->get('mbh.serializer')->normalizeByGroup($roomType, MBHSerializer::API_GROUP);
                }
            }
            $this->responseCompiler->setData($responseData);
        }

        return $this->responseCompiler->getResponse();
    }

    /**
     * @Cache(expires="+1 hour", public=true)
     * @Method("GET")
     * @SWG\Get(
     *     path="/api/v1/tariffs",
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Return array of tariffs"),
     *     @SWG\Parameter(name="onlineFormId", in="query", type="string", required=true, description="Id of the online form"),
     *     @SWG\Parameter(name="isOnline", in="query", type="boolean", required=false, description="Show online tariffs only?"),
     *     @SWG\Parameter(name="hotelIds", in="query", type="array", required=false, @SWG\Items(type="string"), description="List of hotel ids"),
     *     @SWG\Parameter(name="isEnabled", in="query", type="boolean", required=false, description="Show enabled tariffs only?"),
     *     @SWG\Parameter(name="locale", in="query", type="string", required=false, description="Locale of the response"),
     * )
     * @Route("/tariffs")
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function getTariffsAction(Request $request)
    {
        $this->setLocaleByRequest();
        $formConfig = $this->getFormConfigAndAddOriginHeader($request->query);
        $tariffs = $this->apiManager->getDocuments($request->query, Tariff::class);

        $responseData = [];
        /** @var Tariff $tariff */
        foreach ($tariffs as $tariff) {
            if (is_null($formConfig) || $formConfig->isHotelContainsInConfig($tariff->getHotel())) {
                $responseData[] = $tariff->getJsonSerialized();
            }
        }

        return $this->responseCompiler
            ->setData($responseData)
            ->getResponse();
    }

    /**
     * @Cache(expires="+1 hour", public=true)
     * @Method("GET")
     * @SWG\Get(
     *     path="/api/v1/hotels",
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Return array of hotels"),
     *     @SWG\Parameter(name="onlineFormId", in="query", type="string", required=true, description="Id of the online form"),
     *     @SWG\Parameter(name="isOnline", in="query", type="boolean", required=false, description="Show only online hotels?"),
     *     @SWG\Parameter(name="isEnabled", in="query", type="boolean", required=false, description="Show only enabled hotels?"),
     *     @SWG\Parameter(name="locale", in="query", type="string", required=false, description="Locale of the response"),
     * )
     * @Route("/hotels")
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function getHotelsAction(Request $request)
    {
        $this->setLocaleByRequest();
        $formConfig = $this->getFormConfigAndAddOriginHeader($request->query);

        $hotels = $this->apiManager->getDocuments($request->query, Hotel::class);
        if (!$this->responseCompiler->isSuccessful()) {
            $responseData = [];
            /** @var Hotel $hotel */
            foreach ($hotels as $hotel) {
                if (is_null($formConfig) || $formConfig->isHotelContainsInConfig($hotel)) {
                    $this->refreshDocumentByLocale($request, $hotel);

                    $hotelData = $hotel->getJsonSerialized(
                        true,
                        $this->get('vich_uploader.templating.helper.uploader_helper'),
                        $this->get('liip_imagine.cache.manager')
                    );
                    if ($hotel->getCityId()) {
                        $hotelData['city'] = $this->get('mbh.billing.api')->getCityById($hotel->getCityId())->getName();
                    }
                    $responseData[] = $hotelData;
                }
            }
            $this->responseCompiler->setData($responseData);
        }

        return $this->responseCompiler->getResponse();
    }

    /**
     * @Cache(expires="+1 hour", public=true)
     * @Method("GET")
     * @SWG\Get(
     *     path="/api/v1/services",
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Return array of services for tariff"),
     *     @SWG\Parameter(name="tariffId", in="query", type="string", required=true, description="The ID of the rate for which receive the services"),
     *     @SWG\Parameter(name="locale", in="query", type="string", required=false, description="Locale of the response"),
     *     @SWG\Parameter(name="onlineFormId", in="query", type="string", required=true, description="Id of the online form")
     * )
     * @Route("/services")
     * @param Request $request
     * @return JsonResponse
     */
    public function getServicesAction(Request $request)
    {
        $queryData = $request->query;

        $this->getFormConfigAndAddOriginHeader($queryData);

        $this
            ->get('mbh.api_request_manager')
            ->checkMandatoryFields($queryData, ['tariffId']);

        if (!$this->responseCompiler->isSuccessful()) {
            return $this->responseCompiler->getResponse();
        }

        $tariffId = $queryData->get('tariffId');
        $tariff = $this->dm->find('MBHPriceBundle:Tariff', $tariffId);
        $services = $this->dm->getRepository('MBHPriceBundle:Service')->getAvailableServicesForTariff($tariff);

        $responseData = [];
        foreach ($services as $serviceByCategory) {
            /** @var Service $service */
            foreach ($serviceByCategory as $service) {
                if ($service->getIsOnline()) {
                    $responseData[] = $service->getJsonSerialized();
                }
            }
        }
        $this->responseCompiler->setData($responseData);

        return $this->responseCompiler->getResponse();
    }

    /**
     * @Route("/api_payment/{id}")
     * @param Order $order
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addCashDocumentAndRedirectToPayment(Order $order)
    {
        $prepaymentPercent = 10;
        if ($order->getCashDocuments()->count() == 0) {
            $cashDocument = new CashDocument();
            $cashDocument->setIsConfirmed(false)
                ->setIsPaid(false)
                ->setMethod('electronic')
                ->setOperation('in')
                ->setOrder($order)
                ->setTouristPayer($order->getMainTourist())
                ->setTotal($order->getPrice() * $prepaymentPercent / 100);

            $order->addCashDocument($cashDocument);
            $this->dm->persist($cashDocument);
            $this->dm->flush();
        } else {
            $cashDocument = $order->getCashDocuments()->toArray()[0];
        }

        $formData = $this->clientConfig->getRnkb()->getFormData($cashDocument);
        $url = $formData['action'] . '?Shop_IDP=' . $formData['shopId']
            . '&Subtotal_P=' . $formData['total']
            . '&Order_IDP=' . $formData['orderId']
            . '&URL_RETURN=' . urlencode('http://livadia.madgentle.ru/')
            . '&Email=' . $formData['touristEmail']
            . '&Phone=' . $formData['touristPhone']
            . '&Comment=' . urlencode($formData['comment'])
            . '&Signature=' . $formData['signature']
            . '&Currency=RUB';

        return $this->redirect($url);
    }

    /**
     * @Method("GET")
     * @SWG\Get(
     *     path="/api/v1/booking_options",
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Return array of booking options"),
     *     @SWG\Parameter(name="onlineFormId", in="query", type="string", required=true, description="Id of the online form"),
     *     @SWG\Parameter(name="begin", in="query", type="string", required=true, description="Begin date"),
     *     @SWG\Parameter(name="end", in="query", type="string", required=true, description="End date"),
     *     @SWG\Parameter(name="adults", in="query", type="string", required=false, description="Number of adults"),
     *     @SWG\Parameter(name="locale", in="query", type="string", required=false, description="Locale of the response"),
     *     @SWG\Parameter(name="hotelIds", in="query", type="array", required=false, @SWG\Items(type="string"), description="List of hotel ids"),
     *     @SWG\Parameter(name="roomTypeIds", in="query", type="array", required=false, @SWG\Items(type="string"), description="List of room type ids"),
     *     @SWG\Parameter(name="childrenAges", in="query", type="array", required=false, @SWG\Items(type="string"), description="List of children ages")
     * )
     * @Route("/booking_options")
     * @param Request $request
     * @return JsonResponse
     */
    public function getBookingOptions(Request $request)
    {
        $requestHandler = $this->get('mbh.api_handler');
        $queryData = $request->query;

        $this->responseCompiler = $requestHandler->checkIsArrayFields($queryData, ['hotelIds', 'roomTypeIds', 'childrenAges'], $this->responseCompiler);
        $this->responseCompiler = $requestHandler->checkMandatoryFields($queryData, ['begin', 'end', 'adults'], $this->responseCompiler);

        $formConfig = $this->getFormConfigAndAddOriginHeader($queryData);

        $hotelIds = $queryData->get('hotelIds');
        $roomTypeIds = $queryData->get('roomTypeIds');
        $this->setLocaleByRequest();

        if (!$this->responseCompiler->isSuccessful()) {
            return $this->responseCompiler->getResponse();
        }

        $query = new SearchQuery();
        $query->isOnline = false;
        $query->begin = $this->helper->getDateFromString($request->get('begin'));
        $query->end = $this->helper->getDateFromString($request->get('end'));
        $query->adults = (int)$request->get('adults');

        $filteredRoomTypeIds = $requestHandler->getFilteredRoomTypeIds($roomTypeIds, $this->responseCompiler, $formConfig);
        if (empty($filteredRoomTypeIds)) {
            $filteredHotels = $requestHandler->getFilteredHotels($hotelIds, $this->responseCompiler, $formConfig);
            $hotels = !empty($filteredHotels) ? $filteredHotels : $formConfig->getHotels();
            foreach ($hotels as $hotel) {
                $query->addHotel($hotel);
            }
        } else {
            $query->roomTypes = $filteredRoomTypeIds;
        }

        $query->setChildrenAges(
            !empty($request->get('childrenAges')) ? $request->get('childrenAges') : []
        );

        $query->children = !is_array($request->get('childrenAges')) ? 0 : count($request->get('childrenAges'));

        if (!$this->responseCompiler->isSuccessful()) {
            return $this->responseCompiler->getResponse();
        }

        $responseData = [];
        $requestedTariffs = $request->get('tariffIds');

        if (!is_null($requestedTariffs)) {
            foreach ($requestedTariffs as $tariffId) {
                $query->tariff = $tariffId;
                $results = $this->get('mbh.package.search')->search($query);
                /** @var SearchResult $searchResult */
                foreach ($results as $searchResult) {
                    $responseData[] = $searchResult->getJsonSerialized();
                }
            };
        } else {
            $resultsByRoomTypes = $this->get('mbh.package.search')->setWithTariffs()->search($query);
            foreach ($resultsByRoomTypes as $resultsByRoomType) {
                foreach ($resultsByRoomType['results'] as $searchResult) {
                    /** @var SearchResult $searchResult */
                    $responseData[] = $searchResult->getJsonSerialized();
                }
            }
        }

        $this->responseCompiler->setData($responseData);

        return $this->responseCompiler->getResponse();
    }

    /**
     * @Cache(expires="+1 hour", public=true)
     * @Route("/minPrices")
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function minPricesForRoomTypesAction(Request $request)
    {
        $requestHandler = $this->get('mbh.api_handler');
        $queryData = $request->query;

        $this->responseCompiler = $requestHandler->checkMandatoryFields($queryData, ['hotelId', 'onlineFormId'], $this->responseCompiler);
        if (!$this->responseCompiler->isSuccessful()) {
            return $this->responseCompiler->getResponse();
        }

        $this->getFormConfigAndAddOriginHeader($queryData);

        $hotel = $this->dm->find('MBHHotelBundle:Hotel', $queryData->get('hotelId'));
        $onlineTariffs = $this->dm
            ->getRepository('MBHPriceBundle:Tariff')
            ->fetch($hotel, null, true, true);
        $onlineTariffsIds = $this->helper->toIds($onlineTariffs);

        $minPrices = $this->get('mbh.calculation')
            ->getMinPricesForRooms($hotel->getRoomTypes()->toArray(), $onlineTariffsIds, $requestHandler::MIN_PRICES_PERIOD_IN_DAYS);

        $this->responseCompiler->setData($minPrices);

        return $this->responseCompiler->getResponse();
    }

    /**
     * @Cache(expires="+1 hour", public=true)
     * @Route("/facilities_data")
     * @param Request $request
     * @return JsonResponse
     */
    public function getFacilitiesData(Request $request)
    {
        $requestHandler = $this->get('mbh.api_handler');
        $queryData = $request->query;

        $this->responseCompiler = $requestHandler->checkMandatoryFields($queryData, ['hotelId', 'onlineFormId'], $this->responseCompiler);
        if (!$this->responseCompiler->isSuccessful()) {
            return $this->responseCompiler->getResponse();
        }
        $this->getFormConfigAndAddOriginHeader($queryData);
        $hotel = $this->dm->find('MBHHotelBundle:Hotel', $queryData->get('hotelId'));

        $facilitiesData = $this->get('mbh.facility_repository')->getActualFacilitiesData($hotel, $queryData->get('locale'));
        $this->responseCompiler->setData($facilitiesData);

        return $this->responseCompiler->getResponse();
    }

    /**
     * @param ParameterBag $queryData
     * @param ApiHandler $requestHandler
     * @return FormConfig
     */
    private function getFormConfigAndAddOriginHeader(ParameterBag $queryData)
    {
        $onlineFormId = $queryData->get('onlineFormId');
        /** @var FormConfig $formConfig */
        $formConfig = $this->apiManager->getFormConfig($onlineFormId);
        if (!is_null($formConfig)) {
            $this->responseCompiler->addHeader(ApiResponseCompiler::ACCESS_CONTROL_ORIGIN_HEADER, $formConfig->getResultsUrl());
        }

        return $formConfig;
    }

    /**
     * @param Request $request
     * @param $document
     */
    private function refreshDocumentByLocale(Request $request, $document): void
    {
        if ($request->get('locale') && !$this->get('mbh.client_config_manager')->hasSingleLanguage()) {
            $document->setLocale($request->getLocale());
            $this->dm->refresh($document);
        }
    }
}
