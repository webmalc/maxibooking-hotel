<?php

namespace MBH\Bundle\PriceBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Form\SpecialType;
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
     * Lists all entities.
     *
     * @Route("/", name="special")
     * @Method("GET")
     * @Security("is_granted('ROLE_SPECIAL_VIEW')")
     * @Template()
     */
    public function indexAction()
    {
        return [
            'entities' => []
        ];
    }

    /**
     * Displays a form to create a new entity.
     *
     * @Route("/new", name="special_new")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_SPECIAL_NEW')")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Special();
        $form = $this->createForm(SpecialType::class, $entity);

        return [
            'form' => $form->createView(),
        ];
    }
}
