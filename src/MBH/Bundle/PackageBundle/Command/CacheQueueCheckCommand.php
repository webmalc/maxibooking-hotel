<?php

namespace MBH\Bundle\PackageBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CacheQueueCheckCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:cache:check')
            ->setDescription('Check CacheQueue & run cache generate')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime();
        $total = 0;
        
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();

        if ($dm->getRepository('MBHPackageBundle:CacheQueue')->findOneBy(['status' => 'working'])) {
            $output->writeln("<error>RoomСache is generating now!</error>");
            return false;
        }
        $queue = $dm->getRepository('MBHPackageBundle:CacheQueue')->findOneBy(['status' => 'waiting']);

        if ($queue) {
            $id = $queue->getId();

            $queue->setStatus('working');
            $dm->persist($queue);
            $dm->flush();

            if($queue->getRoomType()) {
                $total = $this->getContainer()->get('mbh.room.cache.generator')->generateForRoomType($queue->getRoomType(), $queue->getBegin(), $queue->getEnd());
            } else {
                $total = $this->getContainer()->get('mbh.room.cache.generator')->generate();
            }

            $queue = $dm->getRepository('MBHPackageBundle:CacheQueue')->find($id);
            $queue->setStatus('complete');
            $dm->persist($queue);
            $dm->flush();
        }

        $time = $start->diff(new \DateTime());
        $output->writeln('Check & generation complete. Total entries: ' . $total . '. Elapsed time: ' . $time->format('%H:%I:%S'));
    }
}