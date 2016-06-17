<?php

namespace MBH\Bundle\RestaurantBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\RestaurantBundle\Document\IngredientCategory;
use MBH\Bundle\RestaurantBundle\Form\IngredientCategoryType as IngredientCategoryForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IngredientController extends BaseController implements CheckHotelControllerInterface
{
    /**
     * List all ingredient category
     *
     * @Route("/", name="restaurant_ingredient_list")
     * @Security("is_granted('ROLE_RESTAURANT_VIEW')")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $entities = $this->dm->getRepository('MBHRestaurantBundle:IngredientCategory')->createQueryBuilder('q')
            ->field('hotel.id')->equals($this->get('mbh.hotel.selector')->getSelected()->getId())
            ->sort('fullTitle', 'asc')
            ->getQuery()
            ->execute();

        return [
            'entities' => $entities,
            'config' => $this->container->getParameter('mbh.services')
        ];
    }

    /**
     * Displays a form to create a new IngredientCategory
     * @Route("/newcategory", name="restaurant_ingredient_category_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_RESTAURANT_CATEGORY_NEW')")
     * @Template()
     */
    public function newCategoryAction()
    {
        $entity = new IngredientCategory();
        $form = $this->createForm(new IngredientCategoryForm(), $entity);
        $hotel = $this->hotel;
        return [
            'entity' => $entity,
            'form' => $form->createView()
        ];
    }


    /**
     * @param Request $request
     * Creates a new IngredientCategory
     * @Route("/createcategory", name="restaurant_ingredient_category_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_RESTAURANT_CATEGORY_NEW')")
     * @Template("@MBHRestaurant/Ingredient/newCategory.html.twig")
     * @return Response
     */
    public function createCategoryAction(Request $request)
    {
        $entity = new IngredientCategory();
        $entity->setHotel($this->hotel);

        $form = $this->createForm(new IngredientCategoryForm(), $entity);
        $form->submit($request);

        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', 'Запись успешно создана.');

            return $this->afterSaveRedirect('restaurant_ingredient_list', $entity->getId(), ['tab' => $entity->getId()]);

        }
        return [
            'entity' => $entity,
            'form' => $form->createView()
        ];
    }

}
