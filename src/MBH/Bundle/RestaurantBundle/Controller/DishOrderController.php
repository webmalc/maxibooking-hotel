<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 04.07.16
 * Time: 11:24
 */

namespace MBH\Bundle\RestaurantBundle\Controller;


use Doctrine\ODM\MongoDB\Cursor;
use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\BaseBundle\Lib\ClientDataTableParams;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\PackageBundle\Lib\DeleteException;
use MBH\Bundle\RestaurantBundle\Document\DishMenuItemRepository;
use MBH\Bundle\RestaurantBundle\Document\DishOrderCriteria;
use MBH\Bundle\RestaurantBundle\Document\DishOrderItem;
use MBH\Bundle\RestaurantBundle\Form\DishOrder\DIshOrderFilterType;
use MBH\Bundle\RestaurantBundle\Form\DishOrder\DishOrderItemType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/** @Route("dishorder") */

class DishOrderController extends BaseController implements CheckHotelControllerInterface
{
    /**
     * @Route("/", name="restaurant_dishorder_list")
     * @Security("is_granted('ROLE_RESTAURANT_ORDER_MANAGER_VIEW')")
     * @Template()
     */
    public function indexAction()
    {
        $entities = $this->dm->getRepository('MBHRestaurantBundle:DishOrderItem')->createQueryBuilder('q')
            ->field('hotel.id')->equals($this->hotel->getId())
            ->sort('id', 'asc')
            ->getQuery()
            ->execute();

        $form = $this->createForm(new DIshOrderFilterType());
        
        return [
            'entities' => $entities,
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/quicksave", name="restaurant_dishorder_quicksave")
     * @Method("POST")
     * @Security("is_granted('ROLE_RESTAURANT_ORDER_MANAGER_EDIT')")
     *
     */
    public function quickSaveOrderAction(Request $request)
    {
        return [];
    }

    /**
     * @Route("/new", name="restaurant_dishorder_new")
     * @Security("is_granted('ROLE_RESTAURANT_ORDER_MANAGER_NEW')")
     * @Template()
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function newOrderAction(Request $request)
    {
        $order = new DishOrderItem();
        $order->setHotel($this->hotel);

        /** @var DishMenuItemRepository $dishesrepos */
        $dishesrepos = $this->dm->getRepository('MBHRestaurantBundle:DishMenuItem');
        $dishes = $dishesrepos->findByHotelByCategoryId($this->helper, $order->getHotel());


        $form = $this->createForm(DishOrderItemType::class, $order);
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {
            $this->dm->persist($order);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set(
                'success',
                'restaurant.dishorder.common.addsuccess'
            );

            return $this->isSavedRequest() ?
                $this->redirectToRoute('restaurant_dishorder_edit', ['id' => $order->getId()]) :
                $this->redirectToRoute('restaurant_dishorder_list');
        }
        
        return [
            'form' => $form->createView(),
            'order' => $order,
            'dishes' => $dishes
        ];
    }

    /**
     * @Route("/{id}/edit", name="restaurant_dishorder_edit")
     * @Method({"GET","POST"})
     * @Security("is_granted('ROLE_RESTAURANT_ORDER_MANAGER_EDIT')")
     * @Template()
     * @ParamConverter(class="MBHRestaurantBundle:DishOrderItem")
     * @param Request $request
     * @param DishOrderItem $order
     * @return array|RedirectResponse
     */
    public function editOrderAction(Request $request, DishOrderItem $order)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($order->getHotel())) {
            throw $this->createNotFoundException();
        }

        if ($order->isIsFreezed()) {
            $this->denyAccessUnlessGranted('ROLE_RESTAURANT_ORDER_MANAGER_FREEZED_EDIT');
        }

        $form = $this->createForm(DishOrderItemType::class, $order);
        $form->handleRequest($request);

        $dishes = $this->dm->getRepository('MBHRestaurantBundle:DishMenuItem')->findAll();

        if ($form->isValid() && $form->isSubmitted()) {
            $this->dm->persist($order);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set(
                'success',
                'restaurant.dishorder.common.editsuccess'
            );

            return $this->isSavedRequest() ?
                $this->redirectToRoute('restaurant_dishorder_edit', ['id' => $order->getId()]) :
                $this->redirectToRoute('restaurant_dishorder_list');
        }

        return [
            'order' =>  $order,
            'form' => $form->createView(),
            'logs' => $this->logs($order),
            'dishes' => $dishes
        ];
    }

    /**
     * @Route("/{id}/delete", name="restaurant_dishorder_delete")
     * @Security("is_granted('ROLE_RESTAURANT_ORDER_MANAGER_DELETE')")
     * @ParamConverter(class="MBHRestaurantBundle:DishOrderItem")
     * @param Request $request
     * @param DishOrderItem $order
     * @return array|RedirectResponse
     */
    public function deleteOrderAction(Request $request, DishOrderItem $order)
    {
        try {
            if (!$this->container->get('mbh.hotel.selector')->checkPermissions($order->getHotel())) {
                throw $this->createNotFoundException();
            }

            if ($order->isIsFreezed()) {
                $this->denyAccessUnlessGranted('ROLE_RESTAURANT_ORDER_MANAGER_FREEZED_EDIT');
            }

            $this->dm->remove($order);
            $this->dm->flush($order);

            $request->getSession()->getFlashBag()->set('success', 'Запись успешно удалена.');
            //TODO: Как тут поступить с исключением AccessDenied, которое может произойти
        /*} catch(AccessDeniedException $e) {
            $request->getSession()->getFlashBag()->set('danger', $e->getMessage());*/

        } catch (DeleteException $e) {
            $request->getSession()->getFlashBag()->set('danger', $e->getMessage());
        }

        return $this->redirectToRoute('restaurant_dishorder_list');
    }


    /**
     * @Route("/{id}/showfreezed", name="restaurant_dishorder_showfreezed")
     * @Security("is_granted('ROLE_RESTAURANT_ORDER_MANAGER_VIEW')")
     * @Template()
     * @param DishOrderItem $order
     * @return array
     */
    public function showFreezedOrderAction(DishOrderItem $order)
    {
        return [
            'order' => $order
        ];
    }

    /**
     * @Route("/{id}/freeze", name="restaurant_dishorder_freeze")
     * @Security("is_granted('ROLE_RESTAURANT_ORDER_MANAGER_PAY')")
     * @ParamConverter(class="MBHRestaurantBundle:DishOrderItem")
     * @param DishOrderItem $order
     * @return RedirectResponse
     */
    public function freezOrderAction(DishOrderItem $order)
    {
        $order->setIsFreezed(true);

        $dm = $this->dm;
        $dm->persist($order);
        $dm->flush();

        return $this->redirectToRoute('restaurant_dishorder_list');
    }

    /**
     * Lists all entities as json.
     *
     * @Route("/json", name="restaurant_json", defaults={"_format"="json"}, options={"expose"=true})
     * @Security("is_granted('ROLE_RESTAURANT_ORDER_MANAGER_VIEW')")
     * @Template()
     * @param Request $request
     * @return array|JsonResponse
     */
    public function jsonAction(Request $request)
    {

        $tableParams = ClientDataTableParams::createFromRequest($request);

        $formRequestSubmit = (array) $request->request->get('form');
        $formRequestSubmit['search'] = $tableParams->getSearch();
        /** @var Form $form */
        $form = $this->createForm(DIshOrderFilterType::class, new DishOrderCriteria());
        $form->submit($formRequestSubmit);

        if (!$form->isValid()) {
            return new JsonResponse(['error' => $form->getErrors()[0]->getMessage()]);
        }

        /** @var DishOrderCriteria $criteria */
        $criteria = $form->getData();

        /** @var  $dishOrderRepository  */
        $dishOrderRepository = $this->dm->getRepository('MBHRestaurantBundle:DishOrderItem');
        /* @var Cursor $dishorders*/
        $dishorders = $dishOrderRepository->findByQueryCriteria($criteria, $tableParams->getStart(), $tableParams->getLength(), $this->hotel);
        
        return [
            'dishorders' => $dishorders,
            'draw' => $request->get('draw'),
            'total' => count($dishorders->toArray()),
            'recordsFiltered' => count($dishorders)
        ];
    }

}