<?php

namespace MBH\Bundle\HotelBundle\Command;

use Documents\UserRepository;
use MBH\Bundle\BaseBundle\Document\NotificationType;
use MBH\Bundle\BaseBundle\Service\Messenger\NotifierMessage;
use MBH\Bundle\HotelBundle\Document\Task;
use MBH\Bundle\HotelBundle\Document\TaskRepository;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class NotificationSenderCommand

 */
class TaskNotifySendCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:task:notify')
            ->setDescription('Send notifications about new closed tasks.')
            ->addArgument('hours', InputArgument::REQUIRED, 'Quantity of hours ago');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        /** @var TaskRepository $taskRepository */
        $taskRepository = $dm->getRepository('MBHHotelBundle:Task');
        /** @var UserRepository $userRepository */
        $userRepository = $dm->getRepository('MBHUserBundle:User');
        $hours = $input->getArgument('hours');

        $end = new \DateTime("- $hours hours");

        /** @var User[] $users */
        $users = $userRepository->findBy([
            'taskNotify' => true,
            'email' => ['$exists' => true],
            'enabled' => true,
        ]);

        $mailer = $this->getContainer()->get('mbh.notifier.mailer');
        /** @var Router $router */
        $router = $this->getContainer()->get('router');

        $message = new NotifierMessage();
        $message
            ->setSubject('mailer.closedTasks.subject')
            ->setLink($router->generate('task', [], Router::ABSOLUTE_URL))
            ->setTemplate('MBHBaseBundle:Mailer:closedTasks.html.twig')
            ->setText('mailer.closedTasks.text')
            ->setMessageType(NotificationType::TASK_TYPE);

        $counter = 0;
        foreach ($users as $user) {
            /** @var Task[] $closedTasks */
            $closedTasks = $taskRepository->findBy([
                'status' => Task::STATUS_CLOSED,
                'end' => ['$gte' => $end],
                'createdBy' => $user->getUsername()
            ]);
            if (count($closedTasks) > 0) {
                $message->setAdditionalData(['tasks' => $closedTasks]);

                $currentMessage = clone($message);
                $currentMessage->addRecipient($user);
                $output->writeln("Sent to " . $user->getEmail());
                $mailer->setMessage($currentMessage)->notify();
                ++$counter;
            }
        }

        $output->writeln('Sent ' . $counter . ' notifications with end from ' . $end->format('d.m.Y H:i'));
    }
}