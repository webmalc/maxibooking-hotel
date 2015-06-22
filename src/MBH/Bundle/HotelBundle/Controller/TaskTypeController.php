<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Document\TaskType;
use MBH\Bundle\HotelBundle\Form\TaskTypeType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


/**
 * Class TaskTypeController
 * @package MBH\Bundle\HotelBundle\Controller
 * @Route("/tasktype")
 */
class TaskTypeController extends Controller
{
    /**
     *
     * @Route("/", name="tasktype")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $entity = new TaskType();
        $form = $this->createForm(new TaskTypeType(), $entity);

        $entities = $this->dm->getRepository('MBHHotelBundle:TaskType')->createQueryBuilder('s')
            ->sort('title', 'asc')
            ->getQuery()
            ->execute();

        if (!count($entities)) {
            foreach ($this->container->getParameter('mbh.default.taskType') as $default) {
                $new = new TaskType();
                $new->setTitle($default);
                $this->dm->persist($new);
            }
            $this->dm->flush();
        }

        if ($request->isMethod('POST')) {
            $form->submit($request);

            if($form->isValid()) {
                $this->dm->persist($entity);
                $this->dm->flush();

                $request->getSession()->getFlashBag()
                    ->set('success', $this->get('translator')->trans('controller.taskTypeController.record_created_success'));
                return $this->redirect($this->generateUrl('tasktype'));
            }
        }
        return [
            'form' => $form->createView(),
            'entities' => $entities
        ];
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="tasktype_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     * @ParamConverter("entity", class="MBHHotelBundle:TaskType")
     */
    public function editAction(TaskType $entity)
    {
        $form = $this->createForm(new TaskTypeType(), $entity, []);

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }


    /**
     * Delete entity.
     *
     * @Route("/{id}/delete", name="tasktype_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function deleteAction($id)
    {
        return $this->deleteEntity($id, 'MBHHotelBundle:TaskType', 'tasktype');

    }

    /**
     * Edits an existing entity.
     *
     * @Route("/{id}", name="tasktype_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHHotelBundle:TaskType:edit.html.twig")
     * @ParamConverter("entity", class="MBHHotelBundle:TaskType")
     */
    public function updateAction(Request $request, TaskType $entity)
    {
        $form = $this->createForm(new TaskTypeType(), $entity, []);

        $form->submit($request);

        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.TaskTypeController.record_edited_success'))
            ;
            return $this->afterSaveRedirect('tasktype', $entity->getId());
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }
}