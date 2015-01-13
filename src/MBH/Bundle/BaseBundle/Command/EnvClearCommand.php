<?php

namespace MBH\Bundle\BaseBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EnvClearCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:env:clear')
            ->setDescription('Removes old packages, tourists & cash documents (not in "prod" environment)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime();
        $total = 0;

        if ($this->getContainer()->getParameter('mbh.environment') == 'prod') {
            return false;
        }
        
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();

        $user = 'mbh:env:clear_command';
        $date = $now = new \DateTime();
        $date->setTime(0, 0, 0)->modify('-14 days');


        foreach (['MBHPackageBundle:Tourist', 'MBHCashBundle:CashDocument', 'MBHPackageBundle:Package'] as $repo) {

            $docs = $dm->getRepository($repo)->createQueryBuilder('q')
                ->field('createdAt')->lte($date)
                ->getQuery()
                ->execute()
            ;

            foreach($docs as $doc) {
                $doc->setUpdatedBy($user)->setDeletedAt($now);
                $dm->persist($doc);
                $total++;
            }
            $dm->flush();
        }

        $time = $start->diff(new \DateTime());
        $output->writeln('Clearing complete. Total entries: ' . $total . '. Elapsed time: ' . $time->format('%H:%I:%S'));
    }
}