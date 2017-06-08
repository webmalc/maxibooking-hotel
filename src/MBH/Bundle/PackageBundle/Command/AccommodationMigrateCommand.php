<?php

namespace MBH\Bundle\PackageBundle\Command;

use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageAccommodation;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AccommodationMigrateCommand
 * @deprecated
 * @package MBH\Bundle\PackageBundle\Command
 */
class AccommodationMigrateCommand extends ContainerAwareCommand
{
    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    private $dm;

    protected function configure()
    {
        $this
            ->setName('mbh:package:accommodation_migrate')
            ->setDescription('Accommodation migrate')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime();
        $this->dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $packages = $this->dm->getRepository('MBHPackageBundle:Package')->findBy(['accommodation' => ['$ne' => null]]);
        $iterator = 0;
        foreach ($packages as $package) {
            $this->accommodationMigrate($package->getId());
            $iterator++;
        }
        $time = $start->diff(new \DateTime());
        $output->writeln('Migration complete. Elapsed time: ' . $time->format('%H:%I:%S') . '. Packages: ' . $iterator);
    }

    private function accommodationMigrate(string $packageId)
    {
        $package = $this->dm->find('MBHPackageBundle:Package', $packageId);
        /** @var Package $package */
        if ($package && !count($package->getAccommodations())) {
            $accommodation = new PackageAccommodation();
            $accommodation
                ->setBegin($package->getBegin())
                ->setEnd($package->getEnd())
                ->setAccommodation($package->getAccommodation(true));
            $package->addAccommodation($accommodation);
            $this->dm->persist($accommodation);
            $this->dm->flush();
        }
        $this->dm->clear();
    }

}