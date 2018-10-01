<?php

namespace MBH\Bundle\ApiBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;

class BaseApiController extends BaseController
{
    protected function addAccessControlHeaders()
    {
        $this->addAccessControlAllowOriginHeaders($this->getParameter('api_domains'));
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE, PATCH');
        header('Access-Control-Allow-Headers: Content-Type, *');
    }
}