<?php

namespace MBH\Bundle\BaseBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EnvClearCommand extends ContainerAwareCommand
{
    protected $dm;

    protected function configure()
    {
        $this
            ->setName('mbh:env:clear')
            ->setDescription('Removes old packages, tourists & cash documents (not in "prod" environment)')
        ;
    }

    /**
     * @param $doc
     * @return mixed
     */
    private function delete($doc)
    {
        if (!is_object($doc)) {
            return false;
        }
        $doc->setUpdatedBy('mbh:env:clear_command')->setDeletedAt(new \DateTime());
        $this->dm->persist($doc);

        return $doc;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime();
        $total = 0;

        if ($this->getContainer()->getParameter('mbh.environment') == 'prod') {
            return false;
        }
        
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $this->dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $date = new \DateTime();
        $date->setTime(0, 0, 0)->modify('-3 days');

        $packages = $this->dm->getRepository('MBHPackageBundle:Package')
            ->createQueryBuilder('q')
            ->field('end')->lte($date)
            ->getQuery()
            ->execute()
        ;

        try {
            foreach($packages as $package) {
                $this->delete($package);
                $this->delete($package->getMainTourist());

                foreach ($package->getTourists() as $tourist) {
                    $this->delete($tourist);
                }
                foreach ($package->getCashDocuments() as $cashDoc) {
                    $this->delete($cashDoc);
                }

                $total++;
            }
        } catch (\Exception $e){

        }

        $this->dm->flush();

        $time = $start->diff(new \DateTime());
        $output->writeln('Clearing complete. Total packages: ' . $total . '. Elapsed time: ' . $time->format('%H:%I:%S'));
    }
}