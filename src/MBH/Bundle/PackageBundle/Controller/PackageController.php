<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Controller\DeletableControllerInterface;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomRepository;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageAccommodation;
use MBH\Bundle\PackageBundle\Document\PackageRepository;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Form\PackageDeleteReasonType;
use MBH\Bundle\PackageBundle\Form\OrderTouristType;
use MBH\Bundle\PackageBundle\Form\PackageAccommodationType;
use MBH\Bundle\PackageBundle\Form\PackageCsvType;
use MBH\Bundle\PackageBundle\Form\PackageMainType;
use MBH\Bundle\PackageBundle\Form\PackageAccommodationRoomType;
use MBH\Bundle\PackageBundle\Form\PackageServiceType;
use MBH\Bundle\PackageBundle\Lib\DeleteException;
use MBH\Bundle\PackageBundle\Lib\PackageAccommodationException;
use MBH\Bundle\PackageBundle\Lib\PackageCreationException;
use MBH\Bundle\PackageBundle\Services\OrderManager;
use MBH\Bundle\PriceBundle\Document\Promotion;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

/**
 * Class PackageController
 * @package MBH\Bundle\PackageBundle\Controller
 */
class PackageController extends Controller implements CheckHotelControllerInterface, DeletableControllerInterface
{
    /**
     * List entities
     *
     * @Route("/", name="package", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_PACKAGE_VIEW')")
     * @Template()
     * @throws \Exception
     */
    public function indexAction()
    {
        $now = new \DateTime('midnight');
        $tomorrow = new \DateTime('midnight +1 day');
        $this->dm->getFilterCollection()->enable('softdeleteable');

        $data['deleted'] = false;

        //begin today count
        $data = [
            'count' => true,
            'hotel' => $this->get('mbh.hotel.selector')->getSelected()
        ];

        /** @var PackageRepository $repository */
        $repository = $this->dm->getRepository('MBHPackageBundle:Package');

        $count['begin_today'] = $repository->fetch(array_merge([
                'begin' => $now->format('d.m.Y'),
                'end' => $now->format('d.m.Y'),
                'dates' => 'begin',
                'checkOut' => false,
                'checkIn' => false
            ], $data));
        //begin tomorrow count
        $count['begin_tomorrow'] = $repository->fetch(array_merge([
                'begin' => $tomorrow->format('d.m.Y'),
                'end' => $tomorrow->format('d.m.Y'),
                'dates' => 'begin',
                'checkOut' => false,
                'checkIn' => false
            ], $data));
        //live now count
        $count['live_now'] = $repository->fetch(array_merge([
                'filter' => 'live_now',
                'checkOut' => false,
                'checkIn' => true
            ], $data));
        //without-approval count
        $count['without_approval'] = $repository->fetch(array_merge([
                'confirmed' => '0'
            ], $data));
        //without_accommodation count
        $count['without_accommodation'] = $repository->fetch(array_merge([
                'filter' => 'without_accommodation',
                'end' => $now->format('d.m.Y'),
                'dates' => 'begin'
            ], $data));
        //not_paid count
        $count['not_paid'] = $repository->fetch(array_merge([
                'paid' => 'not_paid'
            ], $data));
        //not_paid time count
        $count['not_paid_time'] = $repository->fetch(array_merge([
                'paid' => 'not_paid',
                'end' => new \DateTime('-' . $this->clientConfig->getNumberOfDaysForPayment() . 'days'),
                'dates' => 'createdAt'
            ], $data));
        //created_by count
        $count['created_by'] = $repository->fetch(array_merge([
                'createdBy' => $this->getUser()->getUsername()
            ], $data));
        //checkIn count
        $count['not_check_in'] = $repository->fetch(array_merge([
                'checkIn' => false,
                'end' => $now->format('d.m.Y'),
                'dates' => 'begin'
            ], $data));

        return [
            'packageSources' => $this->dm->getRepository('MBHPackageBundle:PackageSource')->findAll(),
            'roomTypes' => $this->get('mbh.hotel.selector')->getSelected()->getRoomTypes(),
            'statuses' => $this->container->getParameter('mbh.package.statuses'),
            'count' => $count,
        ];
    }

    /**
     * Lists all entities as json.
     *
     * @Route("/csv", name="package_csv", options={"expose"=true})
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_PACKAGE_VIEW')")
     * @Template()
     */
    public function csvAction(Request $request)
    {
        $form = $this->createForm(PackageCsvType::class);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $formData = $form->getData();

            $data = [
                'hotel' => $this->get('mbh.hotel.selector')->getSelected(),
                'roomType' => $this->helper->getDataFromMultipleSelectField(explode(',', $formData['roomType'])),
                'source' => $formData['source'],
                'status' => $formData['status'],
                'deleted' => (boolean)$formData['deleted'],
                'begin' => $formData['begin'],
                'end' => $formData['end'],
                'dates' => $formData['dates'],
                'paid' => $formData['paid'],
                'query' => $formData['query'],
                'confirmed' => $formData['confirmed'],
            ];

            //quick links
            switch ($formData['quick_link']) {
                case 'begin-today':
                    $data['dates'] = 'begin';
                    $now = new \DateTime('midnight');
                    $data['begin'] = $now->format('d.m.Y');
                    $data['end'] = $now->format('d.m.Y');
                    $data['checkOut'] = false;
                    $data['checkIn'] = false;
                    break;

                case 'begin-tomorrow':
                    $data['dates'] = 'begin';
                    $now = new \DateTime('midnight');
                    $now->modify('+1 day');
                    $data['begin'] = $now->format('d.m.Y');
                    $data['end'] = $now->format('d.m.Y');
                    $data['checkOut'] = false;
                    $data['checkIn'] = false;
                    break;

                case 'live-now':
                    $data['filter'] = 'live_now';
                    $data['checkIn'] = true;
                    $data['checkOut'] = false;
                    break;

                case 'without-approval':
                    $data['confirmed'] = '0';
                    break;

                case 'without-accommodation':
                    $data['filter'] = 'without_accommodation';
                    $data['dates'] = 'begin';
                    $now = new \DateTime('midnight');
                    $data['end'] = $now->format('d.m.Y');
                    break;

                case 'not-paid':
                    $data['paid'] = 'not_paid';
                    break;

                case 'not-paid-time':
                    $notPaidTime = new \DateTime('-' . $this->clientConfig->getNumberOfDaysForPayment().'days');

                    $data['paid'] = 'not_paid';
                    $data['dates'] = 'createdAt';
                    $data['end'] = $notPaidTime->format('d.m.Y');
                    break;

                case 'not-check-in':
                    $data['checkIn'] = false;
                    $data['dates'] = 'begin';
                    $now = new \DateTime('midnight');
                    $data['end'] = $now->format('d.m.Y');
                    break;

                case 'created-by':
                    $data['createdBy'] = $this->getUser()->getUsername();
                    break;
                default:
            }

            $generate = $this->get('mbh.package.csv.generator')->generateCsv($data, $formData);
            $response = new Response($generate);
            $response->setStatusCode(200);
            $response->headers->set('Content-Type', 'text/csv; charset=windows-1251');
            $response->headers->set('Content-Disposition', 'attachment; filename="export.csv"');

            return $response;
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Lists all entities as json.
     *
     * @Route("/json", name="package_json", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_PACKAGE_VIEW')")
     * @Template()
     * @param Request $request
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function jsonAction(Request $request)
    {
        $this->dm->getFilterCollection()->enable('softdeleteable');
        $qb = $this->get('mbh.order_manager')
            ->getQueryBuilderByRequestData($request, $this->getUser(), $this->get('mbh.hotel.selector')->getSelected());

        $entities = $qb->getQuery()->execute();
        $summary = $this->get('mbh.order_manager')
            ->calculateSummary($qb->limit(0)->skip(0));
        //TODO: Check $entities->count() must be != count($entities)
        //see cdec6e8455be089388e580a32838f42241dc7d25
        return [
            'entities' => $entities,
            'total' => $entities->count(),
            'draw' => $request->get('draw'),
            'statuses' => $this->container->getParameter('mbh.package.statuses'),
            'summary' => $summary
        ];
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="package_edit", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_PACKAGE_VIEW_ALL') or (is_granted('VIEW', package) and is_granted('ROLE_PACKAGE_VIEW'))")
     * @Template()
     * @ParamConverter("entity", class="MBHPackageBundle:Package")
     * @param Package $package
     * @return array
     */
    public function editAction(Package $package)
    {
        if (!$this->container->get('mbh.package.permissions')->checkHotel($package)) {
            throw $this->createNotFoundException();
        }

        /** @var AuthorizationChecker $authorizationChecker */
        $authorizationChecker = $this->get('security.authorization_checker');

        $promotions = [];
        if ($authorizationChecker->isGranted('ROLE_PROMOTION_ADD')) {
            /** @var Promotion[] $promotions */
            $promotions = $package->getTariff()->getDefaultPromotion() ?
                [$package->getTariff()->getDefaultPromotion()] :
                iterator_to_array($package->getTariff()->getPromotions());

            if (!$authorizationChecker->isGranted('ROLE_INDIVIDUAL_PROMOTION_ADD')) {
                $promotions = array_filter($promotions, function ($promotion) {
                    /** @var Promotion $promotion */
                    return $promotion->getIsIndividual() === false;
                });
            }
        }

        $form = $this->createForm(PackageMainType::class, $package, [
            'discount' => $authorizationChecker->isGranted('ROLE_DISCOUNT_ADD'),
            'promotion' => $authorizationChecker->isGranted('ROLE_PROMOTION_ADD'),
            'price' => $authorizationChecker->isGranted('ROLE_PACKAGE_PRICE_EDIT'),
            'special' => $authorizationChecker->isGranted('ROLE_SPECIAL_ADD'),
            'promotions' => $promotions,
            'package' => $package,
            'hotel' => $package->getRoomType()->getHotel(),
            'corrupted' => $package->getCorrupted(),
            'virtualRooms' => $this->clientConfig->getSearchWindows()
        ]);

        return [
            'package' => $package,
            'status' => $package->getStatus(),
            'form' => $form->createView(),
            'logs' => $this->logs($package),
            'statuses' => $this->container->getParameter('mbh.package.statuses')
        ];
    }

    /**
     * @Route("/service/resetValue/{id}", name="reset_total_overwrite_value")
     * @Security("is_granted('ROLE_ORDER_EDIT')")
     * @param Package $package
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function resetTotalOverwriteValue(Package $package)
    {
        $package->setTotalOverwrite(0);
        $package->getOrder()->setTotalOverwrite(0);
        $this->dm->flush();
        $this->addFlash(
            'success',
            $this->get('translator')->trans('controller.packageController.record_edited_success')
        );

        return $this->redirectToRoute('package_edit', ['id' => $package->getId()]);
    }

    /**
     * Edits an existing entity.
     *
     * @Route("/{id}", name="package_update")
     * @Method("POST")
     * @Security("is_granted('ROLE_PACKAGE_EDIT') and (is_granted('EDIT', package) or is_granted('ROLE_PACKAGE_EDIT_ALL'))")
     * @Template("MBHPackageBundle:Package:edit.html.twig")
     * @ParamConverter("package", class="MBHPackageBundle:Package")
     * @param Request $request
     * @param Package $package
     * @return array|RedirectResponse
     */
    public function updateAction(Request $request, Package $package)
    {
        if (!$this->container->get('mbh.package.permissions')->checkHotel($package)) {
            throw $this->createNotFoundException();
        }
        if (!empty($package->getDeletedAt())) {
            throw new \InvalidArgumentException('Package with id "' . $package->getId() . '" is already deleted!');
        }

        /** @var AuthorizationChecker $authorizationChecker */
        $authorizationChecker = $this->get('security.authorization_checker');

        $promotions = [];
        if ($authorizationChecker->isGranted('ROLE_PROMOTION_ADD')) {
            /** @var Promotion[] $promotions */
            $promotions = $package->getTariff()->getDefaultPromotion() ?
                [$package->getTariff()->getDefaultPromotion()] :
                iterator_to_array($package->getTariff()->getPromotions());

            if (!$authorizationChecker->isGranted('ROLE_INDIVIDUAL_PROMOTION_ADD')) {
                $promotions = array_filter($promotions, function ($promotion) {
                    /** @var Promotion $promotion */
                    return $promotion->getIsIndividual() === false;
                });
            }
        }

        $oldPackage = clone $package;
        $form = $this->createForm(PackageMainType::class, $package, [
            'discount' => $authorizationChecker->isGranted('ROLE_DISCOUNT_ADD'),
            'promotion' => $authorizationChecker->isGranted('ROLE_PROMOTION_ADD'),
            'price' => $authorizationChecker->isGranted('ROLE_PACKAGE_PRICE_EDIT'),
            'special' => $authorizationChecker->isGranted('ROLE_SPECIAL_ADD'),
            'promotions' => $promotions,
            'package' => $package,
            'hotel' => $package->getRoomType()->getHotel(),
            'corrupted' => $package->getCorrupted(),
            'virtualRooms' => $this->clientConfig->getSearchWindows()
        ]);

        $form->handleRequest($request);
        if ($form->isValid() && !$package->getIsLocked()) {
            //check by search
            $newTariff = $form->get('tariff')->getData();
            $orderManager = $this->get('mbh.order_manager');

            $result = $orderManager->updatePackage($oldPackage, $package, $newTariff);
            if ($result instanceof Package) {
                $this->dm->persist($package);
                $this->dm->flush();
                $this->addFlash('success', 'controller.packageController.record_edited_success');

                $updateResult = $orderManager->tryUpdateAccommodations($package, $oldPackage);
                foreach ($updateResult['dangerNotifications'] as $messages) {
                    $this->addFlash('danger', $messages);
                }
                if ($updateResult['success'] === true) {
                    $this->dm->flush();
                }

                return $this->afterSaveRedirect('package', $package->getId());
            } else {
                $this->addFlash('danger', $result);
            }
        }

        return [
            'package' => $package,
            'status' => $package->getStatus(),
            'form' => $form->createView(),
            'logs' => $this->logs($package),
            'statuses' => $this->container->getParameter('mbh.package.statuses')
        ];
    }

    /**
     * Create new entity
     *
     * @Route("/new", name="package_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_PACKAGE_NEW')")
     * @Template()
     * @param Request $request
     * @return array|JsonResponse|RedirectResponse
     */
    public function newAction(Request $request)
    {
        $order = null;
        if ($request->get('order')) {
            $order = $this->dm->getRepository('MBHPackageBundle:Order')->find($request->get('order'));
        }
        $quantity = (int)$request->get('quantity');
        /** @var OrderManager $orderManager */
        $orderManager = $this->container->get('mbh.order_manager');

        $package = [
            'begin' => $request->get('begin'),
            'end' => $request->get('end'),
            'adults' => $request->get('adults'),
            'children' => $request->get('children'),
            'roomType' => $request->get('roomType'),
            'tariff' => $request->get('tariff'),
            'special' => $request->get('special'),
            'accommodation' => $request->get('accommodation'),
            'forceBooking' => $request->get('forceBooking'),
            'infants' => $request->get('infants'),
            'childrenAges' => $request->get('children_age'),
            'savedQueryId' => $request->get('query_id')

        ];

        if ($quantity > 20 || $quantity < 1) {
            $quantity = 1;
        }

        $packages = [];
        for ($i = 1; $i <= $quantity; $i++) {
            $packages[] = $package;
        }

        $data = [
            'packages' => $packages,
            'status' => 'offline',
            'confirmed' => false,
            'tourist' => $request->get('tourist'),
        ];
        try {
            $order = $orderManager->createPackages($data, $order, $this->getUser());
        } catch (PackageCreationException $e) {
            $createdPackageCount = count($e->order->getPackages());
            if ($packages > 1 && $createdPackageCount > 0) {
                $packageCreationMessage = $this->get('translator')->trans('controller.package_controller.package_creation_flash', [
                    '%packagesCount%' => $createdPackageCount,
                    '%requestedPackagesCount%' => $packages
                ]);
                $this->addFlash('danger', $packageCreationMessage);
                $order = $e->order;
            } else {
                if ($this->container->get('kernel')->getEnvironment() == 'dev') {
                    dump($e);
                };

                return [];
            }
        } catch (\Exception $e) {
            if ($this->container->get('kernel')->getEnvironment() == 'dev') {
                dump($e);
            };

            return [];
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(json_encode([
                'success' => [
                    $this->get('translator')->trans('controller.chessboard.package_create.success')
                ],
                'errors' => [],
                'data' => [
                    'packageId' => $order->getPackages()[0]->getId()
                ]
            ]));
        }

        $this->addFlash('success', 'controller.packageController.order_created_success');

        $route = $order->getPayer() ? 'package_order_cash' : 'package_order_tourist_edit';

        return $this->redirectToRoute($route, [
            'id' => $order->getId(),
            'packageId' => $order->getPackages()[0]->getId()
        ]);
    }

    /**
     * Guests
     *
     * @Route("/{id}/guest", name="package_guest")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_PACKAGE_VIEW_ALL') or (is_granted('VIEW', package) and is_granted('ROLE_PACKAGE_VIEW'))")
     * @ParamConverter("entity", class="MBHPackageBundle:Package")
     * @Template()
     */
    public function guestAction(Request $request, Package $package)
    {
        if (!$this->container->get('mbh.package.permissions')->checkHotel($package)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(OrderTouristType::class, null, ['guest' => false]);

        $authorizationChecker = $this->container->get('security.authorization_checker');
        if ($request->getMethod() == 'POST' &&
            !$package->getIsLocked() &&
            $authorizationChecker->isGranted('ROLE_PACKAGE_GUESTS') && (
                $authorizationChecker->isGranted('ROLE_PACKAGE_EDIT_ALL') ||
                $authorizationChecker->isGranted('EDIT', $package)
            )
        ) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();
                $tourist = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
                    $data['lastName'],
                    $data['firstName'],
                    $data['patronymic'],
                    $data['birthday'],
                    $data['email'],
                    $data['phone'],
                    $data['communicationLanguage']
                );
                $package->addTourist($tourist);
                $this->dm->persist($package);
                $this->dm->flush();

                $this->addFlash('success', 'controller.packageController.guest_added_success');
                if ($tourist->getIsUnwelcome()) {
                    $this->addFlash('warning', '<i class="fa fa-user-secret"></i> '
                        . $this->get('translator')->trans('package.tourist_in_unwelcome'));
                }

                return $this->afterSaveRedirect('package', $package->getId(), [], '_guest');
            }
        }

        return [
            'package' => $package,
            'form' => $form->createView(),
            'logs' => $this->logs($package)
        ];
    }

    /**
     * Guests delete
     *
     * @Route("/{id}/guest/{touristId}/delete", name="package_guest_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_PACKAGE_GUESTS') and (is_granted('EDIT', entity) or is_granted('ROLE_PACKAGE_EDIT_ALL'))")
     * @ParamConverter("entity", class="MBHPackageBundle:Package")
     * @ParamConverter("tourist", class="MBHPackageBundle:Tourist", options={"id" = "touristId"})
     */
    public function guestDeleteAction(Request $request, Package $package, Tourist $tourist)
    {
        if (!$this->container->get('mbh.package.permissions')->checkHotel($package)) {
            throw $this->createNotFoundException();
        }
        $package->removeTourist($tourist);

        $this->dm->persist($tourist);
        $this->dm->persist($package);
        $this->dm->flush();

        $this->addFlash('success', 'controller.packageController.guest_removed_successful');

        return $this->redirectToRoute('package_guest', ['id' => $package->getId()]);
    }

    /**
     * Services
     *
     * @param Request $request
     * @param Package $package
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ODM\MongoDB\LockException
     * @Route("/{id}/services", name="package_service")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_PACKAGE_VIEW_ALL') or (is_granted('VIEW', package) and is_granted('ROLE_PACKAGE_VIEW'))")
     * @Template()
     * @ParamConverter("package", class="MBHPackageBundle:Package")
     */
    public function serviceAction(Request $request, Package $package)
    {
        if (!$this->container->get('mbh.package.permissions')->checkHotel($package)) {
            throw $this->createNotFoundException();
        }

        $packageService = new PackageService();
        $packageService
            ->setBegin($package->getBegin())
            ->setEnd($package->getEnd())
            ->setTime($package->getBegin())
            ->setPackage($package);


        $form = $this->createForm(PackageServiceType::class, $packageService, [
            'package' => $package,
            'dm' => $this->dm
        ]);

        if ($request->getMethod() == 'POST' &&
            !$package->getIsLocked() &&
            $this->container->get('security.authorization_checker')->isGranted('ROLE_PACKAGE_SERVICES') && (
                $this->container->get('security.authorization_checker')->isGranted('ROLE_PACKAGE_EDIT_ALL') ||
                $this->container->get('security.authorization_checker')->isGranted('EDIT', $package)
            )
        ) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->dm->persist($packageService);
                $this->dm->flush();

                $this->addFlash('success', 'controller.packageController.service_added_success');

                return $this->afterSaveRedirect('package', $package->getId(), [], '_service');
            }
        }

        return [
            'package' => $package,
            'logs' => $this->logs($package),
            'form' => $form->createView(),
            'config' => $this->container->getParameter('mbh.services'),
        ];
    }

    /**
     * Service document edit
     *
     * @Route("/{id}/service/{serviceId}/edit", name="package_service_edit", options={"expose"=true})
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_PACKAGE_SERVICES') and (is_granted('EDIT', package) or is_granted('ROLE_PACKAGE_EDIT_ALL'))")
     * @Template("MBHPackageBundle:Package:editService.html.twig")
     * @ParamConverter("entity", class="MBHPackageBundle:Package")
     * @ParamConverter("service", class="MBHPackageBundle:PackageService", options={"id" = "serviceId"})
     * @param Request $request
     * @param Package $package
     * @param PackageService $service
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     */
    public function serviceEditAction(Request $request, Package $package, PackageService $service)
    {
        if (!$this->container->get('mbh.package.permissions')->checkHotel($package)) {
            throw $this->createNotFoundException();
        }

        if (!$service->getTime()) {
            $service->setTime($package->getBegin());
        }

        $form = $this->createForm(PackageServiceType::class, $service, [
            'package' => $package,
            'dm' => $this->dm
        ]);

        if ($request->getMethod() == Request::METHOD_POST) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->dm->persist($service);
                $this->dm->flush();

                $this->addFlash('success', 'controller.packageController.service_edit_success');

                return $request->get('save') !== null ?
                    $this->redirectToRoute(
                        'package_service_edit',
                        ['id' => $package->getId(), 'serviceId' => $service->getId()]
                    ) :
                    $this->redirectToRoute('package_service', ['id' => $package->getId()]);
            }
        }

        return [
            'package' => $package,
            'service' => $service,
            'logs' => $this->logs($package),
            'form' => $form->createView(),
            'config' => $this->container->getParameter('mbh.services'),
        ];
    }

    /**
     * Service document delete
     *
     * @Route("/{id}/service/{serviceId}/delete", name="package_service_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_PACKAGE_SERVICES') and (is_granted('EDIT', entity) or is_granted('ROLE_PACKAGE_EDIT_ALL'))")
     * @ParamConverter("entity", class="MBHPackageBundle:Package")
     * @ParamConverter("service", class="MBHPackageBundle:PackageService", options={"id" = "serviceId"})
     */
    public function serviceDeleteAction(Request $request, Package $entity, PackageService $service)
    {
        if (!$this->container->get('mbh.package.permissions')->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $this->dm->remove($service);
        $this->dm->flush();

        $request->getSession()->getFlashBag()
            ->set('success', $this->get('translator')->trans('controller.packageController.service_deleted_success'));

        return $this->redirect($this->generateUrl('package_service', ['id' => $entity->getId()]));
    }

    /**
     * @Route("/{id}/accommodation/set/{roomId}", name="package_accommodation_set")
     * @Method({"GET"})
     * @Security("is_granted('ROLE_PACKAGE_VIEW_ALL') or (is_granted('VIEW', package) and is_granted('ROLE_PACKAGE_VIEW'))")
     * @ParamConverter("package", class="MBHPackageBundle:Package")
     * @ParamConverter("room", class="MBHHotelBundle:Room", options={"id" = "roomId"})
     *
     * @param Request $request
     * @param Package $package
     * @param Room $room
     * @return RedirectResponse
     * @deprecated
     */
    public function accommodationSetAction(Request $request, Package $package, Room $room)
    {
        if (!$this->container->get('mbh.package.permissions')->checkHotel($package)) {
            throw $this->createNotFoundException();
        }

        $availableRooms = $this->dm->getRepository('MBHHotelBundle:Room')->fetchAccommodationRooms(
            $package->getBegin(),
            $package->getEnd(),
            $this->hotel,
            null,
            null,
            $package,
            false
        );

        if (!in_array($room->getId(), $this->helper->toIds($availableRooms))) {
            $this->addFlash('danger', 'controller.packageController.record_edited_fail_accommodation');
        } else {
            $package->setAccommodation($room);
            $this->dm->persist($package);
            $this->dm->flush();

            $this->addFlash('success', 'controller.packageController.placement_saved_success');
        }

        return $this->redirectToRoute('package_accommodation', ['id' => $package->getId()]);
    }

    /**
     * @Route("/{id}/{room}/accommodation/new/", name="package_accommodation_new", options={"expose"=true})
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_PACKAGE_VIEW_ALL') or (is_granted('VIEW', package) and is_granted('ROLE_PACKAGE_VIEW'))")
     * @ParamConverter("package", class="MBHPackageBundle:Package")
     * @ParamConverter("room", class="MBHHotelBundle:Room", options={"id" = "room"})
     * @Template("MBHPackageBundle:Package:accommodationForm.html.twig")
     * @param Request $request
     * @param $id
     * @param Room $room
     * @return array|Response
     * @throws Exception
     */
    public function accommodationNewAction(Request $request, $id, Room $room)
    {
        if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->enable('softdeleteable');
        }
        if (!empty($room->getDeletedAt())) {
            throw new Exception('Room ' . $room->getId() . ' is deleted!');
        }
        $package = $this->dm->getRepository('MBHPackageBundle:Package')->find($id);
        $accommodation = new PackageAccommodation();
        $accommodation
            ->setRoom($room)
            ->setBegin($package->getLastEndAccommodation())
            ->setEnd($package->getEnd())
            ->setPackageForValidator($package);

        $form = $this->createForm(PackageAccommodationRoomType::class, $accommodation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $additionResult = $this->get('mbh_bundle_package.services.package_accommodation_manipulator')
                ->addAccommodation($accommodation, $package);
            if ($additionResult instanceof PackageAccommodation) {
                $this->addFlash('success', 'controller.packageController.placement_saved_success');
                if ($request->isXmlHttpRequest()) {
                    return new Response('', 302);
                }

                $this->redirectToRoute('package_accommodation', ['id' => $package->getId()]);
            }

            $form->addError(new FormError($additionResult));
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * Accommodation edit
     * @Route("/{id}/accommodation/edit", name="package_accommodation_edit", options={"expose"=true})
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_PACKAGE_ACCOMMODATION') and (is_granted('EDIT', accommodation) or is_granted('ROLE_PACKAGE_EDIT_ALL'))")
     * @Template("MBHPackageBundle:Package:accommodationForm.html.twig")
     * @param Request $request
     * @param PackageAccommodation $accommodation
     * @return array|Response
     * @throws Exception
     */
    public function accommodationEditAction(Request $request, PackageAccommodation $accommodation)
    {
        if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->enable('softdeleteable');
        }

        $form = $this->createForm(PackageAccommodationRoomType::class, $accommodation);
        if (!empty($accommodation->getRoom()->getDeletedAt())) {
            throw new Exception('Room ' . $accommodation->getRoom()->getId() . ' is deleted!');
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $editResult = $this->get('mbh_bundle_package.services.package_accommodation_manipulator')
                ->editAccommodation($accommodation);

            if ($editResult instanceof PackageAccommodation) {
                $this->addFlash('success', 'controller.packageController.placement_edited_success');
                if ($request->isXmlHttpRequest()) {
                    return new Response('', 302);
                }

                $package = $this->dm->getRepository('MBHPackageBundle:Package')
                    ->getPackageByPackageAccommodationId($accommodation->getId());
                return $this->redirectToRoute(
                    'package_accommodation',
                    ['id' => $package->getId(), 'begin' => null, 'end' => null]
                );
            }

            $form->addError(new FormError($editResult));
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * Accommodation delete
     *
     * @Route("/{id}/accommodation/delete", name="package_accommodation_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_PACKAGE_ACCOMMODATION') and (is_granted('EDIT', entity) or is_granted('ROLE_PACKAGE_EDIT_ALL'))")
     * @ParamConverter("entity", class="MBHPackageBundle:PackageAccommodation")
     * @param Request $request
     * @param PackageAccommodation $entity
     * @return RedirectResponse
     */
    public function accommodationDeleteAction(Request $request, PackageAccommodation $entity)
    {
        $package = $this->dm->getRepository('MBHPackageBundle:Package')
            ->getPackageByPackageAccommodationId($entity->getId());
        if (!$this->container->get('mbh.package.permissions')->checkHotel($package)) {
            throw $this->createNotFoundException();
        }
        try {
            $this->dm->remove($entity);
            $this->dm->flush();
            $this->addFlash(
                'success',
                $this->get('translator')->trans('controller.packageController.placement_deleted_success')
            );
        } catch (DeleteException $exception) {
            $this->addFlash('error', $this->get('translator')->trans($exception->getMessage()));
        }

        return $this->redirect($this->generateUrl('package_accommodation', ['id' => $package->getId()]));
    }

    /**
     * Accommodation
     *
     * @Route("/{id}/accommodation/{begin}/{end}", name="package_accommodation", defaults={"begin" = null, "end" = null}, options={"expose"=true})
     * @ParamConverter("begin", options={"format":"Y-m-d"})
     * @ParamConverter("end", options={"format":"Y-m-d"})
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_PACKAGE_VIEW_ALL') or (is_granted('VIEW', package) and is_granted('ROLE_PACKAGE_VIEW'))")
     * @Template()
     * @param Request $request
     * @param Package $package
     * @param \DateTime $begin
     * @param \DateTime $end
     * @return array
     * @throws PackageAccommodationException
     */
    public function accommodationAction(
        Request $request,
        Package $package,
        \DateTime $begin = null,
        \DateTime $end = null
    ) {
        if (!$this->container->get('mbh.package.permissions')->checkHotel($package)) {
            throw $this->createNotFoundException();
        }

        if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->enable('softdeleteable');
        }

        /** @var RoomRepository $roomRepository */
        $roomRepository = $this->dm->getRepository('MBHHotelBundle:Room');

        $pAccManipulator = $this->get('mbh_bundle_package.services.package_accommodation_manipulator');
        $accIntervals = $pAccManipulator->getEmptyIntervals($package);
        if (!is_null($begin) && !is_null($end)) {
            $begin->setTime(0, 0, 0);
            $end->setTime(0, 0, 0);
        } elseif (!$begin && !$end) {
            if ($accIntervals->first()) {
                $begin = $accIntervals->first()['begin'];
                $end = $accIntervals->first()['end'];
            } else {
                $begin = $package->getBegin();
                $end = $package->getEnd();
            }
        } elseif ((!is_null($begin) && is_null($end)) || (!is_null($end) && is_null($begin))) {
            throw new PackageAccommodationException($this->get('translator')
                ->trans('controller.packageController.accommodation_add.passed_only_one_date'));
        } elseif ($begin->getTimestamp() == $end->getTimestamp() || $begin->getTimestamp() > $end->getTimestamp()) {
            throw new PackageAccommodationException($this->get('translator')
                ->trans('controller.packageController.accommodation_add.begin_equal_or_later_end_error'));
        }

        $groupedRooms = $roomRepository->fetchAccommodationRooms($begin, $end, $this->hotel, null, null, null, true);
        $optGroupRooms = $roomRepository->optGroupRooms($groupedRooms);

        $roomTypeName = $package->getRoomType()->getName();
        uksort($optGroupRooms, function ($a) use ($roomTypeName) {
            if ($a == $roomTypeName) {
                return -1;
            }

            return 1;
        });

        if (!$package->getArrivalTime()) {
            $package->setArrivalTime(new \DateTime());
        }

        if (!$package->getDepartureTime()) {
            $package->setDepartureTime(new \DateTime());
        }

        $hasEarlyCheckIn = false;
        $hasLateCheckOut = false;
        foreach ($package->getServices() as $service) {
            $code = $service->getService()->getCode();
            if ($code == 'Early check-in') {
                $hasEarlyCheckIn = true;
            } elseif ($code == 'Late check-out') {
                $hasLateCheckOut = true;
            }
        }

        $order = $this->helper->getWithoutFilter(function() use ($package) {
            /** @var \Doctrine\ODM\MongoDB\Proxy\Proxy $order */
            $order = $package->getOrder();
            $order->__load();

            return $order;
        });

        $form = $this->createForm(PackageAccommodationType::class, $package, [
            'optGroupRooms' => $optGroupRooms,
            'roomType' => $package->getRoomType(),
            'arrivals' => $this->container->getParameter('mbh.package.arrivals'),
            'roomStatusIcons' => $this->container->getParameter('mbh.room_status_icons'),
            'debt' => $order->getPaidStatus() != 'success' && !$package->getIsCheckOut(),
            'hasEarlyCheckIn' => $hasEarlyCheckIn,
            'hasLateCheckOut' => $hasLateCheckOut
        ]);
        $this->dm->getFilterCollection()->enable('softdeleteable');

        $authorizationChecker = $this->container->get('security.authorization_checker');

        $serviceRepository = $this->dm->getRepository('MBHPriceBundle:Service');
        $lateCheckOutService = $serviceRepository->findOneBy(['code' => 'Late check-out']);
        $earlyCheckInService = $serviceRepository->findOneBy(['code' => 'Early check-in']);

        $earlyCheckInServiceIsEnabled = $earlyCheckInService && $lateCheckOutService->getIsEnabled();
        $lateCheckOutServiceIsEnabled = $lateCheckOutService && $earlyCheckInService->getIsEnabled();

        if ($request->getMethod() == 'POST' && !$package->getIsLocked() && $authorizationChecker->isGranted('ROLE_PACKAGE_ACCOMMODATION') && (
                $authorizationChecker->isGranted('ROLE_PACKAGE_EDIT_ALL') ||
                $authorizationChecker->isGranted('EDIT', $package)
            )
        ) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->dm->persist($package);
                $this->dm->flush();

                $request->getSession()->getFlashBag()->set(
                    'success',
                    $this->get('translator')->trans('controller.packageController.placement_saved_success')
                );

                return $this->afterSaveRedirect('package', $package->getId(), [], '_accommodation');
            }
        }

        return [
            'periodBegin' => $begin,
            'periodEnd' => $end,
            'emptyIntervalsAccommodation' => $accIntervals,
            'package' => $package,
            'arrivalTime' => $this->hotel->getPackageArrivalTime(),
            'earlyCheckInServiceIsEnabled' => $earlyCheckInServiceIsEnabled,
            'lateCheckOutServiceIsEnabled' => $lateCheckOutServiceIsEnabled,
            'form' => $form->createView(),
            'logs' => $this->logs($package),
            'optGroupRooms' => $optGroupRooms,
            'facilities' => $this->get('mbh.facility_repository')->getAll(),
            'roomStatusIcons' => $this->container->getParameter('mbh.room_status_icons'),
        ];
    }

    /**
     * Package_delete_modal
     *
     * @param Request $request
     * @param Package $entity
     *
     * @Route("/{id}/modal/package_delete_modal", name="package_delete", options={"expose"=true})
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_PACKAGE_DELETE') and (is_granted('DELETE', id) or is_granted('ROLE_PACKAGE_DELETE_ALL'))")
     * @Template("@MBHPackage/Package/deleteModalContent.html.twig")
     * @return array|JsonResponse|RedirectResponse|Response
     */
    public function deleteModalAction(Request $request, Package $entity)
    {
        if (!empty($entity->getDeletedAt())) {
            throw new \InvalidArgumentException('Package with id "' . $entity->getId() . '" is already deleted!');
        }

        $form = $this->createForm(PackageDeleteReasonType::class, $entity);
        $form->handleRequest($request);

        if (!$this->container->get('mbh.package.permissions')->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->dm->remove($entity);
            $this->dm->flush($entity);

            if (empty($request->query->get('from_chessboard'))) {
                $this->addFlash('success', 'controller.packageController.record_deleted_success');
            }

            return new Response('', 302);
        }

        return [
            'entity' => $entity,
            'controllerName' => 'package_delete',
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/{id}/unlock", name="package_unlock")
     * @Method("GET")
     * @Security("is_granted('ROLE_PACKAGE_UNLOCK')")
     * @ParamConverter("package", class="MBHPackageBundle:Package")
     */
    public function unlockAction(Package $package)
    {
        $package->setIsLocked(false);
        $this->dm->persist($package);
        $this->dm->flush();

        return $this->redirectToRoute('package_edit', ['id' => $package->getId()]);
    }

    /**
     * @Route("/{id}/lock", name="package_lock")
     * @Method("GET")
     * @Security("is_granted('ROLE_PACKAGE_UNLOCK')")
     * @ParamConverter("package", class="MBHPackageBundle:Package")
     */
    public function lockAction(Package $package)
    {
        if ($package->getIsCheckOut()) {
            $package->setIsLocked(true);
            $this->dm->persist($package);
            $this->dm->flush();
        }

        return $this->redirectToRoute('package_edit', ['id' => $package->getId()]);
    }

    /**
     * @param null $id
     * @return JsonResponse
     * @throws \Doctrine\ODM\MongoDB\LockException
     * @Route("/getPackageJsonById/{id}", name="getPackageJsonById", options={"expose"=true})
     */
    public function getPackageJsonByIdAction($id = null)
    {
        if (!$id) {
            return new JsonResponse([]);
        }
        $result = [];
        $package = $this->dm->getRepository('MBHPackageBundle:Package')->find($id);

        if ($package) {
            $result = ['id' => $package->getId(), 'text' => $package->getTitle(true, true)];
        }

        return new JsonResponse($result);
    }


    /**
     * TODO: add Secure
     * @param Request $request
     * @return JsonResponse
     * @Route("/getPackageJsonSearch", name="getPackageJsonSearch", options={"expose"=true})
     */
    public function getPackageJsonSearchAction(Request $request)
    {
        $data = [];
        if (!$request->get('query')) {
            return new JsonResponse([]);
        }
        $packages = $this->dm->getRepository('MBHPackageBundle:Package')->findByOrderOrRoom(
            $request->get('query'),
            $this->helper
        );
        if (!$packages) {
            return new JsonResponse([
                'results' => [[]]
            ]);
        }
        foreach ($packages as $item) {
            /** @var Package $item */
            $data[] = [
                'id' => $item->getId(),
                'text' => $item->getTitle(true, true)
            ];
        }

        return new JsonResponse(['results' => $data]);
    }
}
