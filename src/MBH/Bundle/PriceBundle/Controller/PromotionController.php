<?php

namespace MBH\Bundle\PriceBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Form\PromotionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class PromotionController
 *
 * @Route("promotions")
 */
class PromotionController extends BaseController
{
    /**
     * @Route("/", name="promotions")
     * @Method("GET")
     * @Security("is_granted('ROLE_PROMOTION_VIEW')")
     * @Template()
     */
    public function indexAction()
    {
        $promotions = $this->dm->getRepository('MBHPriceBundle:Promotion')->findAll();

        return [
            'promotions' => $promotions
        ];
    }

    /**
     * @Route("/new", name="promotion_new")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_PROMOTION_NEW')")
     * @Template()
     */
    public function newAction(Request $request)
    {
        $promotion = new Promotion();
        $form = $this->createForm(PromotionType::class, $promotion, [
            'method' => Request::METHOD_POST
        ]);

        $form->handleRequest($request);
        if($form->isValid()) {
            $this->dm->persist($promotion);
            $this->dm->flush();

            return $this->redirectToRoute('promotions');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("{id}/edit", name="promotion_edit")
     * @Method({"GET", "POST"})
     * @Template()
     * @Security("is_granted('ROLE_PROMOTION_EDIT')")
     * @ParamConverter(class="MBH\Bundle\PriceBundle\Document\Promotion")
     */
    public function editAction(Request $request, Promotion $promotion)
    {
        $form = $this->createForm(PromotionType::class, $promotion, [
            'method' => Request::METHOD_POST
        ]);

        $form->handleRequest($request);
        if($form->isValid()) {
            $this->dm->persist($promotion);
            $this->dm->flush();

            return $this->isSavedRequest() ?
                $this->redirectToRoute('promotion_edit', ['id' => $promotion->getId()]) :
                $this->redirectToRoute('promotions');
        }

        return [
            'promotion' => $promotion,
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/{id}/delete", name="promotion_delete")
     * @Security("is_granted('ROLE_PROMOTION_DELETE')")
     * @ParamConverter(class="MBH\Bundle\PriceBundle\Document\Promotion")
     * @param Promotion $promotion
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function deleteAction(Promotion $promotion)
    {
        if ($this->container->get('mbh.promotion.helper')->tryToHandlePromotionRelations($promotion)) {
            return $this->deleteEntity($promotion->getId(), Promotion::class, 'promotions');
        } else {
            $this->addFlash('error', $this->get('translator')->trans('promotion.controller.delete.error'));
            return $this->redirectToRoute('promotions');
        }
    }
}
