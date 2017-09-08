<?php

namespace MBH\Bundle\RestaurantBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\RestaurantBundle\Document\Ingredient;
use MBH\Bundle\RestaurantBundle\Document\IngredientCategory;
use MBH\Bundle\RestaurantBundle\Form\IngredientCategoryType as IngredientCategoryForm;
use MBH\Bundle\RestaurantBundle\Form\IngredientType as IngredientForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

/**
 * Class IngredientController
 * @package MBH\Bundle\RestaurantBundle\Controller
 * @Route("/ingredients")
 */
class IngredientController extends BaseController implements CheckHotelControllerInterface
{

    /**
     * List all ingredient category
     *
     * @Route("/", name="restaurant_ingredient_category")
     * @Route("/", name="restaurant_ingredient")
     * @Security("is_granted('ROLE_RESTAURANT_INGREDIENT_VIEW')")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $entities = $this->dm->getRepository('MBHRestaurantBundle:IngredientCategory')->createQueryBuilder()
            ->field('hotel.id')->equals($this->hotel->getId())
            ->sort('fullTitle', 'asc')
            ->getQuery()
            ->execute();

        return [
            'entities' => $entities,
            'config' => $this->container->getParameter('mbh.units')
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
        $form = $this->createForm(IngredientCategoryForm::class, $entity);
        return [
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
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createCategoryAction(Request $request)
    {
        $entity = new IngredientCategory();
        $entity->setHotel($this->hotel);

        $form = $this->createForm(IngredientCategoryForm::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', 'Запись успешно создана.');

            return $this->afterSaveRedirect('restaurant_ingredient_category', $entity->getId(), ['tab' => $entity->getId()]);

        }
        return [
            'form' => $form->createView()
        ];
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="restaurant_ingredient_category_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_RESTAURANT_CATEGORY_EDIT')")
     * @Template()
     * @ParamConverter(class="MBHRestaurantBundle:IngredientCategory")
     */
    public function editCategoryAction(IngredientCategory $entity)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(IngredientCategoryForm::class, $entity);

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * Edits an existing entity.
     *
     * @Route("/{id}", name="restaurant_ingredient_category_update")
     * @Method("POST")
     * @Security("is_granted('ROLE_RESTAURANT_CATEGORY_EDIT')")
     * @Template("MBHRestaurantBundle:Ingredient:editCategory.html.twig")
     * @ParamConverter(class="MBHRestaurantBundle:IngredientCategory")
     * @param Request $request
     * @param IngredientCategory $entity
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateCategoryAction(Request $request, IngredientCategory $entity)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(IngredientCategoryForm::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', 'Запись успешно отредактирована.');

            return $this->afterSaveRedirect('restaurant_ingredient_category', $entity->getId(), ['tab' => $entity->getId()]);
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }
    
    /**
     * @Route("/{id}/delete", name="restaurant_ingredient_category_delete")
     * @Security("is_granted('ROLE_RESTAURANT_CATEGORY_DELETE')")
     */
    public function deleteCategoryAction($id)
    {
        return $this->deleteEntity($id, 'MBHRestaurantBundle:IngredientCategory', 'restaurant_ingredient_category');
    }


    /**
     * @Route("/{id}/new/ingredient", name="restaurant_ingredient_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_RESTAURANT_INGREDIENT_NEW')")
     * @Template()
     * @ParamConverter(class="MBHRestaurantBundle:IngredientCategory")
     * @param IngredientCategory $category
     * @return array
     */
    public function newIngredientAction(IngredientCategory $category)
    {
        $hotel = $category->getHotel();
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($hotel)) {
            throw $this->createNotFoundException();
        }
        
        $ingredient = new Ingredient();

        $form = $this->createForm(IngredientForm::class, $ingredient, [
            'calcTypes' => $this->container->getParameter('mbh.units')
        ]);

        return [
            'entity' => $category,
            'form' => $form->createView()
        ];

    }

    /**
     * Creates a new ingredient.
     *
     * @Route("/{id}/create/ingredient", name="restaurant_ingredient_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_RESTAURANT_INGREDIENT_NEW')")
     * @Template("MBHRestaurantBundle:Ingredient:newIngredient.html.twig")
     * @ParamConverter(class="MBHRestaurantBundle:IngredientCategory")
     * @param Request $request
     * @param IngredientCategory $category
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createIngredientAction(Request $request, IngredientCategory $category)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($category->getHotel())) {
            throw $this->createNotFoundException();
        }

        $ingredient = new Ingredient();
        $ingredient->setCategory($category);

        $form = $this->createForm(IngredientForm::class, $ingredient, [
            'calcTypes' => $this->container->getParameter('mbh.units')
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->dm->persist($ingredient);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', 'Запись успешно создана.');

            return $this->afterSaveRedirect('restaurant_ingredient', $ingredient->getId(), ['tab' => $category->getId()]);

        }

        return array(
            'entity' => $category,
            'form' => $form->createView(),
        );
    }

    /**
     * Displays a form to edit a new entity.
     *
     * @Route("/{id}/edit/ingredient", name="restaurant_ingredient_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_RESTAURANT_INGREDIENT_EDIT')")
     * @Template()
     * @ParamConverter(class="MBHRestaurantBundle:Ingredient")
     * @param Ingredient $ingredient
     * @return array
     */
    public function editIngredientAction(Ingredient $ingredient)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($ingredient->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(IngredientForm::class, $ingredient, [
            'calcTypes' => $this->container->getParameter('mbh.units')
        ]);

        return [
            'entry' => $ingredient,
            'form' => $form->createView(),
            'logs' => $this->logs($ingredient)
        ];
    }

    /**
     * Displays a form to edit a new entity.
     *
     * @Route("/{id}/ingredient/update", name="restaurant_ingredient_update")
     * @Method("POST")
     * @Security("is_granted('ROLE_RESTAURANT_INGREDIENT_EDIT')")
     * @Template("MBHRestaurantBundle:Ingredient:editIngredient.html.twig")
     * @ParamConverter(class="MBHRestaurantBundle:Ingredient")
     * @param Request $request
     * @param Ingredient $ingredient
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateIngredientAction(Request $request, Ingredient $ingredient)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($ingredient->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(IngredientForm::class, $ingredient, [
            'calcTypes' => $this->container->getParameter('mbh.units')
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->dm->persist($ingredient);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', 'Запись успешно отредактирована.');

            return $this->afterSaveRedirect('restaurant_ingredient', $ingredient->getId(), ['tab' => $ingredient->getCategory()->getId()]);
        }

        return [
            'entry' => $ingredient,
            'form' => $form->createView(),
            'logs' => $this->logs($ingredient)
        ];
    }

    /**
     * Delete entry.
     * @Route("/{id}/ingredient/delete", name="restaurant_ingredient_delete")
     * @ParamConverter(class="MBHRestaurantBundle:Ingredient")
     * @Security("is_granted('ROLE_RESTAURANT_INGREDIENT_DELETE')")
     * @param Ingredient $ingredient
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteIngredientAction(Ingredient $ingredient)
    {
        return $this->deleteEntity($ingredient->getId(), 'MBHRestaurantBundle:Ingredient', 'restaurant_ingredient', ['tab' => $ingredient->getCategory()->getId()]);
    }

    /**
     * save entries prices
     *
     * @Route("/quicksave", name="restaurant_category_save_prices")
     * @Method("POST")
     * @Security("is_granted('ROLE_RESTAURANT_INGREDIENT_EDIT')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function savePricesAction(Request $request)
    {
        $entries = $request->get('entries');
        $hotel = $this->hotel;
        $ingredientRepository = $this->dm->getRepository('MBHRestaurantBundle:Ingredient');

        $success = true;

        foreach ($entries as $id => $data) {
            $entity = $ingredientRepository->find($id);
            $price = (float)$data['price'];
            $isEnabled = $data['is_enabled'] ?? false;

            if (!$entity || !$this->container->get('mbh.hotel.selector')->checkPermissions($hotel)) {
                continue;
            }

            $entity->setPrice($price);
            $entity->setIsEnabled((boolean)$isEnabled);

            $validator = $this->get('validator');
            $errors = $validator->validate($entity);
            if (count($errors) > 0) {
                $success = false;
                continue;
            }

            $this->dm->persist($entity);
            $this->dm->flush();
        };

        /** @var FlashBag $flashBag */
        $flashBag = $request->getSession()->getFlashBag();

        $success ?
            $flashBag->set('success', 'Цены успешно сохранены.'):
            $flashBag->set('danger', 'Внимание, не все параметры сохранены успешно');

        $activetab = $request->get('activetab')?:null;

        return $this->redirectToRoute('restaurant_ingredient_category',  ['tab' => substr($activetab,1)]);
    }



}
