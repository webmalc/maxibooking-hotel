<?php

namespace MBH\Bundle\ApiBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\OnlineBundle\Document\FormConfig;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/search")
 */
class SearchController extends Controller
{

    /**
     * Online form
     * @Route("/form/{id}", name="api_search_from")
     * @ParamConverter("fromConfig", class="MBHOnlineBundle:FormConfig")
     * @Method("GET")
     * @Template("")
     * @param FormConfig $formConfig
     * @return array
     */
    public function getFormAction(FormConfig $formConfig)
    {
        // TODO: @Cache(expires="tomorrow", public=true)

        return [
            'config' => $formConfig
        ];
    }
}
