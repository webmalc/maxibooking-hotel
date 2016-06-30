<?php

namespace MBH\Bundle\RestaurantBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\PackageBundle\Lib\DeleteException;
use MBH\Bundle\RestaurantBundle\Document\Ingredient;
use MBH\Bundle\RestaurantBundle\Document\IngredientCategory;
use MBH\Bundle\RestaurantBundle\Form\IngredientCategoryType as IngredientCategoryForm;
use MBH\Bundle\RestaurantBundle\Form\IngredientType as IngredientForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @Security("is_granted('ROLE_RESTAURANT_INGREDIENT_VIEW')")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $entities = $this->dm->getRepository('MBHRestaurantBundle:IngredientCategory')->createQueryBuilder('q')
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
     * save entries prices
     *
     * @Route("/quicksave", name="restaurant_category_save_prices")
     * @Method("POST")
     * @Security("is_granted('ROLE_RESTAURANT_INGREDIENT_EDIT')")
     *
     */
    public function savePricesAction(Request $request)
    {
        $entries = $request->get('entries');
        $ingredientRepository = $this->dm->getRepository('MBHRestaurantBundle:Ingredient');

        $success = true;

        foreach ($entries as $id => $data) {
            $entity = $ingredientRepository->find($id);
            $price = (float)$data['price'];
            $isEnabled = $data['is_enabled'] ?? false;

            if (!$entity || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
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

        $flashBag = $request->getSession()->getFlashBag();

        $success ?
            $flashBag->set('success', 'Цены успешно сохранены.'):
            $flashBag->set('danger', 'Внимание, не все параметры сохранены успешно');


        return $this->redirectToRoute('restaurant_ingredient_category');
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

            return $this->afterSaveRedirect('restaurant_ingredient_category', $entity->getId(), ['tab' => $entity->getId()]);

        }
        return [
            'entity' => $entity,
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

        $form = $this->createForm(new IngredientCategoryForm(), $entity);

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
     * @Method("PUT")
     * @Security("is_granted('ROLE_RESTAURANT_CATEGORY_EDIT')")
     * @Template("MBHRestaurantBundle:Ingredient:editCategory.html.twig")
     * @ParamConverter(class="MBHRestaurantBundle:IngredientCategory")
     */
    public function updateCategoryAction(Request $request, IngredientCategory $entity)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new IngredientCategoryForm(), $entity);
        $form->submit($request);

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
     * Delete entity.
     *
     * @Route("/{id}/delete", name="restaurant_ingredient_category_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_RESTAURANT_CATEGORY_DELETE')")
     */
    public function deleteCategoryAction(IngredientCategory $category)
    {
        return $this->deleteEntity($category->getId(), 'MBHRestaurantBundle:IngredientCategory', 'restaurant_ingredient_category');
    }
    
    
    /**
     * @Route("/{id}/new/ingredient", name="restaurant_ingredient_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_RESTAURANT_INGREDIENT_NEW')")
     * @Template()
     * @ParamConverter(class="MBHRestaurantBundle:IngredientCategory")
     *
     */
    public function newIngredientAction(IngredientCategory $entity)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }
        
        $ingredient = new Ingredient();

        $form = $this->createForm(new IngredientForm(), $ingredient, [
            'calcTypes' => $this->container->getParameter('mbh.units')
        ]);

        return [
            'entry' => $ingredient,
            'entity' => $entity,
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
     */
    public function createIngredientAction(Request $request, IngredientCategory $entity)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        $ingredient = new Ingredient();
        $ingredient->setCategory($entity);

        $form = $this->createForm(new IngredientForm(), $ingredient, [
            'calcTypes' => $this->container->getParameter('mbh.units')
        ]);

        $form->submit($request);

        if ($form->isValid()) {
            $this->dm->persist($ingredient);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', 'Запись успешно создана.');

            return $this->isSavedRequest() ?
                $this->redirectToRoute('restaurant_ingredient_edit', ['id' => $ingredient->getId()]) :
                $this->redirectToRoute('restaurant_ingredient_category', ['tab' => $entity->getId()]);
        }

        return array(
            'entity' => $entity,
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
     */
    public function editIngredientAction(Ingredient $ingredient)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($ingredient->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new IngredientForm(), $ingredient, [
            'calcTypes' => $this->container->getParameter('mbh.units')
        ]);

        return [
            'entry' => $ingredient,
            'entity' => $ingredient->getCategory(),
            'form' => $form->createView(),
            'logs' => $this->logs($ingredient)
        ];
    }

    /**
     * Displays a form to edit a new entity.
     *
     * @Route("/{id}/ingredient/update", name="restaurant_ingredient_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_RESTAURANT_INGREDIENT_EDIT')")
     * @Template("MBHRestaurantBundle:Ingredient:editIngredient.html.twig")
     * @ParamConverter(class="MBHRestaurantBundle:Ingredient")
     */
    public function updateIngredientAction(Request $request, Ingredient $ingredient)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($ingredient->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new IngredientForm(), $ingredient, [
            'calcTypes' => $this->container->getParameter('mbh.units')
        ]);

        $form->submit($request);
        if ($form->isValid()) {
            $this->dm->persist($ingredient);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', 'Запись успешно отредактирована.');

            if ($request->get('save') !== null) {
                return $this->redirectToRoute('restaurant_ingredient_edit', ['id' => $ingredient->getId()]);
            }

            return $this->redirectToRoute('restaurant_ingredient_category', ['tab' => $ingredient->getCategory()->getId()]);
        }

        return [
            'entry' => $ingredient,
            'entity' => $ingredient->getCategory(),
            'form' => $form->createView(),
            'logs' => $this->logs($ingredient)
        ];
    }

    /**
     * Delete entry.
     * @Route("/{id}/ingredient/delete", name="restaurant_ingredient_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_RESTAURANT_INGREDIENT_DELETE')")
     * @ParamConverter(class="MBHRestaurantBundle:Ingredient")
     */
    public function deleteIngredientAction(Request $request, Ingredient $ingredient)
    {
        try {
            if (!$this->container->get('mbh.hotel.selector')->checkPermissions($ingredient->getHotel())) {
                throw $this->createNotFoundException();
            }
            $this->dm->remove($ingredient);
            $this->dm->flush($ingredient);

            $request->getSession()->getFlashBag()->set('success', 'Запись успешно удалена.');
        } catch (DeleteException $e) {
            $request->getSession()->getFlashBag()->set('danger', $e->getMessage());
        }

        return $this->redirectToRoute('restaurant_ingredient_category', ['tab' => $ingredient->getCategory()->getId()]);
    }

}
