<?php

namespace MBH\Bundle\PriceBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Form\SpecialFilterType;
use MBH\Bundle\PriceBundle\Form\SpecialType;
use MBH\Bundle\PriceBundle\Lib\SpecialFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("management/special")
 */
class SpecialController extends Controller implements CheckHotelControllerInterface
{
    /**
     * Show list filter
     *
     * @Route("/", name="special", options={"expose"=true})
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_SPECIAL_VIEW')")
     * @Template()
     *
     * @param Request $request
     * @return array| \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $filter = new SpecialFilter();
        $filter->setHotel($this->hotel);

        $form = $this->createForm(SpecialFilterType::class, $filter);

        if ($request->isXmlHttpRequest()) {
            $form->submit($request->get('form'));
            $entities = $this->dm->getRepository('MBHPriceBundle:Special')->getFiltered($filter);

            return $this->render('MBHPriceBundle:Special:list.json.twig', [
                'entities' => $entities,
                'draw' => $request->get('draw'),
                'total' => $entities->count()
            ]);
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Displays a form to create a new entity.
     *
     * @Route("/new", name="special_new")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_SPECIAL_NEW')")
     * @Template()

     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function newAction(Request $request)
    {
        $entity = new Special();
        $entity->setHotel($this->hotel);

        $form = $this->createForm(SpecialType::class, $entity);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $this->addFlash('success', 'document.saved');

            return $this->afterSaveRedirect('special', $entity->getId());
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="special_edit")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_SPECIAL_EDIT')")
     * @Template()
     * @ParamConverter(class="MBHPriceBundle:Special")
     *
     * @param Request $request
     * @param Special $entity
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editAction(Request $request, Special $entity)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(SpecialType::class, $entity);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $this->addFlash('success', 'document.saved');

            return $this->afterSaveRedirect('special', $entity->getId());
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * Delete entity.
     *
     * @Route("/{id}/delete", name="special_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_TARIFF_DELETE')")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id)
    {
        return $this->deleteEntity($id, 'MBHPriceBundle:Special', 'special');
    }
}
