<?php

namespace MBH\Bundle\PackageBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class CreditCardsCommand extends ContainerAwareCommand
{
    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    private $dm;

    protected function configure()
    {
        $this
            ->setName('mbh:cards:remove')
            ->setDescription('Remove credit cards from old orders.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime();
        $this->dm = $this->getContainer()->get('doctrine_mongodb')->getManager();

        $orders = $this->dm->getRepository('MBHPackageBundle:Order')
            ->createQueryBuilder()
            ->field('creditCard')->notEqual(null)
            ->field('deletedAt')->equals(null)
            ->getQuery()
            ->execute()
        ;
        foreach ($orders as $order) {
            $maxDate = null;
            foreach ($order->getPackages() as $package) {
                if (!$maxDate || $package->getEnd() > $maxDate) {
                    $maxDate = $package->getEnd();
                }
            }

            if (!$maxDate || $maxDate < new \DateTime('midnight - 2 days')) {
                $order->setCard(null);
                $order->setCreditCard(null);
                $this->dm->persist($order);
                $this->dm->flush();
            }
        }

        $time = $start->diff(new \DateTime());
        $output->writeln('Process complete. Elapsed time: ' . $time->format('%H:%I:%S'));
    }

}