<?php

namespace MBH\Bundle\ApiBundle\Controller;

use MBH\Bundle\BaseBundle\Lib\Normalization\SerializerSettings;
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
                if (is_null($formConfig) || $formConfig->containsRoomType($roomType)) {
                    $this->refreshDocumentByLocale($request, $roomType);
                    $responseData[] = $this->get('mbh.serializer')->normalizeByGroup($roomType, SerializerSettings::API_GROUP);
                }
            }
            $this->responseCompiler->setData($responseData);
        }

        return $this->responseCompiler->getResponse();
    }

    /**
     * @Cache(expires="+1 hour", public=true)
     * @Method("GET")
     * @Route("/tariffs")
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     * @throws \MBH\Bundle\BaseBundle\Lib\Normalization\NormalizationException
     */
    public function getTariffsAction(Request $request)
    {
        $this->setLocaleByRequest();
        $formConfig = $this->getFormConfigAndAddOriginHeader($request->query);
        $tariffs = $this->apiManager->getDocuments($request->query, Tariff::class);

        $responseData = [];
        /** @var Tariff $tariff */
        foreach ($tariffs as $tariff) {
            if (is_null($formConfig) || $formConfig->containsHotel($tariff->getHotel())) {
                $responseData[] = $this->get('mbh.serializer')->normalizeByGroup($tariff, SerializerSettings::API_GROUP);
            }
        }

        return $this->responseCompiler
            ->setData($responseData)
            ->getResponse();
    }

    /**
     * @Cache(expires="+1 hour", public=true)
     * @Method("GET")
     * @Route("/hotels")
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     * @throws \MBH\Bundle\BaseBundle\Lib\Normalization\NormalizationException
     */
    public function getHotelsAction(Request $request)
    {
        $this->setLocaleByRequest();
        $formConfig = $this->getFormConfigAndAddOriginHeader($request->query);

        $hotels = $this->apiManager->getDocuments($request->query, Hotel::class);
        if ($this->responseCompiler->isSuccessful()) {
            $responseData = [];
            /** @var Hotel $hotel */
            foreach ($hotels as $hotel) {
                if (is_null($formConfig) || $formConfig->containsHotel($hotel)) {
                    $this->refreshDocumentByLocale($request, $hotel);

                    $hotelData = $this->get('mbh.serializer')->normalizeByGroup($hotel, SerializerSettings::API_GROUP);
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
     * @Route("/services")
     * @param Request $request
     * @return JsonResponse
     */
    public function getServicesAction(Request $request)
    {
        $queryData = $request->query;

        $this->getFormConfigAndAddOriginHeader($queryData);

        $this->requestManager
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
     * @Route("/booking_options")
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function getBookingOptions(Request $request)
    {
        $this->setLocaleByRequest();
        $queryData = $request->query;
        $formConfig = $this->getFormConfigAndAddOriginHeader($queryData);


        $query = $this->requestManager->getCriteria($queryData, SearchQuery::class);
        if (!$this->responseCompiler->isSuccessful()) {
            return $this->responseCompiler->getResponse();
        }

        $roomTypeIds = $this->requestManager->getFilteredRoomTypeIdsByFormConfig($queryData, $formConfig);
        $query->roomTypes = $roomTypeIds;
        $query->hotel = null;

        if (!$this->responseCompiler->isSuccessful()) {
            return $this->responseCompiler->getResponse();
        }

        $responseData = [];
        $requestedTariffs = $request->get('tariffIds');

        if (!is_null($requestedTariffs) && is_array($requestedTariffs)) {
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

        $this->requestManager
            ->checkMandatoryFields($queryData, ['hotelId', 'onlineFormId']);
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
        $queryData = $request->query;

        $this->requestManager
            ->checkMandatoryFields($queryData, ['hotelId', 'onlineFormId']);
        if (!$this->responseCompiler->isSuccessful()) {
            return $this->responseCompiler->getResponse();
        }

        $this->getFormConfigAndAddOriginHeader($queryData);
        $hotel = $this->dm->find('MBHHotelBundle:Hotel', $queryData->get('hotelId'));

        $facilitiesData = $this->get('mbh.facility_repository')->getActualFacilitiesData($hotel, $request->getLocale());
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
    private function refreshDocumentByLocale(Request $request, $document)
    {
        if ($request->get('locale') && !$this->get('mbh.client_config_manager')->hasSingleLanguage()) {
            $document->setLocale($request->getLocale());
            $this->dm->refresh($document);
        }
    }
}
