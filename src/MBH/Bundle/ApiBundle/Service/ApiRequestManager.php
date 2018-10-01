<?php

namespace MBH\Bundle\ApiBundle\Service;

use MBH\Bundle\OnlineBundle\Services\ApiResponseCompiler;
use Symfony\Component\HttpFoundation\ParameterBag;

class ApiRequestManager
{
    const CRITERIA_PARAM = 'criteria';
    const LIMIT_PARAM = 'limit';
    const DEFAULT_LIMIT = 50;
    const OFFSET_PARAM = 'offset';
    const DEFAULT_OFFSET = 0;
    const DATE_FORMAT = 'd.m.Y';

    private $apiSerializer;

    public function __construct(ApiSerializer $apiSerializer) {
        $this->apiSerializer = $apiSerializer;
    }

    public function getRequestOffset(ParameterBag $bag, ApiResponseCompiler $responseCompiler)
    {
        if (!$bag->has(self::OFFSET_PARAM)) {
            return self::DEFAULT_OFFSET;
        }

        $requestedOffset = (int)$bag->get(self::OFFSET_PARAM);
        if ($requestedOffset < 0) {
            $responseCompiler->addErrorMessage('The value of the field "offset" must be greater of equal to zero');

            return self::DEFAULT_OFFSET;
        }

        return $requestedOffset;
    }

    public function getRequestLimit(ParameterBag $bag, ApiResponseCompiler $responseCompiler, $default = self::DEFAULT_LIMIT)
    {
        if (!$bag->has(self::OFFSET_PARAM)) {
            return $default;
        }

        $requestedLimit = (int)$bag->get(self::LIMIT_PARAM);
        if ($requestedLimit < 0) {
            $responseCompiler->addErrorMessage('The value of the field "limit" must be greater of equal to zero');
        }

        return $default;
    }

    public function getPackageCriteria(ParameterBag $bag, ApiResponseCompiler $responseCompiler)
    {
        if ($bag->has(self::CRITERIA_PARAM)) {
            $requestedCriteria = $bag->get(self::CRITERIA_PARAM);
            $this->checkIsArrayFields($bag, [self::CRITERIA_PARAM], $responseCompiler);
        } else {
            $requestedCriteria = [];
        }

        $packageCriteria = $this->apiSerializer->denormalizePackageCriteria($requestedCriteria);
        $packageCriteria->limit = $this->getRequestLimit($bag, $responseCompiler);
        $packageCriteria->skip = $this->getRequestOffset($bag, $responseCompiler);

        return $packageCriteria;
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
}