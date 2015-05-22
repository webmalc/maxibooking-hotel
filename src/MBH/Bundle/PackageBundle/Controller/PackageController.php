<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Form\PackageDocumentsType;
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

class PackageController extends Controller implements CheckHotelControllerInterface, DeletableControllerInterface
{
    /**
     * Return pdf doc
     *
     * @Route("/{id}/pdf", name="package_pdf")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function pdfAction($id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $entity = $dm->getRepository('MBHPackageBundle:Package')->find($id);

        if (!$entity || !$entity->getIsPaid() || !$this->container->get('mbh.package.permissions')->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $html = $this->renderView('MBHPackageBundle:Package:pdf.html.twig', [
                'entity' => $entity,
                'organization' => $this->container->getParameter('mbh.organization'),
                'strPrice' => $this->get('mbh.helper')->num2str($entity->getPrice())
            ]);

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            200,
            array(
                'Content-Type'          => 'application/pdf',
                'Content-Disposition'   => 'attachment; filename="act_' . $entity->getNumberWithPrefix() . '.pdf"'
            )
        );
    }

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
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $now = new \DateTime('midnight');
        $tomorrow = new \DateTime('midnight +1 day');

        $data['deleted'] = false;

        //begin today count
        $data = [
            'count' => true,
            'hotel' => $this->get('mbh.hotel.selector')->getSelected()
        ];
        $repo = $dm->getRepository('MBHPackageBundle:Package');

        $count['begin_today'] = $repo->fetch(array_merge([
                'begin' => $now->format('d.m.Y'),
                'end' => $now->format('d.m.Y'),
                'dates' => 'begin'
            ], $data)
        );
        //begin tomorrow count
        $count['begin_tomorrow'] = $repo->fetch(array_merge([
                    'begin' => $tomorrow->format('d.m.Y'),
                    'end' => $tomorrow->format('d.m.Y'),
                    'dates' => 'begin'
                ], $data)
        );
        //live now count
        $count['live_now'] = $repo->fetch(array_merge([
                    'filter' => 'live_now'
                ], $data)
        );
        //without-approval count
        $count['without_approval'] = $repo->fetch(array_merge([
                    'confirmed' => '0'
                ], $data)
        );
        //without_accommodation count
        $count['without_accommodation'] = $repo->fetch(array_merge([
                    'filter' => 'without_accommodation'
                ], $data)
        );
        //not_paid count
        $count['not_paid'] = $repo->fetch(array_merge([
                    'paid' => 'not_paid'
                ], $data)
        );
        //not_paid time count
        $count['not_paid_time'] = $repo->fetch(array_merge([
            'paid' => 'not_paid',
            'end' => new \DateTime($this->container->getParameter('mbh.package.notpaid.time')),
            'dates' => 'createdAt'
                ], $data)
        );
        //created_by count
        $count['created_by'] = $repo->fetch(array_merge([
                    'created_by' => $this->getUser()->getUsername()
                ], $data)
        );
        //checkIn count
        $count['not_check_in'] = $repo->fetch(array_merge([
                'checkIn' => false,
                'begin' => $now->format('d.m.Y'),
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
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $dm->getFilterCollection()->enable('softdeleteable');

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
        switch ($request->get('quick_link')){
            case 'begin-today':
                $data['dates'] = 'begin';
                $now = new \DateTime('midnight');
                $data['begin'] = $now->format('d.m.Y');
                $data['end'] = $now->format('d.m.Y');
                break;

            case 'begin-tomorrow':
                $data['dates'] = 'begin';
                $now = new \DateTime('midnight');
                $now->modify('+1 day');
                $data['begin'] = $now->format('d.m.Y');
                $data['end'] = $now->format('d.m.Y');
                break;

            case 'live-now':
                $data['filter'] = 'live_now';
                break;

            case 'without-approval':
                $data['confirmed'] = '0';
                break;

            case 'without-accommodation':
                $data['filter'] = 'without_accommodation';
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
                $data['begin'] = $now->format('d.m.Y');
                $data['end'] = $now->format('d.m.Y');
                break;

            case 'created-by':
                $data['createdBy'] = $this->getUser()->getUsername();
                break;
            default:
        }

        $entities = $dm->getRepository('MBHPackageBundle:Package')->fetch($data);

        return [
            'entities' => $entities,
            'total' => $entities->count(),
            'draw' => $request->get('draw'),
            'statuses' => $this->container->getParameter('mbh.package.statuses')
        ];
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="package_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function editAction($id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHPackageBundle:Package')->find($id);

        if (!$entity || !$this->container->get('mbh.package.permissions')->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(
            new PackageMainType(),
            $entity,
            [
                'arrivals' => $this->container->getParameter('mbh.package.arrivals'),
                'defaultTime' => $this->container->getParameter('mbh.package.arrival.time'),
                'price' => $this->get('security.context')->isGranted(['ROLE_BOOKKEEPER', 'ROLE_SENIOR_MANAGER']),
                'hotel' => $entity->getRoomType()->getHotel()
            ]
        );

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
     */
    public function updateAction(Request $request, $id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHPackageBundle:Package')->find($id);

        if (!$entity || !$this->container->get('mbh.package.permissions')->check($entity) || !$this->container->get('mbh.package.permissions')->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $oldPackage = clone $entity;
        $form = $this->createForm(
            new PackageMainType(),
            $entity,
            [
                'arrivals' => $this->container->getParameter('mbh.package.arrivals'),
                'price' => $this->get('security.context')->isGranted(['ROLE_BOOKKEEPER']),
                'hotel' => $entity->getRoomType()->getHotel()
            ]
        );

        $form->submit($request);
        if ($form->isValid()) {

            //check by search
            $result = $this->container->get('mbh.order')->updatePackage($oldPackage, $entity);
            if ($result instanceof Package) {
                /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
                $dm = $this->get('doctrine_mongodb')->getManager();
                $dm->persist($entity);
                $dm->flush();

                $request->getSession()->getFlashBag()
                    ->set('success', $this->get('translator')->trans('controller.packageController.record_edited_success'));

                return $this->afterSaveRedirect('package', $entity->getId());
            } else {
                $request->getSession()->getFlashBag()
                    ->set('danger', $this->get('translator')->trans($result));
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
                /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
                $dm = $this->get('doctrine_mongodb')->getManager();
                $order = $dm->getRepository('MBHPackageBundle:Order')->find($request->get('order'));
            }
            $order = $this->container->get('mbh.order')->createPackages([
                'packages' => [[
                    'begin' => $request->get('begin'),
                    'end' => $request->get('end'),
                    'adults' => $request->get('adults'),
                    'children' => $request->get('children'),
                    'roomType' => $request->get('roomType'),
                    'tariff' => $request->get('tariff'),
                ]],
                'status' => 'offline',
                'confirmed' => true
            ], $order, $this->getUser());
        } catch (\Exception $e) {
            if ($this->container->get('kernel')->getEnvironment() == 'dev') {
                var_dump($e);
            };
            return [];
        }

        $request->getSession()
            ->getFlashBag()
            ->set('success', $this->get('translator')->trans('controller.packageController.order_created_success'));

        return $this->redirect($this->generateUrl('package_edit', [
            'id' => $order->getPackages()[0]->getId()
        ]));
    }

    /**
     * Guests
     *
     * @Route("/{id}/guest", name="package_guest")
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function guestAction(Request $request, $id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHPackageBundle:Package')->find($id);

        if (!$entity || !$this->container->get('mbh.package.permissions')->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(
            new PackageGuestType()
        );

        if ($request->getMethod() == 'PUT'  && $this->container->get('mbh.package.permissions')->check($entity)) {
            $form->bind($request);

            if ($form->isValid()) {

                $data = $form->getData();
                $criteria = [
                    'firstName' => $data['firstName'],
                    'lastName' => $data['lastName']
                ];
                if (!empty($data['patronymic'])) {
                    $criteria['patronymic'] = $data['patronymic'];
                }
                if (!empty($data['birthday'])) {
                    $criteria['birthday'] = $data['birthday'];
                }

                $tourist = $dm->getRepository('MBHPackageBundle:Tourist')->findOneBy($criteria);

                if (empty($tourist)) {
                    $tourist = new Tourist();
                    $tourist->setFirstName($data['firstName'])
                        ->setLastName($data['lastName'])
                        ->setPatronymic($data['patronymic'])
                        ->setBirthday($data['birthday']);
                    $dm->persist($tourist);
                }

                $entity->addTourist($tourist);

                $dm->persist($entity);
                $dm->flush();

                $request->getSession()
                    ->getFlashBag()
                    ->set('success', $this->get('translator')->trans('controller.packageController.guest_added_success'));

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
     */
    public function guestDeleteAction(Request $request, $id, $touristId)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHPackageBundle:Package')->find($id);
        $tourist = $dm->getRepository('MBHPackageBundle:Tourist')->find($touristId);

        if (!$entity || !$tourist  || !$this->container->get('mbh.package.permissions')->check($entity) || !$this->container->get('mbh.package.permissions')->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $entity->removeTourist($tourist);
        $dm->persist($entity);
        $dm->flush();

        $request->getSession()
            ->getFlashBag()
            ->set('success', 'Гость успешно удален.');

        return $this->redirect($this->generateUrl('package_guest', ['id' => $id]));
    }

    /**
     * Services
     *
     * @Route("/{id}/services", name="package_service")
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function serviceAction(Request $request, $id)
    {

        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHPackageBundle:Package')->find($id);

        if (!$entity || !$this->container->get('mbh.package.permissions')->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $packageService = new PackageService();

        if ($this->container->get('mbh.package.permissions')->check($entity)) {
            $form = $this->createForm(
                new PackageServiceType(),
                $packageService,
                ['package' => $entity, 'form_label' => $this->container->get('translator')->trans('form.packageServiceType.add_service')]
            );
        }
        $time = [];
        foreach($entity->getServices() as $s) {
            $serv =  $dm->getRepository('MBHPriceBundle:Service')->find($s->getService()->getId());
            $time[] = $serv->getTime();
        }
        if ($request->getMethod() == 'PUT') {

            $packageService->setPackage($entity);
            $packageService->setService($dm->getRepository('MBHPriceBundle:Service')->find($request->get("mbh_bundle_packagebundle_package_service_type")["service"] ));

            $form->submit($request);

            if ($form->isValid()) {
                $data = $form->getData();

                if (!$packageService->getService() || ($form->get('amount')->getData() == '')) {
                    $request->getSession()
                        ->getFlashBag()
                        ->set(
                            'danger',
                            $this->get('translator')->trans('controller.packageController.service_adding_error_refresh_and_try_again')
                        );

                    return $this->redirect($this->generateUrl('package_service', ['id' => $id]));
                }
                if ($packageService->getService()->getDate() == false){
                    $date = date_create($request->get('mbh_bundle_packagebundle_package_service_type')['time']);
                    $packageService->setBegin($date->getTimestamp());
                } else {
                    $date = date_create($request->get('mbh_bundle_packagebundle_package_service_type')['begin'] . ' ' . $request->get('mbh_bundle_packagebundle_package_service_type')['time']);
                    $packageService->setBegin($date->getTimestamp());
                }
                $dm->persist($packageService);
                $dm->flush();

                $request->getSession()
                    ->getFlashBag()
                    ->set(
                        'success',
                        $this->get('translator')->trans('controller.packageController.service_added_success')
                    );

                return $this->afterSaveRedirect('package', $id, [], '_service');
            }
        }

        return [
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'form' => $form->createView(),
            'config' => $this->container->getParameter('mbh.services'),
            'time' => $time
        ];
    }

    /**
     * Service document delete
     *
     * @Route("/{id}/service/{serviceId}/delete", name="package_service_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     */
    public function serviceDeleteAction(Request $request, $id, $serviceId)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHPackageBundle:Package')->find($id);
        $service = $dm->getRepository('MBHPackageBundle:PackageService')->find($serviceId);

        if (!$entity || !$service || !$this->container->get('mbh.package.permissions')->check($entity) || !$this->container->get('mbh.package.permissions')->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $dm->remove($service);
        $dm->flush();

        $request->getSession()
            ->getFlashBag()
            ->set('success', $this->get('translator')->trans('controller.packageController.service_deleted_success'));

        return $this->redirect($this->generateUrl('package_service', ['id' => $id]));
    }
    /**
     * Service document edit
     *
     * @Route("/{id}/service/{serviceId}/edit", name="package_service_edit", options={"expose"=true})
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_USER')")
     * @Template("MBHPackageBundle:Package:editService.html.twig")
     */
    public function serviceEditAction(Request $request, $id, $serviceId)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHPackageBundle:Package')->find($id);

        if (!$entity || !$this->container->get('mbh.package.permissions')->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $service = $dm->getRepository('MBHPackageBundle:PackageService')->find($serviceId);
        if (!$service){
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(
            new PackageServiceType(),
            $service,
            ['package' => $entity, 'serviceId' => $service->getService()->getId(), 'form_label' => $this->container->get('translator')->trans('form.packageServiceType.edit_service'),
                'time' => $service->getBegin()->format('H:i')]
        );

        if ($request->getMethod() == 'PUT' && $this->container->get('mbh.package.permissions')->check($entity)) {
            $form->submit($request);

            if ($form->isValid()) {
                $data = $form->getData();
                $service->setService($dm->getRepository('MBHPriceBundle:Service')->find($request->get("mbh_bundle_packagebundle_package_service_type")["service"] ));
                $date = date_create($request->get('mbh_bundle_packagebundle_package_service_type')['begin'] . ' ' . $request->get('mbh_bundle_packagebundle_package_service_type')['time']);
                $service->setBegin($date->getTimestamp());
                $dm->persist($service);
                $dm->flush();

                $request->getSession()
                    ->getFlashBag()
                    ->set(
                        'success',
                        $this->get('translator')->trans('controller.packageController.service_added_success')
                    );

                if ($request->get('save') !== null) {

                    return $this->redirect($this->generateUrl('package_service_edit', ['id' => $id, 'serviceId' => $serviceId]));
                }

                return $this->redirect($this->generateUrl('package'));

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
     * Accommodation check-in
     *
     * @Route("/{id}/accommodation/check_in", name="package_check_in")
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_USER')")
     * @ParamConverter("order", class="MBHPackageBundle:Package")
     * @Template()
     * @param Request $request
     * @param Package $doc
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function checkInAction(Request $request, Package $doc)
    {
        if (!$this->container->get('mbh.package.permissions')->checkHotel($doc) || empty($doc->getAccommodation())) {
            throw $this->createNotFoundException();
        }

        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $doc->setIsCheckIn(true);
        $dm->persist($doc);
        $dm->flush();

        $request->getSession()
            ->getFlashBag()
            ->set('success', 'Гости заехали.');

        return $this->redirect($this->generateUrl('package_accommodation', ['id' => $doc->getId()]));
    }

    /**
     * Accommodation
     *
     * @Route("/{id}/accommodation", name="package_accommodation")
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function accommodationAction(Request $request, Package $entity)
    {
        if (!$this->container->get('mbh.package.permissions')->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }
        $hotel = $this->get('mbh.hotel.selector')->getSelected();

        $groupedRooms = $this->dm->getRepository('MBHHotelBundle:Room')->fetchAccommodationRooms(
            $entity->getBegin(), $entity->getEnd(), $hotel, null, null, null, true
        );

        $form = $this->createForm(
            new PackageAccommodationType(),
            $entity,
            [
                'rooms' => $groupedRooms,
                'isHostel' => $hotel->getIsHostel(),
                'roomType' => $entity->getRoomType(),
            ]);

        if ($request->getMethod() == 'PUT'  && $this->container->get('mbh.package.permissions')->check($entity)) {
            $form->submit($request);

            if ($form->isValid()) {
                $this->dm->persist($entity);
                $this->dm->flush();

                $request->getSession()
                    ->getFlashBag()
                    ->set('success', $this->get('translator')->trans('controller.packageController.placement_saved_success'));

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
     */
    public function accommodationDeleteAction(Request $request, $id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHPackageBundle:Package')->find($id);

        if (!$entity || !$this->container->get('mbh.package.permissions')->check($entity) || !$this->container->get('mbh.package.permissions')->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }
        $entity->removeAccommodation();
        $dm->persist($entity);
        $dm->flush();

        $request->getSession()
            ->getFlashBag()
            ->set('success', $this->get('translator')->trans('controller.packageController.placement_deleted_success'));

        return $this->redirect($this->generateUrl('package_accommodation', ['id' => $id]));
    }

    /**
     * Delete entity.
     *
     * @Route("/{id}/delete", name="package_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     */
    public function deleteAction($id, Request $request)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $entity = $dm->getRepository('MBHPackageBundle:Package')->find($id);

        if (!$entity || !$this->container->get('mbh.package.permissions')->check($entity) || !$this->container->get('mbh.package.permissions')->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }
        $orderId = $entity->getOrder()->getId();
        $dm->remove($entity);
        $dm->flush($entity);

        $request
            ->getSession()
            ->getFlashBag()
            ->set('success', $this->get('translator')->trans('controller.packageController.record_deleted_success'));

        if (!empty($request->get('order'))) {
            return $this->redirect($this->generateUrl('package_order_packages', ['id' => $orderId]));
        }

        return $this->redirect($this->generateUrl('package'));

        return $response;
    }

}
