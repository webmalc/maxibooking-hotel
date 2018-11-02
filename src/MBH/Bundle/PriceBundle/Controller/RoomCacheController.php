<?php

namespace MBH\Bundle\PriceBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\PriceBundle\Form\RoomCacheGeneratorType;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateQuery;
use MBH\Bundle\SearchBundle\Lib\Exceptions\InvalidateException;
use MBH\Bundle\PriceBundle\Services\GraphExtraData;
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
            'displayDisabledRoomType' => !$isDisableableOn
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
            'extraData' => $extraData->get($request, $generator, $this->hotel)
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

        $period = new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $to);

        $response = [
            'period' => iterator_to_array($period),
            'begin'  => $begin,
            'end'    => $end,
            'hotel'  => $hotel,
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
            'roomTypes' => $roomTypes,
            'tariffs' => $tariffs,
            'roomCaches' => $roomCaches
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

        $roomCachesToInvalidate = [];
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
                        ->setPackagesCount(0);

                    $dates[] = $newRoomCache->getDate();

                    $roomCachesByDates[$newRoomCache->getDate()->format('d.m.Y')][] = $newRoomCache;
                    if ($tariffId && isset($tariff) && !is_null($tariff)) {
                        $newRoomCache->setTariff($tariff);
                    }

                    if ($validator->validate($newRoomCache)) {
                        $this->dm->persist($newRoomCache);
                        $roomCachesToInvalidate[] = $newRoomCache;
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
            if ($validator->validate($roomCache)) {
                $this->dm->persist($roomCache);
                $roomCachesToInvalidate[] = $roomCache;
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
                    '%overviewUrl%' => $this->generateUrl('total_rooms_overview')
                ]);
            $this->addFlash('error', $limitErrorMessage);
        } else {
            $this->dm->flush();
            $this->addFlash('success', 'price.tariffcontroller.update_successfully_saved');

            if (!empty($dates)) {
                list($minDate, $maxDate) = $this->helper->getMinAndMaxDates($dates);
                $this->get('mbh.channelmanager')->updateRoomsInBackground($minDate, $maxDate);
                $invalidateQueue = $this->get('mbh_search.invalidate_queue_creator');
                try {
                    $invalidateQueue->addBatchToQueue($roomCachesToInvalidate);
                } catch (InvalidateException $e) {
                    $this->addFlash('error', 'Проблемы с инвалидацией кэша.');
                }
            }

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

        $form = $this->createForm(RoomCacheGeneratorType::class, [], [
            'weekdays' => $this->container->getParameter('mbh.weekdays'),
            'hotel' => $hotel,
        ]);

        return [
            'form' => $form->createView()
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

        $form = $this->createForm(RoomCacheGeneratorType::class, [], [
            'weekdays' => $this->container->getParameter('mbh.weekdays'),
            'hotel' => $hotel,
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            $error = $this->get('mbh.room.cache')->update(
                $data['begin'], $data['end'], $hotel, $data['rooms'], false,  $data['roomTypes']->toArray(), $data['tariffs']->toArray(), $data['weekdays']
            );
            if (empty($error)) {
                $this->addFlash('success', 'price.tariffcontroller.data_successfully_generated');
                $this->get('mbh.channelmanager')->updateRoomsInBackground($data['begin'], $data['end']);
                $invalidateData = [
                    'begin' => $data['begin'],
                    'end' => $data['end'],
                    'roomTypeIds' => Helper::toIds($data['roomTypes']->toAarray()),
                    'type' => InvalidateQuery::ROOM_CACHE_GENERATOR
                ];
                $cacheInvalidate = $this->get('mbh_search.invalidate_queue_creator');
                try {
                    $cacheInvalidate->addToQueue($invalidateData);
                } catch (InvalidateException $e) {
                    $this->addFlash('error', 'Cache invalidate Error!');
                }

                return $this->isSavedRequest() ?
                    $this->redirectToRoute('room_cache_generator') :
                    $this->redirectToRoute('room_cache_overview');
            } else {
                $this->addFlash('error', $error);
            }
        }

        return [
            'form' => $form->createView()
        ];
    }
}
