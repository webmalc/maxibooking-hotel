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
    const SPECIAL_MONTH_BEGIN = 4;
    const SPECIAL_MONTH_END = 11;
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        $specialsFilter = new SpecialFilter();

        $specialsFilter->setBegin(new \DateTime("now midnight"));
        $specials = $this->dm->getRepository('MBHPriceBundle:Special')->getFiltered($specialsFilter);
        $preparer = $this->get('mbh.online.special_data_preparer');

        $result = $preparer->getPreparedDataByMonth($specials->toArray());
//        $result = $preparer->getPreparedData($specials->toArray());
        return [
            'data' => $result,
            'monthList' => $this->getMonthList()
        ];
    }

    private function getMonthList()
    {

        $now = new \DateTime();

        $begin = \DateTime::createFromFormat('d-n-Y', '01-'.self::SPECIAL_MONTH_BEGIN.'-'.$now->format('Y'));
        $end = \DateTime::createFromFormat('d-n-Y', '01-'.self::SPECIAL_MONTH_END.'-'.$now->format('Y'));

        $result = [];

        $fmt = new \IntlDateFormatter(
            'ru_RU',
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            'Europe/Moscow',
            null,
            "LLLL"
        );
        foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 month'), $end) as $month) {

            $result['month_'.$month->format('m')] = $fmt->format($month);
        }

        return $result;
    }



}