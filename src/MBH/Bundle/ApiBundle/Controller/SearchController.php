<?php

namespace MBH\Bundle\ApiBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\OnlineBundle\Document\FormConfig;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use MBH\Bundle\ApiBundle\Lib\Controller\LanguageInterface;

/**
 * @Route("/search")
 */
class SearchController extends Controller implements LanguageInterface
{

    /**
     * Online form
     * @Route("/form/{id}/{locale}", name="api_search_from", defaults={"locale": "ru"})
     * @ParamConverter("fromConfig", class="MBHOnlineBundle:FormConfig")
     * @Method("GET")
     * @Template("")
     * @param FormConfig $formConfig
     * @return array
     */
    public function getFormAction(FormConfig $formConfig)
    {
        // TODO: @Cache(expires="tomorrow", public=true)
        $query = new SearchQuery();
        $query->begin = new \DateTime();
        $query->end = new \DateTime("+1 day");
        $query->hotels = count($formConfig->getHotels()) > 1 ? [] : $formConfig->getHotels();
        $form = $this->createForm('mbh_api_search_type', $query, ['config' => $formConfig]);

        return [
            'config' => $formConfig,
            'form' => $form->createView(),
            'restrictions' => json_encode($this->dm->getRepository('MBHPriceBundle:Restriction')->fetchInOut()),
        ];
    }
}
