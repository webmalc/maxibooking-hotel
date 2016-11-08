<?php

namespace MBH\Bundle\RestaurantBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\RestaurantBundle\Document\DishMenuCategory;
use MBH\Bundle\RestaurantBundle\Document\DishMenuItem;
use MBH\Bundle\RestaurantBundle\Document\Ingredient;
use MBH\Bundle\RestaurantBundle\Form\DishMenuCategoryType as DishMenuCategoryForm;
use MBH\Bundle\RestaurantBundle\Form\DishMenuItemType as DishMenuItemForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DishMenuController
 * @package MBH\Bundle\RestaurantBundle\Controller
 * @Route("dishmenu")
 */
class DishMenuController extends BaseController implements CheckHotelControllerInterface
{
    /**
     * List all  category
     *
     * @Route("/", name="restaurant_dishmenu_category")
     * @Route("/", name="restaurant_dishmenu_item")
     * @Security("is_granted('ROLE_RESTAURANT_DISHMENU_ITEM_VIEW')")
     * @Template()
     */
    public function indexAction()
    {
        $entities = $this->dm->getRepository('MBHRestaurantBundle:DishMenuCategory')->createQueryBuilder('q')
            ->field('hotel.id')->equals($this->hotel->getId())
            ->sort('fullTitle', 'asc')
            ->getQuery()
            ->execute();

        return [
            'entities' => $entities
        ];
    }

    /**
     * @param Request $request
     * Displays a form to create a new category
     * @Route("/newcategory", name="restaurant_dishmenu_category_new")
     * @Security("is_granted('ROLE_RESTAURANT_DISHMENU_CATEGORY_NEW')")
     * @Template()
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function newCategoryAction(Request $request)
    {
        $entity = new DishMenuCategory();
        $entity->setHotel($this->hotel);
        
        $form = $this->createForm(new DishMenuCategoryForm(), $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', 'Запись успешно создана.');

            return $this->afterSaveRedirect('restaurant_dishmenu_category', $entity->getId(), ['tab' => $entity->getId()]);
        }
        
        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/{id}/edit", name="restaurant_dishmenu_category_edit")
     * @Security("is_granted('ROLE_RESTAURANT_DISHMENU_CATEGORY_EDIT')")
     * @Template()
     * @ParamConverter(class="MBHRestaurantBundle:DishMenuCategory")
     * @param Request $request
     * @param DishMenuCategory $category
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editCategoryAction(Request $request, DishMenuCategory $category)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($category->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new DishMenuCategoryForm(), $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->dm->persist($category);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', 'Запись успешно отредактирована.');

            return $this->afterSaveRedirect('restaurant_dishmenu_category', $category->getId(), ['tab' => $category->getId()]);
        }

        return [
            'entity' => $category,
            'form' => $form->createView(),
            'logs' => $this->logs($category)
        ];
    }

    /**
     * @Route("/{id}/delete", name="restaurant_dishmenu_category_delete")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Security("is_granted('ROLE_RESTAURANT_CATEGORY_DELETE')")
     */

    public function deleteCategoryAction($id)
    {
        return $this->deleteEntity($id, 'MBHRestaurantBundle:DishMenuCategory', 'restaurant_dishmenu_category');
    }


    /**
     * @Route("/{id}/new/dishmenuitem", name="restaurant_dishmenu_item_new")
     * @Template()
     * @ParamConverter("entity", class="MBHRestaurantBundle:DishMenuCategory")
     * @param Request $request
     * @param DishMenuCategory $category
     * @return array
     * @Security("is_granted('ROLE_RESTAURANT_DISHMENU_ITEM_NEW')")
     */
    public function newItemAction(Request $request,DishMenuCategory $category)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($category->getHotel())) {
            throw $this->createNotFoundException();
        }

        $ingredients = $this->dm->getRepository('MBHRestaurantBundle:Ingredient')->findByHotelByCategoryId($this->helper, $this->hotel);

        $item = new DishMenuItem();
        $item->setCategory($category);

        $form = $this->createForm(new DishMenuItemForm(), $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->dm->persist($item);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', 'Запись успешно создана.');

            return $this->afterSaveRedirect('restaurant_dishmenu_item', $item->getId(), ['tab' => $category->getId()]);
        }

        return [
            'form' => $form->createView(),
            'entry' => $item,
            'entity' => $category,
            'ingredients' => $ingredients
        ];
    }

    /**
     * Displays a form to edit a new entity.
     *
     * @Route("/{id}/edit/dishmenuitem", name="restaurant_dishmenu_item_edit")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_RESTAURANT_DISHMENU_ITEM_EDIT')")
     * @Template()
     * @ParamConverter(class="MBHRestaurantBundle:DishMenuItem")
     * @param Request $request
     * @param DishMenuItem $item
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editItemAction(Request $request, DishMenuItem $item)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($item->getHotel())) {
            throw $this->createNotFoundException();
        }

        $ingredients = $this->dm->getRepository('MBHRestaurantBundle:Ingredient')->findByHotelByCategoryId($this->helper, $this->hotel);

        $form = $this->createForm(new DishMenuItemForm(), $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->dm->persist($item);
            $this->dm->flush();
            $request->getSession()->getFlashBag()->set('success', 'Запись успешно отредактирована.');

            if ($request->get('save') !== null) {
                return $this->redirectToRoute('restaurant_dishmenu_item_edit', ['id' =>$item->getId()]);
            }

            return $this->redirectToRoute('restaurant_dishmenu_category', ['tab' => $item->getCategory()->getId()]);
        }

        return [
            'form' => $form->createView(),
            'entry' => $item,
            'entity' => $item->getCategory(),
            'logs' => $this->logs($item),
            'ingredients' => $ingredients
        ];
    }

    /**
     * Delete entry.
     * @Route("/{id}/delete/dishmenuitem", name="restaurant_dishmenu_item_delete")
     * @Security("is_granted('ROLE_RESTAURANT_DISHMENU_ITEM_DELETE')")
     * @ParamConverter(class="MBHRestaurantBundle:DishMenuItem")
     * @param DishMenuItem $dishMenuItem
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteItemAction(DishMenuItem $dishMenuItem)
    {
        return $this->deleteEntity($dishMenuItem->getId(), 'MBHRestaurantBundle:DishMenuItem', 'restaurant_dishmenu_item', ['tab' => $dishMenuItem->getCategory()->getId() ]);
    }

    /**
     * save entries prices
     *
     * @Route("/quicksave", name="restaurant_dishmenu_save_prices")
     * @Method("POST")
     * @Security("is_granted('ROLE_RESTAURANT_INGREDIENT_EDIT')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function savePricesAction(Request $request)
    {
        $entries = $request->get('entries');
        $ingredientRepository = $this->dm->getRepository('MBHRestaurantBundle:DishMenuItem');

        $success = true;

        foreach ($entries as $id => $data) {
            $entity = $ingredientRepository->find($id);
            $price = $data['price'] ?? $entity->getPrice();
            $isEnabled = $data['is_enabled'] ?? false;

            if (!$entity || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
                continue;
            }

            $entity->setPrice((float)$price);
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

        $activetab = $request->get('activetab')?:null;
        return $this->redirectToRoute('restaurant_dishmenu_category', ['tab' => substr($activetab,1)]);
    }



}
