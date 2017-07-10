<?php
/**
 * Created by PhpStorm.
 * User: danya
 * Date: 10.07.17
 * Time: 12:19
 */

namespace MBH\Bundle\OnlineBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\OnlineBundle\Document\FormConfig;
use Symfony\Component\HttpFoundation\ParameterBag;

class ApiHandler
{
    const DATE_FORMAT = 'd.m.Y';

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
                $responseCompiler->addErrorMessage(ApiResponseCompiler::MANDATORY_FIELD_MISSING, ['%field%' => $fieldName]);
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
            ->getRepository('MBHOnlineBundle:FormConfig')
            ->findOneBy(['hotels.id' => $onlineFormId]);
        if (!is_null($formConfig)) {
            if (!is_null($onlineFormId)) {
                $responseCompiler->addErrorMessage(ApiResponseCompiler::FORM_CONFIG_NOT_EXISTS);
            }
            if (!$formConfig->getIsEnabled()) {
                $responseCompiler->addErrorMessage(ApiResponseCompiler::FORM_CONFIG_NOT_ENABLED);
            }
        }

        return $formConfig;
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
                $responseCompiler->addErrorMessage($responseCompiler::FIELD_MUST_BE_TYPE_OF_ARRAY, ['%field%' => $fieldName]);
            }
        }

        return $responseCompiler;
    }
}