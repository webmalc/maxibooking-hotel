<?php

namespace MBH\Bundle\ApiBundle\Controller;

use MBH\Bundle\ApiBundle\Service\ApiManager;
use MBH\Bundle\ApiBundle\Service\ApiRequestManager;
use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\OnlineBundle\Services\ApiResponseCompiler;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BaseApiController extends BaseController
{
    /** @var ApiResponseCompiler */
    protected $responseCompiler;
    /** @var ApiManager */
    protected $apiManager;
    /** @var ApiRequestManager */
    protected $requestManager;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->responseCompiler = $this->get('mbh.api_response_compiler');
        $this->apiManager = $this->get('mbh.api_manager')->setResponseCompiler($this->responseCompiler);
        $this->requestManager = $this->get('mbh.api_request_manager')->setResponseCompiler($this->responseCompiler);
    }
}