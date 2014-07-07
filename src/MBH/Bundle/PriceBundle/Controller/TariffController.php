<?php

namespace MBH\Bundle\PriceBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
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
        /* @var $dm  Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $defaults = $dm->getRepository('MBHPriceBundle:Tariff')->createQueryBuilder('s')
                       ->field('isDefault')->equals(true)
                       ->field('end')->gte(new \DateTime())
                       ->sort(['begin' => 'desc', 'end' => 'desc'])
                       ->getQuery()
                       ->execute()
        ;
        
        $others = $dm->getRepository('MBHPriceBundle:Tariff')->createQueryBuilder('s')
                     ->field('isDefault')->equals(false)
                     ->field('end')->gte(new \DateTime())
                     ->sort(['begin' => 'desc', 'end' => 'desc'])
                     ->getQuery()
                     ->execute()
        ;
        
        $old = $dm->getRepository('MBHPriceBundle:Tariff')->createQueryBuilder('s')
                     ->field('end')->lt(new \DateTime())
                     ->sort(['begin' => 'desc', 'end' => 'desc'])
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
                new TariffMainType(), $entity, ['types' => $this->container->getParameter('maxibooking.tariff.types')]
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
                new TariffMainType(), $entity, ['types' => $this->container->getParameter('maxibooking.tariff.types')]
        );
        $form->bind($request);

        if ($form->isValid()) {
            /* @var $dm  Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entity);
            $dm->flush();

            $this->getRequest()->getSession()->getFlashBag()
                    ->set('success', 'Тариф успешно создана. Теперь необходимо заполнить цены.')
            ;

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
        /* @var $dm  Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHPriceBundle:Tariff')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(
                new TariffMainType(), $entity, ['types' => $this->container->getParameter('maxibooking.tariff.types')]
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
        /* @var $dm  Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHPriceBundle:Tariff')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(
                new TariffMainType(), $entity, ['types' => $this->container->getParameter('maxibooking.tariff.types')]
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
     * @Route("/{id}/delete", name="tariff_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function deleteAction($id)
    {
        return $this->deleteEntity($id, 'MBHPriceBundle:Tariff', 'tariff');
    }

}
