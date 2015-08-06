<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Document\TaskTypeRepository;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use MBH\Bundle\HotelBundle\Document\TaskTypeCategory;
use MBH\Bundle\HotelBundle\Form\TaskTypeCategoryType;


/**
 * Class TaskTypeCategoryController
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 * @Route("/tasktype/category")
 */
class TaskTypeCategoryController extends Controller
{
    /**
     * @Route("/new", name="task_type_category_new")
     * @Method({"GET","PUT"})
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function newAction(Request $request)
    {
        $entity = new TaskTypeCategory();
        $form = $this->createForm(new TaskTypeCategoryType(), $entity);

        if($request->isMethod(Request::METHOD_PUT)){
            if($form->submit($request)->isValid()){
                $entity->setIsSystem(false);
                $this->dm->persist($entity);
                $this->dm->flush();

                return $this->isSavedRequest() ?
                    $this->redirectToRoute('task_type_category_edit', ['id' => $entity->getId()]) :
                    $this->redirectToRoute('tasktype', ['category' => $entity->getId()]);
            };
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="task_type_category_edit")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     * @ParamConverter("entity", class="MBHHotelBundle:TaskTypeCategory")
     */
    public function editAction(TaskTypeCategory $entity, Request $request)
    {
        $form = $this->createForm(new TaskTypeCategoryType($this->dm), $entity, []);

        if($request->isMethod(Request::METHOD_POST)){
            if($form->submit($request)->isValid()){
                //$entity->setIsSystem(false);
                $this->dm->persist($entity);
                $this->dm->flush();

                return $this->isSavedRequest() ?
                    $this->redirectToRoute('task_type_category_edit', ['id' => $entity->getId()]) :
                    $this->redirectToRoute('tasktype', ['category' => $entity->getId()]);
            };
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     *
     * @Route("/{id}/delete", name="task_type_category_delete")
     * @Method({"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     * @ParamConverter("entity", class="MBHHotelBundle:TaskTypeCategory")
     */
    public function deleteAction(TaskTypeCategory $entity)
    {
        if($entity->isSystem()) {
            throw $this->createNotFoundException();
        }
        /** @var TaskTypeRepository $repository */
        $repository = $this->dm->getRepository('MBHHotelBundle:TaskType');
        if($repository->getCountByCategory($entity) > 0) {
            throw $this->createNotFoundException('Category have task types');
        }
        return $this->deleteEntity($entity->getId(), get_class($entity), 'tasktype');
    }
}