<?php

namespace MBH\Bundle\BaseBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LoadPackagesToExampleDBCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mbhbase:load_packages_to_example_dbcommand')
            ->setDescription('Remove created by fixtures packages and create new randomly');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getContainer()->get('logger');
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $packages = $dm->getRepository('MBHPackageBundle:Package')->findAll();
        foreach ($packages as $package) {
            $dm->remove($package);
        }
        $dm->flush();

        $res = $this
            ->getContainer()
            ->get('mbh.random_packages_generator')
            ->generate(new \DateTime('midnight -1 month'), new \DateTime('midnight +3 month'), 150);
        $logger->err(json_encode($res));
    }
}
