<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Form\RoomTypeType;

/**
 * @Route("/roomtype")
 */
class RoomTypeController extends Controller implements CheckHotelControllerInterface
{   
    /**
     * Lists all entities.
     *
     * @Route("/", name="room_type")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function indexAction()
    {
        /* @var $dm  Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        
        $entities = $dm->getRepository('MBHHotelBundle:RoomType')->createQueryBuilder('s')
                       ->sort('fullTitle', 'asc')
                       ->getQuery()
                       ->execute()
        ;
        
        if (!$entities->count()) {
            return $this->redirect($this->generateUrl('room_type_new'));
        }

        return array(
            'entities' => $entities,
        );
    }
    
    /**
     * Displays a form to create a new entity.
     *
     * @Route("/new", name="room_type_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function newAction()
    {
        $entity = new RoomType();
        $form = $this->createForm(
                new RoomTypeType(),
                $entity,
                ['calculationTypes' => $this->container->getParameter('maxibooking.calculation.types')]
        );

        return array(
            'form' => $form->createView(),
        );
    }
    
    /**
     * Creates a new entity.
     *
     * @Route("/create", name="room_type_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHHotelBundle:RoomType:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new RoomType();
        $entity->setHotel($this->get('mbh.hotel.selector')->getSelected());
        
        $form = $this->createForm(
                new RoomTypeType(),
                $entity,
                ['calculationTypes' => $this->container->getParameter('maxibooking.calculation.types')]
        );
        $form->bind($request);

        if ($form->isValid()) {
            /* @var $dm  Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entity);
            $dm->flush();

            $this->getRequest()->getSession()->getFlashBag()
                    ->set('success', 'Запись успешно создана.')
            ;
            
            return $this->afterSaveRedirect('room_type', $entity->getId());
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Edits an existing entity.
     *
     * @Route("/{id}", name="room_type_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHHotelBundle:RoomType:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        /* @var $dm  Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHHotelBundle:RoomType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(
                new RoomTypeType(),
                $entity,
                ['calculationTypes' => $this->container->getParameter('maxibooking.calculation.types')]
        );
        $form->bind($request);

        if ($form->isValid()) {

            /* @var $dm  Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entity);
            $dm->flush();

            $this->getRequest()->getSession()->getFlashBag()
                    ->set('success', 'Запись успешно отредактирована.')
            ;
            
            return $this->afterSaveRedirect('room_type', $entity->getId());
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
     * @Route("/{id}/edit", name="room_type_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function editAction($id)
    {
        /* @var $dm  Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHHotelBundle:RoomType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }
        
        $form = $this->createForm(
                new RoomTypeType(),
                $entity,
                ['calculationTypes' => $this->container->getParameter('maxibooking.calculation.types')]
        );
        
        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }
    
    /**
     * Delete entity.
     *
     * @Route("/{id}/delete", name="room_type_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function deleteAction($id)
    {
        return $this->deleteEntity($id, 'MBHHotelBundle:RoomType', 'room_type');
    }
}
