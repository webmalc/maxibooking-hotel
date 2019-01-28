<?php

namespace MBH\Bundle\OnlineBookingBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\OnlineBookingBundle\Service\SpecialDataPreparer;
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
    public const SPECIAL_MONTH_BEGIN = 5;
    public const SPECIAL_MONTH_END = 10;
    public const SPECIAL_PER_PAGE = 5;
    public const SPECIALS_SORT_BY_PRICE = 'mbh.online.special_data_preparer.price_sort';
    public const SPECIALS_SORT_BY_DATE = 'mbh.online.special_data_preparer.date_sort';

    public const DATA_PREPARER = [
        'byDate' => self::SPECIALS_SORT_BY_DATE,
        'byPrice' => self::SPECIALS_SORT_BY_PRICE,
    ];


    /**
     * @Route("/{id}", name="all_specials", defaults={"id":""})
     * @Template()
     */
    public function indexAction(Hotel $hotel = null)
    {

        $sortParameter = $this->getParameter('online_booking')['special_list_sort'];
        $preparerService = self::DATA_PREPARER[$sortParameter];
        /** @var SpecialDataPreparer $preparer */
        $preparer = $this->get($preparerService);
        /** @var ArrayCollection $specials */
        $specials = $preparer->getSpecials($hotel);

        $preparedData = $preparer->getSpecialsPageFormatWithMonth($specials->toArray());
        $hotelsIds = $this->getHotelsIdsFromSpecials($specials->toArray());
        $hotels = $this->dm->getRepository('MBHHotelBundle:Hotel')->getHotelsByIds($hotelsIds);

        return [
            'data' => $preparedData,
            'monthList' => $this->getMonthList(),
            'hotels' => $hotels,
            'multiHotel' => $hotel === null,
            'specialPerPage' => self::SPECIAL_PER_PAGE,
        ];
    }

    private function getHotelsIdsFromSpecials(array $specials): array
    {
        $ids = [];
        foreach ($specials as $special) {
            /** @var Special $special */
            $ids[] = $special->getHotel()->getId();
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