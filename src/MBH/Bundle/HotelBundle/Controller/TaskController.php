<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Lib\ClientDataTableParams;
use MBH\Bundle\BaseBundle\Service\Messenger\NotifierMessage;
use MBH\Bundle\HotelBundle\Document\QueryCriteria\TaskQueryCriteria;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\Task;
use MBH\Bundle\HotelBundle\Document\TaskRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
     * @Security("is_granted('ROLE_TASK_OWN_VIEW')")
     */
    public function indexAction()
    {
        $authorizationChecker = $this->get('security.authorization_checker');

        $statuses = $this->container->getParameter('mbh.task.statuses');
        $priorities = $this->container->getParameter('mbh.tasktype.priority');

        if ($authorizationChecker->isGranted('ROLE_TASK_VIEW')) {
            $performers = $this->dm->getRepository('MBHUserBundle:User')->findAll();
            $key = array_search($this->getUser(), $performers);
            if ($key !== false) {
                unset($performers[$key]);
            }
            array_unshift($performers, $this->getUser());

            return $this->render('MBHHotelBundle:Task:index.html.twig', [
                'roomTypes' => $this->get('mbh.hotel.selector')->getSelected()->getRoomTypes(),
                'priorities' => $priorities,
                'performers' => $performers,
                'statuses' => $statuses,
                'groups' => $this->getRoleList(),
                'tasks' => []
            ]);
        } elseif ($authorizationChecker->isGranted('ROLE_TASK_OWN_VIEW')) {
            $taskRepository = $this->dm->getRepository('MBHHotelBundle:Task');
            /** @var Task[] $processTasks */
            $criteria = ['performer.id' => $this->getUser()->getId()];
            $sort = ['priority' => -1, 'createdAt' => -1];
            $processTasks = $taskRepository->findBy($criteria + ['status' => Task::STATUS_PROCESS], $sort);

            $taskQueryCriteria = new TaskQueryCriteria();
            $taskQueryCriteria->roles = $this->getUser()->getRoles();
            $taskQueryCriteria->performer = $this->getUser()->getId();
            $taskQueryCriteria->onlyOwned = true;
            $taskQueryCriteria->status = Task::STATUS_OPEN;
            $taskQueryCriteria->sort = $sort;
            $taskQueryCriteria->hotel = $this->hotel;
            /** @var Task[] $tasks */
            $tasks = $taskRepository->getAcceptableTasks($taskQueryCriteria);

            return $this->render('MBHHotelBundle:Task:indexOnlyOwned.html.twig', [
                'processTasks' => $processTasks,
                'tasks' => $tasks,
                'priorities' => $priorities,
                'statuses' => $statuses,
            ]);
        } else {
            throw $this->createAccessDeniedException();
        }
    }

    private function getRoleList()
    {
        $roles = array_keys($this->container->getParameter('security.role_hierarchy.roles'));

        return array_combine($roles, $roles);
    }

    /**
     * List entities
     *
     * @Route("/json_list", name="task_json", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_TASK_MANAGER')")
     * @Template("MBHHotelBundle:Task:list.json.twig")
     */
    public function jsonListAction(Request $request)
    {
        $queryCriteria = new TaskQueryCriteria();
        $queryCriteria->onlyOwned = false;

        $tableParams = ClientDataTableParams::createFromRequest($request);
        $queryCriteria->offset = $tableParams->getStart();
        $queryCriteria->limit = $tableParams->getLength();
        $queryCriteria->hotel = $this->hotel;
        $firstSort = $tableParams->getFirstSort();
        /** @var TaskRepository $taskRepository */
        $taskRepository = $this->dm->getRepository('MBHHotelBundle:Task');

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
        if ($request->get('group')) {
            $queryCriteria->roles[] = $request->get('group');
        }
        if ($request->get('performer')) {
            $queryCriteria->performer = $request->get('performer');
        }
        if ($request->get('deleted') == 'true') {
            $queryCriteria->deleted = true;
        }

        $tasks = $taskRepository->getAcceptableTasks($queryCriteria);
        $recordsTotal = $taskRepository->getCountByCriteria($queryCriteria);

        return [
            'roomTypes' => $this->hotel->getRoomTypes(),
            'statuses' => $this->container->getParameter('mbh.task.statuses'),
            'priorities' => $this->container->getParameter('mbh.tasktype.priority'),
            'recordsTotal' => $recordsTotal,
            'tasks' => iterator_to_array($tasks),
            'draw' => $request->get('draw'),
            'taskRepository' => $taskRepository
        ];
    }

    /**
     * @Route("/change_status/{id}/{status}", name="task_change_status", options={"expose":true})
     * @Method({"GET"})
     * @Security("is_granted('ROLE_TASK_OWN_VIEW')")
     * @ParamConverter("entity", class="MBHHotelBundle:Task")
     */
    public function changeStatusAction(Task $entity, $status)
    {
        $entity->setStatus($status);
        $room = $entity->getRoom();

        if ($status == Task::STATUS_PROCESS) {
            if (!$entity->getPerformer()) {
                $entity->setPerformer($this->getUser());
            }
            $entity->setStart(new \DateTime());
        } elseif ($status == Task::STATUS_CLOSED) {
            $entity->setEnd(new \DateTime());
        }
        $this->dm->persist($room);

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
        $entity->setStatus(Task::STATUS_OPEN);
        $entity->setPriority(Task::PRIORITY_AVERAGE);

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
                $this->sendNotifications($task);

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
        $statuses = $this->getParameter('mbh.task.statuses');
        $translator = $this->get('translator');
        $priorities = $this->container->getParameter('mbh.tasktype.priority');
        $priorities = array_map(function ($name) use ($translator) {
            return $translator->trans('views.task.priority.' . $name, [], 'MBHHotelBundle');
        }, $priorities);

        return [
            'roles' => $this->getRoleList(),
            'statuses' => array_combine(array_keys($statuses), array_column($statuses, 'title')),
            'priorities' => $priorities,
            'hotel' => $this->hotel
        ];
    }

    private function sendNotifications(Task $task)
    {
        $recipients = [];
        if ($task->getRole()) {
            $recipients = $this->dm->getRepository('MBHUserBundle:User')->findBy([
                'roles' => $task->getRole(),
                'taskNotify' => true,
                'email' => ['$exists' => true],
                'enabled' => true,
            ]);
        }

        if ($task->getPerformer() && $task->getPerformer()->getTaskNotify() && !in_array($task->getPerformer(), $recipients)) {
            $recipients[] = $task->getPerformer();
        }

        if ($recipients) {
            $translator = $this->get('translator');
            $message = new NotifierMessage();
            $message->setSubject($translator->trans('mailer.new_task.subject'));
            $message->setText($translator->trans('mailer.new_task.text', ['%title%' => $task->getType()->getTitle()]));
            $message->setLink($this->generateUrl('task'));
            foreach ($recipients as $recipient) {
                $message->addRecipient([$recipient->getEmail() => $recipient->getFullName(true)]);
            }
            $this->get('mbh.notifier.mailer')->setMessage($message)->notify();
        }
    }

    /**
     * Edits an existing entity.
     *
     * @Route("/edit/{id}", name="task_edit")
     * @Method({"GET","PUT"})
     * @Security("is_granted('ROLE_TASK_EDIT')")
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
     * @Security("is_granted('ROLE_TASK_DELETE')")
     */
    public function deleteAction($id)
    {
        return $this->deleteEntity($id, 'MBHHotelBundle:Task', 'task');
    }

    /**
     * @Route("/{id}/ajax", name="ajax_task_details", options={"expose":true})
     * @Method("GET")
     * @ParamConverter("entity", class="MBHHotelBundle:Task")
     * @Security("is_granted('ROLE_STAFF')")
     */
    public function ajaxTaskDerailsAction(Task $entity)
    {
        $priorities = $this->getParameter('mbh.tasktype.priority');
        $data = [
            'id' => $entity->getId(),
            'role' => $entity->getRole() ?
                $this->get('translator')->trans($entity->getRole(), [], 'MBHUserBundleRoles') :
                '',
            'type' => $entity->getType() ? $entity->getType()->getTitle() : '',
            'performer' => $entity->getPerformer() ? $entity->getPerformer()->getFullName(true) : [],
            'date' => $entity->getDate() ? $entity->getDate()->format('d.m.Y') : '',
            'createdAt' => $entity->getCreatedAt() ? $entity->getCreatedAt()->format('d.m.Y') : '',
            'createdBy' => $entity->getCreatedBy(),
            'description' => $entity->getDescription() ? nl2br($entity->getDescription()) : '',
            'room' => $entity->getRoom()->getName(),
            'priority' => is_int($entity->getPriority()) ?
                $this->get('translator')->trans('views.task.priority.' . $priorities[$entity->getPriority()], [],
                    'MBHHotelBundle') :
                '',
            'status' => $entity->getStatus() ?
                $this->container->getParameter('mbh.task.statuses')[$entity->getStatus()]['title'] :
                '',
        ];

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax/my_total", name="task_ajax_total_my_open", options={"expose": true})
     * @Method("GET")
     * @Security("is_granted('ROLE_TASK_OWN_VIEW')")
     */
    public function ajaxMyOpenTaskTotal()
    {
        $queryCriteria = new TaskQueryCriteria();
        $queryCriteria->roles = $this->getUser()->getRoles();
        $queryCriteria->performer = $this->getUser()->getId();
        $queryCriteria->onlyOwned = true;
        $queryCriteria->status = 'open';

        return new JsonResponse([
            'total' => $this->dm->getRepository('MBHHotelBundle:Task')->getCountByCriteria($queryCriteria)
        ]);
    }
}
