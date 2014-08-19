<?php

namespace MBH\Bundle\ChannelManagerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ChannelManagerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:channelmanager:update')
            ->setDescription('Update channel manager services')
            ->addOption('roomType', null, InputOption::VALUE_REQUIRED, 'RoomType ObjectId')
            ->addOption('begin', null, InputOption::VALUE_REQUIRED, 'Generate from (date - d.m.Y)')
            ->addOption('end', null, InputOption::VALUE_REQUIRED, 'Generate to (date - d.m.Y)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime();
        
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $roomType = $begin = $end = null;
        
        // Get room type
        if(!empty($input->getOption('roomType'))) {
            $roomType = $dm->getRepository('MBHHotelBundle:RoomType')->find($input->getOption('roomType'));
        }
        
        // Get begin
        if(!empty($input->getOption('begin'))) {
            $begin = \DateTime::createFromFormat('d.m.Y', $input->getOption('begin'));
            ($begin) ? $begin->setTime(0, 0, 0) : $begin = null;
        }
        
        // Get end
        if(!empty($input->getOption('end'))) {
            $end = \DateTime::createFromFormat('d.m.Y', $input->getOption('end'));
            ($end) ? $end->setTime(0, 0, 0) : $end = null;
        }

        $this->getContainer()->get('mbh.channelmanager')->update($begin, $end, $roomType);
        
        $time = $start->diff(new \DateTime());
        $output->writeln('Command complete. Elapsed time: ' . $time->format('%H:%I:%S'));
    }
}