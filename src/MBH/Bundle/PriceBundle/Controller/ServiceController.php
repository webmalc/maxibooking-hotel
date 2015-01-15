<?php

namespace MBH\Bundle\PriceBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\PackageBundle\Lib\DeleteException;
use MBH\Bundle\PriceBundle\Document\Service;
use MBH\Bundle\PriceBundle\Document\ServiceCategory;
use MBH\Bundle\PriceBundle\Form\ServiceCategoryType;
use MBH\Bundle\PriceBundle\Form\ServiceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;

/**
 * @Route("service")
 */
class ServiceController extends Controller implements CheckHotelControllerInterface
{

    /**
     * Lists all entities.
     *
     * @Route("/", name="price_service_category")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template()
     */
    public function indexAction()
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entities = $dm->getRepository('MBHPriceBundle:ServiceCategory')->createQueryBuilder('q')
            ->field('hotel.id')->equals($this->get('mbh.hotel.selector')->getSelected()->getId())
            ->sort('fullTitle', 'asc')
            ->getQuery()
            ->execute()
        ;

        if (!$entities->count()) {
            $serviceCategory = new ServiceCategory();
            $serviceCategory->setFullTitle('Основные')
                ->setHotel($this->get('mbh.hotel.selector')->getSelected())
            ;
            $dm->persist($serviceCategory);
            $dm->flush();
            $entities = [$serviceCategory];
        }
        
        return [
            'entities' => $entities,
            'config' => $this->container->getParameter('mbh.services')
        ];
    }

    /**
     * save entries prices
     *
     * @Route("/", name="price_service_category_save_prices")
     * @Method("POST")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template()
     */
    public function savePricesAction(Request $request)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $entries = $request->get('entries');

        foreach ($entries as $id => $price) {
            $entity = $dm->getRepository('MBHPriceBundle:Service')->find($id);

            if (!$entity || $entity->getPrice() == $price || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
                continue;
            }

            $entity->setPrice((empty($price)) ? null : (int) $price);
            $dm->persist($entity);

        }
        $dm->flush();

        $request->getSession()
            ->getFlashBag()
            ->set('success', 'Цены успешно сохранены.');

        return $this->redirect($this->generateUrl('price_service_category'));
    }

    /**
     * Displays a form to create a new entity.
     *
     * @Route("/{id}/new/entry", name="price_service_category_entry_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template()
     */
    public function newEntryAction($id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $entity = $dm->getRepository('MBHPriceBundle:ServiceCategory')->find($id);

        if (!$entity || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        $entry = new Service();
        $form = $this->createForm(
            new ServiceType(),
            $entry,
            ['units' => $this->container->getParameter('mbh.services')['units']]
        );

        return [
            'entry' => $entry,
            'entity' => $entity,
            'form' => $form->createView()
        ];
    }

    /**
     * Creates a new entity.
     *
     * @Route("/{id}/create/entry", name="price_service_category_entry_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template("MBHPriceBundle:Service:newEntry.html.twig")
     */
    public function createEntryAction(Request $request, $id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $entity = $dm->getRepository('MBHPriceBundle:ServiceCategory')->find($id);

        if (!$entity || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        $entry = new Service();
        $entry->setCategory($entity);

        $form = $this->createForm(
            new ServiceType(),
            $entry,
            ['units' => $this->container->getParameter('mbh.services')['units']]
        );

        $form->submit($request);

        if ($form->isValid()) {
            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entry);
            $dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', 'Запись успешно создана.')
            ;

            if ($request->get('save') !== null) {
                return $this->redirect($this->generateUrl('price_service_category_entry_edit', ['id' => $entry->getId()]));
            }

            return $this->redirect($this->generateUrl('price_service_category', ['tab' => $id]));

        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Displays a form to edit a new entity.
     *
     * @Route("/{id}/edit/entry", name="price_service_category_entry_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template()
     */
    public function editEntryAction($id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $entry = $dm->getRepository('MBHPriceBundle:Service')->find($id);

        if (!$entry || !$this->container->get('mbh.hotel.selector')->checkPermissions($entry->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(
            new ServiceType(),
            $entry,
            ['units' => $this->container->getParameter('mbh.services')['units']]
        );

        return [
            'entry' => $entry,
            'entity' => $entry->getCategory(),
            'form' => $form->createView(),
            'logs' => $this->logs($entry)
        ];
    }

    /**
     * Displays a form to edit a new entity.
     *
     * @Route("/{id}/update/entry", name="price_service_category_entry_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template("MBHPriceBundle:Service:editEntry.html.twig")
     */
    public function updateEntryAction(Request $request, $id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $entry = $dm->getRepository('MBHPriceBundle:Service')->find($id);

        if (!$entry || !$this->container->get('mbh.hotel.selector')->checkPermissions($entry->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(
            new ServiceType(),
            $entry,
            ['units' => $this->container->getParameter('mbh.services')['units']]
        );

        $form->submit($request);

        if ($form->isValid()) {

            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entry);
            $dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', 'Запись успешно отредактирована.')
            ;

            if ($request->get('save') !== null) {
                return $this->redirect($this->generateUrl('price_service_category_entry_edit', ['id' => $entry->getId()]));
            }

            return $this->redirect($this->generateUrl('price_service_category', ['tab' => $entry->getCategory()->getId()]));
        }

        return [
            'entry' => $entry,
            'entity' => $entry->getCategory(),
            'form' => $form->createView(),
            'logs' => $this->logs($entry)
        ];
    }

    /**
     * Displays a form to create a new entity.
     *
     * @Route("/new", name="price_service_category_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template()
     */
    public function newAction()
    {
        $entity = new ServiceCategory();
        $form = $this->createForm(
            new ServiceCategoryType(), $entity
        );

        return [
            'entity' => $entity,
            'form' => $form->createView()
        ];
    }

    /**
     * Creates a new entity.
     *
     * @Route("/create", name="price_service_category_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template("MBHPriceBundle:Service:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new ServiceCategory();
        $entity->setHotel($this->get('mbh.hotel.selector')->getSelected());

        $form = $this->createForm(
            new ServiceCategoryType(), $entity
        );
        $form->submit($request);

        if ($form->isValid()) {
            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entity);
            $dm->flush();

            $this->getRequest()->getSession()->getFlashBag()
                ->set('success', 'Запись успешно создана.')
            ;

            return $this->afterSaveRedirect('price_service_category', $entity->getId(), ['tab' => $entity->getId()]);
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="price_service_category_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template()
     */
    public function editAction($id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHPriceBundle:ServiceCategory')->find($id);

        if (!$entity || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(
            new ServiceCategoryType(), $entity
        );

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }

    /**
     * Edits an existing entity.
     *
     * @Route("/{id}", name="price_service_category_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template("MBHPriceBundle:Service:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHPriceBundle:ServiceCategory')->find($id);

        if (!$entity || !$this->container->get('mbh.hotel.selector')->checkPermissions($entry->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(
            new ServiceCategoryType(), $entity
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

            return $this->afterSaveRedirect('price_service_category', $entity->getId(), ['tab' => $entity->getId()]);
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }

    /**
     * Delete entity.
     *
     * @Route("/{id}/delete", name="price_service_category_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     */
    public function deleteAction($id)
    {
        return $this->deleteEntity($id, 'MBHPriceBundle:ServiceCategory', 'price_service_category');
    }

    /**
     * Delete entry.
     *
     * @Route("/{id}/entry/delete", name="price_service_category_entry_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     */
    public function deleteEntryAction(Request $request, $id)
    {
        try {
            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();

            $entity = $dm->getRepository('MBHPriceBundle:Service')->find($id);

            if (!$entity || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
                throw $this->createNotFoundException();
            }
            $catId = $entity->getCategory()->getId();
            $dm->remove($entity);
            $dm->flush($entity);

            $request->getSession()
                ->getFlashBag()
                ->set('success', 'Запись успешно удалена.');
        } catch (DeleteException $e) {
            $request->getSession()
                ->getFlashBag()
                ->set('danger', $e->getMessage());
        }


        return $this->redirect($this->generateUrl('price_service_category', ['tab' => $catId]));
    }
}
