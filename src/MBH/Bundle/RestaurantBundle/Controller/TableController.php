<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 05.07.16
 * Time: 14:25
 */

namespace MBH\Bundle\RestaurantBundle\Controller;


use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\RestaurantBundle\Document\Chair;
use MBH\Bundle\RestaurantBundle\Document\Table;
use MBH\Bundle\RestaurantBundle\Form\ChairType;
use MBH\Bundle\RestaurantBundle\Form\TableType;
use MBH\Bundle\RestaurantBundle\Form\TableTypeType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use MBH\Bundle\RestaurantBundle\Service\TableManager;


/** @Route("tables") */
class TableController extends BaseController implements CheckHotelControllerInterface
{
    /**
     * List all TableTypes category
     *
     * @Route("/", name="restaurant_table_category")
     * @Route("/", name="restaurant_table")
     * @Security("is_granted('ROLE_RESTAURANT_TABLE_VIEW')")
     * @Template()
     */
    public function indexAction()
    {
        $entities = $this->dm->getRepository('MBHRestaurantBundle:TableType')->createQueryBuilder()
            ->field('hotel.id')->equals($this->hotel->getId())
            ->sort('id', 'asc')
            ->getQuery()
            ->execute();
        return [
            'entities' => $entities
        ];

    }

    /**
     * @param Request $request
     * Displays a form to create a new category
     * @Route("/newcategory", name="restaurant_table_category_new")
     * @Security("is_granted('ROLE_RESTAURANT_CATEGORY_NEW')")
     * @Template()
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function newCategoryAction(Request $request)
    {
        $entity = new \MBH\Bundle\RestaurantBundle\Document\TableType();
        $entity->setHotel($this->hotel);

        $form = $this->createForm(new TableTypeType(), $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', $this->get('translator')->trans('restaurant.exceptions.editsuccsess'));

            return $this->afterSaveRedirect('restaurant_table_category', $entity->getId());
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/{id}/edit", name="restaurant_table_category_edit")
     * @Security("is_granted('ROLE_RESTAURANT_TABLE_CATEGORY_EDIT')")
     * @Template()
     * @Method({"GET","POST"})
     * @ParamConverter(class="MBHRestaurantBundle:TableType")
     * @param Request $request
     * @param \MBH\Bundle\RestaurantBundle\Document\TableType $category
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editCategoryAction(Request $request, \MBH\Bundle\RestaurantBundle\Document\TableType $category)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($category->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new TableTypeType(), $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->dm->persist($category);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', $this->get('translator')->trans('restaurant.exceptions.editsuccsess'));

            return $this->afterSaveRedirect('restaurant_table_category', $category->getId());
        }

        return [
            'entity' => $category,
            'form' => $form->createView(),
            'logs' => $this->logs($category)
        ];
    }

    /**
     * @Route("/{id}/delete", name="restaurant_table_category_delete")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Security("is_granted('ROLE_RESTAURANT_TABLE_CATEGORY_DELETE')")
     */

    public function deleteCategoryAction($id)
    {
        return $this->deleteEntity($id, 'MBHRestaurantBundle:TableType', 'restaurant_table_category');
    }

    /**
     * @Route("/{id}/new/tabletype", name="restaurant_table_item_new")
     * @Template()
     * @ParamConverter("entity", class="MBHRestaurantBundle:TableType")
     * @param Request $request
     * @param \MBH\Bundle\RestaurantBundle\Document\TableType $category
     * @return array
     * @Security("is_granted('ROLE_RESTAURANT_TABLE_ITEM_NEW')")
     */
    public function newTableAction(Request $request, \MBH\Bundle\RestaurantBundle\Document\TableType $category)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($category->getHotel())) {
            throw $this->createNotFoundException();
        }

        $table = new Table();
        $table->setCategory($category);

        $form = $this->createForm(new TableType($this->dm), $table);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->dm->persist($table);
            $this->dm->flush();
            $request->getSession()->getFlashBag()->set('success', $this->get('translator')->trans('restaurant.exceptions.editsuccsess'));
            return $this->afterSaveRedirect('restaurant_table', $table->getId(), ['tab' => $category->getId()]);
        }
        return array(
            'entity' => $category,
            'form' => $form->createView(),
        );
    }

    /**
     * Displays a form to edit a new entity.
     *
     * @Route("/{id}/edit/tabletype", name="restaurant_table_edit")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_RESTAURANT_TABLE_ITEM_EDIT')")
     * @Template()
     * @ParamConverter(class="MBHRestaurantBundle:Table")
     * @param Request $request
     * @param Table $item
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editTableAction(Request $request, Table $item)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($item->getHotel())) {
            throw $this->createNotFoundException();
        }
        
        $form = $this->createForm(new TableType($this->dm), $item, [
            'tableId' => $item->getId()
        ]);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $this->dm->persist($item);
            $this->dm->flush();
            $request->getSession()->getFlashBag()->set('success', $this->get('translator')->trans('restaurant.exceptions.editsuccsess'));

            if ($request->get('save') !== null) {
                return $this->redirectToRoute('restaurant_table_edit', ['id' => $item->getId()]);
            }

            return $this->redirectToRoute('restaurant_table_category', ['tab' => $item->getCategory()->getId()]);
        }

        return [
            'form' => $form->createView(),
            'entry' => $item,
            'entity' => $item->getCategory(),
            'logs' => $this->logs($item),
        ];
    }

    /**
     * Delete entry.
     * @Route("/{id}/delete/tabletype", name="restaurant_table_delete")
     * @Security("is_granted('ROLE_RESTAURANT_TABLE_ITEM_DELETE')")
     * @ParamConverter(class="MBHRestaurantBundle:Table")
     * @param Table $item
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteItemAction(Table $item)
    {
        return $this->deleteEntity($item->getId(), 'MBHRestaurantBundle:Table', 'restaurant_table', ['tab' => $item->getCategory()->getId()]);
    }

    /**
     * @Route("/quicksave", name="restaurant_table_save")
     * @Method("GET")
     * @Security("is_granted('ROLE_RESTAURANT_TABLE_EDIT')")
     * @param Request $request
     * @return RedirectResponse
     */
    public function quickSaveAction(Request $request)
    {
        $entries = $request->get('entries');

        $tableRepository = $this->dm->getRepository('MBHRestaurantBundle:Table');

        $success = true;

        if ($entries) {
            foreach ($entries as $id => $data) {
                $entity = $tableRepository->find($id);
                $isEnabled = $data['is_enabled'] ?? false;

                if (!$entity || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
                    continue;
                }

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
                $flashBag->set('success', $this->get('translator')->trans('restaurant.exceptions.edittables')) :
                $flashBag->set('danger', $this->get('translator')->trans('restaurant.exceptions.danger'));
        }

        return $this->redirectToRoute('restaurant_table');
    }


    /**
     * Displays a form to edit a new entity.
     *
     * @Route("/{id}/new/chair", name="restaurant_chair_new")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_RESTAURANT_TABLE_ITEM_EDIT')")
     * @Template()
     * @ParamConverter(class="MBHRestaurantBundle:Table")
     * @param Request $request
     * @param Table $item
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function newChairAction(Request $request, Table $item)
    {
        $chairDescription = [
            'adult' => '',
            'type' => false,
        ];

        $form = $this->createForm(ChairType::class, $chairDescription);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $chairDescription = $form->getData();
            /** @var TableManager $generator */
            $generator = $this->get('mbh.table_manager');
            $generator->generateChair($chairDescription['adult'], $chairDescription['type'], $item);

            $request->getSession()->getFlashBag()->set('success', $this->get('translator')->trans('restaurant.exceptions.editsuccsess'));

            if ($request->get('save') !== null) {
                return $this->redirectToRoute('restaurant_chair_new', ['id' => $item->getId()]);
            }
            return $this->redirectToRoute('restaurant_table_category', ['tab' => $item->getCategory()->getId()]);

        }

        return [
            'entry' => $item,
            'form' => $form->createView(),
        ];

    }

    /**
     * @Route("/{id}/add/chair", name="restaurant_chair_add")
     * @Security("is_granted('ROLE_RESTAURANT_TABLE_ITEM_NEW')")
     * @ParamConverter(class="MBHRestaurantBundle:Table")
     * @param Table $item
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addChairAction(Request $request, Table $item)
    {
        $generator = $this->get('mbh.table_manager');
        $generator->generateChair(1, false, $item);
        return $this->redirectToRoute('restaurant_chair_new', ['id' => $item->getId()]);
    }

    /**
     * Delete entry.
     * @Route("/{id}/delete/chair", name="restaurant_chair_delete")
     * @Security("is_granted('ROLE_RESTAURANT_TABLE_ITEM_DELETE')")
     * @ParamConverter(class="MBHRestaurantBundle:Chair")
     * @param Chair $item
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteChairAction(Chair $item)
    {
        return $this->deleteEntity($item->getId(), 'MBHRestaurantBundle:Chair', 'restaurant_chair_new', ['id' => $item->getTable()->getId()]);
    }

}