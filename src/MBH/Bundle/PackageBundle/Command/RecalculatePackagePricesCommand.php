<?php

namespace MBH\Bundle\PackageBundle\Command;

use Doctrine\MongoDB\Query\Builder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RecalculatePackagePricesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mbhpackage:recalculate_package_prices_command')
            ->setDescription('Recalculate package prices with inconsistency of sum of prices by dates and package price');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        /** @var Builder $qb */
        $packages = $dm->getRepository('MBHPackageBundle:Package')
            ->getPackagesWithInconsistencyOfPackagePriceAndSumOfPricesByDate();

        $manager = $this->getContainer()->get('mbh.order_manager');
        foreach ($packages as $package) {
            $manager->updatePricesByDate($package, null);
        }
        $dm->flush();
    }
}
