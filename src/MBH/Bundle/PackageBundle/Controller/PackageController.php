<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Form\PackageServiceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Form\PackageMainType;
use MBH\Bundle\PackageBundle\Form\PackageGuestType;
use MBH\Bundle\PackageBundle\Form\PackageCopyType;
use MBH\Bundle\PackageBundle\Form\PackageAccommodationType;
use MBH\Bundle\CashBundle\Form\CashDocumentType;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\PackageBundle\Document\Tourist;

class PackageController extends Controller implements CheckHotelControllerInterface
{

    /**
     * Copy data from one package to another package
     *
     * @Route("/{id}/copy", name="package_copy")
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function copyAction(Request $request, $id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $dm->getFilterCollection()->disable('softdeleteable');

        $entity = $dm->getRepository('MBHPackageBundle:Package')->find($id);

        $dm->getFilterCollection()->enable('softdeleteable');

        if (!$entity || !$entity->getDeletedAt()) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(
            new PackageCopyType()
        );

        if ($request->getMethod() == 'PUT') {
            $form->submit($request);

            if ($form->isValid()) {
                $data = $form->getData();

                $package = $data['package'];

                //copy tourists & main tourist
                if ($data['tourists']) {
                    foreach($entity->getTourists() as $tourist) {
                        $package->addTourist($tourist);
                        if ($entity->getMainTourist() && empty($package->getMainTourist())) {
                            $package->setMainTourist($entity->getMainTourist());
                        }
                    }
                }
                //copy services
                if ($data['services']) {
                    foreach ($entity->getServices() as $service) {
                        $newService = new PackageService();
                        $newService->setPackage($package)
                            ->setAmount($service->getAmount())
                            ->setPrice($service->getPrice())
                            ->setService($service->getService())
                        ;
                        $dm->persist($newService);
                    }
                    $dm->flush();
                }

                //copy cash
                if ($data['cash'] && $entity->getPaid()) {
                    $in = new CashDocument();
                    $in->setPackage($package)
                        ->setIsConfirmed(false)
                        ->setMethod('cash')
                        ->setOperation('in')
                        ->setNote('Перенос с брони ' . $entity->getNumberWithPrefix())
                        ->setPrefix($entity->getNumberWithPrefix())
                        ->setTotal($entity->getPaid())
                        ;
                        $dm->persist($in);

                    $out = new CashDocument();
                    $out->setPackage($entity)
                        ->setIsConfirmed(false)
                        ->setMethod('cash')
                        ->setOperation('out')
                        ->setNote('Перенос на бронь ' . $package->getNumberWithPrefix())
                        ->setPrefix($entity->getNumberWithPrefix())
                        ->setTotal($entity->getPaid())
                    ;
                    $dm->persist($out);

                    $dm->flush();
                }

                //copy accommodation
                if ($data['accommodation'] && $entity->getAccommodation()) {

                    $qb = $dm->getRepository('MBHPackageBundle:Package')->createQueryBuilder('q');
                    $qb->field('accommodation')->notEqual(null)
                        ->addOr(
                            $qb->expr()
                                ->field('begin')->gte($package->getBegin())
                                ->field('begin')->lt($entity->getEnd())
                        )
                        ->addOr(
                            $qb->expr()
                                ->field('end')->gt($package->getBegin())
                                ->field('end')->lte($package->getEnd())
                        )
                        ->addOr(
                            $qb->expr()
                                ->field('end')->gte($package->getEnd())
                                ->field('begin')->lte($package->getBegin())
                        );

                    $notIds = [];
                    foreach ($qb->getQuery()->execute() as $accommodationPackage) {
                        $notIds[] = $accommodationPackage->getAccommodation()->getId();
                    };
                    if (in_array($entity->getAccommodation()->getId(), $notIds)) {
                        $request->getSession()
                            ->getFlashBag()
                            ->set('danger', 'Размещение не перенесено. Номер уже занят.');
                    } else {
                        $package->setAccommodation($entity->getAccommodation());
                    }
                }

                $dm->persist($package);
                $dm->flush();

                $request->getSession()
                    ->getFlashBag()
                    ->set('success', 'Данные успешно пересены.');

                return $this->afterSaveRedirect('package', $entity->getId(), [], '_copy');
            }
        }

        return [
            'entity' => $entity,
            'logs'   => $this->logs($entity),
            'form'   => $form->createView()
        ];
    }

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
        $dm->getFilterCollection()->disable('softdeleteable');

        $entity = $dm->getRepository('MBHPackageBundle:Package')->find($id);

        $dm->getFilterCollection()->enable('softdeleteable');

        if (!$entity || !$entity->getIsPaid()) {
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
        return [
            'roomTypes' => $this->get('mbh.hotel.selector')->getSelected()->getRoomTypes(),
            'statuses' => $this->container->getParameter('mbh.package.statuses')
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

        $entities = $dm->getRepository('MBHPackageBundle:Package')->fetch(
            [
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
            ]
        );

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
        $dm->getFilterCollection()->disable('softdeleteable');

        $entity = $dm->getRepository('MBHPackageBundle:Package')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(
            new PackageMainType(),
            $entity,
            [
                'arrivals' => $this->container->getParameter('mbh.package.arrivals'),
                'defaultTime' => $this->container->getParameter('mbh.package.arrival.time')
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

        if (!$entity) {
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(
            new PackageMainType(),
            $entity,
            [
                'arrivals' => $this->container->getParameter('mbh.package.arrivals')
            ]
        );

        $form->bind($request);

        if ($form->isValid()) {
            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entity);
            $dm->flush();

            $this->getRequest()->getSession()->getFlashBag()
                ->set('success', 'Запись успешно отредактирована.');

            return $this->afterSaveRedirect('package', $entity->getId());
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
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        if (!$request->get('begin') ||
            !$request->get('end') ||
            !$request->get('adults') === null ||
            !$request->get('children') === null ||
            !$request->get('roomType') ||
            !$request->get('food')
        ) {
            return [];
        }

        //Set query
        $query = new SearchQuery();
        $query->begin = \DateTime::createFromFormat('d.m.Y H:i:s', $request->get('begin') . ' 00:00:00');
        $query->end = \DateTime::createFromFormat('d.m.Y H:i:s', $request->get('end') . ' 00:00:00');
        $query->adults = (int)$request->get('adults');
        $query->children = (int)$request->get('children');
        if (!empty($request->get('tariff'))) {
            $query->tariff = $request->get('tariff');
        }
        $query->addRoomType($request->get('roomType'));

        $results = $this->get('mbh.package.search')->search($query);

        if (count($results) != 1) {
            return [];
        }

        $package = new Package();
        $package->setBegin($results[0]->getBegin())
            ->setEnd($results[0]->getEnd())
            ->setAdults($results[0]->getAdults())
            ->setChildren($results[0]->getChildren())
            ->setTariff($results[0]->getTariff())
            ->setStatus('offline')
            ->setRoomType($results[0]->getRoomType())
            ->setFood($request->get('food'))
            ->setPaid(0)
            ->setPrice(
                $results[0]->getPrice($package->getFood(), $results[0]->getAdults(), $results[0]->getChildren())
            );

        $errors = $this->get('validator')->validate($package);

        if (count($errors)) {
            return [];
        }

        $dm->persist($package);
        $dm->flush();

        $request->getSession()
            ->getFlashBag()
            ->set('success', 'Бронь успешно создана.');

        return $this->redirect($this->generateUrl('package_edit', ['id' => $package->getId()]));
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

        $dm->getFilterCollection()->disable('softdeleteable');
        $entity = $dm->getRepository('MBHPackageBundle:Package')->find($id);
        $dm->getFilterCollection()->enable('softdeleteable');

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(
            new PackageGuestType()
        );

        if ($request->getMethod() == 'PUT') {
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

                if ($data['main']) {
                    $entity->setMainTourist($tourist);
                }

                $dm->persist($entity);
                $dm->flush();

                $request->getSession()
                    ->getFlashBag()
                    ->set('success', 'Гость успешно добавлен.');

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

        if (!$entity || !$tourist) {
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
     * Cash documents
     *
     * @Route("/{id}/cash", name="package_cash")
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function cashAction(Request $request, $id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $dm->getFilterCollection()->disable('softdeleteable');
        /* @var $entity  Package */
        $entity = $dm->getRepository('MBHPackageBundle:Package')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $cashes = $dm->getRepository('MBHCashBundle:CashDocument')->findBy(['package.id' => $entity->getId()]);
        $dm->getFilterCollection()->enable('softdeleteable');

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $cash = new CashDocument();
        $cash->setPackage($entity);

        $form = $this->createForm(
            new CashDocumentType(),
            $cash,
            [
                'methods' => $this->container->getParameter('mbh.cash.methods'),
                'operations' => $this->container->getParameter('mbh.cash.operations'),
                'groupName' => 'Добавить кассовый документ'
            ]
        );

        if ($request->getMethod() == 'PUT') {
            $form->bind($request);

            if ($form->isValid()) {
                $dm->persist($cash);
                $dm->flush();

                $request->getSession()
                    ->getFlashBag()
                    ->set('success', 'Кассовый документ успешно добавлен.');

                return $this->afterSaveRedirect('package', $entity->getId(), [], '_cash');
            }
        }

        return [
            'entity' => $entity,
            'cashes' => $cashes,
            'methods' => $this->container->getParameter('mbh.cash.methods'),
            'operations' => $this->container->getParameter('mbh.cash.operations'),
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * Cash documents
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

        $dm->getFilterCollection()->disable('softdeleteable');
        $entity = $dm->getRepository('MBHPackageBundle:Package')->find($id);
        $dm->getFilterCollection()->enable('softdeleteable');

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(
            new PackageServiceType(),
            null,
            ['package' => $entity]
        );

        if ($request->getMethod() == 'PUT') {
            $form->submit($request);

            if ($form->isValid()) {
                $data = $form->getData();

                $service = $dm->getRepository('MBHPriceBundle:Service')->find($data['service']);

                if (!$service || empty($data['amount'])) {
                    $request->getSession()
                        ->getFlashBag()
                        ->set(
                            'danger',
                            'Произошла ошибка при добавлении услуги. Обновите страницу и попробуйте еще раз.'
                        );

                    return $this->redirect($this->generateUrl('package_service', ['id' => $id]));
                }

                $packageService = new PackageService();
                $packageService->setPackage($entity)
                    ->setService($service)
                    ->setAmount((int)$data['amount'])
                    ->setPrice($service->getPrice());

                $dm->persist($packageService);
                $dm->flush();

                $request->getSession()
                    ->getFlashBag()
                    ->set(
                        'success',
                        'Услуга успешно добавлена.'
                    );

                return $this->afterSaveRedirect('package', $id, [], '_service');
            }
        }

        return [
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'form' => $form->createView(),
            'config' => $this->container->getParameter('mbh.services')
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

        if (!$entity || !$service) {
            throw $this->createNotFoundException();
        }

        $dm->remove($service);
        $dm->flush();

        $request->getSession()
            ->getFlashBag()
            ->set('success', 'Услуга успешно удалена.');

        return $this->redirect($this->generateUrl('package_service', ['id' => $id]));
    }

    /**
     * Cash document delete
     *
     * @Route("/{id}/cash/{cashId}/delete", name="package_cash_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     */
    public function cashDeleteAction(Request $request, $id, $cashId)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $dm->getFilterCollection()->disable('softdeleteable');
        $entity = $dm->getRepository('MBHPackageBundle:Package')->find($id);
        $dm->getFilterCollection()->enable('softdeleteable');
        $cash = $dm->getRepository('MBHCashBundle:CashDocument')->find($cashId);

        if (!$entity || !$cash) {
            throw $this->createNotFoundException();
        }

        $dm->remove($cash);
        $dm->flush();

        $request->getSession()
            ->getFlashBag()
            ->set('success', 'Кассовый документ успешно удален.');

        return $this->redirect($this->generateUrl('package_cash', ['id' => $id]));
    }

    /**
     * Accommodation
     *
     * @Route("/{id}/accommodation", name="package_accommodation")
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function accommodationAction(Request $request, $id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $dm->getFilterCollection()->disable('softdeleteable');
        $entity = $dm->getRepository('MBHPackageBundle:Package')->find($id);
        $dm->getFilterCollection()->enable('softdeleteable');

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $roomTypes = $dm->getRepository('MBHHotelBundle:RoomType')
            ->createQueryBuilder('q')
            ->sort('fullTitle', 'asc')
            ->field('hotel.id')->equals($entity->getRoomType()->getHotel()->getId())
            ->getQuery()
            ->execute()
            ->toArray();
        $groupedRooms = [];

        foreach ($roomTypes as $key => $roomType) {
            if ($roomType->getId() == $entity->getRoomType()->getId()) {
                unset($roomTypes[$key]);
                array_unshift($roomTypes, $roomType);
            }
        }

        $qb = $dm->getRepository('MBHPackageBundle:Package')->createQueryBuilder('q');
        $qb->field('accommodation')->notEqual(null)
            ->addOr(
                $qb->expr()
                    ->field('begin')->gte($entity->getBegin())
                    ->field('begin')->lt($entity->getEnd())
            )
            ->addOr(
                $qb->expr()
                    ->field('end')->gt($entity->getBegin())
                    ->field('end')->lte($entity->getEnd())
            )
            ->addOr(
                $qb->expr()
                    ->field('end')->gte($entity->getEnd())
                    ->field('begin')->lte($entity->getBegin())
            );

        $notIds = [];
        foreach ($qb->getQuery()->execute() as $package) {
            $notIds[] = $package->getAccommodation()->getId();
        };

        foreach ($roomTypes as $roomType) {

            $rooms = $dm->getRepository('MBHHotelBundle:Room')
                ->createQueryBuilder('q')
                ->sort('fullTitle', 'asc')
                ->field('roomType.id')->equals($roomType->getId())
                ->field('id')->notIn($notIds)
                ->getQuery()
                ->execute();
            if (!count($rooms)) {
                continue;
            }
            foreach ($rooms as $room) {
                $groupedRooms[$roomType->getName()][$room->getId()] = $room->getName();
            }
        }

        $form = $this->createForm(
            new PackageAccommodationType(),
            [],
            [
                'rooms' => $groupedRooms,
                'isHostel' => $this->get('mbh.hotel.selector')->getSelected()->getIsHostel()
            ]);

        if ($request->getMethod() == 'PUT') {
            $form->bind($request);

            if ($form->isValid()) {

                $entity->setAccommodation($dm->getRepository('MBHHotelBundle:Room')->find($form->getData()['room']));
                $dm->persist($entity);
                $dm->flush();

                $request->getSession()
                    ->getFlashBag()
                    ->set('success', 'Размещение успешно сохранено.');

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

        if (!$entity) {
            throw $this->createNotFoundException();
        }
        $entity->removeAccommodation();
        $dm->persist($entity);
        $dm->flush();

        $request->getSession()
            ->getFlashBag()
            ->set('success', 'Размещение успешно удалено.');

        return $this->redirect($this->generateUrl('package_accommodation', ['id' => $id]));
    }

    /**
     * Delete entity.
     *
     * @Route("/{id}/delete", name="package_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     */
    public function deleteAction($id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHPackageBundle:Package')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $dm->remove($entity);
        $dm->flush($entity);

        $this->getRequest()
            ->getSession()
            ->getFlashBag()
            ->set('success', 'Запись успешно удалена.');

        return $this->redirect($this->generateUrl('package'));

        return $response;
    }

}
