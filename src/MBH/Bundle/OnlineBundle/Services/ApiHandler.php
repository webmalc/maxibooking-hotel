<?php

namespace MBH\Bundle\OnlineBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\OnlineBundle\Document\FormConfig;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class ApiHandler
{
    const DATE_FORMAT = 'd.m.Y';
    const MIN_PRICES_PERIOD_IN_DAYS = 100;

    /** @var DocumentManager $dm */
    private $dm;

    public function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    /**
     * @param ParameterBag $queryData
     * @param array $fieldNames
     * @param ApiResponseCompiler $responseCompiler
     * @return ApiResponseCompiler
     */
    public function checkMandatoryFields(ParameterBag $queryData, array $fieldNames, ApiResponseCompiler $responseCompiler)
    {
        foreach ($fieldNames as $fieldName) {
            if (is_null($queryData->get($fieldName))) {
                $responseCompiler->addErrorMessage(ApiResponseCompiler::MANDATORY_FIELD_MISSING, $fieldName);
            }
        }

        return $responseCompiler;
    }

    /**
     * @param ResponseInterface $response
     * @param ApiResponseCompiler $responseCompiler
     * @return ApiResponseCompiler
     */
    public function checkBillingEntityResponse(ResponseInterface $response, ApiResponseCompiler $responseCompiler)
    {
        $decodedResponse = json_decode($response->getBody(), true);

        if (!isset($decodedResponse['id'])) {
            foreach ($decodedResponse as $fieldName => $errors) {
                foreach ($errors as $error) {
                    $responseCompiler->addErrorMessage($error, $fieldName);
                }
            }
        }

        return $responseCompiler;
    }

    /**
     * @param $onlineFormId
     * @param ApiResponseCompiler $responseCompiler
     * @return FormConfig|string
     */
    public function getFormConfig($onlineFormId, ApiResponseCompiler &$responseCompiler)
    {
        /** @var FormConfig $formConfig */
        $formConfig = $this->dm
            ->find('MBHOnlineBundle:FormConfig', $onlineFormId);

        if (is_null($formConfig)) {
            if (!is_null($onlineFormId)) {
                $responseCompiler->addErrorMessage(ApiResponseCompiler::FORM_CONFIG_NOT_EXISTS, 'onlineFormId');
            }
        } else {
            if (!$formConfig->getIsEnabled()) {
                $responseCompiler->addErrorMessage(ApiResponseCompiler::FORM_CONFIG_NOT_ENABLED, 'onlineFormId');
            }
        }

        return $formConfig;
    }

    /**
     * @param $roomTypeIds
     * @param ApiResponseCompiler $responseCompiler
     * @param FormConfig|null $formConfig
     * @return array
     */
    public function getFilteredRoomTypeIds($roomTypeIds, ApiResponseCompiler &$responseCompiler, ?FormConfig $formConfig)
    {
        $filteredRoomTypeIds = [];
        if (!is_null($roomTypeIds)) {
            foreach ($roomTypeIds as $roomTypeId) {
                $roomType = $this->dm->find('MBHHotelBundle:RoomType', $roomTypeId);
                if (is_null($roomType)) {
                    $responseCompiler->addErrorMessage($responseCompiler::ROOM_TYPE_WITH_SPECIFIED_ID_NOT_EXISTS,
                        'roomTypeIds',
                        ['%roomTypeId%' => $roomTypeId]
                    );
                } elseif (!is_null($formConfig) && $formConfig->getRoomTypes() && !$formConfig->getRoomTypeChoices()->contains($roomType)) {
                    $responseCompiler->addErrorMessage($responseCompiler::FORM_CONFIG_NOT_CONTAINS_SPECIFIED_ROOM_TYPE,
                        'roomTypeIds',
                        ['%roomTypeId%' => $roomTypeId]
                    );
                } else {
                    $filteredRoomTypeIds[] = $roomTypeId;
                }
            }
        }

        return $filteredRoomTypeIds;
    }

    /**
     * @param $hotelIds
     * @param ApiResponseCompiler $responseCompiler
     * @param FormConfig|null $formConfig
     * @return array
     */
    public function getFilteredHotels($hotelIds, ApiResponseCompiler &$responseCompiler, ?FormConfig $formConfig)
    {
        $filteredHotels = [];
        foreach ($hotelIds as $hotelId) {
            $hotel = $this->dm->find('MBHHotelBundle:Hotel', $hotelId);
            if (is_null($hotel)) {
                $responseCompiler->addErrorMessage($responseCompiler::HOTEL_WITH_SPECIFIED_ID_NOT_EXISTS,
                    'hotelIds',
                    ['%hotelId%' => $hotelId]
                );
            } elseif (!is_null($formConfig) && !$formConfig->getHotels()->contains($hotel)) {
                $responseCompiler->addErrorMessage($responseCompiler::FORM_CONFIG_NOT_CONTAINS_SPECIFIED_HOTEL,
                    'hotelIds',
                    ['%hotelId%' => $hotelId]
                );
            } else {
                $filteredHotels[] = $hotel;
            }
        }

        return $filteredHotels;
    }

    /**
     * @param ParameterBag $queryData
     * @param array $fieldNames
     * @param ApiResponseCompiler $responseCompiler
     * @return ApiResponseCompiler
     */
    public function checkIsArrayFields(ParameterBag $queryData, array $fieldNames, ApiResponseCompiler $responseCompiler)
    {
        foreach ($fieldNames as $fieldName) {
            $fieldData = $queryData->get($fieldName);
            if (!is_array($fieldData) && !is_null($fieldData)) {
                $responseCompiler->addErrorMessage($responseCompiler::FIELD_MUST_BE_TYPE_OF_ARRAY, $fieldName);
            }
        }

        return $responseCompiler;
    }
}