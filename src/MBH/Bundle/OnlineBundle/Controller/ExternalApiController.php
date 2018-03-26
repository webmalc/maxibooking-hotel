<?php
/**
 * Created by PhpStorm.
 * User: danya
 * Date: 07.07.17
 * Time: 15:17
 */

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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ExternalApiController
 * @package MBH\Bundle\OnlineBundle\Controller
 * @Route("/api")
 */
class ExternalApiController extends BaseController
{
    /**
     * @Route("/roomTypes")
     * @param Request $request
     * @return JsonResponse
     */
    public function getRoomTypesAction(Request $request)
    {
        $requestHandler = $this->get('mbh.api_handler');
        $responseCompiler = $this->get('mbh.api_response_compiler');
        $queryData = $request->query;

        $responseCompiler = $requestHandler->checkIsArrayFields($queryData, ['roomTypeIds', 'hotelIds'], $responseCompiler);

        $isEnabled = !empty($queryData->get('isEnabled')) ? $queryData->get('isEnabled') === 'true' : true;
        $isFull = !empty($queryData->get('isFull')) ? $queryData->get('isFull') === 'true' : false;
        $onlineFormId = $queryData->get('onlineFormId');

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

        /** @var FormConfig $formConfig */
        $formConfig = $requestHandler->getFormConfig($onlineFormId, $responseCompiler);
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
     * @Route("/tariffs")
     * @param Request $request
     * @return JsonResponse
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
     * @Route("/hotels")
     * @param Request $request
     * @return JsonResponse
     */
    public function getHotelsAction(Request $request)
    {
        $requestHandler = $this->get('mbh.api_handler');
        $queryData = $request->query;
        $isEnabled = !empty($queryData->get('isEnabled')) ? $queryData->get('isEnabled') === 'true' : true;
        $isFull = !empty($queryData->get('isFull')) ? $queryData->get('isFull') === 'true' : false;
        $onlineFormId = $queryData->get('onlineFormId');
        $this->setLocaleByRequest();

        $hotelRepository = $this->dm->getRepository('MBHHotelBundle:Hotel');
        if ($isEnabled) {
            $hotels = $hotelRepository->findBy(['isEnabled' => $isEnabled]);
        } else {
            $hotels = $hotelRepository->findAll();
        }

        $responseCompiler = $this->get('mbh.api_response_compiler');
        /** @var FormConfig $formConfig */
        $formConfig = $requestHandler->getFormConfig($onlineFormId, $responseCompiler);
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
                $responseData[] = $hotel->getJsonSerialized($isFull);
            }
        }
        $responseCompiler->setData($responseData);

        return $responseCompiler->getResponse();
    }

    /**
     * @Route("/services")
     * @param Request $request
     * @return JsonResponse
     */
    public function getServicesAction(Request $request)
    {
        $responseCompiler = $this->get('mbh.api_response_compiler');
        $requestHandler = $this->get('mbh.api_handler');
        $queryData = $request->query;
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
        $responseCompiler = $requestHandler->checkMandatoryFields($queryData, ['begin', 'end'], $responseCompiler);

        $onlineFormId = $queryData->get('onlineFormId');
        $hotelIds = $queryData->get('hotelIds');
        $roomTypeIds = $queryData->get('roomTypeIds');
        $this->setLocaleByRequest();

        if (!$responseCompiler->isSuccessful()) {
            return $responseCompiler->getResponse();
        }

        /** @var FormConfig $formConfigData */
        $formConfig = $requestHandler->getFormConfig($onlineFormId, $responseCompiler);

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
}