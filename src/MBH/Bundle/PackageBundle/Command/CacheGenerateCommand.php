<?php

namespace MBH\Bundle\PackageBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CacheGenerateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:cache:generate')
            ->setDescription('Generates а RoomCache entities')
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
        
        if($roomType) {
            $total = $this->getContainer()->get('mbh.room.cache.generator')->generateForRoomType($roomType, $begin, $end);
        } else {
            $total = $this->getContainer()->get('mbh.room.cache.generator')->generate();
        }
        
        $time = $start->diff(new \DateTime());
        $output->writeln('Generation complete. Total entries: ' . $total . '. Elapsed time: ' . $time->format('%H:%I:%S'));
    }
}