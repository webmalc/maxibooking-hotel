<?php

namespace MBH\Bundle\HotelBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AutoTasksCommand
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class AutoTasksCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:task:auto')
            ->setDescription('Create auto tasks')
            ->addArgument('check', null, 'In or Out')
            ->addArgument('package')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $taskModuleEnabled = $this->getContainer()->getParameter('mbh_modules')['tasks'];
        if(!$taskModuleEnabled) {
            $output->writeln("Tasks module is disabled.");
        }
        $creator = $this->getContainer()->get('mbh.hotel.auto_task_creator');
        $check = $input->getArgument('check');
        if(!$check) {
            $count = $creator->createDailyTasks();
            $output->writeln("Created task total: " . $count. ". Done");
        } else {
            $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
            $package = $dm->getRepository('MBHPackageBundle:Package')->find($input->getArgument('package'));
            if(!$package)
                throw new \InvalidArgumentException("Package is not exists");
            switch($check) {
                case 'In' : $creator->createCheckInTasks($package); break;
                case 'Out' : $creator->createCheckOutTasks($package); break;
                default : throw new \InvalidArgumentException("Argument 'check' must have value In or Out"); break;
            }
            $output->writeln("Done");
        }
    }
}