<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Lib\ClientDataTableParams;
use MBH\Bundle\HotelBundle\Document\QueryCriteria\TaskQueryCriteria;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\TaskRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Symfony\Component\HttpFoundation\JsonResponse;
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
        $onlyOwned = !$this->get('security.authorization_checker')->isGranted('ROLE_TASK_MANAGER');
        $statuses = $this->container->getParameter('mbh.task.statuses');
        $priorities = $this->container->getParameter('mbh.tasktype.priority');

        if($onlyOwned) {
            $taskRepository = $this->dm->getRepository('MBHHotelBundle:Task');
            /** @var Task[] $processTasks */
            $criteria = ['performer.id' => $this->getUser()->getId()];
            $sort = ['priority' => -1, 'createdAt' => -1];
            $processTasks = $taskRepository->findBy($criteria + ['status' => 'process'], $sort);

            /** @var Task[] $tasks */
            $tasks = $taskRepository->findBy($criteria + ['status' => 'open'], $sort);

            return $this->render('MBHHotelBundle:Task:indexOnlyOwned.html.twig', [
                'processTasks' => $processTasks,
                'tasks' => $tasks,
                'priorities' => $priorities,
                'statuses' => $statuses,
            ]);
        } else {
            $performers = $this->dm->getRepository('MBHUserBundle:User')->findAll();
            $key = array_search($this->getUser(), $performers);
            if($key !== false){
                unset($performers[$key]);
            }
            array_unshift($performers, $this->getUser());

            return [
                'roomTypes' => $this->get('mbh.hotel.selector')->getSelected()->getRoomTypes(),
                'priorities' => $priorities,
                'performers' => $performers,
                'statuses' => $statuses,
                'groups' => $this->getRoleList(),
                'tasks' => []
            ];
        }
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

        $firstSort = $tableParams->getFirstSort();

        $queryCriteria->onlyOwned = !$this->get('security.authorization_checker')->isGranted('ROLE_TASK_MANAGER');//$this->get('security.context')->isGranted

        /** @var TaskRepository $taskRepository */
        $taskRepository = $this->dm->getRepository('MBHHotelBundle:Task');

        if ($queryCriteria->onlyOwned) {
            $queryCriteria->sort = $firstSort ? [$firstSort[0] => $firstSort[1]] : [];
            $queryCriteria->roles = $user->getRoles();
            $queryCriteria->performer = $user->getId();

            $tasks = $taskRepository->getAcceptableTaskForUser($queryCriteria);
        } else{
            $queryCriteria->sort = $firstSort ? [$firstSort[0] => $firstSort[1]] : ['createdAt' => -1];
            $helper = $this->get('mbh.helper');

            if ($request->get('status')) {
                $queryCriteria->status = $request->get('status');
            }
            if ($request->get('priority')) {
                $queryCriteria->priority = $request->get('priority');
            }
            if ($request->get('begin')) {
                $queryCriteria->begin = $helper->getDateFromString($request->get('begin'));
            }
            if ($request->get('end')) {
                $queryCriteria->end = $helper->getDateFromString($request->get('end'));
                $queryCriteria->end->modify('+ 23 hours 59 minutes');
            }
            if($request->get('group')) {
                $queryCriteria->roles[] = $request->get('group');
            }
            if($request->get('performer')) {
                $queryCriteria->performer = $request->get('performer');
            }
            if ($request->get('deleted') == 'true') {
                $queryCriteria->deleted = true;
            }

            $tasks = $taskRepository->getAcceptableTasksForManager($queryCriteria);
        }

        $recordsTotal = $taskRepository->getCountByCriteria($queryCriteria);

        return [
            'roomTypes' => $this->get('mbh.hotel.selector')->getSelected()->getRoomTypes(),
            'statuses' => $this->container->getParameter('mbh.task.statuses'),
            'priorities' => $this->container->getParameter('mbh.tasktype.priority'),
            'recordsTotal' => $recordsTotal,
            'tasks' => $tasks,
            'draw' => $request->get('draw'),
            'taskRepository' => $taskRepository
        ];
    }

    /**
     * @Route("/change_status/{id}/{status}", name="task_change_status", options={"expose":true})
     * @Method({"GET"})
     * @Security("is_granted('ROLE_USER')")
     * @ParamConverter("entity", class="MBHHotelBundle:Task")
     * @todo checkAccess performer
     */
    public function changeStatusAction(Task $entity, $status)
    {
        $entity->setStatus($status);
        $violations = $this->get('validator')->validate($entity);

        if ($violations->count() > 0) {
            throw $this->createNotFoundException($violations->get(0)->getMessage());
        }

        $this->dm->persist($entity);
        $this->dm->flush();

        return $this->redirectToRoute('task');
    }

    /**
     * Displays a form to create a new entity.
     *
     * @Route("/new", name="task_new")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_TASK_MANAGER')")
     * @Template()
     */
    public function newAction(Request $request)
    {
        if (!$this->get('mbh.hotel.selector')->checkPermissions($this->hotel)) {
            throw $this->createNotFoundException();
        }
        $entity = new Task();
        $entity->setStatus('open');
        $entity->setPriority(1);

        $form = $this->createForm(new TaskType($this->dm), $entity, $this->getFormTaskTypeOptions());

        if ($request->isMethod(Request::METHOD_POST)) {
            if ($form->submit($request)->isValid()) {
                /** @var Room[] $rooms */
                $rooms = $form['rooms']->getData();
                foreach ($rooms as $room) {
                    $task = clone($entity);
                    $task->setRoom($room);
                    $this->dm->persist($task);
                }
                $this->dm->flush();

                $request->getSession()->getFlashBag()->set('success',
                    $this->get('translator')->trans('controller.taskTypeController.record_created_success'));

                return $this->redirectToRoute('task');
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
        $translator = $this->get('translator');
        $priorities = $this->container->getParameter('mbh.tasktype.priority');
        $priorities = array_map(function($name) use ($translator) {
            return $translator->trans('views.task.priority.' . $name, [], 'MBHHotelBundle');
        }, $priorities);

        return [
            'taskTypes' => $this->dm->getRepository('MBHHotelBundle:TaskType')->getOptCategoryGroupList(),
            'roles' => $this->getRoleList(),
            'statuses' => array_combine(array_keys($statuses), array_column($statuses, 'title')),
            'priorities' => $priorities,
            'optGroupRooms' => $roomRepository->optGroupRooms($roomRepository->getRoomsByType($this->hotel, true)),
            'hotel' => $this->hotel
        ];
    }

    private function getRoleList()
    {
        $roles = array_keys($this->container->getParameter('security.role_hierarchy.roles'));

        return array_combine($roles, $roles);
    }

    /**
     * Edits an existing entity.
     *
     * @Route("/edit/{id}", name="task_edit")
     * @Method({"GET","PUT"})
     * @Security("is_granted('ROLE_TASK_MANAGER')")
     * @Template()
     * @ParamConverter("entity", class="MBHHotelBundle:Task")
     */
    public function editAction(Request $request, Task $entity)
    {
        if (!$this->get('mbh.hotel.selector')->checkPermissions($this->hotel)) {
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(new TaskType($this->dm), $entity, $this->getFormTaskTypeOptions() + [
                'scenario' => TaskType::SCENARIO_EDIT
            ]);

        if ($request->isMethod(Request::METHOD_PUT)) {
            if ($form->submit($request)->isValid()) {
                $this->dm->persist($entity);
                $this->dm->flush();

                $request->getSession()->getFlashBag()
                    ->set('success',
                        $this->get('translator')->trans('controller.taskTypeController.record_edited_success'));

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

    /**
     * @Route("/{id}/ajax", name="ajax_task_details", options={"expose":true})
     * @Method("GET")
     * @ParamConverter("entity", class="MBHHotelBundle:Task")
     * @Security("is_granted('ROLE_USER')")
     */
    public function ajaxTaskDerailsAction(Task $entity)
    {
        $data = [
            'role' => $entity->getRole() ?
                $this->get('translator')->trans($entity->getRole(), [], 'MBHUserBundleRoles') :
                '',
            'type' => $entity->getType() ? $entity->getType()->getTitle() : '',
            'performer' => $entity->getPerformer() ? $entity->getPerformer()->getFullName(true) : [],
            'date' => $entity->getDate() ? $entity->getDate()->format('d.m.g') : '',
            'createdAt' => $entity->getCreatedAt() ? $entity->getCreatedAt()->format('d.m.g') : '',
            'createdBy' => $entity->getCreatedBy(),
            'description' => $entity->getDescription() ? nl2br($entity->getDescription()) : '',
            'number' => $entity->getNumber(),
            'priority' => $entity->getPriority(),
            'status' => $entity->getStatus() ?
                $this->container->getParameter('mbh.task.statuses')[$entity->getStatus()]['title'] :
                '',
            //'room' => $entity->getRoom() ? $entity->getRoom()->getTitle() : '',
        ];

        return new JsonResponse($data);
    }
}
