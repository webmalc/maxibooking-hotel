<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use MBH\Bundle\HotelBundle\Form\RoomTypeCategoryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RoomTypeCategoryController
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 * @Route("/room_type/category")
 */
class RoomTypeCategoryController extends BaseController
{
    /**
     * @Route("/", name="room_type_category")
     * @Method("GET")
     * @Security("is_granted('ROLE_ROOM_TYPE_CATEGORY_VIEW')")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $repository = $this->dm->getRepository('MBHHotelBundle:RoomTypeCategory');
        $categories = $repository->findBy([
            //'hotel' => $this->hotel->getId()
        ]);

        return [
            'categories' => $categories
        ];
    }

    /**
     * @Route("/new", name="room_type_category_new")
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_ROOM_TYPE_CATEGORY_NEW')")
     * @Template()
     */
    public function newAction(Request $request)
    {
        $category = new RoomTypeCategory();
        $form = $this->createForm(new RoomTypeCategoryType(), $category, [
            'method' => Request::METHOD_PUT
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->dm->persist($category);
            $this->dm->flush();

            return $this->afterSaveRedirect('room_type_category', $category->getId());
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/edit/{id}", name="room_type_category_edit")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_ROOM_TYPE_CATEGORY_EDIT')")
     * @ParamConverter(class="MBH\Bundle\HotelBundle\DocumentС\RoomTypeCategory")
     * @Template()
     */
    public function editAction(Request $request, RoomTypeCategory $category)
    {
        $form = $this->createForm(new RoomTypeCategoryType(), $category, [
            'method' => Request::METHOD_POST
        ]);


        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->dm->persist($category);
            $this->dm->flush();

            return $this->afterSaveRedirect('room_type_category', $category->getId());
        }

        return [
            'category' => $category,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/delete/{id}", name="room_type_category_delete")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_ROOM_TYPE_CATEGORY_DELETE')")
     * @ParamConverter(class="MBH\Bundle\HotelBundle\Document\RoomTypeCategory")
     * @Template()
     */
    public function deleteAction($id)
    {
        return $this->deleteEntity($id, RoomTypeCategory::class, 'room_type_category');
    }

}