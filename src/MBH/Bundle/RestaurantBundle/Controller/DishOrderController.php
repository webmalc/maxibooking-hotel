<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 04.07.16
 * Time: 11:24
 */

namespace MBH\Bundle\RestaurantBundle\Controller;


use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Lib\DeleteException;
use MBH\Bundle\RestaurantBundle\Document\DishOrderItem;
use MBH\Bundle\RestaurantBundle\Form\DishOrder\DishOrderItemType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
            ->sort('id', 'ascX')
            ->getQuery()
            ->execute();
        return [
            'entities' => $entities
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
     * @return Response|RedirectResponse
     */
    public function newOrderAction(Request $request)
    {
        $order = new DishOrderItem();
        $order->setHotel($this->hotel);

        $dishes = $this->dm->getRepository('MBHRestaurantBundle:DishMenuItem')->findAll();

        $form = $this->createForm(new DishOrderItemType($this->dm), $order);
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
     * @return RedirectResponse
     */
    public function editOrderAction(Request $request, DishOrderItem $order)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($order->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new DishOrderItemType($this->dm), $order);
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
     * @Template()
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
            $this->dm->remove($order);
            $this->dm->flush($order);

            $request->getSession()->getFlashBag()->set('success', 'Запись успешно удалена.');
        } catch (DeleteException $e) {
            $request->getSession()->getFlashBag()->set('danger', $e->getMessage());
        }

        return $this->redirectToRoute('restaurant_dishorder_list');
    }


    /**
     * @Route("/{id}/showfreezed", name="restaurant_dishorder_showfreezed")
     * @Security("is_granted('ROLE_RESTAURANT_ORDER_MANAGER_VIEW')")
     * @Template()
     */
    public function showFreezedOrderAction()
    {
        return [];
    }


}