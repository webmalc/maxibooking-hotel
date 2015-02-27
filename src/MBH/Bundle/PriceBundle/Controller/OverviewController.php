<?php

namespace MBH\Bundle\PriceBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\PackageBundle\Document\RoomCacheOverwrite;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;

/**
 * @Route("overview")
 */
class OverviewController extends Controller implements CheckHotelControllerInterface
{

    /**
     * @Route("/", name="prices_overview")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template()
     */
    public function indexAction()
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();

        $now = new \DateTime('00:00');
        $tariffs = [
            'Основные' => [], 'Дополниетльные' => [], 'Завершенные' => []
        ];
        foreach ($hotel->getTariffs() as $tariff) {
            if ($tariff->getEnd() > $now && $tariff->getIsDefault()) {
                $tariffs['Основные'][] = $tariff;
            } elseif ($tariff->getEnd() > $now && !$tariff->getIsDefault()) {
                $tariffs['Дополнительные'][] = $tariff;
            } elseif ($tariff->getEnd() <= $now) {
                $tariffs['Завершенные'][] = $tariff;
            }
        }

        return [
            'roomTypes' => $hotel->getRoomTypes(),
            'tariffs' => $tariffs,
        ];
    }

    /**
     * @param Request $request
     * @return Response
     * @Route("/", name="prices_overview_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     */
    public function saveAction(Request $request)
    {
        $response = $this->redirect($this->generateUrl('prices_overview'));
        $prices = $request->get('prices');
        $helper = $this->container->get('mbh.helper');
        
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $begin = $helper->getDateFromString($request->get('begin'));
        $end = $helper->getDateFromString($request->get('end'));
        $tariffs = array_unique($request->get('tariffs'));
        $roomTypes = array_unique($request->get('roomTypes'));

        //remove old roomCacheOverwrite
        $qb = $dm->getRepository('MBHPackageBundle:RoomCacheOverwrite')->createQueryBuilder('q');
        $roomCacheOverwrite = $qb->field('tariff.id')->in($tariffs)
            ->field('roomType.id')->in($roomTypes)
            ->field('date')->range($begin, $end)
            ->getQuery()->execute();
        ;

        foreach ($roomCacheOverwrite as $doc) {
            $dm->remove($doc);
        }
        $dm->flush();

        if (!is_array($prices) || !count($prices)) {
            return $response;
        }

        foreach ($prices as $tariffId => $tariffData) {

            $tariff = $dm->getRepository('MBHPriceBundle:Tariff')->find($tariffId);
            if (!$tariff) {
                continue;
            }
            foreach ($tariffData as $roomTypeId => $roomTypeData) {

                $roomType = $dm->getRepository('MBHHotelBundle:RoomType')->find($roomTypeId);
                if (!$roomType) {
                    continue;
                }
                foreach ($roomTypeData as $dateString => $dateData) {
                    $date = $this->container->get('mbh.helper')->getDateFromString($dateString);
                    if(!$date) {
                        continue;
                    }

                    $doc = new RoomCacheOverwrite();
                    $doc->setTariff($tariff)
                        ->setRoomType($roomType)
                        ->setDate($date)
                        ->setPlaces((isset($dateData['places']['places']) && is_numeric($dateData['places']['places'])) ? (int) $dateData['places']['places'] : null)
                    ;

                    if (isset($dateData['roomPrices']) && is_array($dateData['roomPrices'])) {
                        $doc->setPrices($dateData['roomPrices']);
                    }

                    if (!count($this->container->get('validator')->validate($doc))) {
                        $dm->persist($doc);
                    }
                }
            }
        }
        $dm->flush();

        $now = new \DateTime();
        $now->modify('+ 1 minute');
        $request->getSession()->getFlashBag()
            ->set('success', 'Изменения успешно сохранены. Автоматический пересчет цен начнется в ' . $now->format('H:i'));
        
        $this->get('mbh.room.cache.generator')->generateInBackground();
        return $response;
    }

    /**
     * @param Request $request
     * @return Response
     * @Route("/table", name="prices_overview_table", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template()
     */
    public function tableAction(Request $request)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $helper = $this->container->get('mbh.helper');
        $hotel = $this->get('mbh.hotel.selector')->getSelected();


        //dates
        $begin = $helper->getDateFromString($request->get('begin'));
        if(!$begin) {
            $begin = new \DateTime('00:00');
        }
        $end = $helper->getDateFromString($request->get('end'));
        if(!$end || $end->diff($begin)->format("%a") > 95 || $end <= $begin) {
            $end = clone $begin;
            $end->modify('+2 months');
        }

        $to = clone $end;
        $to->modify('+1 day');

        $period = new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $to);

        //get tariffs
        $qb = $dm->getRepository('MBHPriceBundle:Tariff')->createQueryBuilder('q');

        $response = [
            'period' => iterator_to_array($period),
            'begin' => $begin,
            'end' => $end,
            'hotel' => $hotel
        ];

        $qb->field('hotel.id')->equals($hotel->getId())
            ->addOr($qb->expr()->field('begin')->range($begin, $end))
            ->addOr($qb->expr()->field('end')->range($begin, $end))
            ->addOr(
                $qb->expr()
                    ->field('end')->gte($end)
                    ->field('begin')->lte($begin)
            )
            ->sort(['isDefault' => 'desc', 'fullTitle' => 'asc', 'title' => 'asc'])

        ;
        if(is_array($request->get('tariffs'))) {
            $qb->field('id')->in($request->get('tariffs'));
        }
        $tariffs = $qb->getQuery()->execute();

        if (!count($tariffs)) {
            return array_merge($response, ['error' => 'Тарифы не найдены']);
        }

        //get roomTypes
        $qb = $dm->getRepository('MBHHotelBundle:RoomType')->createQueryBuilder('q');
        $qb->field('hotel.id')->equals($hotel->getId())
            ->sort(['fullTitle' => 'asc', 'title' => 'asc'])
        ;
        if(is_array($request->get('roomTypes'))) {
            $qb->field('id')->in($request->get('roomTypes'));
        }
        $roomTypes = $qb->getQuery()->execute();

        if (!count($roomTypes)) {
            return array_merge($response, ['error' => 'Типы номеров не найдены']);
        }

        //get roomCacheOverwrite
        $roomCacheOverwrite = $dm->getRepository('MBHPackageBundle:RoomCacheOverwrite')
                                 ->findStructured($begin, $end, $tariffs, $roomTypes)
        ;

        return array_merge($response, [
            'tariffs' => $tariffs,
            'roomTypes' => $roomTypes,
            'roomCacheOverwrite' => $roomCacheOverwrite
        ]);
    }
}
