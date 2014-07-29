<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Form\PackageMainType;
use MBH\Bundle\PackageBundle\Form\PackageGuestType;
use MBH\Bundle\PackageBundle\Form\PackageAccommodationType;
use MBH\Bundle\CashBundle\Form\CashDocumentType;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\PackageBundle\Document\Tourist;

class PackageController extends Controller implements CheckHotelControllerInterface
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
        return [];
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

        if (!$entity) {
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(
                new PackageMainType(), $entity, ['arrivals' => $this->container->getParameter('mbh.package.arrivals')]
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
                new PackageMainType(), $entity, ['arrivals' => $this->container->getParameter('mbh.package.arrivals')]
        );

        $form->bind($request);

        if ($form->isValid()) {
            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entity);
            $dm->flush();

            $this->getRequest()->getSession()->getFlashBag()
                    ->set('success', 'Запись успешно отредактирована.')
            ;

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
        $query->adults = (int) $request->get('adults');
        $query->children = (int) $request->get('children');
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
                ->setPrice($results[0]->getPrice($package->getFood()))
        ;

        $errors = $this->get('validator')->validate($package);

        if (count($errors)) {
            return [];
        }

        $dm->persist($package);
        $dm->flush();

        $this->get('mbh.room.cache.generator')->decrease(
                $package->getRoomType(), $package->getBegin(), $package->getEnd()
        );

        $request->getSession()
                ->getFlashBag()
                ->set('success', 'Бронь успешно создана.')
        ;

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

        $entity = $dm->getRepository('MBHPackageBundle:Package')->find($id);

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
                    'firstName' => $data['firstName'], 'lastName' => $data['lastName']
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
                            ->setBirthday($data['birthday'])
                    ;
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
                        ->set('success', 'Турист успешно добавлен.')
                ;
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
                ->set('success', 'Турист успешно удален.')
        ;
        
        return $this->redirect($this->generateUrl('package_guest', ['id' => $id]));
    }

    /**
     * Cash documents
     *
     * @Route("/{id}/cahs", name="package_cash")
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function cashAction(Request $request, $id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        /* @var $entity  Package */
        $entity = $dm->getRepository('MBHPackageBundle:Package')->find($id);

        $dm->getFilterCollection()->disable('softdeleteable');
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
                        ->set('success', 'Кассовый документ успешно добавлен.')
                ;
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

        $entity = $dm->getRepository('MBHPackageBundle:Package')->find($id);
        $cash = $dm->getRepository('MBHCashBundle:CashDocument')->find($cashId);

        if (!$entity || !$cash) {
            throw $this->createNotFoundException();
        }

        $dm->remove($cash);
        $dm->flush();

        $request->getSession()
                ->getFlashBag()
                ->set('success', 'Кассовый документ успешно удален.')
        ;
        
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

        $entity = $dm->getRepository('MBHPackageBundle:Package')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $roomTypes = $dm->getRepository('MBHHotelBundle:RoomType')
                    ->createQueryBuilder('q')
                    ->sort('fullTitle', 'asc')
                    ->getQuery()
                    ->execute()
                    ->toArray()
        ;
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
           )
        ;

        $notIds = [];
        foreach ($qb->getQuery()->execute() as $package) {
            $notIds[] = $package->getAccommodation()->getId();
        }
        ;

        foreach ($roomTypes as $roomType) {

            $rooms = $dm->getRepository('MBHHotelBundle:Room')
                        ->createQueryBuilder('q')
                        ->sort('fullTitle', 'asc')
                        ->field('roomType.id')->equals($roomType->getId())
                        ->field('id')->notIn($notIds)
                        ->getQuery()
                        ->execute()
            ;
            if (!count($rooms)) {
                continue;
            }
            foreach($rooms as $room) {
                $groupedRooms[$roomType->getName()][$room->getId()] = $room->getName();
            }
        }

        $form = $this->createForm(new PackageAccommodationType(), [], ['rooms' => $groupedRooms]);

        if ($request->getMethod() == 'PUT') {
            $form->bind($request);

            if ($form->isValid()) {

                $entity->setAccommodation($dm->getRepository('MBHHotelBundle:Room')->find($form->getData()['room']));
                $dm->persist($entity);
                $dm->flush();

                $request->getSession()
                    ->getFlashBag()
                    ->set('success', 'Размещение успешно сохранено.')
                ;
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
            ->set('success', 'Размещение успешно удалено.')
        ;

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

        $roomType = $entity->getRoomType();
        $begin = $entity->getBegin();
        $end = $entity->getEnd();

        $dm->remove($entity);
        $dm->flush($entity);

        $this->get('mbh.room.cache.generator')->increase($roomType, $begin, $end);

        $this->getRequest()
                ->getSession()
                ->getFlashBag()
                ->set('success', 'Запись успешно удалена.');

        return $this->redirect($this->generateUrl('package'));

        return $response;
    }

}
