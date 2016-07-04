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
use MBH\Bundle\PackageBundle\Lib\DeleteException;
use MBH\Bundle\RestaurantBundle\Document\DishMenuItem;
use MBH\Bundle\RestaurantBundle\Document\DishOrderItem;
use MBH\Bundle\RestaurantBundle\Document\DishOrderItemEmbedded;
use MBH\Bundle\RestaurantBundle\Form\DishOrder\DishOrderItemType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

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
        return [
            'entities' => $entities
        ];
    }

    /**
     * @Route("/quicksave", name="restaurant_dishoerder_quicksave")
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


        // Для теста //
        $dish = $this->dm->getRepository('MBHRestaurantBundle:DishMenuItem')->findOneBy([
            'fullTitle' => 'Блюдо1'
        ]);
        $dishembed = new DishOrderItemEmbedded();
        $dishembed->setAmount(2);
        $dishembed->addDishMenuItem($dish);
        $order->addDishes($dishembed);
        $order->addDishes($dishembed);


        $form = $this->createForm(new DishOrderItemType(), $order);
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
            'order' => $order
        ];
    }

    /**
     * @Route("/{id}/edit", name="restaurant_dishorder_edit")
     * @Security("is_granted('ROLE_RESTAURANT_ORDER_MANAGER_EDIT')")
     * @Template()
     */
    public function editOrderAction()
    {
        return [];
    }

    /**
     * @Route("/{id}/delete", name="restaurant_dishorder_delete")
     * @Security("is_granted('ROLE_RESTAURANT_ORDER_MANAGER_DELETE')")
     * @Template()
     */
    public function deleteOrderAction()
    {
        return [];
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