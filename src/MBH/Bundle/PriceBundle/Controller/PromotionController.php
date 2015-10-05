<?php

namespace MBH\Bundle\PriceBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Form\PromotionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class PromotionController
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 *
 * @Route("promotions")
 */
class PromotionController extends BaseController
{
    /**
     * @Route("/", name="promotions")
     * @Method("GET")
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
     * @Method({"GET", "PUT"})
     * @Template()
     */
    public function newAction(Request $request)
    {
        $promotion = new Promotion();
        $form = $this->createForm(new PromotionType(), $promotion, [
            'method' => Request::METHOD_PUT
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
     * @ParamConverter(class="MBH\Bundle\PriceBundle\Document\Promotion")
     */
    public function editAction(Request $request, Promotion $promotion)
    {
        $form = $this->createForm(new PromotionType(), $promotion, [
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
     * @ParamConverter(class="MBH\Bundle\PriceBundle\Document\Promotion")
     */
    public function deleteAction(Promotion $promotion)
    {
        return $this->deleteEntity($promotion->getId(), Promotion::class, 'promotions');
    }
}