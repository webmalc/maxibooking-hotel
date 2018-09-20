<?php

namespace MBH\Bundle\PriceBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\PriceBundle\Document\RoomCacheGenerator;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Form\RoomCacheGeneratorType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("room_cache")
 */
class RoomCacheController extends Controller implements CheckHotelControllerInterface
{
    /**
     * @Route("/", name="room_cache_overview")
     * @Method("GET")
     * @Security("is_granted('ROLE_ROOM_CACHE_VIEW')")
     * @Template()
     * @throws \Exception
     */
    public function indexAction()
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $isDisableableOn = $this->clientConfig->isDisableableOn();
        //get roomTypes
        $roomTypesCallback = function () use ($hotel) {
            return $this->dm->getRepository('MBHHotelBundle:RoomType')->findBy(['hotel.id' => $hotel->getId()]);
        };
        $roomTypes = $this->helper->getFilteredResult($this->dm, $roomTypesCallback, $isDisableableOn);
        if ($this->clientConfig->isMBSiteEnabled()) {
            $emptyPeriodWarnings = $this->get('mbh.warnings_compiler')->getEmptyCacheWarningsAsStrings($this->hotel, 'room');
        }
        if (!empty($emptyPeriodWarnings)) {
            $this->addFlash('warning', join('<br>', $emptyPeriodWarnings));
        }

        return [
            'roomTypes' => $roomTypes,
            'tariffs' => $this->dm->getRepository('MBHPriceBundle:Tariff')->fetchChildTariffs($this->hotel, 'rooms'),
            'displayDisabledRoomType' => !$isDisableableOn,
        ];
    }

    /**
     * @param Request $request
     * @return array
     * @Route("/graph", name="room_cache_overview_graph", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_ROOM_CACHE_VIEW')")
     * @Template()
     */
    public function graphAction(Request $request)
    {
        $generator = $this->get('mbh.room.cache.graph.generator');

        $extraData = $this->get('mbh.room.cache.graph.extra_data');

        return [
            'data'      => $generator->generate($request, $this->hotel),
            'begin'     => $generator->getBegin(),
            'end'       => $generator->getEnd(),
            'error'     => $generator->getError(),
            'extraData' => $extraData->get($request, $generator, $this->hotel),
        ];
    }

    /**
     * @param Request $request
     * @return array
     * @Route("/table", name="room_cache_overview_table", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_ROOM_CACHE_VIEW')")
     * @Template()
     */
    public function tableAction(Request $request)
    {
        $helper = $this->container->get('mbh.helper');
        $hotel = $this->get('mbh.hotel.selector')->getSelected();

        list($begin, $end) = $helper->getReportDates($request);
        $to = (clone $end)->modify('+1 day');

        $period = [];

        /** @var \DateTime $date */
        foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $to) as $date) {
            $period[] = [
                'date'      => $date,
                'isWeekend' => (integer)$date->format('N') > 5,
            ];
        }

        $response = [
            'period'     => $period,
            'begin'      => $begin,
            'end'        => $end,
            'hotel'      => $hotel,
            'categories' => $this->getCategories(),
        ];

        //get roomTypes
        $requestedRoomTypes = $this->helper->getDataFromMultipleSelectField($request->get('roomTypes'));
        $roomTypesCallback = function () use ($hotel, $requestedRoomTypes) {
            return $this->dm->getRepository('MBHHotelBundle:RoomType')->fetch($hotel, $requestedRoomTypes);
        };
        $isDisableableOn = $this->clientConfig->isDisableableOn();
        $roomTypes = $helper->getFilteredResult($this->dm, $roomTypesCallback, $isDisableableOn);

        if (!count($roomTypes)) {
            return array_merge($response, ['error' => $this->container->get('translator')->trans('price.tariffcontroller.room_type_is_not_found')]);
        }

        //get tariffs
        if (!empty($request->get('tariffs'))) {
            $tariffs = $this->dm->getRepository('MBHPriceBundle:Tariff')
                ->fetchChildTariffs($hotel, 'rooms', $request->get('tariffs'))
            ;
        } else {
            $tariffs = [null];
        }

        //get roomCaches
        $roomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')
            ->fetch(
                $begin, $end, $hotel,
                $requestedRoomTypes,
                false, true)
        ;

        return array_merge($response, [
            'roomTypes'  => $roomTypes,
            'tariffs'    => $tariffs,
            'roomCaches' => $roomCaches,
        ]);
    }

    /**
     * @Route("/save", name="room_cache_overview_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_ROOM_CACHE_EDIT')")
     * @Template("MBHPriceBundle:RoomCache:index.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function saveAction(Request $request)
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $helper = $this->get('mbh.helper');
        $validator = $this->get('validator');
        (empty($request->get('updateRoomCaches'))) ? $updateData = [] : $updateData = $request->get('updateRoomCaches');
        (empty($request->get('newRoomCaches'))) ? $newData = [] : $newData = $request->get('newRoomCaches');
        $availableTariffs = $this->helper->toIds(
            $this->dm->getRepository('MBHPriceBundle:Tariff')->fetchChildTariffs($this->hotel, 'rooms')
        );

        $dates = [];

        $roomCachesByDates = [];
        //new
        foreach ($newData as $roomTypeId => $roomTypeArray) {

            $roomType = $this->dm->getRepository('MBHHotelBundle:RoomType')->find($roomTypeId);
            if (!$roomType || $roomType->getHotel() != $hotel) {
                continue;
            }

            foreach ($roomTypeArray as $tariffId => $tariffArray) {

                if ($tariffId) {
                    if (!in_array($tariffId, $availableTariffs)) {
                        continue;
                    }

                    $tariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->find($tariffId);
                    if (!$tariff || $tariff->getHotel() != $hotel) {
                        continue;
                    }
                }

                foreach ($tariffArray as $date => $totalRooms) {
                    if (trim($totalRooms['rooms']) === '' || $totalRooms['rooms'] === null) {
                        continue;
                    }
                    $newRoomCache = new RoomCache();
                    $newRoomCache->setHotel($hotel)
                        ->setRoomType($roomType)
                        ->setDate($helper->getDateFromString($date))
                        ->setTotalRooms((int) $totalRooms['rooms'])
                        ->setIsOpen(!empty($totalRooms['isOpen']))
                        ->setPackagesCount(0);

                    $dates[] = $newRoomCache->getDate();
                    
                    $roomCachesByDates[$newRoomCache->getDate()->format('d.m.Y')][] = $newRoomCache;
                    if ($tariffId && isset($tariff) && $tariff !== null) {
                        $newRoomCache->setTariff($tariff);
                    }

                    if ($validator->validate($newRoomCache)) {
                        $this->dm->persist($newRoomCache);
                    }
                }
            }
        }

        //update
        foreach ($updateData as $roomCacheId => $val) {
            $roomCache = $this->dm->getRepository('MBHPriceBundle:RoomCache')->find($roomCacheId);
            if (!$roomCache || $roomCache->getHotel() != $hotel) {
                continue;
            }

            //delete
            if (isset($val['rooms']) && (trim($val['rooms']) === '' || $val['rooms'] === null)) {
                if ($roomCache->getPackagesCount() <= 0) {
                    $this->dm->remove($roomCache);
                }
                continue;
            }

            if (isset($val['rooms'])) {
                $roomCache->setTotalRooms((int) $val['rooms']);
            }
            $roomCache->setIsClosed(isset($val['closed']) && !empty($val['closed']) ? true : false);
            $roomCache->setIsOpen(!empty($val['isOpen']));
            if ($validator->validate($roomCache)) {
                $this->dm->persist($roomCache);
            }
            $roomCachesByDates[$roomCache->getDate()->format('d.m.Y')][] = $roomCache;

            $dates[] = $roomCache->getDate();
        }

        $busyDays = [];
        $limitManager = $this->get('mbh.client_manager');
        foreach ($roomCachesByDates as $dateString => $roomCachesByDate) {
            $isExceedLimit = $limitManager->isLimitOfRoomCachesExceeded($roomCachesByDate);
            if ($isExceedLimit) {
                $busyDays[] = $dateString;
            }
        }

        if (count($busyDays) > 0) {
            $limitErrorMessage = $this->get('translator')
                ->trans('room_cache_controller.limit_of_rooms_exceeded', [
                    '%busyDays%' => join(', ', $busyDays),
                    '%availableNumberOfRooms%' => $limitManager->getAvailableNumberOfRooms(),
                    '%overviewUrl%' => $this->generateUrl('total_rooms_overview'),
                ]);
            $this->addFlash('error', $limitErrorMessage);
        } else {
            $this->dm->flush();
            $this->addFlash('success', 'price.tariffcontroller.update_successfully_saved');

            if (!empty($dates)) {
                list($minDate, $maxDate) = $this->helper->getMinAndMaxDates($dates);
                $this->get('mbh.channelmanager')->updateRoomsInBackground($minDate, $maxDate);
            }

            $this->get('mbh.cache')->clear('room_cache');
        }

        return $this->redirectToRoute('room_cache_overview', [
            'begin' => $request->get('begin'),
            'end' => $request->get('end'),
            'roomTypes' => $request->get('roomTypes'),
        ]);
    }

    /**
     * @Route("/generator", name="room_cache_generator")
     * @Method("GET")
     * @Security("is_granted('ROLE_ROOM_CACHE_EDIT')")
     * @Template()
     */
    public function generatorAction()
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();

        $generator = new RoomCacheGenerator();
        $generator->setHotel($hotel);
        $generator->setWeekdays($this->container->getParameter('mbh.weekdays'));

        $form = $this->createForm(RoomCacheGeneratorType::class, $generator);

        return [
            'form'            => $form->createView(),
            'tariffNotOpened' => json_encode($this->getTariffNotOpened($hotel), JSON_FORCE_OBJECT),
        ];
    }

    /**
     * @Route("/generator/save", name="room_cache_generator_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_ROOM_CACHE_EDIT')")
     * @Template("MBHPriceBundle:RoomCache:generator.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function generatorSaveAction(Request $request)
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();

        $generator = new RoomCacheGenerator();
        $generator->setHotel($hotel);
        $generator->setWeekdays($this->container->getParameter('mbh.weekdays'));

        $form = $this->createForm(RoomCacheGeneratorType::class, $generator);

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var RoomCacheGenerator $data */
            $data = $form->getData();

            $error = $this->get('mbh.room.cache')->update($data);
            if (empty($error)) {
                $this->addFlash('success', 'price.tariffcontroller.data_successfully_generated');
                $this->get('mbh.channelmanager')->updateRoomsInBackground($data->getBegin(), $data->getEnd());
                $this->get('mbh.cache')->clear('room_cache');

                return $this->isSavedRequest() ?
                    $this->redirectToRoute('room_cache_generator') :
                    $this->redirectToRoute('room_cache_overview');
            } else {
                $this->addFlash('error', $error);
            }
        }

        return [
            'form'            => $form->createView(),
            'tariffNotOpened' => json_encode($this->getTariffNotOpened($hotel), JSON_FORCE_OBJECT),
        ];
    }

    /**
     * @return array
     */
    private function getCategories(): array
    {
        $trans = $this->container->get('translator');

        return [
            'totalRooms'           => $trans->trans('price.resources.views.roomcache.in_sales'),
            'packagesCount'        => $trans->trans('price.resources.views.booking'),
            'packagesCountPercent' => $trans->trans('price.resources.views.booking_in_percents'),
            'leftRooms'            => $trans->trans('price.resources.views.left'),
        ];
    }

    /**
     * @param Hotel $hotel
     * @return array
     */
    private function getTariffNotOpened(Hotel $hotel): array
    {
        $tariffNotOpened = [];
        /** @var Tariff $tariff */
        foreach ($hotel->getTariffs() as $tariff) {
            if (!$tariff->isOpen()) {
                $tariffNotOpened[$tariff->getId()] = true;
            }
        }

        return $tariffNotOpened;
    }
}
