<?php

namespace MBH\Bundle\HotelBundle\Command;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\RoomStatus;
use MBH\Bundle\HotelBundle\Document\TaskType;
use MBH\Bundle\HotelBundle\Document\TaskTypeCategory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TaskLoadCommand
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class TaskLoadCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:task:load')
            ->setDescription('Loading system tasks')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var DocumentManager $dm */
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();

        $repairStatus = new RoomStatus();
        $repairStatus->setTitle('Ремонт')->setCode('repair');
        $dm->persist($repairStatus);

        $cleaningStatus = new RoomStatus();
        $cleaningStatus->setTitle('Уборка')->setCode('cleaning');
        $dm->persist($cleaningStatus);

        $reserveStatus = new RoomStatus();
        $reserveStatus->setTitle('Резерв')->setCode('reserve');
        $dm->persist($reserveStatus);

        $otherStatus = new RoomStatus();
        $otherStatus->setTitle('Другое')->setCode('other');
        $dm->persist($otherStatus);

        $dm->flush();


        $category = new TaskTypeCategory();
        $category->setIsSystem(true);
        $category->setCode('clean');
        $category->setTitle('Уборка');
        $category->getFullTitle('Уборка помещений');

        $taskType = new TaskType();
        $taskType->setIsSystem(true);
        $taskType->setCode('clean_room');
        $taskType->setTitle('Убрать комнату');
        $taskType->setCategory($category);
        $taskType->setRoomStatus($cleaningStatus);

        $dm->persist($category);
        $dm->persist($taskType);
        $dm->flush();
        $output->writeln('Done');
    }
}