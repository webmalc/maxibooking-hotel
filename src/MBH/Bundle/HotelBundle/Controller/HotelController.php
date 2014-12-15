<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Form\HotelType;
use MBH\Bundle\HotelBundle\Form\HotelExtendedType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class HotelController extends Controller
{   
    /**
     * Select hotel.
     *
     * @Route("/notfound", name="hotel_not_found")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function notFoundAction()
    {
        return [];
    }
    
    /**
     * Select hotel.
     *
     * @Route("/{id}/select", name="hotel_select")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     */
    public function selectHotelAction(Request $request, $id)
    {
        $this->get('mbh.hotel.selector')->setSelected($id);
        
        if ($request->get('url')) {
            return $this->redirect($request->get('url'));
        }
        
        return $this->redirect($this->generateUrl('_welcome'));
    }
    
    /**
     * Lists all entities.
     *
     * @Route("/", name="hotel")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function indexAction()
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        
        $entities = $dm->getRepository('MBHHotelBundle:Hotel')->createQueryBuilder('s')
                       ->sort('fullTitle', 'asc')
                       ->getQuery()
                       ->execute()
        ;

        return array(
            'entities' => $entities,
        );
    }
    
    /**
     * Displays a form to create a new entity.
     *
     * @Route("/new", name="hotel_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Hotel();
        $entity->setSaleDays(365);
        $form = $this->createForm(new HotelType(), $entity, ['food' => $this->container->getParameter('mbh.food.types')]);

        return array(
            'form' => $form->createView(),
        );
    }
    
    /**
     * Creates a new entity.
     *
     * @Route("/create", name="hotel_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHHotelBundle:Hotel:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Hotel();
        $form = $this->createForm(new HotelType(), $entity, ['food' => $this->container->getParameter('mbh.food.types')]);
        $form->bind($request);

        if ($form->isValid()) {
            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entity);
            $dm->flush();

            $this->getRequest()->getSession()->getFlashBag()
                    ->set('success', 'Запись успешно создана.')
            ;
            $this->get('mbh.room.cache.generator')->generateInBackground();

            return $this->afterSaveRedirect('hotel', $entity->getId());
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Edits an existing entity.
     *
     * @Route("/{id}", name="hotel_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHHotelBundle:Hotel:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHHotelBundle:Hotel')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new HotelType(), $entity, ['food' => $this->container->getParameter('mbh.food.types')]);

        $form->bind($request);

        if ($form->isValid()) {

            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entity);
            $dm->flush();

            $this->getRequest()->getSession()->getFlashBag()
                    ->set('success', 'Запись успешно отредактирована.')
            ;
            $this->get('mbh.room.cache.generator')->generateInBackground();
            
            return $this->afterSaveRedirect('hotel', $entity->getId());
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
     * @Route("/{id}/edit", name="hotel_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     * @param Hotel $entity
     * @return Response
     */
    public function editAction(Hotel $entity)
    {
        $form = $this->createForm(new HotelType(), $entity, ['food' => $this->container->getParameter('mbh.food.types')]);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }

    /**
     * Displays a form to edit extended config of an existing entity.
     *
     * @Route("/{id}/edit/extended", name="hotel_edit_extended")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     * @param Hotel $entity
     * @return Response
     */
    public function extendedAction(Hotel $entity)
    {
        $form = $this->createForm(new HotelExtendedType(), $entity, ['city' => $entity->getCity()]);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }

    /**
     * Save extended config of an existing entity.
     *
     * @Route("/{id}/edit/extended", name="hotel_edit_extended_save")
     * @Method("PUT")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHHotelBundle:Hotel:extended.html.twig")
     * @param Hotel $entity
     * @return Response
     */
    public function extendedSaveAction(Request $request, Hotel $entity)
    {
        $form = $this->createForm(new HotelExtendedType(), $entity);

        $form->submit($request);

        if ($form->isValid()) {
            //save address
            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();

            $city = $dm->getRepository('MBHHotelBundle:City')->find($form['address']->getData());
            if ($city) {
                $entity->setCountry($city->getCountry())
                    ->setRegion($city->getRegion())
                    ->setCity($city)
                ;
            }

            $dm->persist($entity);
            $dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', 'Запись успешно отредактирована.')
            ;

            return $this->afterSaveRedirect('hotel', $entity->getId(), [], '_edit_extended');
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }

    /**
     * Get city by query
     *
     * @Route("/city/{id}", name="hotel_city", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @return JsonResponse
     */
    public function cityAction(Request $request, $id = null)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        if (empty($id) && empty($request->get('query'))) {
            return new JsonResponse([]);
        }

        if (!empty($id)) {
            $city =  $dm->getRepository('MBHHotelBundle:City')->find($id);

            if ($city) {
                return new JsonResponse([
                    'id' => $city->getId(),
                    'text' => $city->getCountry()->getTitle() . ', ' . $city->getRegion()->getTitle() . ', ' .$city->getTitle()
                ]);
            }
        }

        $cities = $dm->getRepository('MBHHotelBundle:City')->createQueryBuilder('q')
            ->field('title')->equals(new \MongoRegex('/.*' . $request->get('query') . '.*/i'))
            ->getQuery()
            ->execute()
        ;

        $data = [];

        foreach ($cities as $city) {
            $data[] = [
                'id' => $city->getId(),
                'text' => $city->getCountry()->getTitle() . ', ' . $city->getRegion()->getTitle() . ', ' .$city->getTitle()
            ];
        }

        $regions = $dm->getRepository('MBHHotelBundle:Region')->createQueryBuilder('q')
            ->field('title')->equals(new \MongoRegex('/.*' . $request->get('query') . '.*/i'))
            ->getQuery()
            ->execute()
        ;

        foreach ($regions as $region) {
            foreach ($region->getCities() as $city) {


                $data[] = [
                    'id' => $city->getId(),
                    'text' => $city->getCountry()->getTitle() . ', ' . $city->getRegion()->getTitle() . ', ' .$city->getTitle()
                ];
            }
        }

        return new JsonResponse($data);
    }
    
    /**
     * Delete entity.
     *
     * @Route("/{id}/delete", name="hotel_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function deleteAction($id)
    {
        $response = $this->deleteEntity($id, 'MBHHotelBundle:Hotel', 'hotel');
        $this->get('mbh.room.cache.generator')->generateInBackground();
        
        return $response;
    }
}
