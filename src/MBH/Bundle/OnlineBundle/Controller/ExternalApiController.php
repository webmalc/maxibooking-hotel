<?php

namespace MBH\Bundle\OnlineBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\OnlineBundle\Document\FormConfig;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\Service;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;

/**
 * Class ExternalApiController
 * @package MBH\Bundle\OnlineBundle\Controller
 * @Route("/api")
 */
class ExternalApiController extends BaseController
{
    /**
     * @Method("GET")
     * @SWG\Get(
     *     path="/management/online/api/roomTypes",
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Return array of room types"),
     *     @SWG\Parameter(name="onlineFormId", in="query", type="string", required=true, description="Id of the online form"),
     *     @SWG\Parameter(name="roomTypeIds", in="query", type="array", required=false, @SWG\Items(type="string"), description="List of room type ids"),
     *     @SWG\Parameter(name="hotelIds", in="query", type="array", required=false, @SWG\Items(type="string"), description="List of hotel ids"),
     *     @SWG\Parameter(name="isEnabled", in="query", type="boolean", required=false, description="Show enabled room types only?"),
     *     @SWG\Parameter(name="locale", in="query", type="string", required=false, description="Response language"),
     * )
     * @Route("/roomTypes")
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function getRoomTypesAction(Request $request)
    {
        $this->addAccessControlAllowOriginHeaders(['http://localhost:4200']);
        $requestHandler = $this->get('mbh.api_handler');
        $responseCompiler = $this->get('mbh.api_response_compiler');
        $queryData = $request->query;

        $onlineFormId = $queryData->get('onlineFormId');
        /** @var FormConfig $formConfig */
        $formConfig = $requestHandler->getFormConfig($onlineFormId, $responseCompiler);
        if (!is_null($formConfig)) {
            $this->addAccessControlAllowOriginHeaders([$formConfig->getResultsUrl()]);
        }

        $responseCompiler = $requestHandler->checkIsArrayFields($queryData, ['roomTypeIds', 'hotelIds'], $responseCompiler);

        $isEnabled = !empty($queryData->get('isEnabled')) ? $queryData->get('isEnabled') === 'true' : true;
        $isFull = !empty($queryData->get('isFull')) ? $queryData->get('isFull') === 'true' : false;

        $roomTypeIds = $queryData->get('roomTypeIds');
        $hotelIds = $queryData->get('hotelIds');

        $this->setLocaleByRequest();

        $roomTypesQB = $this->dm->getRepository('MBHHotelBundle:RoomType')
            ->createQueryBuilder()
            ->field('isEnabled')->equals($isEnabled);
        if (!is_null($hotelIds)) {
            $roomTypesQB->field('hotel.id')->in($hotelIds);
        }
        if (!is_null($roomTypeIds)) {
            $roomTypesQB->field('id')->in($roomTypeIds);
        }
        if ($isEnabled) {
            $roomTypesQB->field('isEnabled')->equals(true);
        }

        if (!$responseCompiler->isSuccessful()) {
            return $responseCompiler->getResponse();
        }

        $roomTypes = $roomTypesQB
            ->getQuery()
            ->execute();

        if ($responseCompiler->isSuccessful()) {
            $responseData = [];
            /** @var RoomType $roomType */
            foreach ($roomTypes as $roomType) {
                if (is_null($formConfig)
                    || ($formConfig->getHotels()->contains($roomType->getHotel())
                        && (!$formConfig->getRoomTypes() || $formConfig->getRoomTypeChoices()->contains($roomType)))
                ) {
                    $responseData[] = $roomType->getJsonSerialized($isFull,
                        $this->getParameter('router.request_context.host'),
                        $this->get('vich_uploader.templating.helper.uploader_helper'));
                }
            }
            $responseCompiler->setData($responseData);
        }

        return $responseCompiler->getResponse();
    }

    /**
     * @Method("GET")
     * @SWG\Get(
     *     path="/management/online/api/tariffs",
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
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function getTariffsAction(Request $request)
    {
        $requestHandler = $this->get('mbh.api_handler');
        $responseCompiler = $this->get('mbh.api_response_compiler');
        $this->setLocaleByRequest();
        $queryData = $request->query;

        $responseCompiler = $requestHandler->checkIsArrayFields($queryData, ['hotelIds'], $responseCompiler);

        $isEnabled = !empty($queryData->get('isEnabled')) ? $queryData->get('isEnabled') === 'true' : true;
        $isFull = !empty($queryData->get('isFull')) ? $queryData->get('isFull') === 'true' : false;
        $isOnline = !empty($queryData->get('isOnline')) ? $queryData->get('isOnline') === 'true' : true;

        /** @var FormConfig $formConfig */
        $onlineFormId = $queryData->get('onlineFormId');
        $formConfig = $requestHandler->getFormConfig($onlineFormId, $responseCompiler);
        if (!is_null($formConfig)) {
            $this->addAccessControlAllowOriginHeaders([$formConfig->getResultsUrl()]);
        }

        $hotelIds = $queryData->get('hotelIds');

        $tariffsQB = $this->dm
            ->getRepository('MBHPriceBundle:Tariff')
            ->fetchQueryBuilder(null, null, $isEnabled, $isOnline);

        if (!is_null($hotelIds)) {
            $tariffsQB->field('hotel.id')->in($hotelIds);
        }
        $tariffs = $tariffsQB
            ->getQuery()
            ->execute();

        $responseData = [];
        /** @var Tariff $tariff */
        foreach ($tariffs as $tariff) {
            if (is_null($formConfig) || $formConfig->getHotels()->contains($tariff->getHotel())) {
                $responseData[] = $tariff->getJsonSerialized($isFull);
            }
        }

        return $responseCompiler
            ->setData($responseData)
            ->getResponse();
    }

    /**
     * @Method("GET")
     * @SWG\Get(
     *     path="/management/online/api/hotels",
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
     */
    public function getHotelsAction(Request $request)
    {
        $this->addAccessControlAllowOriginHeaders(['http://localhost:4200']);
        $requestHandler = $this->get('mbh.api_handler');
        $queryData = $request->query;
        $isEnabled = !empty($queryData->get('isEnabled')) ? $queryData->get('isEnabled') === 'true' : true;
        $isFull = !empty($queryData->get('isFull')) ? $queryData->get('isFull') === 'true' : false;
        $onlineFormId = $queryData->get('onlineFormId');
        $this->setLocaleByRequest();
//        $locale = $queryData->get('locale');

        $hotelRepository = $this->dm->getRepository('MBHHotelBundle:Hotel');
        if ($isEnabled) {
            $hotels = $hotelRepository->findBy(['isEnabled' => $isEnabled]);
        } else {
            $hotels = $hotelRepository->findAll();
        }

        $responseCompiler = $this->get('mbh.api_response_compiler');
        /** @var FormConfig $formConfig */
        $formConfig = $requestHandler->getFormConfig($onlineFormId, $responseCompiler);
        if (!is_null($formConfig)) {
            $this->addAccessControlAllowOriginHeaders([$formConfig->getResultsUrl()]);
        }

        if (!$responseCompiler->isSuccessful()) {
            return $responseCompiler->getResponse();
        }

        $responseData = [];
        /** @var Hotel $hotel */
        foreach ($hotels as $hotel) {
            if (is_null($formConfig)
                || $formConfig->getHotels()->count() == 0
                || in_array($hotel, $formConfig->getHotels()->toArray())
            ) {
//                $hotel->setLocale($locale);
//                $this->dm->refresh($hotel);
                $hotelData = $hotel->getJsonSerialized($isFull);
                if ($isFull && $hotel->getCityId()) {
                    $hotelData['city'] = $this->get('mbh.billing.api')->getCityById($hotel->getCityId())->getName();
                }
                $responseData[] = $hotelData;
            }
        }
        $responseCompiler->setData($responseData);

        return $responseCompiler->getResponse();
    }

    /**
     * @Method("GET")
     * @SWG\Get(
     *     path="/management/online/api/services",
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
        $responseCompiler = $this->get('mbh.api_response_compiler');
        $requestHandler = $this->get('mbh.api_handler');
        $queryData = $request->query;

        $onlineFormId = $queryData->get('onlineFormId');
        /** @var FormConfig $formConfig */
        $formConfig = $requestHandler->getFormConfig($onlineFormId, $responseCompiler);
        if (!is_null($formConfig)) {
            $this->addAccessControlAllowOriginHeaders([$formConfig->getResultsUrl()]);
        }

        $requestHandler->checkMandatoryFields($queryData, ['tariffId'], $responseCompiler);

        if (!$responseCompiler->isSuccessful()) {
            return $responseCompiler->getResponse();
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
        $responseCompiler->setData($responseData);

        return $responseCompiler->getResponse();
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
     *     path="/management/online/api/booking_options",
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
        $responseCompiler = $this->get('mbh.api_response_compiler');
        $requestHandler = $this->get('mbh.api_handler');
        $queryData = $request->query;

        $responseCompiler = $requestHandler->checkIsArrayFields($queryData, ['hotelIds', 'roomTypeIds', 'childrenAges'], $responseCompiler);
        $responseCompiler = $requestHandler->checkMandatoryFields($queryData, ['begin', 'end', 'adults'], $responseCompiler);

        $onlineFormId = $queryData->get('onlineFormId');
        /** @var FormConfig $formConfigData */
        $formConfig = $requestHandler->getFormConfig($onlineFormId, $responseCompiler);
        if (!is_null($formConfig)) {
            $this->addAccessControlAllowOriginHeaders([$formConfig->getResultsUrl()]);
        }

        $hotelIds = $queryData->get('hotelIds');
        $roomTypeIds = $queryData->get('roomTypeIds');
        $this->setLocaleByRequest();

        if (!$responseCompiler->isSuccessful()) {
            return $responseCompiler->getResponse();
        }

        $query = new SearchQuery();
        $query->isOnline = false;
        $query->begin = $this->helper->getDateFromString($request->get('begin'));
        $query->end = $this->helper->getDateFromString($request->get('end'));
        $query->adults = (int)$request->get('adults');

        $filteredRoomTypeIds = $requestHandler->getFilteredRoomTypeIds($roomTypeIds, $responseCompiler, $formConfig);
        if (empty($filteredRoomTypeIds)) {
            $filteredHotels = $requestHandler->getFilteredHotels($hotelIds, $responseCompiler, $formConfig);
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

        if (!$responseCompiler->isSuccessful()) {
            return $responseCompiler->getResponse();
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

        $responseCompiler->setData($responseData);

        return $responseCompiler->getResponse();
    }

    /**
     * @Route("/minPrices")
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function minPricesForRoomTypesAction(Request $request)
    {
        $this->addAccessControlAllowOriginHeaders(['http://localhost:4200']);
        $responseCompiler = $this->get('mbh.api_response_compiler');
        $requestHandler = $this->get('mbh.api_handler');
        $queryData = $request->query;

        $responseCompiler = $requestHandler->checkMandatoryFields($queryData, ['hotelId'], $responseCompiler);
        if (!$responseCompiler->isSuccessful()) {
            return $responseCompiler->getResponse();
        }

        $hotel = $this->dm->find('MBHHotelBundle:Hotel', $queryData->get('hotelId'));
        $onlineTariffs = $this->dm
            ->getRepository('MBHPriceBundle:Tariff')
            ->fetch($hotel, null, true, true);
        $onlineTariffsIds = $this->helper->toIds($onlineTariffs);

        $minPrices = [];
        foreach ($hotel->getRoomTypes() as $roomType) {
            $begin = new \DateTime('midnight');
            $end = new \DateTime('midnight +' . $requestHandler::MIN_PRICES_PERIOD_IN_DAYS . 'days');
            $priceCacheWithMinPrice = $this->dm
                ->getRepository('MBHPriceBundle:PriceCache')
                ->getWithMinPrice($roomType, $begin, $end, $onlineTariffsIds);

            if (is_null($priceCacheWithMinPrice)) {
                $minPrices[$roomType->getId()] = ['hasPrices' => false];
            } else {
                $minPriceDate = $priceCacheWithMinPrice->getDate();
                $priceForSingle = $this->get('mbh.calculation')
                    ->calcPrices($roomType, $priceCacheWithMinPrice->getTariff(), $minPriceDate, $minPriceDate, 1);
                if (!$priceForSingle || !isset($priceForSingle['1_0'])) {
                    $minPrices[$roomType->getId()] = ['hasPrices' => false];
                } else {
                    $minPrices[$roomType->getId()] = ['hasPrices' => true, 'price' => $priceForSingle['1_0']['total']];
                }
            }
        }

        $responseCompiler->setData($minPrices);

        return $responseCompiler->getResponse();
    }
}
