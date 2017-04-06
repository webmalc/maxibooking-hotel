<?php

namespace MBH\Bundle\OnlineBookingBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Lib\SpecialFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SpecialController
 * @package MBH\Bundle\OnlineBookingBundle\Controller
 * @Route("/specials")
 */
class SpecialsController extends BaseController
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $specialsFilter = new SpecialFilter();
        $specialsFilter->setBegin(new \DateTime("now midnight"));
        $specials = $this->dm->getRepository('MBHPriceBundle:Special')->getFiltered($specialsFilter);
        $preparer = $this->get('mbh.online.special_data_preparer');

        return [
            'specials' => $preparer->getPreparedData($specials->toArray())
        ];
    }

}