<?php

namespace MBH\Bundle\PackageBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Query\FilterCollection;
use function GuzzleHttp\Promise\queue;
use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeRepository;
use MBH\Bundle\OnlineBundle\Document\Invite;
use MBH\Bundle\PackageBundle\Component\RoomTypeReport;
use MBH\Bundle\PackageBundle\Component\RoomTypeReportCriteria;
use MBH\Bundle\PackageBundle\Document\Criteria\PackageQueryCriteria;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Form\PackageVirtualRoomType;
use MBH\Bundle\PackageBundle\Services\SalesChannelsReportCompiler;
use MBH\Bundle\UserBundle\Document\WorkShift;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/report")
 */
class ReportController extends Controller implements CheckHotelControllerInterface
{
    /**
     * Porter report.
     *
     * @Route("/windows", name="report_windows")
     * @Method("GET")
     * @Security("is_granted('ROLE_WINDOWS_REPORT')")
     * @Template()
     */
    public function windowsAction()
    {
        return [
            'roomTypes' => $this->hotel->getRoomTypes(),
        ];
    }

    /**
     * Windows report table.
     *
     * @Route("/windows/table", name="report_windows_table", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_WINDOWS_REPORT')")
     * @Template()
     * @param $request Request
     * @return array
     */
    public function windowsTableAction(Request $request)
    {
        $generator = $this->get('mbh.package.windows.report.generator');
        $notVirtualRooms = $this->dm->getRepository('MBHPackageBundle:Package')->getNotVirtualRoom(
            new \DateTime($request->get('begin')),
            new \DateTime($request->get('end'))
        );

        return [
            'data' => $generator->generate($request, $this->hotel),
            'error' => $generator->getError(),
            'notVirtualRooms' => $notVirtualRooms
        ];
    }

    /**
     * Windows package info.
     *
     * @Route("/windows/package/{id}", name="report_windows_package", options={"expose"=true})
     * @Method({"GET", "POST"})
     * @ParamConverter("package", class="MBHPackageBundle:Package")
     * @Security("is_granted('ROLE_PACKAGE_EDIT') and (is_granted('EDIT', package) or is_granted('ROLE_PACKAGE_EDIT_ALL'))")
     * @Template()
     * @param $request Request
     * @param $package Package
     * @return array
     */
    public function windowPackageAction(Request $request, Package $package)
    {
        if (!$this->container->get('mbh.package.permissions')->checkHotel($package)) {
            throw $this->createNotFoundException();
        }

        $response = ['package' => $package];

        if ($this->clientConfig->getSearchWindows()) {
            $form = $this->createForm(PackageVirtualRoomType::class, $package, [
                'package' => $package
            ]);

            if ($request->isMethod('POST')) {
                $form->submit($request->request->get($form->getName()));

                if ($form->isValid()) {
                    $this->dm->persist($package);
                    $this->dm->flush();
                    $this->addFlash('success', 'controller.packageController.record_edited_success');

                    return $this->redirectToRoute('report_windows');
                }
            }

            $response['form'] = $form->createView();
        }

        return $response;
    }

    /**
     * Porter report.
     *
     * @Route("/porter/{type}", name="report_porter", defaults={"type"="lives"}, requirements={
     *      "type" : "lives|arrivals|out"
     * })
     * @Method("GET")
     * @Security("is_granted('ROLE_PORTER_REPORT')")
     * @Template()
     */
    public function porterAction($type = 'lives')
    {
        $menuItem = $this->get('knp_menu.factory')->createItem('types');
        $menuItem->setChildrenAttribute('id', 'porter-report-tabs');
        $menuItem->setChildrenAttribute('class', 'nav nav-tabs');

        $menuItem
            ->addChild('arrivals', [
                'route' => 'report_porter',
                'routeParameters' => ['type' => 'arrivals'],
                'label' => $this->get('translator')->trans('report.porter.menu.arrival')
            ]);
        $menuItem
            ->addChild('lives', ['route' => 'report_porter', 'routeParameters' => ['type' => 'lives']])
            ->setLabel($this->get('translator')->trans('report.porter.menu.accommodation'));
        $menuItem
            ->addChild('out', ['route' => 'report_porter', 'routeParameters' => ['type' => 'out']])
            ->setLabel($this->get('translator')->trans('report.porter.menu.departure'));

        $packageRepository = $this->dm->getRepository('MBHPackageBundle:Package');

        foreach ($menuItem->getChildren() as $child) {
            $count = $packageRepository->countByType($child->getName(), true, $this->hotel);
            if ($count > 0) {
                $class = 'default';
                $class = $child->getName() == 'arrivals' ? 'danger' : $class;
                $class = $child->getName() == 'out' ? 'success' : $class;
                $child->setLabel($child->getLabel() . ' <small class="label label-' . $class . ' label-as-badge">' . $count . '</small>');
                $child->setExtras(['safe_label' => true]);
            }
            if ($child->getName() == $type) {
                $child->setCurrent(true);
            }
        }

        return [
            'type' => $type,
            'menuItem' => $menuItem
        ];
    }

    /**
     * Porter report table.
     *
     * @Route("/porter/table", name="report_porter_table", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_PORTER_REPORT')")
     * @Template()
     */
    public function porterTableAction(Request $request)
    {
        $type = $request->get('type');


        $begin = new \DateTime('midnight');
        $end = new \DateTime('midnight');
        $end->modify('+ 1 day');

        $packageRepository = $this->dm->getRepository('MBHPackageBundle:Package');

        $packages = $packageRepository->findByType($type, $this->hotel);

        return [
            'begin' => $begin,
            'end' => $end,
            'packages' => $packages,
            'type' => $type,
            'statuses' => $this->container->getParameter('mbh.package.statuses'),
        ];
    }

    /**
     * Packages by users report.
     *
     * @Route("/users/index", name="report_users")
     * @Method("GET")
     * @Security("is_granted('ROLE_MANAGERS_REPORT')")
     * @Template()
     */
    public function userAction()
    {
        $roomTypes = $roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')
            ->getWithPackages();
        $tariffs = $this->dm->getRepository('MBHPriceBundle:Tariff')
            ->getWithPackages();
        $users = $this->dm->getRepository('MBHPackageBundle:Package')
            ->createQueryBuilder()
            ->distinct('createdBy')
            ->getQuery()
            ->execute();
        return [
            'users' => $users,
            'tariffs' => $tariffs,
            'roomTypes' => $roomTypes
        ];
    }

    /**
     * Packages by users report.
     *
     * @Route("/users/table", name="report_users_table", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_MANAGERS_REPORT')")
     * @Template()
     * @param Request $request
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function userTableAction(Request $request)
    {
        $helper = $this->container->get('mbh.helper');

        //dates
        $begin = $helper->getDateFromString($request->get('begin'));
        $begin ?: $begin = new \DateTime('midnight - 14 days');

        $end = $helper->getDateFromString($request->get('end'));
        if (!$end || $end->diff($begin)->format("%a") > 750 || $end <= $begin) {
            $end = new \DateTime('midnight + 1 day');
        }

        $qb = $this->dm->getRepository('MBHPackageBundle:Package')
            ->createQueryBuilder()
            ->field('createdAt')->gte($begin)
            ->field('createdAt')->lte($end)
            ->field('createdBy')->notEqual(null)
            ->sort('createdAt');

        if ($request->get('users') && is_array($request->get('users'))) {
            $qb->field('createdBy')->in($request->get('users'));
        }
        if ($request->get('roomTypes') && is_array($request->get('roomTypes'))) {
            $qb->field('roomType.id')->in($request->get('roomTypes'));
        }
        if ($request->get('tariffs') && is_array($request->get('tariffs'))) {
            $qb->field('tariff.id')->in($request->get('tariffs'));
        }

        /** @var Package[] $packages */
        $packages = $qb->getQuery()->execute();

        $data = $dates = $total = $allTotal = $dayTotal = [];

        $default = [
            'sold' => 0,
            'price' => 0,
            'services' => 0,
            'packagePrice' => 0,
            'servicesPrice' => 0,
            'paid' => 0,
        ];

        $packageOrderIds = [];
        $packageIds = [];
        foreach ($packages as $package) {
            $packageOrderIds[] = $package->getOrder()->getId();
            $packageIds[] = $package->getId();
        }
        //preload orders and package services
        $this->dm->getRepository('MBHPackageBundle:Order')->getByOrdersIds($packageOrderIds)->toArray();
        /** @var PackageService[] $packageServices */
        $packageServices = $this->dm->getRepository('MBHPackageBundle:PackageService')->findBy(['package.id' => ['$in' => $packageIds]]);
        $packageServicesByPackageIds = [];

        foreach ($packageServices as $packageService) {
            isset($packageServicesByPackageIds[$packageService->getPackage()->getId()])
                ? $packageServicesByPackageIds[$packageService->getPackage()->getId()][] = $packageService
                : $packageServicesByPackageIds[$packageService->getPackage()->getId()] = [$packageService];
        }
        $usersByUsername = [];

        foreach ($packages as $package) {
            /** @var Package $package */
            if (empty($package->getOrder()->getChannelManagerType())
                && (is_null($package->getOrder()->getSource())
                    || $package->getOrder()->getSource()->getCode() != 'online')
            ) {
                $day = $package->getCreatedAt()->format('d.m.Y');
                $user = $package->getCreatedBy();
                $dates[$day] = $package->getCreatedAt();
                $default['date'] = $package->getCreatedAt();
                $default['user'] = $package->getCreatedBy();

                if (isset($usersByUsername[$package->getCreatedBy()])) {
                    $userDoc = $usersByUsername[$package->getCreatedBy()];
                } else {
                    $userDoc = $this->dm->getRepository('MBHUserBundle:User')->findOneBy(['username' => $default['user']]);
                    $usersByUsername[$package->getCreatedBy()] = $userDoc;
                }

                if ($userDoc) {
                    $default['user'] = $userDoc->getFullName(true);
                }

                if (empty($data[$user][$day])) {
                    $data[$user][$day] = $default;
                }
                if (empty($total[$user])) {
                    $total[$user] = $default;
                }
                if (empty($dayTotal[$day])) {
                    $dayTotal[$day] = $default;
                }
                $packageOrderId = $package->getOrder()->getId();
                $add = function ($entry, Package $package) use ($packageOrderId, $packageServices) {
                    $entry['sold']++;
                    $entry['packagePrice'] += $package->getPackagePrice();
                    $entry['price'] += $package->getPrice();
                    $entry['servicesPrice'] += $package->getServicesPrice();
                    $entry['paid'] += $package->getCalculatedPayment();
                    $packageServicesList = isset($packageServices[$package->getId()]) ? $packageServices[$package->getId()] : [];
                    foreach ($packageServicesList as $packageService) {
                        $entry['services'] += $packageService->getTotalAmount();
                    }

                    return $entry;
                };

                $data[$user][$day] = $add($data[$user][$day], $package);
                $total[$user] = $add($total[$user], $package);
                $dayTotal[$day] = $add($dayTotal[$day], $package);
            }
            $allTotal = $default;
            foreach ($total as $i => $tData) {
                foreach ($tData as $k => $val) {
                    if (!in_array($k, ['date', 'user'])) {
                        $allTotal[$k] += $val;
                    }
                }
            }
        }

        return [
            'allTotal' => $allTotal,
            'dayTotal' => $dayTotal,
            'begin' => $begin,
            'end' => $end,
            'data' => $data,
            'dates' => $dates,
            'total' => $total
        ];
    }

    /**
     * @Route("/fms", name="report_fms", options={"expose"=true})
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_BASE_USER')")
     * @Template()
     */
    public function fmsAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $helper = $this->get('mbh.helper');
            $begin = $helper->getDateFromString($request->get('begin'));
            $end = $helper->getDateFromString($request->get('end'));
            $hotel = $this->get('mbh.hotel.selector')->getSelected();

            if (!$hotel || !$begin || !$end || !$hotel->getRoomTypes()) {
                $this->createNotFoundException();
            }

            $end->modify('+ 23 hours  59 minutes');

            $packages = $this->dm->getRepository('MBHPackageBundle:Package')->findBy([
                'isCheckIn' => true,
                '$or' => [
                    ['arrivalTime' => ['$lt' => $end]],
                    ['arrivalTime' => ['$gt' => $begin]],
                ],
                'accommodation' => ['$exists' => 1],
                //'roomType' => ['$in' => $roomTypeIds]
            ]);

            $zipFile = $this->get('mbh.vega.vega_export')->exportToZip($packages, $hotel);

            if (!$zipFile) {
                return [
                    'message' => $this->get('translator')->trans('controller.report_controller.fms_report_error.no_data_for_upload')
                ];
            }

            $response = new BinaryFileResponse($zipFile);
            $response->setContentDisposition('attachment', $zipFile->getBasename());

            return $response;
        }

        return [
        ];
    }

    /**
     * @param Request $request
     * @return array
     * @Route("/polls", name="report_polls")
     * @Method({"GET"})
     * @Security("is_granted('ROLE_POLLS_REPORT')")
     * @Template()
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function pollsAction(Request $request)
    {
        $helper = $this->get('mbh.helper');
        $begin = $request->get('begin') ? $helper->getDateFromString($request->get('begin')) : new \DateTime('midnight - 45 days');
        $end = $request->get('end') ? $helper->getDateFromString($request->get('end')) : new \DateTime('midnight + 1 days');

        $packageCriteria = new PackageQueryCriteria();
        $packageCriteria->liveBegin = $begin;
        $packageCriteria->liveEnd = $end;
        $packageCriteria->filter = 'live_between';
        $orderIds = $this->dm
            ->getRepository('MBHPackageBundle:Package')
            ->queryCriteriaToBuilder($packageCriteria)
            ->distinct('order.id')
            ->getQuery()
            ->toArray();

        $orders = $this->dm
            ->getRepository('MBHPackageBundle:Order')
            ->fetchWithPolls($orderIds, true);

        return [
            'orders' => $orders,
            'begin' => $begin,
            'end' => $end
        ];
    }

    /**
     * @return array
     * @Route("/polls/{id}/view", name="report_polls_view")
     * @Method({"GET"})
     * @Security("is_granted('ROLE_POLLS_REPORT')")
     * @Template()
     * @ParamConverter("order", class="MBHPackageBundle:Order")
     */
    public function pollsViewAction(Order $order)
    {
        if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->disable('softdeleteable');
        }
        return [
            'order' => $order
        ];
    }

    /**
     * @return array
     * @Route("/roomtypes", name="report_room_types")
     * @Method({"GET"})
     * @Security("is_granted('ROLE_ROOMS_REPORT')")
     * @Template()
     */
    public function roomTypesAction()
    {
        /** @var RoomTypeRepository $roomTypeRepository */
        $roomTypeRepository = $this->dm->getRepository('MBHHotelBundle:RoomType');

        /** @var RoomType[] $roomTypes */
        $roomTypes = $roomTypeRepository->findBy(['hotel.id' => $this->hotel->getId()]);
        $housings = $this->dm->getRepository('MBHHotelBundle:Housing')->findAll();
        $floors = $this->dm->getRepository('MBHHotelBundle:Room')
            ->createQueryBuilder()
            ->select('floor')
            ->distinct('floor')
            ->getQuery()
            ->execute();

        $criteria = new RoomTypeReportCriteria($this->hotel);

        $roomTypeReport = new RoomTypeReport($this->container);
        $result = $roomTypeReport->findByCriteria($criteria);

        return [
            'roomTypes'       => $roomTypes,
            'housings'        => $housings,
            'floors'          => $floors,
            'result'          => $result,
            'facilities'      => $this->get('mbh.facility_repository')->getAll(),
            'statuses'        => Package::getRoomStatuses(),
            'roomStatuses'    => $this->dm->getRepository('MBHHotelBundle:RoomStatus')->findAll(),
            'roomStatusIcons' => $this->getParameter('mbh.room_status_icons'),
        ];
    }


    /**
     * @return array
     * @Route("/roomtypes_table", name="report_room_types_table", options={"expose"=true})
     * @Method({"GET"})
     * @Security("is_granted('ROLE_ROOMS_REPORT')")
     * @Template()
     */
    public function roomTypesTableAction(Request $request)
    {
        $criteria = new RoomTypeReportCriteria($this->hotel, $request);

        $roomTypeReport = new RoomTypeReport($this->container);
        $result = $roomTypeReport->findByCriteria($criteria);

        return [
            'result'          => $result,
            'facilities'      => $this->get('mbh.facility_repository')->getAll(),
            'roomStatuses'    => $this->dm->getRepository('MBHHotelBundle:RoomStatus')->findAll(),
            'roomStatusIcons' => $this->getParameter('mbh.room_status_icons'),
        ];
    }

    /**
     * @return array
     * @Route("/set_room_status", name="report_set_room_status", options={"expose"=true})
     * @Method({"GET"})
     * @Security("is_granted('ROLE_ROOM_STATUS_EDIT')")
     */
    public function setRoomStatusAction(Request $request)
    {
        $roomID = $request->get('roomID');
        $code = $request->get('value');
        $room = $this->dm->getRepository('MBHHotelBundle:Room')->find($roomID);
        $roomStatus = $this->dm->getRepository('MBHHotelBundle:RoomStatus')->findOneBy(['code' => $code]);
        if (!$room) {
            throw $this->createNotFoundException();
        }
        $room->setStatus($roomStatus);
        $this->dm->persist($room);
        $this->dm->flush();

        return new JsonResponse([
            'success' => true
        ]);
    }

    /**
     * @return array
     * @Route("/invite", name="report_invite", options={"expose"=true})
     * @Method({"GET"})
     * @Security("is_granted('ROLE_ROOMS_REPORT')")
     * @Template()
     */
    public function inviteAction()
    {
        /** @var Invite[] $invites */
        $invites = $this->dm->getRepository('MBHOnlineBundle:Invite')->findAll();

        return [
            'invites' => $invites
        ];
    }

    /**
     * @param Request $request
     * @return array
     * @Route("/filling", name="report_filling", options={"expose"=true})
     * @Method({"GET"})
     * @Security("is_granted('ROLE_ROOMS_REPORT')")
     * @Template()
     */
    public function fillingAction(Request $request)
    {
        $roomTypeRepository = $this->dm->getRepository('MBHHotelBundle:RoomType');
        $roomTypes = [];
        if ($request->get('roomType')) {
            $roomType = $roomTypeRepository->find($request->get('roomType'));
            if ($roomType) {
                $roomTypes = [$roomType];
            }
        }

        $roomTypeList = $roomTypeRepository->findBy(['hotel.id' => $this->hotel->getId()]);
        $roomStatuses = $this->dm
            ->getRepository('MBHHotelBundle:RoomStatus')
            ->findBy([
                'hotel' => $this->hotel,
                'isEnabled' => true
            ]);

        if (!$roomTypes) {
            $roomTypes = $roomTypeList;
        }
        $hotels = $this->dm->getRepository('MBHHotelBundle:Hotel')->findAll();
        $sortedRoomTypes = $this->get('mbh.hotel.room_type_manager')->getSortedByHotels();

        $begin = new \DateTime('midnight -1 day');
        $end = new \DateTime('midnight +6 day');

        $generator = $this->get('mbh.package.report.filling_report_generator');
        $result = $generator->generate($begin, $end, $roomTypes, [], false);
        $roomStatusOptions = array_merge([
            'withoutStatus' => $this->get('translator')->trans('report.filling.filling.room_status.without_status')
        ], $this->helper->sortByValue($roomStatuses));

        return [
                'roomStatusOptions' => $roomStatusOptions,
                'roomTypes' => $roomTypes,
                'roomTypeList' => $sortedRoomTypes,
                'begin' => $begin,
                'end' => $end,
                'hotels' => $hotels
            ] + $result;
    }

    /**
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\Response
     * @Route("/filling/table", name="report_filling_table", options={"expose"=true})
     * @Method({"GET"})
     * @Security("is_granted('ROLE_ROOMS_REPORT')")
     * @Template()
     *
     * ParamConverter("begin", options={"format": "d.m.Y"})
     * ParamConverter("end", options={"format": "d.m.Y"})
     */
    public function fillingTableAction(Request $request)//\DateTime $begin = null, \DateTime $end  = null)
    {
        /** @var RoomTypeRepository $roomTypeRepository */
        $roomTypeRepository = $this->dm->getRepository('MBHHotelBundle:RoomType');

        $begin = $this->get('mbh.helper')->getDateFromString($request->get('begin'));
        $end = $this->get('mbh.helper')->getDateFromString($request->get('end'));
        if (!$begin && !$end) {
            $begin = new \DateTime('midnight -1 day');
            $end = new \DateTime('midnight +6 day');
        } elseif (!$begin) {
            $begin = clone($end);
            $begin->modify('-7 day');
        } elseif (!$end) {
            $end = clone($begin);
            $end->modify('+7 day');
        }

        if ($begin->diff($end)->days > 90) {
            return $this->render('MBHPackageBundle:Report:reportFillingTableError.html.twig', [
                'message' => $this->get('translator')->trans('controller.report_controller.filling_table_error.period_can_not_be_more_than')
            ]);
        }

        $requestedRoomTypesIds = $this->helper->getDataFromMultipleSelectField($request->get('roomTypes'));
        $requestedHotelIds = $this->helper->getDataFromMultipleSelectField($request->get('hotels'));
        $roomTypeIds = empty($requestedRoomTypesIds) ? null : $requestedRoomTypesIds;
        $hotelIds = empty($requestedHotelIds) ? null : $requestedHotelIds;
        $roomTypes = $roomTypeRepository->getByIdsAndHotelsIds($roomTypeIds, $hotelIds);

        if (!$begin && !$end) {
            $begin = new \DateTime('midnight -1 day');
            $end = new\DateTime('midnight +6 day');
        }
        $roomStatusOptions = $this->helper->getDataFromMultipleSelectField($request->get('roomStatus'));

        $generator = $this->get('mbh.package.report.filling_report_generator');
        $isEnabled = $request->get('isEnabled') === 'true';
        $result = $generator->generate($begin, $end, $roomTypes, $roomStatusOptions, $isEnabled);

        return $result + ['roomTypes' => $roomTypes];
    }

    /**
     * @Route("/work_shift", name="report_work_shift", options={"expose"=true})
     * @Method({"GET"})
     * @Security("is_granted('ROLE_WORK_SHIFT_VIEW')")
     * @Template()
     */
    public function workShiftAction()
    {
        $filterForm = $this->getWorkShiftForm();
        $filterForm->setData([
            'begin' => new \DateTime(),
            'status' => WorkShift::STATUS_OPEN
        ]);

        return [
            'form' => $filterForm->createView()
        ];
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    private function getWorkShiftForm()
    {
        return $this->createFormBuilder(null, [
            'method' => Request::METHOD_GET
        ])
            ->add('user', DocumentType::class, [
                'empty_data' => '',
                'class' => 'MBH\Bundle\UserBundle\Document\User',
                'query_builder' => function (DocumentRepository $repository) {
                    $repository->createQueryBuilder()->field('isEnabledWorkShift')->equals(true);
                }
            ])
            ->add('begin', DateType::class, [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'attr' => ['data-date-format' => 'dd.mm.yyyy'],
            ])
            ->add('end', DateType::class, [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'attr' => ['data-date-format' => 'dd.mm.yyyy'],
            ])
            ->add('status', ChoiceType::class, [
                'empty_data' => '',
                'choices' => array_combine(WorkShift::getAvailableStatuses(), WorkShift::getAvailableStatuses()),
                'choice_label' => function ($label) {
                    return 'workShift.statuses.' . $label;
                },
            ])
            ->getForm();
    }

    /**
     * @Route("/work_shift_table", name="report_work_shift_list", options={"expose"=true})
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_WORK_SHIFT_VIEW')")
     * @Template()
     */
    public function workShiftListAction(Request $request)
    {
        $workShiftRepository = $this->dm->getRepository('MBHUserBundle:WorkShift');

        $filterForm = $this->getWorkShiftForm();
        $filterForm->handleRequest($request);
        if (!$filterForm->isValid()) {
            throw $this->createNotFoundException();
        };

        $requestDate = $filterForm->getData();

        $criteria = [
            'hotel.id' => $this->hotel->getId()
        ];
        if ($requestDate['status']) {
            $criteria['status'] = $requestDate['status'];
        }
        $range = [];
        if ($requestDate['begin']) {
            $range['$gte'] = $requestDate['begin'];
        }
        if ($requestDate['end']) {
            ///$end->modify('+1 day');
            $range['$lte'] = $requestDate['end'];
        }
        if ($range) {
            $criteria['$or'] = [
                ['createdAt' => $range],
                ['updatedAt' => $range],
            ];
        }
        if ($requestDate['user']) {
            $criteria['createdBy'] = $requestDate['user']->getUsername();
        }

        $workShifts = $workShiftRepository->findBy($criteria, ['id' => 1]);

        return [
            'workShifts' => $workShifts,
        ];
    }

    /**
     * @Route("/get_work_shift", name="report_work_shift_table", options={"expose"=true})
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_WORK_SHIFT_VIEW')")
     */
    public function workShiftTableAction(Request $request)
    {
        $id = $request->get('id');
        $workShiftRepository = $this->dm->getRepository('MBHUserBundle:WorkShift');
        $workShift = $workShiftRepository->find($id);
        if (!$workShift) {
            throw $this->createNotFoundException();
        }

        $range = [
            '$gte' => $workShift->getBegin(),
            '$lte' => $workShift->getEnd(),
        ];

        $criteria = [
            '$or' => [
                ['createdAt' => $range],
                ['updatedAt' => $range],
            ],
            'createdBy' => $workShift->getCreatedBy(),
            'hotel.id' => $this->hotel->getId()
        ];

        $cashDocuments = $this->dm->getRepository('MBHCashBundle:CashDocument')->findBy($criteria);
        $packageRepository = $this->dm->getRepository('MBHPackageBundle:Package');

        $criteria = ['arrivalTime' => $range];
        $arrivalPackages = $packageRepository->findBy($criteria);
        $criteria = ['departureTime' => $range];
        $departurePackages = $packageRepository->findBy($criteria);

        $income = 0;
        $expenses = 0;
        $updateCashIDs = [];
        foreach ($cashDocuments as $cashDocument) {
            if ($cashDocument->getUpdatedAt() && $cashDocument->getUpdatedAt() > $workShift->getBegin() && $cashDocument->getUpdatedAt() < $workShift->getEnd()) {
                $updateCashIDs[] = $cashDocument->getId();
            }
            if ($cashDocument->getOperation() == 'in') {
                $income += $cashDocument->getTotal();
            } elseif ($cashDocument->getOperation() == 'out') {
                $expenses += $cashDocument->getTotal();
            }
        }

        $packages = [];
        $updatePackages = [];
        $updatePackageIDs = [];
        foreach ($packageRepository->findBy($criteria) as $package) {
            if ($package->getUpdatedAt() && $package->getUpdatedAt() > $workShift->getBegin() && $package->getUpdatedAt() < $workShift->getEnd()) {
                $updatePackages[] = $package->getId();
            } else {
                $packages[] = $package;
            }
        }

        /** @var FilterCollection $collection */
        $collection = $this->container->get('doctrine_mongodb')->getManager()->getFilterCollection();
        //remove deletable filter
        if ($collection->isEnabled('softdeleteable')) {
            $collection->disable('softdeleteable');
        }

        $criteria['deletedAt'] = ['$type' => 9];
        $deletedPackages = $packageRepository->findBy($criteria);

        $collection->enable('softdeleteable');

        return $this->render('MBHPackageBundle:Report:workShiftTable.html.twig', [
            'workShifts' => [$workShift],
            'cashDocuments' => $cashDocuments,
            'income' => $income,
            'expenses' => $expenses,
            'packages' => $packages,
            'deletedPackages' => $deletedPackages,
            'updatePackages' => $updatePackages,
            'updateCashIDs' => $updateCashIDs,
            'updatePackageIDs' => $updatePackageIDs,
            'arrivalPackages' => $arrivalPackages,
            'departurePackages' => $departurePackages,
            'statuses' => $this->container->getParameter('mbh.package.statuses'),
            'methods' => $this->container->getParameter('mbh.cash.methods'),
            'operations' => $this->container->getParameter('mbh.cash.operations')
        ]);
    }

    /**
     * @Security("is_granted('ROLE_DAILY_REPORT_BY_PACKAGES')")
     * @Route("/packages_daily_report", name="packages_daily_report" )
     * @Template()
     * @return array
     */
    public function packagesDailyReportAction()
    {
        $hotels = $this->dm->getRepository('MBHHotelBundle:Hotel')->findAll();
        $begin = new \DateTime('midnight - 30 days');
        $end = new \DateTime('midnight');

        list($calculationBegin, $calculationEnd) = $this->helper->getDefaultDatesOfSettlement();

        return [
            'begin' => $begin,
            'end' => $end,
            'calculationBegin' => $calculationBegin,
            'calculationEnd' => $calculationEnd,
            'hotels' => $hotels,
        ];
    }

    /**
     * @Security("is_granted('ROLE_DAILY_REPORT_BY_PACKAGES')")
     * @Route("/packages_daily_report_table", name="packages_daily_report_table", options={"expose"=true} )
     * @param Request $request
     * @return Response
     */
    public function packagesDailyReportTableAction(Request $request)
    {
        $begin = $this->helper->getDateFromString($request->query->get('begin')) ?? new \DateTime('midnight - 30 days');;
        $end = $this->helper->getDateFromString($request->query->get('end'))?? new \DateTime('midnight');

        list($defaultCalculationBegin, $defaultCalculationEnd) = $this->helper->getDefaultDatesOfSettlement();
        $calculationBegin = $this->helper->getDateFromString($request->query->get('calcBegin'))
            ?? $defaultCalculationBegin;
        $calculationEnd = $this->helper->getDateFromString($request->query->get('calcEnd'))
            ?? $defaultCalculationEnd;

        $hotels = $this->dm
            ->getRepository('MBHHotelBundle:Hotel')
            ->getByIds($this->helper->getDataFromMultipleSelectField($request->query->get('hotels')));

        $report = $this->get('mbh.packages_daily_report_compiler')
            ->generate($begin, $end, $hotels->toArray(), $calculationBegin, $calculationEnd);

        return $report->generateReportTableResponse();
    }

    /**
     * @Template()
     * @Security("is_granted('ROLE_DISTRIBUTION_BY_DAYS_OF_WEEK_REPORT')")
     * @Route("/distribution_by_days_of_the_week", name="distribution_by_days_of_the_week", options={"expose"=true})
     */
    public function packagesByDaysOfWeekAction()
    {
        $hotels = $this->dm->getRepository('MBHHotelBundle:Hotel')->findAll();

        return [
            'hotels' => $hotels,
        ];
    }

    /**
     * @Security("is_granted('ROLE_DISTRIBUTION_BY_DAYS_OF_WEEK_REPORT')")
     * @Route("/distribution_report_table", name="distribution_report_table", options={"expose"=true})
     * @param Request $request
     * @return Response
     */
    public function distributionReportTableAction(Request $request)
    {
        $defaultBeginDate = $this->clientConfig->getActualBeginDate();

        $begin = $this->helper->getDateFromString($request->query->get('begin')) ?? $defaultBeginDate;
        $end = $this->helper->getDateFromString($request->query->get('end'))
            ?? (clone $defaultBeginDate)->modify('+45 days');

        $creationBegin = $this->helper->getDateFromString($request->query->get('creationBegin'));
        $creationEnd = $this->helper->getDateFromString($request->query->get('creationEnd'));
        $hotels = $this->dm
            ->getRepository('MBHHotelBundle:Hotel')
            ->getByIds($this->helper->getDataFromMultipleSelectField($request->query->get('hotels')), false)
            ->toArray();

        $groupType = $request->query->get('group_type') ? $request->query->get('group_type') : 'arrival';
        $type = $request->query->get('type') ? $request->query->get('type') : 'actual';

        $report = $this->get('mbh.distribution_report_compiler')
            ->generate($begin, $end, $hotels, $groupType, $type, $creationBegin, $creationEnd);

        return $report->generateReportTableResponse();
    }

    /**
     * @Security("is_granted('ROLE_RESERVATION_REPORT')")
     * @Template()
     * @Route("/reservation_report", name="reservation_report")
     */
    public function reservationReportAction()
    {
        return [
            'roomTypes' => $this->hotel->getRoomTypes()
        ];
    }

    /**
     * @Security("is_granted('ROLE_RESERVATION_REPORT')")
     * @Route("/reservation_report_table", name="reservation_report_table", options={"expose"=true})
     * @param Request $request
     * @return Response
     */
    public function reservationReportTableAction(Request $request)
    {
        $date = $this->helper->getDateFromString($request->get('date')) ?? new \DateTime('midnight');
        $periodBegin = $this->helper->getDateFromString($request->get('periodBegin'));
        $periodEnd = $this->helper->getDateFromString($request->get('periodEnd'));

        $roomTypeIds = $this->helper->getDataFromMultipleSelectField($request->get('roomTypes'));
        $roomTypes = empty($roomTypeIds)
            ? $this->hotel->getRoomTypes()
            : $this->dm->getRepository('MBHHotelBundle:RoomType')->fetch(null, $roomTypeIds);

        $report = $this->get('mbh.reservation_report')
            ->generate($periodBegin, $periodEnd, $date, $roomTypes->toArray());

        return $report->generateReportTableResponse();
    }

    /**
     * @Security("is_granted('ROLE_SALES_CHANNELS_REPORT')")
     * @Route("/sales_channels_report", name="sales_channels_report")
     * @Template()
     */
    public function salesChannelsReportAction()
    {
        return [
            'sources' => $this->dm->getRepository('MBHPackageBundle:PackageSource')->findAll(),
            'hotels' => $this->dm->getRepository('MBHHotelBundle:Hotel')->findAll(),
            'packageSources' => $this->dm->getRepository('MBHPackageBundle:PackageSource')->findAll(),
            'roomTypesByHotels' => $this->get('mbh.hotel.room_type_manager')->getSortedByHotels(),
            'dataTypes' => SalesChannelsReportCompiler::DATA_TYPES
        ];
    }

    /**
     * @Security("is_granted('ROLE_SALES_CHANNELS_REPORT')")
     * @Route("/sales_channels_report_table", name="sales_channels_report_table", options={"expose"=true})
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function salesChannelsReportTableAction(Request $request)
    {
        $begin = $this->helper->getDateFromString($request->get('begin'));
        $end = $this->helper->getDateFromString($request->get('end'));
        $filterType = $request->query->get('filterType')
            ? $request->query->get('filterType')
            : SalesChannelsReportCompiler::STATUS_FILTER_TYPE;
        $sourcesIds = $this->helper->getDataFromMultipleSelectField($request->query->get('sources'));
        $roomTypesIds = $this->helper->getDataFromMultipleSelectField($request->query->get('roomTypes'));
        $hotelsIds = $this->helper->getDataFromMultipleSelectField($request->query->get('hotels'));
        $isRelative = $isRelative = $request->query->get('isRelative') === 'true';
        $dataType = $request->query->get('dataType')
            ? $request->query->get('dataType')
            : SalesChannelsReportCompiler::SUM_DATA_TYPE;

        $report = $this->get('mbh.sales_channels_report_compiler')
            ->generate($begin, $end, $filterType, $sourcesIds, $roomTypesIds, $hotelsIds, $isRelative, $dataType);

        return $report->generateReportTableResponse();
    }
}