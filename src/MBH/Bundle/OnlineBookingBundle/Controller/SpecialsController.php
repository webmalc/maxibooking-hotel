<?php

namespace MBH\Bundle\OnlineBookingBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Lib\SpecialFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
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
     * @Route("/{id}", name="all_specials", defaults={"id":""})
     * @Template()
     */
    public function indexAction(Hotel $hotel = null)
    {
        $specialsFilter = new SpecialFilter();
        $specialsFilter->setRemain(1);
        $specialsFilter->setBegin(new \DateTime("now midnight"));
        if ($hotel) {
            $specialsFilter->setHotel($hotel);
        }
        $specials = $this->dm->getRepository('MBHPriceBundle:Special')->getFiltered($specialsFilter);

        $preparer = $this->get('mbh.online.special_data_preparer');
        $preparedData = $preparer->getPreparedDataByMonth($specials->toArray());


        $hotelsIds = $this->getHotelsIdsFromSpecials($specials->toArray());
        $hotels = $this->dm->getRepository('MBHHotelBundle:Hotel')->getHotelsByIds($hotelsIds);

        return [
            'data' => $preparedData,
            'monthList' => $this->getMonthList(),
            'hotels' => $hotels,
            'multiHotel' => $hotel == false

        ];
    }

    private function getHotelsIdsFromSpecials(array $specials): array
    {
        $ids = [];
        foreach ($specials as $special) {
            /** @var Special $special */
            array_push($ids, $special->getHotel()->getId());
        }

        return array_unique($ids);
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