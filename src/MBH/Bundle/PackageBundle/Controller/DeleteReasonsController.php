<?php namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\PackageBundle\Document\DeleteReason;
use MBH\Bundle\PackageBundle\Document\DeleteReasonCategory;
use MBH\Bundle\PackageBundle\Form\DeleteReasonCategoryType;
use MBH\Bundle\PackageBundle\Form\DeleteReasonsType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("management/delete_reasons")
 */
class DeleteReasonsController extends Controller implements CheckHotelControllerInterface
{
    /**
     * List all  category
     *
     * @Route("/", name="package_delete_reasons_category", options={"expose"=true})
     * @Route("/", name="package_delete_reasons_item")
     * @Route("/", name="package_delete_reasons")
     * @Security("is_granted('ROLE_PACKAGE_DELETE_REASONS')")
     * @Template()
     */
    public function indexAction()
    {
        $entities = $this->dm->getRepository(DeleteReasonCategory::class)->createQueryBuilder()
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
     * @Route("/newcategory", name="package_delete_reasons_category_new")
     * @Security("is_granted('ROLE_PACKAGE_DELETE_REASONS')")
     * @Template()
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function newCategoryAction(Request $request)
    {
        $entity = new DeleteReasonCategory();
        $entity->setHotel($this->hotel);

        $form = $this->createForm(DeleteReasonCategoryType::class, $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', 'Запись успешно создана.');

            return $this->afterSaveRedirect('package_delete_reasons_category', $entity->getId(), ['tab' => $entity->getId()]);
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/{id}/edit", name="package_delete_reasons_category_edit")
     * @Security("is_granted('ROLE_PACKAGE_DELETE_REASONS')")
     * @Template()
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editCategoryAction(Request $request, DeleteReasonCategory $category)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($category->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(DeleteReasonCategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->dm->persist($category);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', 'Запись успешно отредактирована.');

            return $this->afterSaveRedirect('package_delete_reasons_category', $category->getId(), ['tab' => $category->getId()]);
        }

        return [
            'entity' => $category,
            'form' => $form->createView(),
            'logs' => $this->logs($category)
        ];
    }

    /**
     * @Route("/{id}/delete", name="package_delete_reasons_category_delete")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Security("is_granted('ROLE_PACKAGE_DELETE_REASONS')")
     */

    public function deleteCategoryAction($id)
    {
        return $this->deleteEntity($id, 'MBHPackageBundle:DeleteReasonCategory', 'package_delete_reasons_category');
    }


    /**
     * @Route("/{id}/new/deletereasonitem", name="package_delete_reasons_item_new")
     * @Template()
     * @param Request $request
     * @return array|RedirectResponse
     * @Security("is_granted('ROLE_PACKAGE_DELETE_REASONS')")
     */
    public function newItemAction(Request $request,DeleteReasonCategory $category)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($category->getHotel())) {
            throw $this->createNotFoundException();
        }

//        $deleteReasons = $this->dm->getRepository(DeleteReason::class)->findByHotelByCategoryId($this->helper, $this->hotel);

        $item = new DeleteReason();
        $item->setCategory($category);

        $form = $this->createForm(DeleteReasonsType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->dm->persist($item);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success', 'Запись успешно создана.');

            return $this->afterSaveRedirect('package_delete_reasons_item', $item->getId(), ['tab' => $category->getId()]);
        }

        return [
            'form' => $form->createView(),
            'entry' => $item,
            'entity' => $category
//            'delete_reasons' => $ingredients
        ];
    }

    /**
     * Displays a form to edit a new entity.
     *
     * @Route("/{id}/edit/deletereasonsitem", name="package_delete_reasons_item_edit")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_PACKAGE_DELETE_REASONS')")
     * @Template("@MBHPackage/DeleteReasons/newItem.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editItemAction(Request $request, DeleteReason $item)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($item->getHotel())) {
            throw $this->createNotFoundException();
        }

        $ingredients = $this->dm->getRepository('MBHRestaurantBundle:Ingredient')->findByHotelByCategoryId($this->helper, $this->hotel);

        $form = $this->createForm(DeleteReasonsType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->dm->persist($item);
            $this->dm->flush();
            $request->getSession()->getFlashBag()->set('success', 'Запись успешно отредактирована.');

            if ($request->get('save') !== null) {
                return $this->redirectToRoute('package_delete_reasons_item_edit', ['id' =>$item->getId()]);
            }

            return $this->redirectToRoute('package_delete_reasons_category', ['tab' => $item->getCategory()->getId()]);
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
     * @Route("/{id}/delete/reasonsitem", name="package_delete_reasons_item_delete")
     * @Security("is_granted('ROLE_PACKAGE_DELETE_REASONS')")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteItemAction(DeleteReason $dishMenuItem)
    {
        return $this->deleteEntity($dishMenuItem->getId(), 'MBHRestaurantBundle:DishMenuItem', 'package_delete_reasons_item', ['tab' => $dishMenuItem->getCategory()->getId() ]);
    }

}