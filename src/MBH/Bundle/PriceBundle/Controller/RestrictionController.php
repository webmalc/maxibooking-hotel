<?php

namespace MBH\Bundle\PriceBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\PriceBundle\Form\RestrictionGeneratorType;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateQuery;
use MBH\Bundle\SearchBundle\Lib\Exceptions\InvalidateException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @Route("restriction")
 */
class RestrictionController extends Controller implements CheckHotelControllerInterface
{

    /**
     * @return JsonResponse
     * @Route("/in/out/json", name="restriction_in_out_json", options={"expose"=true}, defaults={"_format": "json"})
     * @Method("GET")
     * @Security("is_granted('ROLE_RESTRICTION_VIEW') or is_granted('ROLE_SEARCH')")
     * @Cache(expires="tomorrow", public=true)
     */
    public function inOutJsonAction()
    {
        return new JsonResponse($this->dm->getRepository('MBHPriceBundle:Restriction')->fetchInOut());
    }

    /**
     * @Route("/", name="restriction_overview")
     * @Method("GET")
     * @Security("is_granted('ROLE_RESTRICTION_VIEW')")
     * @Template()
     */
    public function indexAction()
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();

        return [
            'roomTypes' => $hotel->getRoomTypes(),
            'tariffs' => $this->dm->getRepository('MBHPriceBundle:Tariff')
                ->fetchChildTariffs($this->hotel, 'restrictions'),
        ];
    }

    /**
     * @param Request $request
     * @return Response
     * @Route("/table", name="restriction_overview_table", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_RESTRICTION_VIEW')")
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
        if (!$begin) {
            $begin = new \DateTime('00:00');
        }
        $end = $helper->getDateFromString($request->get('end'));
        if (!$end || $end->diff($begin)->format("%a") > 366 || $end <= $begin) {
            $end = clone $begin;
            $end->modify('+45 days');
        }

        $to = clone $end;
        $to->modify('+1 day');

        $period = new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $to);

        $response = [
            'period' => iterator_to_array($period),
            'begin' => $begin,
            'end' => $end,
            'hotel' => $hotel
        ];

        //get roomTypes
        $roomTypes = $dm->getRepository('MBHHotelBundle:RoomType')
            ->fetch($hotel, $request->get('roomTypes'))
        ;
        if (!count($roomTypes)) {
            return array_merge($response, ['error' => 'Типы номеров не найдены']);
        }
        //get tariffs
        $tariffs = $dm->getRepository('MBHPriceBundle:Tariff')
            ->fetchChildTariffs($hotel, 'restrictions', $request->get('tariffs'))
        ;
        if (!count($tariffs)) {
            return array_merge($response, ['error' => 'Тарифы не найдены']);
        }

        //get restrictions
        $restrictions = $dm->getRepository('MBHPriceBundle:Restriction')
            ->fetch(
                $begin,
                $end,
                $hotel,
                $request->get('roomTypes') ? $request->get('roomTypes') : [],
                $request->get('tariffs') ? $request->get('tariffs') : [],
                true
            )
        ;

        return array_merge($response, [
            'roomTypes' => $roomTypes,
            'tariffs' => $tariffs,
            'restrictions' => $restrictions
        ]);
    }

    /**
     * @Route("/save", name="restriction_overview_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_RESTRICTION_EDIT')")
     * @Template("MBHPriceBundle:Restriction:index.html.twig")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ODM\MongoDB\LockException
     * @throws \Doctrine\ODM\MongoDB\Mapping\MappingException
     */
    public function saveAction(Request $request)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $helper = $this->get('mbh.helper');
        $validator = $this->get('validator');
        (empty($request->get('updateRestrictions'))) ? $updateData = [] : $updateData = $request->get('updateRestrictions');
        (empty($request->get('newRestrictions'))) ? $newData = [] : $newData = $request->get('newRestrictions');
        $availableTariffs = $this->helper->toIds(
            $this->dm->getRepository('MBHPriceBundle:Tariff')->fetchChildTariffs($this->hotel, 'restrictions')
        );


        $invalidateRestrictions = [];
        //new
        foreach ($newData as $roomTypeId => $roomTypeArray) {
            $roomType = $dm->getRepository('MBHHotelBundle:RoomType')->find($roomTypeId);
            if (!$roomType || $roomType->getHotel() != $hotel) {
                continue;
            }
            foreach ($roomTypeArray as $tariffId => $tariffArray) {
                $tariff = $dm->getRepository('MBHPriceBundle:Tariff')->find($tariffId);
                if (!$tariff || $tariff->getHotel() != $hotel || !in_array($tariffId, $availableTariffs)) {
                    continue;
                }
                foreach ($tariffArray as $date => $values) {
                    if (empty(array_filter($values))) {
                        continue;
                    }
                    $newRestriction = new Restriction();
                    $newRestriction->setHotel($hotel)
                        ->setRoomType($roomType)
                        ->setTariff($tariff)
                        ->setDate($helper->getDateFromString($date))
                        ->setMinStay($values['minStay'] ? (int) $values['minStay'] : null)
                        ->setMaxStay($values['maxStay'] ? (int) $values['maxStay'] : null)
                        ->setMinStayArrival($values['minStayArrival'] ? (int) $values['minStayArrival'] : null)
                        ->setMaxStayArrival($values['maxStayArrival'] ? (int) $values['maxStayArrival'] : null)
                        ->setMinBeforeArrival($values['minBeforeArrival'] ? (int) $values['minBeforeArrival'] : null)
                        ->setMaxBeforeArrival($values['maxBeforeArrival'] ? (int) $values['maxBeforeArrival'] : null)
                        ->setMaxGuest($values['maxGuest'] ? (int) $values['maxGuest'] : null)
                        ->setMinGuest($values['minGuest'] ? (int) $values['minGuest'] : null)
                        ->setClosedOnArrival(isset($values['closedOnArrival']) ? true : false)
                        ->setClosedOnDeparture(isset($values['closedOnDeparture']) ? true : false)
                        ->setClosed(isset($values['closed']) ? true : false)
                    ;

                    if ($validator->validate($newRestriction)) {
                        $dm->persist($newRestriction);
                        $invalidateRestrictions[] = $newRestriction;
                    }
                }
            }
        }
        $dm->flush();

        //update
        foreach ($updateData as $restrictionId => $values) {
            $restriction = $dm->getRepository('MBHPriceBundle:Restriction')->find($restrictionId);
            if (!$restriction || $restriction->getHotel() != $hotel) {
                continue;
            }
            if (empty(array_filter($values))) {
                $dm->remove($restriction);
                continue;
            }

            $restriction->setMinStay($values['minStay'] ? (int) $values['minStay'] : null)
                ->setMaxStay($values['maxStay'] ? (int) $values['maxStay'] : null)
                ->setMinStayArrival($values['minStayArrival'] ? (int) $values['minStayArrival'] : null)
                ->setMaxStayArrival($values['maxStayArrival'] ? (int) $values['maxStayArrival'] : null)
                ->setMinBeforeArrival($values['minBeforeArrival'] ? (int) $values['minBeforeArrival'] : null)
                ->setMaxBeforeArrival($values['maxBeforeArrival'] ? (int) $values['maxBeforeArrival'] : null)
                ->setMaxGuest($values['maxGuest'] ? (int) $values['maxGuest'] : null)
                ->setMinGuest($values['minGuest'] ? (int) $values['minGuest'] : null)
                ->setClosedOnArrival(isset($values['closedOnArrival']) ? true : false)
                ->setClosedOnDeparture(isset($values['closedOnDeparture']) ? true : false)
                ->setClosed(isset($values['closed']) ? true : false)
            ;

            if ($validator->validate($restriction)) {
                $dm->persist($restriction);
                $invalidateRestrictions[] = $restriction;
            }
        }
        $dm->flush();

        $this->get('mbh.channelmanager')->updateRestrictionsInBackground();

        /** @var Session $session */
        $session = $request->getSession();
        $session->getFlashBag()
            ->set('success', 'Изменения успешно сохранены.')
        ;

        $invalidateQueue = $this->get('mbh_search.invalidate_queue_creator');
        try {
            $invalidateQueue->addBatchToQueue($invalidateRestrictions);
        } catch (InvalidateException $e) {
            $session->getFlashBag()->set('error', 'Проблемы с инвалидацией кэша.');
        }

        return $this->redirect($this->generateUrl('restriction_overview', [
            'begin' => $request->get('begin'),
            'end' => $request->get('end'),
            'roomTypes' => $request->get('roomTypes'),
            'tariffs' => $request->get('tariffs'),
        ]));
    }

    /**
     * @Route("/generator", name="restriction_generator")
     * @Method("GET")
     * @Security("is_granted('ROLE_RESTRICTION_EDIT')")
     * @Template()
     */
    public function generatorAction()
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();

        $form = $this->createForm(
            RestrictionGeneratorType::class,
            [],
            [
            'weekdays' => $this->container->getParameter('mbh.weekdays'),
            'hotel' => $hotel,
            ]
        );

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/generator/save", name="restriction_generator_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_RESTRICTION_EDIT')")
     * @Template("MBHPriceBundle:Restriction:generator.html.twig")
     * @param Request $request
     * @return array
     */
    public function generatorSaveAction(Request $request)
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();

        $form = $this->createForm(
            RestrictionGeneratorType::class,
            [],
            [
            'weekdays' => $this->container->getParameter('mbh.weekdays'),
            'hotel' => $hotel,
            ]
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            $request->getSession()->getFlashBag()
                ->set('success', 'Данные успешно сгенерированы.')
            ;

            $data = $form->getData();

            $this->get('mbh.restriction')->update(
                $data['begin'],
                $data['end'],
                $hotel,
                $data['minStay'],
                $data['maxStay'],
                $data['minStayArrival'],
                $data['maxStayArrival'],
                $data['minBeforeArrival'],
                $data['maxBeforeArrival'],
                $data['maxGuest'],
                $data['minGuest'],
                $data['closedOnArrival'],
                $data['closedOnDeparture'],
                $data['closed'],
                $data['roomTypes']->toArray(),
                $data['tariffs']->toArray(),
                $data['weekdays']
            );

            $this->get('mbh.channelmanager')->updateRestrictionsInBackground();

            $invalidateData = [
                'begin' => $data['begin'],
                'end' => $data['end'],
                'roomTypeIds' => Helper::toIds($data['roomTypes']->toArray()),
                'tariffIds' => Helper::toIds($data['tariffs']->toArray()),
                'type' => InvalidateQuery::RESTRICTION_GENERATOR
            ];
            $cacheInvalidate = $this->get('mbh_search.invalidate_queue_creator');
            try {
                $cacheInvalidate->addToQueue($invalidateData);
            } catch (InvalidateException $e) {
                $request->getSession()->getFlashBag()->set('error', 'Cache invalidate Error!');
            }

            if ($request->get('save') !== null) {
                return $this->redirect($this->generateUrl('restriction_generator'));
            }
            return $this->redirect($this->generateUrl('restriction_overview'));
        }
        return [
            'form' => $form->createView()
        ];
    }
}
