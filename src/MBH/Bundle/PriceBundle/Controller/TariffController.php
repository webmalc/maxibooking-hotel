<?php

namespace MBH\Bundle\PriceBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\PriceBundle\Form\TariffPromotionsType;
use MBH\Bundle\PriceBundle\Form\TariffServicesType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Form\TariffType;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;

/**
 * @Route("management/tariff")
 */
class TariffController extends Controller implements CheckHotelControllerInterface
{

    /**
     * Lists all entities.
     *
     * @Route("/", name="tariff")
     * @Method("GET")
     * @Security("is_granted('ROLE_TARIFF_VIEW')")
     * @Template()
     */
    public function indexAction()
    {
        $entities = $this->dm->getRepository('MBHPriceBundle:Tariff')->createQueryBuilder('q')
            ->field('hotel.id')->equals($this->get('mbh.hotel.selector')->getSelected()->getId())
            ->sort('fullTitle', 'asc')
            ->getQuery()
            ->execute()
        ;

        return [
            'entities' => $entities
        ];
    }

    /**
     * Displays a form to create a new entity.
     *
     * @Route("/new", name="tariff_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_TARIFF_NEW')")
     * @Template()
     */
    public function newAction()
    {   
        $entity = new Tariff();
        $form = $this->createForm(new TariffType(), $entity);

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Creates a new entity.
     *
     * @Route("/create", name="tariff_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_TARIFF_NEW')")
     * @Template("MBHPriceBundle:Tariff:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Tariff();
        $entity->setHotel($this->get('mbh.hotel.selector')->getSelected());
        
        $form = $this->createForm(new TariffType(), $entity);
        $form->submit($request);

        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()
                    ->set('success', 'Тариф успешно создан. Теперь необходимо заполнить цены.')
            ;
            return $this->afterSaveRedirect('tariff', $entity->getId());
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }
    
    /**
     * Edits an existing entity.
     *
     * @Route("/{id}", name="tariff_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_TARIFF_EDIT')")
     * @Template("MBHPriceBundle:Tariff:edit.html.twig")
     * @ParamConverter(class="MBHPriceBundle:Tariff")
     */
    public function updateAction(Request $request, Tariff $entity)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new TariffType(), $entity);
        $form->submit($request);

        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', 'Запись успешно отредактирована.');
            
            return $this->afterSaveRedirect('tariff', $entity->getId());
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="tariff_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_TARIFF_EDIT')")
     * @Template()
     * @ParamConverter(class="MBHPriceBundle:Tariff")
     */
    public function editAction(Tariff $entity)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new TariffType(), $entity);

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * @Route("/edit/{id}/promotions", name="tariff_promotions_edit")
     * @Method({"GET", "POST"})
     * @Template()
     * @ParamConverter(class="MBHPriceBundle:Tariff")
     */
    public function editPromotionsAction(Request $request, Tariff $tariff)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($tariff->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new TariffPromotionsType(), $tariff);

        $form->handleRequest($request);
        if($form->isValid()) {
            $this->dm->persist($tariff);
            $this->dm->flush();

            return $this->isSavedRequest() ?
                $this->redirectToRoute('tariff_promotions_edit', ['id' => $tariff->getId()]) :
                $this->redirectToRoute('tariff');
        }

        return [
            'entity' => $tariff,
            'form' => $form->createView(),
            'logs' => $this->logs($tariff)
        ];
    }

    /**
     * @Route("/edit/{id}/services", name="tariff_services_edit")
     * @Method({"GET", "POST"})
     * @Template()
     * @ParamConverter(class="MBHPriceBundle:Tariff")
     */
    public function editServicesAction(Request $request, Tariff $tariff)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($tariff->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new TariffServicesType(), $tariff);

        $form->handleRequest($request);
        if($form->isValid()) {
            $this->dm->persist($tariff);
            $this->dm->flush();

            return $this->isSavedRequest() ?
                $this->redirectToRoute('tariff_services_edit', ['id' => $tariff->getId()]) :
                $this->redirectToRoute('tariff');
        }

        return [
            'tariff' => $tariff,
            'form' => $form->createView(),
            'logs' => $this->logs($tariff),
        ];
    }

    /**
     * Delete entity.
     *
     * @Route("/{id}/delete", name="tariff_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_TARIFF_DELETE')")
     */
    public function deleteAction($id)
    {
        return $this->deleteEntity($id, 'MBHPriceBundle:Tariff', 'tariff');
    }

}
