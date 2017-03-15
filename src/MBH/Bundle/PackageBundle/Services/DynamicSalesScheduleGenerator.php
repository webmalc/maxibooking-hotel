<?php
namespace MBH\Bundle\PackageBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Lib\DynamicSalesSchedule;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class DynamicSalesScheduleGenerator
 * @package MBH\Bundle\PackageBundle\Services
 */
class DynamicSalesScheduleGenerator
{
    /**
     * @var DocumentManager
     */
    protected $dm;
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * DynamicSalesGenerator constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->container = $container;
    }


    /**
     * @param Request $request
     * @param Hotel $hotel
     * @return array
     */
    public function generateDynamicSales(Request $request, Hotel $hotel)
    {

        $begin = $request->get('begin');
        $end = $request->get('end');
        $optionShow = $request->get('optionsShow');
        $growth = $request->get('growth');

        $translator = $this->container->get('translator');

        $result = [];

        $translate = [
            'sales-day' => 'dynamic.sales.schedule.sales.day',
            'count-packages' => 'dynamic.sales.schedule.sales.amount',
            'count-people' => 'dynamic.sales.schedule.sales.count.people',
            'count-numbers' => 'dynamic.sales.schedule.sales.count.numbers',
        ];

        $translateYaxis = [
            'sales-day' => 'dynamic.sales.schedule.sales.day.Yaxis',
            'count-packages' => 'dynamic.sales.schedule.sales.amount.Yaxis',
            'count-people' => 'dynamic.sales.schedule.sales.count.people.Yaxis',
            'count-numbers' => 'dynamic.sales.schedule.sales.count.numbers.Yaxis',
        ];

        $begin = array_diff($begin, array('', NULL, false));
        $end = array_diff($end, array('', NULL, false));

        $begin = array_values($begin);
        $end = array_values($end);

        if ($request->get('roomTypes')) {
            $roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')->fetch($hotel, $request->get('roomTypes'));
        } else {
            $roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')->findBy(['hotel.id' => $hotel->getId()]);
        }

        $result [] = $this->dynamicSalesSchedule($begin, $end, $roomTypes, $optionShow, $growth);
        $result[] = $translator->trans($translate[$optionShow], [], 'MBHPackageBundle');
        $result[] = $translator->trans($translateYaxis[$optionShow], [], 'MBHPackageBundle');

        return $result;
    }

    public function dynamicSalesSchedule($begin, $end, $roomTypes, $optionShow, $growth)
    {

        $roomTypesIds = $this->container->get('mbh.helper')->toIds($roomTypes);
        $periodRange = $this->container->getParameter('mbh_dynamic_sale_period');
        $translator = $this->container->get('translator');

        for ($i = 0; $i < count($begin); $i++) {

            $ends = new \DateTime($end[$i]);
            $begins = new \DateTime($begin[$i]);

            if ($ends->diff($begins)->days > $periodRange) {
                return ['error' => $translator->trans('dynamic.sales.error.range', [], 'MBHPackageBundle') . ' ' . $periodRange . ' ' . $translator->trans('dynamic.sales.error.day', [], 'MBHPackageBundle')];
            }

            $packagesAll[$i] = $this->dm->getRepository('MBHPackageBundle:Package')->getPackgesRoomTypes(new \DateTime($begin[$i]), new \DateTime($end[$i]), $roomTypesIds);
            $packagesAll[$i] = $packagesAll[$i]->toArray();
            $periods[$i] = new \DatePeriod(new \DateTime($begin[$i]), \DateInterval::createFromDateString('1 day'), new \DateTime($end[$i]));

        }

        $res = [];

        foreach ($periods as $period => $valPeriod) {

            $period = [];
            $allAmount = 0;

            foreach ($valPeriod as $day) {

                $DynamicSalesScheduleDay = new DynamicSalesSchedule();
                $DynamicSalesScheduleDay->setDay($day->format('m.d.Y'));

                foreach ($packagesAll as $packages) {
                    foreach ($packages as $package) {

                        if ($package->getCreatedAt()->format('d.m.Y') == $day->format('d.m.Y')) {

                            $values = self::calcValue($package, $optionShow);
                            $DynamicSalesScheduleDay->setAmount($DynamicSalesScheduleDay->getAmount() + $values);

                        }
                    }

                }

                if ($growth == 'true') {
                    $allAmount = $allAmount + $DynamicSalesScheduleDay->getAmount();
                    $DynamicSalesScheduleDay->setAmount($allAmount);
                }

                (isset($DynamicSalesScheduleDay)) ? $period[] = $DynamicSalesScheduleDay : null;

            }
            $res[] = $period;
        }

        return $res;

    }

    private function calcValue($package, $optionShow)
    {
        if ($optionShow == 'sales-day') {
            return $package->getPrice();
        }
        if ($optionShow == 'count-packages') {
            return 1;
        }
        if ($optionShow == 'count-people') {

            $begin = $package->getBegin();
            $end = $package->getEnd();
            return ($package->getAdults() + $package->getChildren()) * ($end->diff($begin)->days);
        }
        if ($optionShow == 'count-numbers') {
            $begin = $package->getBegin();
            $end = $package->getEnd();
            return ($end->diff($begin)->days);
        }

    }

}