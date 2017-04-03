<?php

namespace MBH\Bundle\PackageBundle\Command;

use MBH\Bundle\BaseBundle\Service\Helper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class VirtualRoomMovingCommand extends ContainerAwareCommand
{

    const MONTH_COUNT = 3;
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mbh:virtual_rooms:move')
            ->setDescription('Move virtual room')
            ->addOption('begin', null, InputOption::VALUE_OPTIONAL, 'Begin (date - d.m.Y)')
            ->addOption('end', null, InputOption::VALUE_OPTIONAL, 'End (date - d.m.Y)')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $beginDate = Helper::getDateFromString($input->getOption('begin')) ?? new \DateTime('midnight');
        $endDate = Helper::getDateFromString($input->getOption('end')) ?? new \DateTime('+'.self::MONTH_COUNT.' month');
        $this->getContainer()->get('mbh.package.virtual_room_handler')->setVirtualRooms($beginDate, $endDate);
        $output->writeln('Completed');
    }
}
