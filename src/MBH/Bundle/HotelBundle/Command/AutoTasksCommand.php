<?php

namespace MBH\Bundle\HotelBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AutoTasksCommand
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class AutoTasksCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:task:auto')
            ->setDescription('Create auto tasks');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $creator = $this->getContainer()->get('mbh.hotel.auto_task_creator');
        $count = $creator->create();

        $output->writeln("Created task total: " . $count. ". Done");
    }
}