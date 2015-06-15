<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Document\TaskTypeDocument;
use MBH\Bundle\HotelBundle\Form\TaskTypeType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Form\TaskType;
use MBH\Bundle\HotelBundle\Document\Task;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;



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
        $entity = new TaskTypeDocument();
        $form = $this->createForm(new TaskTypeType(), $entity);

        $entities = $this->dm->getRepository('MBHHotelBundle:TaskTypeDocument')->createQueryBuilder('s')
            ->sort('title', 'asc')
            ->getQuery()
            ->execute();

        if (!count($entities)) {
            foreach ($this->container->getParameter('mbh.default.taskType') as $default) {
                $new = new TaskTypeDocument();
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
     * @ParamConverter(class="TaskTypeDocument")
     */
    public function editAction(TaskTypeDocument $entity)
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
        return $this->deleteEntity($id, 'MBHHotelBundle:TaskTypeDocument', 'tasktype');

    }

    /**
     * Edits an existing entity.
     *
     * @Route("/{id}", name="tasktype_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHHotelBundle:TaskType:edit.html.twig")
     * @ParamConverter(class="TaskTypeDocument")
     */
    public function updateAction(Request $request, TaskTypeDocument $entity)
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