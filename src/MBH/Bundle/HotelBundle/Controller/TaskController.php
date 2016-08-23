<?php

namespace MBH\Bundle\HotelBundle\Controller;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Form\Extension\DateType;
use MBH\Bundle\BaseBundle\Lib\ClientDataTableParams;
use MBH\Bundle\BaseBundle\Lib\QueryCriteriaInterface;
use MBH\Bundle\BaseBundle\Service\Messenger\NotifierMessage;
use MBH\Bundle\HotelBundle\Document\QueryCriteria\TaskQueryCriteria;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\Task;
use MBH\Bundle\HotelBundle\Document\TaskRepository;
use MBH\Bundle\HotelBundle\Form\SearchTaskType;
use MBH\Bundle\UserBundle\Document\Group;
use MBH\Bundle\UserBundle\Document\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Form;
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

        $priorities = $this->container->getParameter('mbh.tasktype.priority');
        $statuses = $this->container->getParameter('mbh.task.statuses');

        if ($authorizationChecker->isGranted('ROLE_TASK_VIEW')) {
            $searchForm = $this->createForm(SearchTaskType::class, new TaskQueryCriteria());

            return $this->render('MBHHotelBundle:Task:index.html.twig', [
                'roomTypes' => $this->get('mbh.hotel.selector')->getSelected()->getRoomTypes(),
                'priorities' => $priorities,
                'statuses' => $statuses,
                'searchForm' => $searchForm->createView(),
                'tasks' => []
            ]);
        } elseif ($authorizationChecker->isGranted('ROLE_TASK_OWN_VIEW')) {
            $taskRepository = $this->getTaskRepository();
            /** @var Task[] $processTasks */
            $criteria = ['performer.id' => $this->getUser()->getId()];
            $sort = ['priority' => -1, 'createdAt' => -1];
            $processTasks = $taskRepository->findBy($criteria + ['status' => Task::STATUS_PROCESS], $sort);

            $taskQueryCriteria = new TaskQueryCriteria();
            $taskQueryCriteria->userGroups = $this->getUser()->getGroups();
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


    /**
     * List entities
     *
     * @Route("/json_list", name="task_json", defaults={"_format"="json"}, options={"expose"=true})
     * @Method({"POST", "GET"})
     * @Security("is_granted('ROLE_TASK_MANAGER')")
     * @Template("MBHHotelBundle:Task:list.json.twig")
     * @param Request $request
     * @return array
     */
    public function jsonListAction(Request $request)
    {
        /** @var TaskRepository $taskRepository */
        $taskRepository = $this->getTaskRepository();

        $tasks = [];
        $recordsTotal = 0;

        $searchForm = $this->createForm(SearchTaskType::class, new TaskQueryCriteria());
        $searchForm->handleRequest($request);
        if($searchForm->isValid()) {
            $queryCriteria = $searchForm->getData();

            $tableParams = ClientDataTableParams::createFromRequest($request);
            $firstSort = $tableParams->getFirstSort();
            /** @var  TaskQueryCriteria $queryCriteria */
            $queryCriteria->onlyOwned = false;
            $queryCriteria->sort = $firstSort ? [$firstSort[0] => $firstSort[1]] : ['createdAt' => -1];
            $queryCriteria->offset = $tableParams->getStart();
            $queryCriteria->limit = $tableParams->getLength();
            $queryCriteria->hotel = $this->hotel;

            $tasks = $taskRepository->getAcceptableTasks($queryCriteria);
            if(is_object($tasks)) {
                $tasks = iterator_to_array($tasks);
            }
            $recordsTotal = $taskRepository->getCountByCriteria($queryCriteria);
        }


        return [
            'roomTypes' => $this->hotel->getRoomTypes(),
            'statuses' => $this->container->getParameter('mbh.task.statuses'),
            'priorities' => $this->container->getParameter('mbh.tasktype.priority'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => count($recordsTotal),
            'tasks' => $tasks,
            'draw' => $request->get('draw'),
            'taskRepository' => $taskRepository
        ];
    }


    /**
     * Displays a form to create a new entity.
     *
     * @Route("/new", name="task_new")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_TASK_MANAGER')")
     * @Template()
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function newAction(Request $request)
    {
        if (!$this->get('mbh.hotel.selector')->checkPermissions($this->hotel)) {
            throw $this->createNotFoundException();
        }

        $entity = new Task();
        $entity->setStatus(Task::STATUS_OPEN);
        $entity->setPriority(Task::PRIORITY_AVERAGE);

        /** @var DocumentManager $dm */
        $dm = $this->dm;

        $form = $this->createForm(new TaskType($dm), $entity, $this->getFormTaskTypeOptions());

        if ($request->isMethod(Request::METHOD_POST)) {
            if ($form->submit($request)->isValid()) {
                /** @var Room[] $rooms */
                $rooms = $form['rooms']->getData();
                $task = null; $numOfTasks = 0;
                foreach ($rooms as $room) {
                    $task = clone($entity);
                    /** @var DocumentManager $this->dm */
                    $taskRepository = $dm->getRepository('MBHHotelBundle:Task');
                    /** @var TaskRepository $taskRepository */
                    if (!$taskRepository->isStatusRoomInProccess($task, $room)) {
                        $task->setRoom($room);
                        $dm->persist($task);
                        $numOfTasks ++;
                    }
                }
                if ($numOfTasks && $numOfTasks !== count($rooms)) {
                    $request->getSession()->getFlashBag()->set('warning',
                        $this->get('translator')->trans('controller.taskTypeController.task_created_notall'));
                };

                $dm->flush();

                if($task) {
                    $this->sendNotifications($task);
                }

                if ($numOfTasks) {
                    $request->getSession()->getFlashBag()->set('success',
                        $this->get('translator')->trans('controller.taskTypeController.record_created_success'));
                } else {
                    $request->getSession()->getFlashBag()->set('danger',
                        $this->get('translator')->trans('controller.taskTypeController.task_created_failed'));
                }


                return $this->redirectToRoute('task');
            }
        }

        return [
            'form' => $form->createView(),
        ];
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
                $dm = $this->dm;
                $taskRepository = $dm->getRepository('MBHHotelBundle:Task');
                /** @var Form $form */
                $task = $form->getData();
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
     * @return array
     */
    private function getFormTaskTypeOptions()
    {
        $statuses = $this->getParameter('mbh.task.statuses');
        $translator = $this->get('translator');
        $priorities = $this->container->getParameter('mbh.tasktype.priority');
        $priorities = array_map(function ($name) use ($translator) {
            return $translator->trans('views.task.priority.' . $name, [], 'MBHHotelBundle');
        }, $priorities);

        return [
            'statuses' => array_combine(array_keys($statuses), array_column($statuses, 'title')),
            'priorities' => $priorities,
            'hotel' => $this->hotel
        ];
    }

    private function sendNotifications(Task $task)
    {
        $recipients = [];
        if ($userGroup = $task->getUserGroup()) {
            /** @var User[] $recipients */
            $recipients = $this->dm->getRepository('MBHUserBundle:User')->findBy([
                'userGroup' => $userGroup,
                'taskNotify' => true,
                'email' => ['$exists' => true],
                'enabled' => true,
            ]);
        }

        if ($task->getPerformer() && $task->getPerformer()->getTaskNotify() && !in_array($task->getPerformer(), $recipients)) {
            $recipients[] = $task->getPerformer();
        }

        if ($recipients) {
            $message = new NotifierMessage();
            $message->setSubject('mailer.new_task.subject');
            $message->setText('mailer.new_task.text');
            $message->setTranslateParams(['%taskType%' => $task->getType()->getTitle()]);
            $message->setLink($this->generateUrl('task'));
            foreach ($recipients as $recipient) {
                $message->addRecipient($recipient);
            }
            $this->get('mbh.notifier.mailer')->setMessage($message)->notify();
        }
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
     * @Security("is_granted('ROLE_TASK_OWN_VIEW')")
     */
    public function ajaxTaskDerailsAction(Task $entity)
    {
        $priorities = $this->getParameter('mbh.tasktype.priority');
        $data = [
            'id' => $entity->getId(),
            'userGroup' => $entity->getUserGroup(),
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
     * @return TaskRepository
     */
    private function getTaskRepository()
    {
        $repository = $this->container->get('mbh.hotel.task_repository');
        $repository->setContainer($this->container);
        return $repository;
    }

    /**
     * @Route("/ajax/my_total", name="task_ajax_total_my_open", options={"expose": true})
     * @Method("GET")
     * @Security("is_granted('ROLE_TASK_OWN_VIEW')")
     */
    public function ajaxMyOpenTaskTotal()
    {
        $queryCriteria = new TaskQueryCriteria();
        $queryCriteria->userGroups = $this->getUser()->getGroups();
        $queryCriteria->performer = $this->getUser()->getId();
        $queryCriteria->onlyOwned = true;
        $queryCriteria->status = 'open';

        return new JsonResponse([
            'total' => $this->getTaskRepository()->getCountByCriteria($queryCriteria)
        ]);
    }
}
