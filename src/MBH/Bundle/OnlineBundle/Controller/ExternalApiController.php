<?php
/**
 * Created by PhpStorm.
 * User: danya
 * Date: 07.07.17
 * Time: 15:17
 */

namespace MBH\Bundle\OnlineBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\OnlineBundle\Document\FormConfig;
use MBH\Bundle\OnlineBundle\Services\ApiResponseCompiler;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\Service;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
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
     * @Route("/roomTypes", name="api_room_types", options={"expose"=true})
     * @param Request $request
     * @return JsonResponse
     */
    public function getRoomTypesAction(Request $request)
    {
        //TODO: Заменить
        header('Access-Control-Allow-Origin: ' . 'null');
        $requestHandler = $this->get('mbh.api_handler');
        $responseCompiler = $this->get('mbh.api_response_compiler');
        $queryData = $request->query;

        $responseCompiler = $requestHandler->checkIsArrayFields($queryData, ['roomTypeIds', 'hotelIds'], $responseCompiler);

        $isEnabled = !empty($queryData->get('isEnabled')) ? $queryData->get('isEnabled') === 'true' : true;
        $isFull = !empty($queryData->get('isFull')) ? $queryData->get('isFull') === 'true' : false;
        $onlineFormId = $queryData->get('onlineFormId');
        /** @var FormConfig $formConfig */
        $formConfig = $requestHandler->getFormConfig($onlineFormId, $responseCompiler);

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
                    || !is_array($formConfig->getRoomTypeChoices())
                    || !$formConfig->getRoomTypes()
                    || in_array($roomType, $formConfig->getRoomTypeChoices())
                ) {
                    $responseData[] = $roomType->getJsonSerialized($isFull, $domainName);
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
        //TODO: Заменить
        header('Access-Control-Allow-Origin: ' . 'null');
        $requestHandler = $this->get('mbh.api_handler');
        $responseCompiler = $this->get('mbh.api_response_compiler');
        $this->setLocaleByRequest();
        $queryData = $request->query;

        $responseCompiler = $requestHandler->checkIsArrayFields($queryData, ['hotelIds'], $responseCompiler);

        $isEnabled = !empty($queryData->get('isEnabled')) ? $queryData->get('isEnabled') === 'true' : true;
        $isFull = !empty($queryData->get('isFull')) ? $queryData->get('isFull') === 'true' : false;
        $isOnline = !empty($queryData->get('isOnline')) ? $queryData->get('isOnline') === 'true' : true;

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
            $responseData[] = $tariff->getJsonSerialized($isFull);
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
     * @Route("/booking_options")
     * @param Request $request
     * @return JsonResponse
     */
    public function getBookingOptions(Request $request)
    {
        //TODO: Заменить
        header('Access-Control-Allow-Origin: ' . 'null');
        $responseCompiler = $this->get('mbh.api_response_compiler');
        $requestHandler = $this->get('mbh.api_handler');
        $queryData = $request->query;

        $responseCompiler = $requestHandler->checkIsArrayFields($queryData, ['hotelIds', 'roomTypeIds'], $responseCompiler);
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
        $query->isOnline = true;
        $query->begin = $this->helper->getDateFromString($request->get('begin'));
        $query->end = $this->helper->getDateFromString($request->get('end'));
        $query->adults = (int)$request->get('adults');
        $query->children = (int)$request->get('children');
        $query->tariff = $request->get('tariff');

        if (!is_null($roomTypeIds)) {
            $filteredRoomTypeIds = $requestHandler->getFilteredRoomTypeIds($roomTypeIds, $responseCompiler, $formConfig);
            $query->roomTypes = $filteredRoomTypeIds;
        }

        if (!is_null($hotelIds)) {
            $filteredHotels = $requestHandler->getFilteredHotels($hotelIds, $responseCompiler, $formConfig);
            foreach ($filteredHotels as $hotel) {
                $query->addHotel($hotel);
            }
        }

        $query->setChildrenAges(
            !empty($request->get('children-ages')) && $query->children > 0 ? $request->get('children-ages') : []
        );

        if (!$responseCompiler->isSuccessFull()) {
            return $responseCompiler->getResponse();
        }

        $results = $this->get('mbh.package.search')->search($query);

        $responseData = [];
        /** @var SearchResult $searchResult */
        foreach ($results as $searchResult) {
            $responseData[] = $searchResult->getJsonSerialized();
        }
        $responseCompiler->setData($responseData);

        return $responseCompiler->getResponse();
    }

    /**
     * @Route("/orders/{orderId}")
     * @param $orderId
     * @return JsonResponse
     */
    public function getOrderAction($orderId)
    {
        //TODO: Заменить
        header('Access-Control-Allow-Origin: ' . 'null');
        $order = $this->dm->find('MBHPackageBundle:Order', $orderId);
        $responseCompiler = $this->get('mbh.api_response_compiler');
        if (is_null($order)) {
            $responseCompiler->addErrorMessage(ApiResponseCompiler::ORDER_WITH_SPECIFIED_ID_TO_EXISTS,
                ['%orderId%' => $orderId]);
        } else {
            $responseCompiler->setData($order->getJsonSerialized());
        }

        return $responseCompiler->getResponse();
    }

    /**
     * @Route("/services")
     * @param Request $request
     * @return JsonResponse
     */
    public function getServicesAction(Request $request)
    {
        //TODO: Заменить
        header('Access-Control-Allow-Origin: ' . 'null');
        $responseCompiler = $this->get('mbh.api_response_compiler');
        $apiHandler = $this->get('mbh.api_handler');

        $queryData = $request->query;
        $isOnline = !is_null($queryData->get('isOnline')) ? $request->get('isOnline') === 'true' : true;
        $isEnabled = !is_null($queryData->get('isEnabled')) ? $request->get('isEnabled') === 'true' : true;
        $tariffId = $queryData->get('tariffId');

        $apiHandler->checkMandatoryFields($queryData, ['tariffId'], $responseCompiler);

        $serviceRepository = $this->dm->getRepository('MBHPriceBundle:Service');
        $tariff = $this->dm->find('MBHPriceBundle:Tariff', $tariffId);
        if (is_null($tariff)) {
            $responseCompiler->addErrorMessage($responseCompiler::TARIFF_WITH_SPECIFIED_ID_NOT_EXISTS);
        } else {
            $servicesByCategories = $serviceRepository->getAvailableServicesForTariff($tariff);
            $servicesData = [];
            foreach ($servicesByCategories as $servicesByCategory) {
                /** @var Service $service */
                foreach ($servicesByCategory as $service) {
                    if ((!$isEnabled || $service->getIsEnabled()) && (!$isOnline || $service->getIsOnline())) {
                        $servicesData[] = $service->getJsonSerialized();
                    }
                }
            }
            $responseCompiler->setData($servicesData);
        }

        return $responseCompiler->getResponse();
    }
}