<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Document\Task;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use MBH\Bundle\UserBundle\Document\User;
use MBH\Bundle\UserBundle\Form\UserType;

/**
 * Class TaskController
 * @package MBH\Bundle\HotelBundle\Controller
 */
class TaskController extends Controller
{

    /**
     * List entities
     *
     * @Route("/", name="task")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function indexAction()
    {
        return [
            'roomTypes' => $this->get('mbh.hotel.selector')->getSelected()->getRoomTypes(),
            'statuses' => $this->container->getParameter('mbh.task.statuses'),//todo translate
            'priorities' => $this->container->getParameter('mbh.tasktype.priority'),
            'tasks' => [],
        ];
    }

    /**
     * List entities
     *
     * @Route("/json_list", name="task_json", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template("MBHHotelBundle:Task:list.json.twig")
     */
    public function jsonListAction()
    {
        /** @var \MBH\Bundle\UserBundle\Document\User $user */
        $user = $this->getUser();

        $userIdentity = $user->getId();
        $userRoles = $user->getRoles();

        $criteria = [];
        if(!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $criteria['$or'] = [
                ['performer' => $userIdentity],
                ['role' => ['$in' => $userRoles]]
            ];
        }

        $repository = $this->dm->getRepository('MBHHotelBundle:Task');
        $tasks = $repository->findBy($criteria, ['createdAt' => -1], 10, 0);

        $recordsTotal = $repository->createQueryBuilder()->getQuery()->count();

        return [
            'roomTypes' => $this->get('mbh.hotel.selector')->getSelected()->getRoomTypes(),
            'statuses' => $this->container->getParameter('mbh.task.statuses'),
            'priorities' => $this->container->getParameter('mbh.tasktype.priority'),
            'recordsTotal' => $recordsTotal,
            'tasks' => $tasks
        ];
    }

    /**
     * @Route("/change_status/{id}/{status}", name="task_change_status")
     * @Method({"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     * @ParamConverter("entity", class="MBHHotelBundle:Task")
     */
    public function changeStatusAction(Request $request, Task $entity, $status)
    {
        $entity->setStatus($status);
        $violations = $this->get('validator')->validate($entity);

        if($violations->count() > 0)
            throw $this->createNotFoundException($violations->get(0)->getMessage());

        $this->dm->persist($entity);
        $this->dm->flush();
        return $this->redirect($this->generateUrl('task'));
    }

    /**
     * Displays a form to create a new entity.
     *
     * @Route("/new", name="task_new")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function newAction(Request $request)
    {
        $entity = new Task();
        $form = $this->createForm(new \MBH\Bundle\HotelBundle\Form\Task(), $entity, [
                'roles' => $this->container->getParameter('security.role_hierarchy.roles'),
                'priorities' => $this->container->getParameter('mbh.tasktype.priority')
            ]
        );

        if ($request->isMethod('POST')) {
            $form->submit($request);
            if ($form->isValid()) {
                $this->dm->persist($entity);
                $this->dm->flush();

                $request->getSession()->getFlashBag()->set('success',
                    $this->get('translator')->trans('controller.taskTypeController.record_created_success'));

                return $this->redirect($this->generateUrl('task'));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * Edits an existing entity.
     *
     * @Route("/{id}", name="task_edit")
     * @Method({"GET","PUT"})
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     * @ParamConverter("entity", class="MBHHotelBundle:Task")
     */
    public function editAction(Request $request, Task $entity)
    {
        $form = $this->createForm(new \MBH\Bundle\HotelBundle\Form\Task(), $entity, [
                'roles' => $this->container->getParameter('security.role_hierarchy.roles'),
                'priorities' => $this->container->getParameter('mbh.tasktype.priority'),
                'scenario' => \MBH\Bundle\HotelBundle\Form\Task::SCENARIO_EDIT
            ]
        );

        if($request->isMethod("PUT")) {
            $form->submit($request);

            if ($form->isValid()) {
                $this->dm->persist($entity);
                $this->dm->flush();

                $request->getSession()->getFlashBag()
                    ->set('success', $this->get('translator')->trans('controller.taskTypeController.record_edited_success'))
                ;
                return $this->afterSaveRedirect('task', $entity->getId());
            }
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }

    /**
     * Delete entity.
     *
     * @Route("/{id}/delete", name="task_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function deleteAction($id)
    {
        return $this->deleteEntity($id, 'MBHHotelBundle:Task', 'task');
    }
}
