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
use MBH\Bundle\RestaurantBundle\Document\DishOrderCriteria;
use MBH\Bundle\RestaurantBundle\Document\DishOrderItem;
use MBH\Bundle\RestaurantBundle\Document\DishOrderItemRepository;
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
     * @Route("/", name="restaurant_dishorder")
     * @Security("is_granted('ROLE_RESTAURANT_DISHORDER_VIEW')")
     * @Template()
     */
    public function indexAction()
    {

        /** @var Form $form */
        $form = $this->createForm(DIshOrderFilterType::class);

        $isDishes = (bool)$this->dm
            ->getRepository('MBHRestaurantBundle:DishMenuItem')
            ->createQueryBuilder()
            ->getQuery()
            ->count();

        return [
            'form' => $form->createView(),
            'isDishes' => $isDishes
        ];
    }

    /**
     * @Route("/new", name="restaurant_dishorder_new")
     * @Security("is_granted('ROLE_RESTAURANT_DISHORDER_NEW')")
     * @Template()
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function newOrderAction(Request $request)
    {
        $order = new DishOrderItem();
        $order->setHotel($this->hotel);

        $dishes = $this->dm->getRepository('MBHRestaurantBundle:DishMenuItem')->findByHotelByCategoryId(
            $this->helper,
            $order->getHotel()
        );

        $form = $this->createForm(DishOrderItemType::class, $order);
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {
            $this->dm->persist($order);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set(
                'success',
                'restaurant.dishorder.common.addsuccess'
            );

            return $this->afterSaveRedirect('restaurant_dishorder', $order->getId());
        }

        return [
            'form' => $form->createView(),
            'dishes' => $dishes,
        ];
    }

    /**
     * @Route("/{id}/edit", name="restaurant_dishorder_edit")
     * @Method({"GET","POST"})
     * @Security("is_granted('ROLE_RESTAURANT_DISHORDER_EDIT')")
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

        if ($order->isIsFreezed() && !$this->isGranted('ROLE_RESTAURANT_DISHORDER_FREEZED_EDIT')) {
            return $this->redirectToRoute('restaurant_dishorder');
        }

        $form = $this->createForm(DishOrderItemType::class, $order);
        $form->handleRequest($request);

        $dishes = $this->dm->getRepository('MBHRestaurantBundle:DishMenuItem')->findByHotelByCategoryId(
            $this->helper,
            $order->getHotel()
        );

        if ($form->isValid() && $form->isSubmitted()) {
            $this->dm->persist($order);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set(
                'success',
                'restaurant.dishorder.common.editsuccess'
            );

            return $this->afterSaveRedirect('restaurant_dishorder', $order->getId());
        }

        return [
            'order' => $order,
            'form' => $form->createView(),
            'logs' => $this->logs($order),
            'dishes' => $dishes,
        ];
    }

    /**
     * @Route("/{id}/delete", name="restaurant_dishorder_delete")
     * @Security("is_granted('ROLE_RESTAURANT_DISHORDER_DELETE')")
     * @param DishOrderItem $dishOrderItem
     * @return array|RedirectResponse
     * @internal param int $id
     * @ParamConverter(class="MBHRestaurantBundle:DishOrderItem")
     */
    public function deleteOrderAction(DishOrderItem $dishOrderItem)
    {
        if ($dishOrderItem->isIsFreezed() && !$this->isGranted('ROLE_RESTAURANT_DISHORDER_FREEZED_DELETE')) {
            return $this->redirectToRoute('restaurant_dishorder');
        }

        return $this->deleteEntity(
            $dishOrderItem->getId(),
            'MBHRestaurantBundle:DishOrderItem',
            'restaurant_dishorder'
        );
    }


    /**
     * @Route("/{id}/showfreezed", name="restaurant_dishorder_showfreezed")
     * @Security("is_granted('ROLE_RESTAURANT_DISHORDER_VIEW')")
     * @Template()
     * @param DishOrderItem $order
     * @return array
     */
    public function showFreezedOrderAction(DishOrderItem $order)
    {
        return [
            'order' => $order,
        ];
    }

    /**
     * @Route("/{id}/freeze", name="restaurant_dishorder_freeze")
     * @Security("is_granted('ROLE_RESTAURANT_DISHORDER_PAY')")
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

        return $this->redirectToRoute('restaurant_dishorder');
    }

    /**
     * Lists all entities as json.
     *
     * @Route("/json", name="restaurant_json", defaults={"_format"="json"}, options={"expose"=true})
     * @Security("is_granted('ROLE_RESTAURANT_DISHORDER_VIEW')")
     * @Template()
     * @param Request $request
     * @return array|JsonResponse
     */
    public function jsonAction(Request $request)
    {

        $tableParams = ClientDataTableParams::createFromRequest($request);

        $formRequestSubmit = (array)$request->request->get('form');
        $formRequestSubmit['search'] = $tableParams->getSearch();
        /** @var Form $form */
        $form = $this->createForm(DIshOrderFilterType::class, new DishOrderCriteria());
        $form->submit($formRequestSubmit);


        if (!$form->isValid()) {
            return new JsonResponse(['error' => $form->getErrors()[0]->getMessage()]);
        }

        /** @var DishOrderCriteria $criteria */
        $criteria = $form->getData();

        /** @var  DishOrderItemRepository $dishOrderRepository */
        $dishOrderRepository = $this->dm->getRepository('MBHRestaurantBundle:DishOrderItem');
        /* @var Cursor $dishorders */
        $qb = $dishOrderRepository->getQueryBuilderQueryCriteria(
            $criteria,
            $tableParams->getStart(),
            $tableParams->getLength(),
            $tableParams->getFirstSort(),
            $this->hotel
        );
        $dishorders = $dishOrderRepository->fetchByQueryBuilder($qb);
        //TODO: Rebuild to map reduce
        /*$summary = $dishOrderRepository->getSummary($qb);*/
        $dishOrderArray = $dishorders->toArray();
        array_walk($dishOrderArray, function ($dishOrderItem) use (&$summary) {
            /** @var DishOrderItem $dishOrderItem */
            $summary += $dishOrderItem->getPrice();
        });
        return [
            'dishorders' => $dishorders,
            'draw' => $request->get('draw'),
            'total' => count($dishorders->toArray()),
            'recordsFiltered' => count($dishorders),
            'restaurant_order_total' => $summary
        ];
    }

}