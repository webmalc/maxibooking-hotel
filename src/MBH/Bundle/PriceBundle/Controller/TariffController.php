<?php

namespace MBH\Bundle\PriceBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\PriceBundle\Document\RoomQuota;
use MongoDBODMProxies\__CG__\MBH\Bundle\PriceBundle\Document\FoodPrice;
use MongoDBODMProxies\__CG__\MBH\Bundle\PriceBundle\Document\RoomPrice;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Form\TariffMainType;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;

/**
 * @Route("tariff")
 */
class TariffController extends Controller implements CheckHotelControllerInterface
{

    /**
     * Lists all entities.
     *
     * @Route("/", name="tariff")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function indexAction()
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        
        $defaults = $dm->getRepository('MBHPriceBundle:Tariff')->createQueryBuilder('s')
                       ->field('isDefault')->equals(true)
                       ->field('end')->gte(new \DateTime())
                       ->field('hotel.id')->equals($hotel->getId())
                       ->sort(['begin' => 'asc', 'end' => 'asc'])
                       ->getQuery()
                       ->execute()
        ;
        
        $others = $dm->getRepository('MBHPriceBundle:Tariff')->createQueryBuilder('s')
                     ->field('isDefault')->equals(false)
                     ->field('end')->gte(new \DateTime())
                     ->field('hotel.id')->equals($hotel->getId())
                     ->sort(['begin' => 'asc', 'end' => 'asc'])
                     ->getQuery()
                     ->execute()
        ;
        
        $old = $dm->getRepository('MBHPriceBundle:Tariff')->createQueryBuilder('s')
                     ->field('end')->lt(new \DateTime())
                     ->field('hotel.id')->equals($hotel->getId())
                     ->sort(['begin' => 'asc', 'end' => 'asc'])
                     ->getQuery()
                     ->execute()
        ;
        
        return array(
            'defaults' => $defaults,
            'others'   => $others,
            'old'      => $old
        );
    }

    /**
     * Displays a form to create a new entity.
     *
     * @Route("/new", name="tariff_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function newAction()
    {   
        $entity = new Tariff();
        $form = $this->createForm(
                new TariffMainType(), $entity, ['types' => $this->container->getParameter('mbh.tariff.types')]
        );

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a new entity.
     *
     * @Route("/create", name="tariff_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHPriceBundle:Tariff:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Tariff();
        $entity->setHotel($this->get('mbh.hotel.selector')->getSelected());
        
        $form = $this->createForm(
                new TariffMainType(), $entity, ['types' => $this->container->getParameter('mbh.tariff.types')]
        );
        $form->bind($request);

        if ($form->isValid()) {
            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entity);
            $dm->flush();

            $this->getRequest()->getSession()->getFlashBag()
                    ->set('success', 'Тариф успешно создана. Теперь необходимо заполнить цены.')
            ;

            $this->get('mbh.room.cache.generator')->generateInBackground();
            
            return $this->afterSaveRedirect('tariff', $entity->getId());
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }
    
    /**
     * Edits an existing entity.
     *
     * @Route("/{id}", name="tariff_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHPriceBundle:Tariff:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHPriceBundle:Tariff')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(
                new TariffMainType(), $entity, ['types' => $this->container->getParameter('mbh.tariff.types')]
        );
        
        $form->submit($request);

        if ($form->isValid()) {

            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entity);
            $dm->flush();

            $this->getRequest()->getSession()->getFlashBag()
                    ->set('success', 'Запись успешно отредактирована.')
            ;

            $this->get('mbh.room.cache.generator')->generateInBackground();
            
            return $this->afterSaveRedirect('tariff', $entity->getId());
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="tariff_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function editAction($id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHPriceBundle:Tariff')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(
                new TariffMainType(), $entity, ['types' => $this->container->getParameter('mbh.tariff.types')]
        );

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/clone", name="tariff_clone")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function cloneAction($id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHPriceBundle:Tariff')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $new = clone $entity;

        $new->setTitle('')
            ->setFullTitle($entity->getFullTitle() . '_копия')
            ->setIsEnabled(false)
        ;

        $dm->persist($new);
        $dm->flush();

        $newId = $new->getId();
        $dm->clear();

        $entity = $dm->getRepository('MBHPriceBundle:Tariff')->find($id);
        $new = $dm->getRepository('MBHPriceBundle:Tariff')->find($newId);

        foreach($entity->getRoomQuotas() as $quota) {
            $newRoomQuota = new RoomQuota();
            $newRoomQuota->setRoomType($quota->getRoomType())->setNumber($quota->getNumber());
            $new->addRoomQuota($newRoomQuota);
        }
        foreach($entity->getFoodPrices() as $foodPrice) {
            $newFoodPrice = new FoodPrice();
            $newFoodPrice->setType($foodPrice->getType())->setPrice($foodPrice->getPrice());
            $new->addFoodPrice($newFoodPrice);
        }
        foreach($entity->getRoomPrices() as $price) {
            $newPrice = new RoomPrice();
            $newPrice->setRoomType($price->getRoomType())
                ->setPrice($price->getPrice())
                ->setAdditionalAdultPrice($price->getAdditionalAdultPrice())
                ->setAdditionalChildPrice($price->getAdditionalChildPrice())
            ;
            $new->addRoomPrice($newPrice);
        }

        $dm->persist($new);
        $dm->flush();

        $this->getRequest()->getSession()->getFlashBag()
            ->set('success', 'Запись успешно скопирована.')
        ;

        $this->get('mbh.room.cache.generator')->generateInBackground();

        return $this->redirect($this->generateUrl('tariff'));
    }

    /**
     * Delete entity.
     *
     * @Route("/{id}/delete", name="tariff_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function deleteAction($id)
    {
        $response = $this->deleteEntity($id, 'MBHPriceBundle:Tariff', 'tariff');
        $this->get('mbh.room.cache.generator')->generateInBackground();
        
        return $response;
    }

}
