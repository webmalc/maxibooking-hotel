<?php

namespace MBH\Bundle\ApiBundle\Service;

use MBH\Bundle\BaseBundle\Service\MBHSerializer;
use MBH\Bundle\OnlineBundle\Services\ApiResponseCompiler;
use MBH\Bundle\PackageBundle\Document\Criteria\PackageQueryCriteria;
use Symfony\Component\HttpFoundation\ParameterBag;

class ApiRequestManager
{
    const CRITERIA_PARAM = 'criteria';
    const LIMIT_PARAM = 'limit';
    const DEFAULT_LIMIT = 50;
    const SKIP_PARAM = 'skip';
    const DEFAULT_SKIP = 0;
    const DATE_FORMAT = 'd.m.Y';

    private $apiSerializer;
    private $serializer;

    public function __construct(ApiSerializer $apiSerializer, MBHSerializer $serializer) {
        $this->apiSerializer = $apiSerializer;
        $this->serializer = $serializer;
    }

    public function getRequestSkip(ParameterBag $bag, ApiResponseCompiler $responseCompiler)
    {
        if (!$bag->has(self::SKIP_PARAM)) {
            return self::DEFAULT_SKIP;
        }

        $requestedSkip = (int)$bag->get(self::SKIP_PARAM);
        if ($requestedSkip < 0) {
            $responseCompiler->addErrorMessage('The value of the field "skip" must be greater of equal to zero');

            return self::DEFAULT_SKIP;
        }

        return $requestedSkip;
    }

    public function getRequestLimit(ParameterBag $bag, ApiResponseCompiler $responseCompiler, $default = self::DEFAULT_LIMIT)
    {
        if (!$bag->has(self::LIMIT_PARAM)) {
            return $default;
        }

        $requestedLimit = (int)$bag->get(self::LIMIT_PARAM);
        if ($requestedLimit < 0) {
            $responseCompiler->addErrorMessage('The value of the field "limit" must be greater of equal to zero');
        }

        return $requestedLimit;
    }

    /**
     * @param ParameterBag $bag
     * @param ApiResponseCompiler $responseCompiler
     * @return PackageQueryCriteria|object
     * @throws \ReflectionException
     */
    public function getPackageCriteria(ParameterBag $bag, ApiResponseCompiler $responseCompiler)
    {
        if ($bag->has(self::CRITERIA_PARAM)) {
            $requestedCriteria = $bag->get(self::CRITERIA_PARAM);
            $this->checkIsArrayFields($bag, [self::CRITERIA_PARAM], $responseCompiler);
        } else {
            $requestedCriteria = [];
        }

        $packageCriteria = $this->serializer->denormalize($requestedCriteria, new PackageQueryCriteria());
        $packageCriteria->limit = $this->getRequestLimit($bag, $responseCompiler);
        $packageCriteria->skip = $this->getRequestSkip($bag, $responseCompiler);

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
                $responseCompiler->addErrorMessage($responseCompiler::FIELD_MUST_BE_TYPE_OF_ARRAY, $fieldName, [
                    '%field%' => $fieldName
                ]);
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
                $responseCompiler->addErrorMessage(ApiResponseCompiler::MANDATORY_FIELD_MISSING, $fieldName, [
                    '%field%' => $fieldName
                ]);
            }
        }

        return $responseCompiler;
    }
}