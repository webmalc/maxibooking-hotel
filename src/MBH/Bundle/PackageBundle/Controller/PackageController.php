<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Document\RoomRepository;
use MBH\Bundle\PackageBundle\Document\PackageRepository;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Form\OrderTouristType;
use MBH\Bundle\PackageBundle\Form\PackageServiceType;
use MBH\Bundle\PriceBundle\Document\Promotion;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Form\PackageMainType;
use MBH\Bundle\PackageBundle\Form\PackageGuestType;
use MBH\Bundle\PackageBundle\Form\PackageAccommodationType;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use MBH\Bundle\BaseBundle\Controller\DeletableControllerInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
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
     * @Route("/", name="package")
     * @Method("GET")
     * @Security("is_granted('ROLE_PACKAGE_VIEW')")
     * @Template()
     */
    public function indexAction()
    {
        $now = new \DateTime('midnight');
        $tomorrow = new \DateTime('midnight +1 day');

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
            ], $data)
        );
        //begin tomorrow count
        $count['begin_tomorrow'] = $repository->fetch(array_merge([
                'begin' => $tomorrow->format('d.m.Y'),
                'end' => $tomorrow->format('d.m.Y'),
                'dates' => 'begin',
                'checkOut' => false,
                'checkIn' => false
            ], $data)
        );
        //live now count
        $count['live_now'] = $repository->fetch(array_merge([
                'filter' => 'live_now',
                'checkOut' => false,
                'checkIn' => true
            ], $data)
        );
        //without-approval count
        $count['without_approval'] = $repository->fetch(array_merge([
                'confirmed' => '0'
            ], $data)
        );
        //without_accommodation count
        $count['without_accommodation'] = $repository->fetch(array_merge([
                'filter' => 'without_accommodation',
                'end' => $now->format('d.m.Y'),
                'dates' => 'begin'
            ], $data)
        );
        //not_paid count
        $count['not_paid'] = $repository->fetch(array_merge([
                'paid' => 'not_paid'
            ], $data)
        );
        //not_paid time count
        $count['not_paid_time'] = $repository->fetch(array_merge([
                'paid' => 'not_paid',
                'end' => new \DateTime($this->container->getParameter('mbh.package.notpaid.time')),
                'dates' => 'createdAt'
            ], $data)
        );
        //created_by count
        $count['created_by'] = $repository->fetch(array_merge([
                'createdBy' => $this->getUser()->getUsername()
            ], $data)
        );
        //checkIn count
        $count['not_check_in'] = $repository->fetch(array_merge([
                'checkIn' => false,
                'end' => $now->format('d.m.Y'),
                'dates' => 'begin'
            ], $data)
        );

        return [
            'roomTypes' => $this->get('mbh.hotel.selector')->getSelected()->getRoomTypes(),
            'statuses' => $this->container->getParameter('mbh.package.statuses'),
            'count' => $count
        ];
    }

    /**
     * Lists all entities as json.
     *
     * @Route("/json", name="package_json", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_PACKAGE_VIEW')")
     * @Template()
     */
    public function jsonAction(Request $request)
    {
        $this->dm->getFilterCollection()->enable('softdeleteable');

        $data = [
            'hotel' => $this->get('mbh.hotel.selector')->getSelected(),
            'roomType' => $request->get('roomType'),
            'status' => $request->get('status'),
            'deleted' => $request->get('deleted'),
            'begin' => $request->get('begin'),
            'end' => $request->get('end'),
            'dates' => $request->get('dates'),
            'skip' => $request->get('start'),
            'limit' => $request->get('length'),
            'query' => $request->get('search')['value'],
            'order' => $request->get('order')['0']['column'],
            'dir' => $request->get('order')['0']['dir'],
            'paid' => $request->get('paid'),
            'confirmed' => $request->get('confirmed'),
        ];

        //quick links
        switch ($request->get('quick_link')) {
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
                $notPaidTime = new \DateTime($this->container->getParameter('mbh.package.notpaid.time'));
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

        //List user package only
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_PACKAGE_VIEW_ALL')) {
            $data['createdBy'] = $this->getUser()->getUsername();
        }

        $entities = $this->dm->getRepository('MBHPackageBundle:Package')->fetch($data);
        $summary = $this->dm->getRepository('MBHPackageBundle:Package')->fetchSummary($data);

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
     * @Route("/{id}/edit", name="package_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_PACKAGE_VIEW_ALL') or (is_granted('VIEW', package) and is_granted('ROLE_PACKAGE_VIEW'))")
     * @Template()
     * @ParamConverter("entity", class="MBHPackageBundle:Package")
     */
    public function editAction(Package $package)
    {
        if (!$this->container->get('mbh.package.permissions')->checkHotel($package)) {
            throw $this->createNotFoundException();
        }

        /** @var AuthorizationChecker $authorizationChecker */
        $authorizationChecker = $this->get('security.authorization_checker');

        $promotions = [];
        if($authorizationChecker->isGranted('ROLE_PROMOTION_ADD')) {
            /** @var Promotion[] $promotions */
            $promotions = $package->getTariff()->getDefaultPromotion() ?
                [$package->getTariff()->getDefaultPromotion()] :
                iterator_to_array($package->getTariff()->getPromotions())
            ;

            if(!$authorizationChecker->isGranted('ROLE_INDIVIDUAL_PROMOTION_ADD')) {
                $promotions = array_filter($promotions, function($promotion) {
                    /** @var Promotion $promotion */
                    return $promotion->getIsIndividual() === false;
                });
            }
        }

        $form = $this->createForm(new PackageMainType(), $package, [
            'price' => $authorizationChecker->isGranted(['ROLE_ADMIN']),
            'discount' => $authorizationChecker->isGranted('ROLE_DISCOUNT_ADD'),
            'promotion' => $authorizationChecker->isGranted('ROLE_PROMOTION_ADD'),
            'promotions' => $promotions,
            'package' => $package,
            'hotel' => $package->getRoomType()->getHotel(),
            'corrupted' => $package->getCorrupted(),
        ]);

        return [
            'package' => $package,
            'form' => $form->createView(),
            'logs' => $this->logs($package),
            'statuses' => $this->container->getParameter('mbh.package.statuses')
        ];
    }


    /**
     * Edits an existing entity.
     *
     * @Route("/{id}", name="package_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_PACKAGE_EDIT') and (is_granted('EDIT', package) or is_granted('ROLE_PACKAGE_EDIT_ALL'))")
     * @Template("MBHPackageBundle:Package:edit.html.twig")
     * @ParamConverter("package", class="MBHPackageBundle:Package")
     */
    public function updateAction(Request $request, Package $package)
    {
        if (!$this->container->get('mbh.package.permissions')->checkHotel($package)) {
            throw $this->createNotFoundException();
        }

        /** @var AuthorizationChecker $authorizationChecker */
        $authorizationChecker = $this->get('security.authorization_checker');

        $promotions = [];
        if($authorizationChecker->isGranted('ROLE_PROMOTION_ADD')) {
            /** @var Promotion[] $promotions */
            $promotions = $package->getTariff()->getDefaultPromotion() ?
                [$package->getTariff()->getDefaultPromotion()] :
                iterator_to_array($package->getTariff()->getPromotions())
            ;

            if(!$authorizationChecker->isGranted('ROLE_INDIVIDUAL_PROMOTION_ADD')) {
                $promotions = array_filter($promotions, function($promotion) {
                    /** @var Promotion $promotion */
                    return $promotion->getIsIndividual() === false;
                });
            }
        }

        $oldPackage = clone $package;
        $form = $this->createForm(new PackageMainType(), $package, [
            'price' => $this->get('security.authorization_checker')->isGranted(['ROLE_ADMIN']),
            'discount' => $authorizationChecker->isGranted('ROLE_DISCOUNT_ADD'),
            'promotion' => $authorizationChecker->isGranted('ROLE_PROMOTION_ADD'),
            'promotions' => $promotions,
            'package' => $package,
            'hotel' => $package->getRoomType()->getHotel(),
            'corrupted' => $package->getCorrupted()
        ]);

        $form->submit($request);
        if ($form->isValid() && !$package->getIsLocked()) {
            //check by search
            $result = $this->container->get('mbh.order')->updatePackage($oldPackage, $package);
            /** @var FlashBagInterface $flashBag */
            $flashBag = $request->getSession()->getFlashBag();
            if ($result instanceof Package) {
                $this->dm->persist($package);
                $this->dm->flush();

                $flashBag->set('success',
                    $this->get('translator')->trans('controller.packageController.record_edited_success'));

                return $this->afterSaveRedirect('package', $package->getId());
            } else {
                $flashBag->set('danger', $this->get('translator')->trans($result));
            }
        }

        return [
            'package' => $package,
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
     */
    public function newAction(Request $request)
    {
        try {
            $order = null;
            if ($request->get('order')) {
                $order = $this->dm->getRepository('MBHPackageBundle:Order')->find($request->get('order'));
            }
            $order = $this->container->get('mbh.order')->createPackages(['packages' => [
                    [
                        'begin' => $request->get('begin'),
                        'end' => $request->get('end'),
                        'adults' => $request->get('adults'),
                        'children' => $request->get('children'),
                        'roomType' => $request->get('roomType'),
                        'tariff' => $request->get('tariff'),
                        'accommodation' => $request->get('accommodation')
                    ]
                ],
                'status' => 'offline',
                'confirmed' => false,
                'tourist' => $request->get('tourist'),
            ], $order, $this->getUser());
        } catch (\Exception $e) {
            if ($this->container->get('kernel')->getEnvironment() == 'dev') {
                dump($e);
            };

            return [];
        }

        $request->getSession()->getFlashBag()
            ->set('success', $this->get('translator')->trans('controller.packageController.order_created_success'));

        $order->getPayer() ? $route = 'package_order_cash' : $route = 'package_order_tourist_edit';

        return $this->redirect($this->generateUrl($route, [
            'id' => $order->getId(),
            'packageId' => $order->getPackages()[0]->getId()
        ]));
    }

    /**
     * Guests
     *
     * @Route("/{id}/guest", name="package_guest")
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_PACKAGE_VIEW_ALL') or (is_granted('VIEW', package) and is_granted('ROLE_PACKAGE_VIEW'))")
     * @ParamConverter("entity", class="MBHPackageBundle:Package")
     * @Template()
     */
    public function guestAction(Request $request, Package $package)
    {
        if (!$this->container->get('mbh.package.permissions')->checkHotel($package)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new OrderTouristType(), null, ['guest' => false]);

        $authorizationChecker = $this->container->get('security.authorization_checker');
        if ($request->getMethod() == 'PUT' &&
            !$package->getIsLocked() &&
            $authorizationChecker->isGranted('ROLE_PACKAGE_GUESTS') && (
            $authorizationChecker->isGranted('ROLE_PACKAGE_EDIT_ALL') ||
            $authorizationChecker->isGranted('EDIT', $package)
            )
        ) {
            $form->submit($request);

            if ($form->isValid()) {

                $data = $form->getData();
                $tourist = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
                    $data['lastName'], $data['firstName'], $data['patronymic'], $data['birthday'], $data['email'],
                    $data['phone'], $data['communicationLanguage']
                );
                $package->addTourist($tourist);

                $this->dm->persist($package);
                $this->dm->flush();

                $flashBag = $request->getSession()->getFlashBag();
                $flashBag->set('success', $this->get('translator')
                    ->trans('controller.packageController.guest_added_success'));
                if($tourist->getIsUnwelcome()) {
                    $flashBag->set('warning', '<i class="fa fa-user-secret"></i> '.$this->get('translator')
                        ->trans('package.tourist_in_unwelcome'));
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

        $request->getSession()->getFlashBag()->set('success', 'Гость успешно удален.');

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
     * @Method({"GET", "PUT"})
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
            ->setTime($package->getBegin())
            ->setPackage($package);


        $form = $this->createForm(new PackageServiceType(), $packageService, [
            'package' => $package
        ]);

        if ($request->getMethod() == 'PUT' &&
            !$package->getIsLocked() &&
            $this->container->get('security.authorization_checker')->isGranted('ROLE_PACKAGE_SERVICES') && (
                $this->container->get('security.authorization_checker')->isGranted('ROLE_PACKAGE_EDIT_ALL') ||
                $this->container->get('security.authorization_checker')->isGranted('EDIT', $package)
            )
        ) {
            $form->submit($request);

            if ($form->isValid()) {
                $this->dm->persist($packageService);
                $this->dm->flush();

                $request->getSession()->getFlashBag()->set('success',
                    $this->get('translator')->trans('controller.packageController.service_added_success')
                );

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
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_PACKAGE_SERVICES') and (is_granted('EDIT', package) or is_granted('ROLE_PACKAGE_EDIT_ALL'))")
     * @Template("MBHPackageBundle:Package:editService.html.twig")
     * @ParamConverter("entity", class="MBHPackageBundle:Package")
     * @ParamConverter("service", class="MBHPackageBundle:PackageService", options={"id" = "serviceId"})
     * @param Request $request
     * @param Package $package
     * @param PackageService $service
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ODM\MongoDB\LockException
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

        $form = $this->createForm(new PackageServiceType(), $service, ['package' => $package]);

        if ($request->getMethod() == Request::METHOD_PUT) {
            $form->submit($request);
            if ($form->isValid()) {
                $this->dm->persist($service);
                $this->dm->flush();

                $request->getSession()->getFlashBag()->set('success',
                    $this->get('translator')->trans('controller.packageController.service_edit_success')
                );

                return $request->get('save') !== null ?
                    $this->redirectToRoute('package_service_edit',
                         ['id' => $package->getId(), 'serviceId' => $service->getId()]) :
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
     * Accommodation
     *
     * @Route("/{id}/accommodation", name="package_accommodation")
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_PACKAGE_VIEW_ALL') or (is_granted('VIEW', package) and is_granted('ROLE_PACKAGE_VIEW'))")
     * @Template()
     * @param Request $request
     * @param Package $package
     * @return array
     */
    public function accommodationAction(Request $request, Package $package)
    {
        if (!$this->container->get('mbh.package.permissions')->checkHotel($package)) {
            throw $this->createNotFoundException();
        }

        if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->enable('softdeleteable');
        }

        /** @var RoomRepository $roomRepository */
        $roomRepository = $this->dm->getRepository('MBHHotelBundle:Room');
        $groupedRooms = $roomRepository->fetchAccommodationRooms($package->getBegin(), $package->getEnd(),
            $this->hotel, null, null, $package->getId(), true
        );
        $optGroupRooms = $roomRepository->optGroupRooms($groupedRooms);

        $this->dm->getFilterCollection()->enable('softdeleteable');

        if (!$package->getArrivalTime()) {
            $package->setArrivalTime(new \DateTime());
        }

        if (!$package->getDepartureTime()) {
            $package->setDepartureTime(new \DateTime());
        }

        $form = $this->createForm(new PackageAccommodationType(), $package, [
            'optGroupRooms' => $optGroupRooms,
            'roomType' => $package->getRoomType(),
            'arrivals' => $this->container->getParameter('mbh.package.arrivals'),
            'roomStatusIcons' => $this->container->getParameter('mbh.room_status_icons'),
            'debt' => $package->getPaidStatus() != 'success' && !$package->getIsCheckOut()
        ]);

        if ($request->getMethod() == 'PUT' &&
            !$package->getIsLocked() &&
            $this->container->get('security.authorization_checker')->isGranted('ROLE_PACKAGE_ACCOMMODATION') && (
                $this->container->get('security.authorization_checker')->isGranted('ROLE_PACKAGE_EDIT_ALL') ||
                $this->container->get('security.authorization_checker')->isGranted('EDIT', $package)
            )
        ) {
            $form->submit($request);

            if ($form->isValid()) {
                $this->dm->persist($package);
                $this->dm->flush();

                $request->getSession()->getFlashBag()->set('success',
                    $this->get('translator')->trans('controller.packageController.placement_saved_success'));

                return $this->afterSaveRedirect('package', $package->getId(), [], '_accommodation');
            }
        }

        return [
            'package' => $package,
            'form' => $form->createView(),
            'logs' => $this->logs($package)
        ];
    }

    /**
     * Accommodation delete
     *
     * @Route("/{id}/accommodation/delete", name="package_accommodation_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_PACKAGE_ACCOMMODATION') and (is_granted('EDIT', entity) or is_granted('ROLE_PACKAGE_EDIT_ALL'))")
     * @ParamConverter("entity", class="MBHPackageBundle:Package")
     */
    public function accommodationDeleteAction(Request $request, Package $entity)
    {
        if (!$this->container->get('mbh.package.permissions')->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $entity->removeAccommodation();
        $this->dm->persist($entity);
        $this->dm->flush();

        $request->getSession()->getFlashBag()
            ->set('success', $this->get('translator')->trans('controller.packageController.placement_deleted_success'));

        return $this->redirect($this->generateUrl('package_accommodation', ['id' => $entity->getId()]));
    }

    /**
     * Delete entity.
     *
     * @Route("/{id}/delete", name="package_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_PACKAGE_DELETE') and (is_granted('DELETE', entity) or is_granted('ROLE_PACKAGE_DELETE_ALL'))")
     * @ParamConverter("entity", class="MBHPackageBundle:Package")
     */
    public function deleteAction(Request $request, Package $entity)
    {
        if (!$this->container->get('mbh.package.permissions')->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }
        $orderId = $entity->getOrder()->getId();
        $this->dm->remove($entity);
        $this->dm->flush($entity);

        $request->getSession()->getFlashBag()
            ->set('success', $this->get('translator')->trans('controller.packageController.record_deleted_success'));

        if (!empty($request->get('order'))) {
            return $this->redirect($this->generateUrl('package_order_edit', ['id' => $orderId, 'packageId' => $entity->getId()]));
        }

        return $this->redirectToRoute('package');
    }

    /**
     * @Route("/{id}/unlock", name="package_unlock")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
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
     * @Security("is_granted('ROLE_ADMIN')")
     * @ParamConverter("package", class="MBHPackageBundle:Package")
     */
    public function lockAction(Package $package)
    {
        if($package->getIsCheckOut()) {
            $package->setIsLocked(true);
            $this->dm->persist($package);
            $this->dm->flush();
        }
        return $this->redirectToRoute('package_edit', ['id' => $package->getId()]);
    }
}
