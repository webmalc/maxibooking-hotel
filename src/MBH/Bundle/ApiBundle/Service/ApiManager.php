<?php

namespace MBH\Bundle\ApiBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\ApiBundle\Lib\RequestParams;
use MBH\Bundle\BaseBundle\Lib\QueryBuilder;
use MBH\Bundle\OnlineBundle\Document\FormConfig;
use MBH\Bundle\OnlineBundle\Services\ApiResponseCompiler;
use Symfony\Component\HttpFoundation\ParameterBag;

class ApiManager
{
    private $dm;
    private $requestManager;
    /** @var ApiResponseCompiler */
    private $responseCompiler;

    public function __construct(DocumentManager $dm, ApiRequestManager $requestManager) {
        $this->dm = $dm;
        $this->requestManager = $requestManager;
    }

    /**
     * @param $onlineFormId
     * @return FormConfig|string
     */
    public function getFormConfig($onlineFormId)
    {
        /** @var FormConfig $formConfig */
        $formConfig = $this->dm->find('MBHOnlineBundle:FormConfig', $onlineFormId);

        if (is_null($formConfig)) {
            if (!is_null($onlineFormId)) {
                $this->responseCompiler->addErrorMessage(ApiResponseCompiler::FORM_CONFIG_NOT_EXISTS, 'onlineFormId');
            }
        } else {
            if (!$formConfig->getIsEnabled()) {
                $this->responseCompiler->addErrorMessage(ApiResponseCompiler::FORM_CONFIG_NOT_ENABLED, 'onlineFormId');
            }
        }

        return $formConfig;
    }

    /**
     * @param ParameterBag $bag
     * @param string $className
     * @return array
     * @throws \ReflectionException
     */
    public function getDocuments(ParameterBag $bag, string $className)
    {
        $requestParams = $this->requestManager->getCriteria($bag, $className);

        if (!$this->responseCompiler->isSuccessful()) {
            return [];
        }

        return $this->findByRequestParams($requestParams, $className);
    }

    /**
     * @param RequestParams $requestParams
     * @param string $className
     * @return array
     */
    public function findByRequestParams(RequestParams $requestParams, string $className)
    {
        $qb = $requestParams->fillQueryBuilder(new QueryBuilder($this->dm, $className));

        return $qb
            ->getQuery()
            ->execute()
            ->toArray();
    }

    public function setResponseCompiler(ApiResponseCompiler $responseCompiler)
    {
        $this->responseCompiler = $responseCompiler;
        $this->requestManager->setResponseCompiler($responseCompiler);

        return $this;
    }
}