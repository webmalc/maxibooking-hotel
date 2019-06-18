<?php

namespace MBH\Bundle\PackageBundle\Command;


use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TotalOverwriteFixMigrationCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:total_overwrite_fix:migrate')
            ->setDescription('Migrate broken total_overwrite in packages');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();

        $packages = $dm->createQueryBuilder(Package::class)
            ->field('totalOverwrite')->equals(0.0)
            ->field('price')->gt(0.0)
            ->hydrate(true)
            ->skip(0)
            ->limit(0)
            ->getQuery()
            ->execute();

        $count = $packages->count();

        if ($count) {
            /** @var Package $package */
            foreach ($packages as $package) {
                $output->writeln('Resetting totalOverride for ' . $package->getId() . ' package');
                $package->setTotalOverwrite(null);
                if ($package->getOrder()) {
                    $output->writeln(
                        'Resetting totalOverride for ' . $package->getOrder()->getId() . ' order'
                    );
                    $package->getOrder()->setTotalOverwrite(null);
                }
            }
            $dm->flush();

            $output->writeln('Handled '. $count . ' packages');
        } else {
            $output->writeln('Nothing to migrate');
        }
    }
}
