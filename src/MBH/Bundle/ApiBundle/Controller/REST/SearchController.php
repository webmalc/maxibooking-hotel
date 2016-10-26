<?php

namespace MBH\Bundle\ApiBundle\Controller\REST;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\OnlineBundle\Document\FormConfig;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use MBH\Bundle\ApiBundle\Lib\Controller\LanguageInterface;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route("/search")
 */
class SearchController extends Controller implements LanguageInterface
{

    /**
     * Online form
     * @Route("/form/{id}/{locale}", name="api_search_from", defaults={"locale": "ru", "_format": "json"})
     * @ParamConverter("fromConfig", class="MBHOnlineBundle:FormConfig")
     * @Method("GET")
     * @View()
     * @param FormConfig $formConfig
     * @return array
     */
    public function getFormAction(FormConfig $formConfig)
    {
        return $formConfig;
    }
}
