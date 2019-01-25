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
    const LIVADIA_SITE = 'https://livadiahotel.ru';
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

        if (!$responseCompiler->isSuccessFull()) {
            return $responseCompiler->getResponse();
        }

        $roomTypes = $roomTypesQB
            ->getQuery()
            ->execute();

        if ($responseCompiler->isSuccessFull()) {
            $responseData = [];
            $domainName = $this->getParameter('router.request_context.host');
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
        header('Access-Control-Allow-Origin: ' . self::LIVADIA_SITE);
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
        header('Access-Control-Allow-Origin: ' . self::LIVADIA_SITE);
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
        if (!$responseCompiler->isSuccessFull()) {
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
        header('Access-Control-Allow-Origin: ' . self::LIVADIA_SITE);
        $responseCompiler = $this->get('mbh.api_response_compiler');
        $requestHandler = $this->get('mbh.api_handler');
        $queryData = $request->query;
        $requestHandler->checkMandatoryFields($queryData, ['tariffId'], $responseCompiler);

        if (!$responseCompiler->isSuccessFull()) {
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
        header('Access-Control-Allow-Origin: ' . self::LIVADIA_SITE);
        $responseCompiler = $this->get('mbh.api_response_compiler');
        $requestHandler = $this->get('mbh.api_handler');
        $queryData = $request->query;

        $responseCompiler = $requestHandler->checkIsArrayFields($queryData, ['hotelIds', 'roomTypeIds', 'childrenAges'], $responseCompiler);
        $responseCompiler = $requestHandler->checkMandatoryFields($queryData, ['begin', 'end'], $responseCompiler);

        $onlineFormId = $queryData->get('onlineFormId');
        $hotelIds = $queryData->get('hotelIds');
        $roomTypeIds = $queryData->get('roomTypeIds');
        $this->setLocaleByRequest();

        if (!$responseCompiler->isSuccessFull()) {
            return $responseCompiler->getResponse();
        }

        /** @var FormConfig $formConfigData */
        $formConfig = $requestHandler->getFormConfig($onlineFormId, $responseCompiler);

        $query = new SearchQuery();
        $query->isOnline = false;
        $query->begin = $this->helper->getDateFromString($request->get('begin'));
        $query->end = $this->helper->getDateFromString($request->get('end'));
        $query->adults = (int)$request->get('adults');

        if (!is_null($roomTypeIds)) {
            foreach ($roomTypeIds as $roomTypeId) {
                $roomType = $this->dm->find('MBHHotelBundle:RoomType', $roomTypeId);
                if (is_null($roomType)) {
                    $responseCompiler->addErrorMessage($responseCompiler::ROOM_TYPE_WITH_SPECIFIED_ID_NOT_EXISTS,
                        ['%roomTypeId%' => $roomTypeId]);
                } elseif (!is_null($formConfig) && !$formConfig->getRoomTypeChoices()->contains($roomType)) {
                    $responseCompiler->addErrorMessage($responseCompiler::FORM_CONFIG_NOT_CONTAINS_SPECIFIED_ROOM_TYPE,
                        ['%roomTypeId%' => $roomTypeId]
                    );
                } else {
                    $query->addRoomType($roomTypeId);
                }
            }
        } elseif (!is_null($hotelIds)) {
            foreach ($hotelIds as $hotelId) {
                $hotel = $this->dm->find('MBHHotelBundle:Hotel', $hotelId);
                if (is_null($hotel)) {
                    $responseCompiler->addErrorMessage($responseCompiler::HOTEL_WITH_SPECIFIED_ID_NOT_EXISTS,
                        ['%hotelId%' => $hotelId]);

                } elseif (!is_null($formConfig) && !$formConfig->getHotels()->contains($hotel)) {
                    $responseCompiler->addErrorMessage($responseCompiler::FORM_CONFIG_NOT_CONTAINS_SPECIFIED_HOTEL,
                        ['%hotelId%' => $hotelId]
                    );
                } else {
                    $query->addHotel($hotel);
                }
            }
        } elseif (!is_null($formConfig)) {
            foreach ($formConfig->getHotels() as $hotel) {
                $query->addHotel($hotel);
            }
        }

        $query->setChildrenAges(
            !empty($request->get('childrenAges')) ? $request->get('childrenAges') : []
        );

        $query->children = !is_array($request->get('childrenAges')) ? 0 : count($request->get('childrenAges'));

        if (!$responseCompiler->isSuccessFull()) {
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