<?php

namespace MBH\Bundle\HotelBundle\Command;

use Documents\UserRepository;
use MBH\Bundle\BaseBundle\Service\Messenger\NotifierMessage;
use MBH\Bundle\HotelBundle\Document\Task;
use MBH\Bundle\HotelBundle\Document\TaskRepository;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class NotificationSenderCommand
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
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

        $message = new NotifierMessage();
        $message->setSubject('Задачи, которые были завершены');
        /** @var Router $router */
        $router = $this->getContainer()->get('router');
        $message->setLink($router->generate('task', [], Router::ABSOLUTE_URL));
        $message->setTemplate('MBHBaseBundle:Mailer:closedTasks.html.twig');
        $message->setText('Завершенные задачи: ');

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
                $currentMessage->addRecipient([$user->getEmail() => $user->getFullName(true)]);
                $output->writeln("Sent to " . $user->getEmail());
                $mailer->setMessage($currentMessage)->notify();
                ++$counter;
            }
        }

        $output->writeln('Sent ' . $counter . ' notifications with end from ' . $end->format('d.m.Y H:i'));
    }
}