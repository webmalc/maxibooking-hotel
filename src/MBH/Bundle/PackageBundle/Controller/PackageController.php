<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Document\RoomRepository;
use MBH\Bundle\PackageBundle\Document\PackageRepository;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Form\OrderTouristType;
use MBH\Bundle\PackageBundle\Form\PackageServiceType;
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
     * @Security("is_granted('ROLE_USER')")
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
     * @Security("is_granted('ROLE_USER')")
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
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     * @ParamConverter("entity", class="MBHPackageBundle:Package")
     */
    public function editAction(Package $entity)
    {
        if (!$this->container->get('mbh.package.permissions')->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new PackageMainType(), $entity, [
            'price' => $this->get('security.authorization_checker')->isGranted([
                'ROLE_ADMIN'
            ]),
            'hotel' => $entity->getRoomType()->getHotel(),
            'corrupted' => $entity->getCorrupted()
        ]);

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity),
            'statuses' => $this->container->getParameter('mbh.package.statuses')
        ];
    }


    /**
     * Edits an existing entity.
     *
     * @Route("/{id}", name="package_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_USER')")
     * @Template("MBHPackageBundle:Package:edit.html.twig")
     * @ParamConverter("entity", class="MBHPackageBundle:Package")
     */
    public function updateAction(Request $request, Package $entity)
    {
        if (!$this->container->get('mbh.package.permissions')->check($entity) || !$this->container->get('mbh.package.permissions')->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $oldPackage = clone $entity;
        $form = $this->createForm(new PackageMainType(), $entity, [
            'price' => $this->get('security.authorization_checker')->isGranted(['ROLE_ADMIN']),
            'hotel' => $entity->getRoomType()->getHotel(),
            'corrupted' => $entity->getCorrupted()
        ]);

        $form->submit($request);
        if ($form->isValid()) {
            //check by search
            $result = $this->container->get('mbh.order')->updatePackage($oldPackage, $entity);
            if ($result instanceof Package) {
                $this->dm->persist($entity);
                $this->dm->flush();

                $request->getSession()->getFlashBag()->set('success',
                    $this->get('translator')->trans('controller.packageController.record_edited_success'));

                return $this->afterSaveRedirect('package', $entity->getId());
            } else {
                $request->getSession()->getFlashBag()->set('danger', $this->get('translator')->trans($result));
            }
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity),
            'statuses' => $this->container->getParameter('mbh.package.statuses')
        ];
    }

    /**
     * Create new entity
     *
     * @Route("/new", name="package_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function newAction(Request $request)
    {
        try {
            $order = null;
            if ($request->get('order')) {
                $order = $this->dm->getRepository('MBHPackageBundle:Order')->find($request->get('order'));
            }
            $order = $this->container->get('mbh.order')->createPackages([
                'packages' => [
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
                'confirmed' => true,
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
     * @Security("is_granted('ROLE_USER')")
     * @ParamConverter("entity", class="MBHPackageBundle:Package")
     * @Template()
     */
    public function guestAction(Request $request, Package $entity)
    {
        if (!$this->container->get('mbh.package.permissions')->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new OrderTouristType(), null, ['guest' => false]);

        if ($request->getMethod() == 'PUT' && $this->container->get('mbh.package.permissions')->check($entity)) {
            $form->submit($request);

            if ($form->isValid()) {

                $data = $form->getData();
                $tourist = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
                    $data['lastName'], $data['firstName'], $data['patronymic'], $data['birthday'], $data['email'],
                    $data['phone']
                );
                $entity->addTourist($tourist);

                $this->dm->persist($entity);
                $this->dm->flush();

                $request->getSession()->getFlashBag()->set('success',
                    $this->get('translator')->trans('controller.packageController.guest_added_success'));

                return $this->afterSaveRedirect('package', $entity->getId(), [], '_guest');
            }
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * Guests delete
     *
     * @Route("/{id}/guest/{touristId}/delete", name="package_guest_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @ParamConverter("entity", class="MBHPackageBundle:Package")
     * @ParamConverter("tourist", class="MBHPackageBundle:Tourist", options={"id" = "touristId"})
     */
    public function guestDeleteAction(Request $request, Package $entity, Tourist $tourist)
    {
        if (!$this->container->get('mbh.package.permissions')->check($entity) || !$this->container->get('mbh.package.permissions')->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }
        $entity->removeTourist($tourist);

        $this->dm->persist($tourist);
        $this->dm->persist($entity);
        $this->dm->flush();

        $request->getSession()->getFlashBag()->set('success', 'Гость успешно удален.');

        return $this->redirectToRoute('package_guest', ['id' => $entity->getId()]);
    }

    /**
     * Services
     *
     * @param Request $request
     * @param Package $entity
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ODM\MongoDB\LockException
     * @Route("/{id}/services", name="package_service")
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     * @ParamConverter("entity", class="MBHPackageBundle:Package")
     */
    public function serviceAction(Request $request, Package $entity)
    {
        if (!$this->container->get('mbh.package.permissions')->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $packageService = new PackageService();
        $packageService
            ->setBegin($entity->getBegin())
            ->setTime($entity->getBegin())
            ->setPackage($entity);


        $form = $this->createForm(new PackageServiceType(), $packageService, [
            'package' => $entity
        ]);

        if ($request->getMethod() == 'PUT' && $this->container->get('mbh.package.permissions')->check($entity)) {
            $form->submit($request);

            if ($form->isValid()) {
                $this->dm->persist($packageService);
                $this->dm->flush();

                $request->getSession()->getFlashBag()->set('success',
                    $this->get('translator')->trans('controller.packageController.service_added_success')
                );

                return $this->afterSaveRedirect('package', $entity->getId(), [], '_service');
            }
        }

        return [
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'form' => $form->createView(),
            'config' => $this->container->getParameter('mbh.services'),
        ];
    }

    /**
     * Service document edit
     *
     * @Route("/{id}/service/{serviceId}/edit", name="package_service_edit", options={"expose"=true})
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_USER')")
     * @Template("MBHPackageBundle:Package:editService.html.twig")
     * @ParamConverter("entity", class="MBHPackageBundle:Package")
     * @ParamConverter("service", class="MBHPackageBundle:PackageService", options={"id" = "serviceId"})
     * @param Request $request
     * @param Package $entity
     * @param PackageService $service
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ODM\MongoDB\LockException
     *
     */
    public function serviceEditAction(Request $request, Package $entity, PackageService $service)
    {
        if (!$this->container->get('mbh.package.permissions')->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        if (!$service->getTime()) {
            $service->setTime($entity->getBegin());
        }

        $form = $this->createForm(new PackageServiceType(), $service, ['package' => $entity]);

        if ($request->getMethod() == Request::METHOD_PUT && $this->container->get('mbh.package.permissions')->check($entity)) {
            $form->submit($request);
            if ($form->isValid()) {
                $this->dm->persist($service);
                $this->dm->flush();

                $request->getSession()->getFlashBag()->set('success',
                    $this->get('translator')->trans('controller.packageController.service_edit_success')
                );

                return $request->get('save') !== null ?
                    $this->redirectToRoute('package_service_edit',
                         ['id' => $entity->getId(), 'serviceId' => $service->getId()]) :
                    $this->redirectToRoute('package_service', ['id' => $entity->getId()]);
            }
        }

        return [
            'entity' => $entity,
            'service' => $service,
            'logs' => $this->logs($entity),
            'form' => $form->createView(),
            'config' => $this->container->getParameter('mbh.services'),
        ];
    }

    /**
     * Service document delete
     *
     * @Route("/{id}/service/{serviceId}/delete", name="package_service_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @ParamConverter("entity", class="MBHPackageBundle:Package")
     * @ParamConverter("service", class="MBHPackageBundle:PackageService", options={"id" = "serviceId"})
     */
    public function serviceDeleteAction(Request $request, Package $entity, PackageService $service)
    {
        if (!$this->container->get('mbh.package.permissions')->check($entity) || !$this->container->get('mbh.package.permissions')->checkHotel($entity)) {
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
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     * @param Request $request
     * @param Package $entity
     * @return array
     */
    public function accommodationAction(Request $request, Package $entity)
    {
        if (!$this->container->get('mbh.package.permissions')->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->enable('softdeleteable');
        }

        /** @var RoomRepository $roomRepository */
        $roomRepository = $this->dm->getRepository('MBHHotelBundle:Room');
        $groupedRooms = $roomRepository->fetchAccommodationRooms($entity->getBegin(), $entity->getEnd(),
            $this->hotel, null, null, $entity->getId(), true
        );
        $optGroupRooms = $roomRepository->optGroupRooms($groupedRooms);

        $this->dm->getFilterCollection()->enable('softdeleteable');

        if (!$entity->getArrivalTime()) {
            $entity->setArrivalTime(new \DateTime());
        }

        if (!$entity->getDepartureTime()) {
            $entity->setDepartureTime(new \DateTime());
        }

        $form = $this->createForm(new PackageAccommodationType(), $entity, [
            'optGroupRooms' => $optGroupRooms,
            'roomType' => $entity->getRoomType(),
            'arrivals' => $this->container->getParameter('mbh.package.arrivals'),
        ]);

        if ($request->getMethod() == 'PUT' && $this->container->get('mbh.package.permissions')->check($entity)) {
            $form->submit($request);

            if ($form->isValid()) {
                $this->dm->persist($entity);
                $this->dm->flush();

                $request->getSession()->getFlashBag()->set('success',
                    $this->get('translator')->trans('controller.packageController.placement_saved_success'));

                return $this->afterSaveRedirect('package', $entity->getId(), [], '_accommodation');
            }
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * Accommodation delete
     *
     * @Route("/{id}/accommodation/delete", name="package_accommodation_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @ParamConverter("entity", class="MBHPackageBundle:Package")
     */
    public function accommodationDeleteAction(Request $request, Package $entity)
    {
        if (!$this->container->get('mbh.package.permissions')->check($entity) || !$this->container->get('mbh.package.permissions')->checkHotel($entity)) {
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
     * @Security("is_granted('ROLE_USER')")
     * @ParamConverter("entity", class="MBHPackageBundle:Package")
     */
    public function deleteAction(Request $request, Package $entity)
    {
        if (!$this->container->get('mbh.package.permissions')->check($entity) || !$this->container->get('mbh.package.permissions')->checkHotel($entity)) {
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

}
