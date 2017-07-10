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
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
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

        if (!$responseCompiler->isSuccessFull()) {
            return $responseCompiler->getResponse();
        }

        $roomTypes = $roomTypesQB
            ->getQuery()
            ->execute();

        /** @var FormConfig $formConfig */
        $formConfig = $requestHandler->getFormConfig($onlineFormId, $responseCompiler);
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
        }

        if (!is_null($hotelIds)) {
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
}