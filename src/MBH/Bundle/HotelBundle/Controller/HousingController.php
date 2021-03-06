<?php

namespace MBH\Bundle\HotelBundle\Controller;


use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\HotelBundle\Document\Housing;
use MBH\Bundle\HotelBundle\Form\HousingType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class HousingController
 * @package MBH\Bundle\HotelBundle\Controller

 * @see Corpus
 * @Route("/housing")
 */
class HousingController extends BaseController
{
    /**
     * @Route("/", name="housing")
     * @Security("is_granted('ROLE_HOUSING_VIEW')")
     * @Template()
     */
    public function indexAction()
    {
        $currentHotel = $this->get('mbh.hotel.selector')->getSelected();
        $entities = $currentHotel->getHousings();//$entities = $this->dm->getRepository('MBHHotelBundle:Housing')->findBy(['hotel.id' => $currentHotel->getId()]);

        return [
            'entities' => $entities,
        ];
    }

    /**
     * @Route("/new", name="housing_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_HOUSING_NEW')")
     * @Template()
     */
    public function newAction()
    {
        $form = $this->createForm(HousingType::class, new Housing(), ['dm' => $this->dm]);
        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/new", name="housing_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_HOUSING_NEW')")
     * @Template("MBHHotelBundle:Housing:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Housing();
        $currentHotel = $this->get('mbh.hotel.selector')->getSelected();
        $entity->setHotel($currentHotel);

        $form = $this->createForm(HousingType::class, $entity, ['dm' => $this->dm]);
        $form->handleRequest($request);

        if($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.corpus.created_success'));

            return $this->afterSaveRedirect('housing', $entity->getId());
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/edit/{id}", name="housing_edit")
     * @Method("GET")
     * @Template()
     * @Security("is_granted('ROLE_HOUSING_EDIT')")
     * @ParamConverter("entity", class="MBHHotelBundle:Housing")
     */
    public function editAction(Housing $entity)
    {
        $form = $this->createForm(HousingType::class, $entity, ['dm' => $this->dm]);
        return [
            'form' => $form->createView(),
            'entity' => $entity,
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * @Route("/update/{id}", name="housing_update")
     * @Method("POST")
     * @Template("MBHHotelBundle:Housing:edit.html.twig")
     * @Security("is_granted('ROLE_HOUSING_EDIT')")
     * @ParamConverter("entity", class="MBHHotelBundle:Housing")
     */
    public function updateAction(Housing $entity, Request $request)
    {
        $form = $this->createForm(HousingType::class, $entity, ['dm' => $this->dm]);
        $form->handleRequest($request);

        if($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.corpus.updated_success'));

            return $this->afterSaveRedirect('housing', $entity->getId());
        }

        return [
            'form' => $form->createView(),
            'entity' => $entity,
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * @Route("/delete/{id}", name="housing_delete")
     * @Security("is_granted('ROLE_HOUSING_DELETE')")
     * @ParamConverter("entity", class="MBHHotelBundle:Housing")
     */
    public function deleteAction(Housing $housing)
    {
        $response = $this->deleteEntity($housing->getId(), 'MBHHotelBundle:Housing', 'housing');
        return $response;
    }
}