<?php

namespace MBH\Bundle\ApiBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\ApiBundle\Lib\RequestParams;
use MBH\Bundle\ApiBundle\Lib\RoomTypesRequestParams;
use MBH\Bundle\ApiBundle\Lib\TariffRequestParams;
use MBH\Bundle\BaseBundle\Lib\Normalization\NormalizationException;
use MBH\Bundle\BaseBundle\Service\MBHSerializer;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\OnlineBundle\Document\FormConfig;
use MBH\Bundle\OnlineBundle\Services\ApiResponseCompiler;
use MBH\Bundle\PackageBundle\Document\Criteria\PackageQueryCriteria;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\HttpFoundation\ParameterBag;

class ApiRequestManager
{
    const LIMIT_PARAM = 'limit';
    const DEFAULT_LIMIT = 50;
    const SKIP_PARAM = 'skip';
    const DEFAULT_SKIP = 0;
    const DATE_FORMAT = 'd.m.Y';

    private $apiSerializer;
    private $serializer;
    /** @var ApiResponseCompiler */
    private $responseCompiler;
    private $dm;

    public function __construct(ApiSerializer $apiSerializer, MBHSerializer $serializer, DocumentManager $dm)
    {
        $this->apiSerializer = $apiSerializer;
        $this->serializer = $serializer;
        $this->dm = $dm;
    }

    /**
     * @param ParameterBag $bag
     * @return PackageQueryCriteria|object
     * @throws \ReflectionException
     */
    public function getPackageCriteria(ParameterBag $bag)
    {
        $packageCriteria = new PackageQueryCriteria();

        $this->checkIsArrayFields($bag, ['sort', 'accommodations', 'roomTypes']);
        if (!$this->responseCompiler->isSuccessful()) {
            return $packageCriteria;
        }

        $packageCriteria = $this->tryDenormalizeRequestData($bag->all(), $packageCriteria);

        return $packageCriteria;
    }

    /**
     * @param ParameterBag $queryData
     * @param string $class
     * @return RequestParams|PackageQueryCriteria|SearchQuery
     * @throws \ReflectionException
     */
    public function getCriteria(ParameterBag $queryData, string $class)
    {
        $config = $this->getCriteriaSettings($class);
        $requestParams = new $config['paramsContainer']();

        $this->checkRequestFields($queryData, $config);

        if (!$this->responseCompiler->isSuccessful()) {
            return new $requestParams();
        }

        return $this->tryDenormalizeRequestData($queryData->all(), $requestParams);
    }

    /**
     * @param ParameterBag $queryData
     * @param array $fieldNames
     * @return ApiRequestManager
     */
    public function checkIsArrayFields(ParameterBag $queryData, array $fieldNames)
    {
        foreach ($fieldNames as $fieldName) {
            $fieldData = $queryData->get($fieldName);
            if (!is_array($fieldData) && !is_null($fieldData)) {
                $this->addErrorMessage(ApiResponseCompiler::FIELD_MUST_BE_TYPE_OF_ARRAY, $fieldName, [
                    '%field%' => $fieldName
                ]);
            }
        }

        return $this;
    }

    /**
     * @param ParameterBag $queryData
     * @param array $fieldNames
     */
    public function checkMandatoryFields(ParameterBag $queryData, array $fieldNames)
    {
        foreach ($fieldNames as $fieldName) {
            if (is_null($queryData->get($fieldName))) {
                $this->addErrorMessage(ApiResponseCompiler::MANDATORY_FIELD_MISSING, $fieldName, [
                    '%field%' => $fieldName
                ]);
            }
        }
    }


    /**
     * @param $roomTypeIds
     * @param FormConfig|null $formConfig
     * @return array
     */
    public function getFilteredRoomTypeIds($roomTypeIds, ?FormConfig $formConfig)
    {
        $filteredRoomTypeIds = [];
        if (!is_null($roomTypeIds)) {
            foreach ($roomTypeIds as $roomTypeId) {
                $roomType = $this->dm->find('MBHHotelBundle:RoomType', $roomTypeId);
                if (is_null($roomType)) {
                    $this->addErrorMessage(ApiResponseCompiler::ROOM_TYPE_WITH_SPECIFIED_ID_NOT_EXISTS,
                        'roomTypeIds',
                        ['%roomTypeId%' => $roomTypeId]
                    );
                } elseif (!is_null($formConfig) && $formConfig->containsRoomType($roomType)) {
                    $this->addErrorMessage(ApiResponseCompiler::FORM_CONFIG_NOT_CONTAINS_SPECIFIED_ROOM_TYPE,
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
     * @param FormConfig|null $formConfig
     * @return array
     */
    public function getFilteredHotels($hotelIds, ?FormConfig $formConfig)
    {
        $filteredHotels = [];
        if (!is_null($hotelIds)) {
            foreach ($hotelIds as $hotelId) {
                $hotel = $this->dm->find('MBHHotelBundle:Hotel', $hotelId);
                if (is_null($hotel)) {
                    $this->addErrorMessage(ApiResponseCompiler::HOTEL_WITH_SPECIFIED_ID_NOT_EXISTS,
                        'hotelIds',
                        ['%hotelId%' => $hotelId]
                    );
                } elseif (!is_null($formConfig) && !$formConfig->containsHotel($hotel)) {
                    $this->addErrorMessage(ApiResponseCompiler::FORM_CONFIG_NOT_CONTAINS_SPECIFIED_HOTEL,
                        'hotelIds',
                        ['%hotelId%' => $hotelId]
                    );
                } else {
                    $filteredHotels[] = $hotel;
                }
            }
        }

        return $filteredHotels;
    }

    /**
     * @param string $textId
     * @param string $fieldName
     * @param array $transParams
     * @return ApiRequestManager
     */
    public function addErrorMessage(string $textId, string $fieldName = null, array $transParams = [])
    {
        if (is_null($this->responseCompiler)) {
            throw new \RuntimeException('Instance of ApiResponseCompiler is not specified');
        }

        $this->responseCompiler->addErrorMessage($textId, $fieldName, $transParams);

        return $this;
    }

    /**
     * @param ApiResponseCompiler $responseCompiler
     * @return ApiRequestManager
     */
    public function setResponseCompiler(ApiResponseCompiler $responseCompiler)
    {
        $this->responseCompiler = $responseCompiler;

        return $this;
    }

    /**
     * @param ParameterBag $bag
     * @param FormConfig|null $formConfig
     * @return array
     */
    public function getFilteredRoomTypeIdsByFormConfig(ParameterBag $bag, ?FormConfig $formConfig)
    {
        $roomTypeIds = $bag->get('roomTypeIds');
        $hotelIds = $bag->get('hotelIds');

        if (!is_null($roomTypeIds)) {
            $roomTypeIds = $this->getFilteredRoomTypeIds($roomTypeIds, $formConfig);
        }

        if (!is_null($hotelIds)) {
            $filteredHotels = $this->getFilteredHotels($hotelIds, $formConfig);
        } else {
            $filteredHotels = !is_null($formConfig)
                ? $formConfig->getHotels()
                : $this->dm->getRepository(Hotel::class)->findBy(['isEnabled' => true]);
        }

        $filteredRoomTypeIds = [];
        foreach ($filteredHotels as $hotel) {
            foreach ($hotel->getRoomTypes() as $roomType) {
                if (is_null($roomTypeIds) || in_array($roomType->getId(), $roomTypeIds)) {
                    $filteredRoomTypeIds[] = $roomType->getId();
                }
            }
        }

        return $filteredRoomTypeIds;
    }

    /**
     * @param $requestedCriteria
     * @param $requestParams
     * @return mixed
     * @throws \ReflectionException
     */
    private function tryDenormalizeRequestData($requestedCriteria, $requestParams)
    {
        //TODO: Добавить валидацию
        try {
            $requestParams = $this->serializer->denormalize($requestedCriteria, $requestParams);
        } catch (NormalizationException $exception) {
            $this->addErrorMessage($exception->getMessage());
        }

        return $requestParams;
    }

    private function getCriteriaSettings(string $class)
    {
        $criteriaSettings = [
            Package::class => [
                'paramsContainer' => PackageQueryCriteria::class,
                'arrayFields' => ['sort', 'accommodations', 'roomTypes']
            ],
            RoomType::class => [
                'paramsContainer' => RoomTypesRequestParams::class,
                'arrayFields' => ['roomTypeIds', 'hotelIds'],
            ],
            Tariff::class => [
                'paramsContainer' => TariffRequestParams::class,
                'arrayFields' => ['hotelIds']
            ],
            Hotel::class => [
                'paramsContainer' => RequestParams::class,
            ],
            SearchQuery::class => [
                'paramsContainer' => SearchQuery::class,
                'arrayFields' => ['hotelIds', 'roomTypeIds', 'childrenAges'],
                'mandatoryFields' => ['begin', 'end', 'adults'],
            ]
        ];

        if (!isset($criteriaSettings[$class])) {
            throw new \InvalidArgumentException('There is no criteria config for class ' . $class);
        }

        return $criteriaSettings[$class];
    }

    /**
     * @param ParameterBag $queryData
     * @param $config
     */
    private function checkRequestFields(ParameterBag $queryData, $config): void
    {
        $arrayFields = $config['arrayFields'] ?? [];
        $this->checkIsArrayFields($queryData, $arrayFields);

        $mandatoryFields = $config['mandatoryFields'] ?? [];
        $this->checkMandatoryFields($queryData, $mandatoryFields);
    }
}