<?php

namespace MBH\Bundle\ApiBundle\Service;

use MBH\Bundle\ApiBundle\Lib\RequestParams;
use MBH\Bundle\ApiBundle\Lib\RoomTypesRequestParams;
use MBH\Bundle\ApiBundle\Lib\TariffRequestParams;
use MBH\Bundle\BaseBundle\Lib\Normalization\NormalizationException;
use MBH\Bundle\BaseBundle\Service\MBHSerializer;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\OnlineBundle\Services\ApiResponseCompiler;
use MBH\Bundle\PackageBundle\Document\Criteria\PackageQueryCriteria;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\HttpFoundation\ParameterBag;

class ApiRequestManager
{
    const CRITERIA_PARAM = 'criteria';
    const LIMIT_PARAM = 'limit';
    const DEFAULT_LIMIT = 50;
    const SKIP_PARAM = 'skip';
    const DEFAULT_SKIP = 0;
    const DATE_FORMAT = 'd.m.Y';
    const PARAMS_CONFIG = [
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
        ]
    ];

    private $apiSerializer;
    private $serializer;
    /** @var ApiResponseCompiler */
    private $responseCompiler;

    public function __construct(ApiSerializer $apiSerializer, MBHSerializer $serializer)
    {
        $this->apiSerializer = $apiSerializer;
        $this->serializer = $serializer;
    }

    /**
     * @param ParameterBag $bag
     * @return PackageQueryCriteria|object
     * @throws \ReflectionException
     */
    public function getPackageCriteria(ParameterBag $bag)
    {
        $packageCriteria = new PackageQueryCriteria();

        if (!$bag->has(self::CRITERIA_PARAM)) {
            return $packageCriteria;
        }

        $requestedCriteria = $bag->get(self::CRITERIA_PARAM);
        $this->checkIsArrayFields($bag, [self::CRITERIA_PARAM]);
        if (!$this->responseCompiler->isSuccessful()) {
            return $packageCriteria;
        }

        $packageCriteria = $this->tryDenormalizeRequestData($requestedCriteria, $packageCriteria);

        return $packageCriteria;
    }

    /**
     * @param ParameterBag $queryData
     * @param string $class
     * @return RoomTypesRequestParams
     * @throws \ReflectionException
     */
    public function getCriteria(ParameterBag $queryData, string $class)
    {
        $config = self::PARAMS_CONFIG[$class];
        $requestParams = new $config['paramsContainer']();
        $arrayFields = $config['arrayFields'] ?? [];
        $this->checkIsArrayFields($queryData, $arrayFields);

        if (!$this->responseCompiler->isSuccessful()) {
            return $requestParams;
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
     * @param $requestedCriteria
     * @param $requestParams
     * @return mixed
     * @throws \ReflectionException
     */
    private function tryDenormalizeRequestData($requestedCriteria, $requestParams)
    {
        try {
            $requestParams = $this->serializer->denormalize($requestedCriteria, $requestParams);
        } catch (NormalizationException $exception) {
            $this->addErrorMessage($exception->getMessage());
        }

        return $requestParams;
    }
}