<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Lib\ClientDataTableParams;
use MBH\Bundle\HotelBundle\Document\QueryCriteria\TaskQueryCriteria;
use MBH\Bundle\HotelBundle\Document\TaskRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Symfony\Component\HttpFoundation\Request;
use MBH\Bundle\HotelBundle\Document\Task;
use MBH\Bundle\HotelBundle\Form\TaskType;

/**
 * Class TaskController
 * @Route("/task")
 */
class TaskController extends Controller
{
    /**
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
            'tasks' => []
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
    public function jsonListAction(Request $request)
    {
        /** @var \MBH\Bundle\UserBundle\Document\User $user Current User */
        $user = $this->getUser();

        $queryCriteria = new TaskQueryCriteria();
        $tableParams = ClientDataTableParams::createFromRequest($request);
        $queryCriteria->offset = $tableParams->getStart();
        $queryCriteria->limit = $tableParams->getLength();

        $sort = $tableParams->getFirstSort();
        if($sort) {
            $sort = [$sort[0] => $sort[1]];
        } else {
            $sort = ['createdAt' => -1];
        }

        $queryCriteria->sort = $sort;
        if ($request->get('status')){
            $queryCriteria->status = $request->get('status');
        }
        if ($request->get('priority')){
            $queryCriteria->priority = $request->get('priority');
        }
        $helper = $this->get('mbh.helper');
        if($request->get('begin')) {
            $queryCriteria->begin = $helper->getDateFromString($request->get('begin'));
        }
        if($request->get('end')) {
            $queryCriteria->end = $helper->getDateFromString($request->get('end'));
            $queryCriteria->end->modify('+ 23 hours 59 minutes');
        }
        /** @var TaskRepository $repository */
        $repository = $this->dm->getRepository('MBHHotelBundle:Task');
        $tasks = $repository->getAcceptableTasksByUser($user, $queryCriteria);

        $recordsTotal = $repository->createQueryBuilder()->getQuery()->count();

        return [
            'roomTypes' => $this->get('mbh.hotel.selector')->getSelected()->getRoomTypes(),
            'statuses' => $this->container->getParameter('mbh.task.statuses'),
            'priorities' => $this->container->getParameter('mbh.tasktype.priority'),
            'recordsTotal' => $recordsTotal,
            'tasks' => $tasks,
            'draw' => $request->get('draw'),
        ];
    }

    /**
     * @Route("/change_status/{id}/{status}", name="task_change_status")
     * @Method({"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     * @ParamConverter("entity", class="MBHHotelBundle:Task")
     * @todo checkAccess save performer
     */
    public function changeStatusAction(Task $entity, $status)
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
        $entity->setStatus('open');
        $entity->setPriority('average');

        $form = $this->createForm(new TaskType($this->dm), $entity, $this->getFormTaskTypeOptions());

        if ($request->isMethod(Request::METHOD_POST)) {
            if ($form->submit($request)->isValid()) {
                $this->dm->persist($entity);
                $this->dm->flush();

                $request->getSession()->getFlashBag()->set('success',
                    $this->get('translator')->trans('controller.taskTypeController.record_created_success'));

                return $this->afterSaveRedirect('task', $entity->getId());
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    private function getFormTaskTypeOptions()
    {
        $roomRepository = $this->dm->getRepository('MBHHotelBundle:Room');
        $statuses = $this->getParameter('mbh.task.statuses');

        return [
            'taskTypes' => $this->dm->getRepository('MBHHotelBundle:TaskType')->getOptCategoryGroupList(),
            'roles' => $this->container->getParameter('security.role_hierarchy.roles'),
            'statuses' => array_combine(array_keys($statuses), array_column($statuses, 'title')),
            'priorities' => $this->container->getParameter('mbh.tasktype.priority'),
            'optGroupRooms' => $roomRepository->optGroupRooms($roomRepository->getRoomsByType($this->hotel, true)),
        ];
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
        $form = $this->createForm(new TaskType($this->dm), $entity, $this->getFormTaskTypeOptions() + [
            'scenario' => TaskType::SCENARIO_EDIT
        ]);

        if($request->isMethod(Request::METHOD_PUT)) {
            if ($form->submit($request)->isValid()) {
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
