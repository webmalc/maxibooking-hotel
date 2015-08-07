<?php

namespace MBH\Bundle\HotelBundle\Command;


use Doctrine\ODM\MongoDB\DocumentManager;
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

        $dm->persist($category);
        $dm->persist($taskType);
        $dm->flush();
        $output->writeln('Done');
    }
}