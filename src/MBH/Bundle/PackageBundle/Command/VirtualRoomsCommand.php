<?php

namespace MBH\Bundle\PackageBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;


class VirtualRoomsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('azovsky:rooms:virtual')
            ->setDescription('Sets virtual rooms to packages.')
            ->addArgument('begin', InputArgument::REQUIRED, 'Start date dd.mm.YYY')
            ->addArgument('end', InputArgument::OPTIONAL, 'Start date dd.mm.YYY')
            ->addOption('force', false, InputOption::VALUE_NONE, 'Write to DB?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime();
        $container = $this->getContainer();
        $dm = $container->get('doctrine_mongodb')->getManager();
        $from = new \DateTime($input->getArgument('begin'));
        if (!$input->getArgument('end')) {
            $to = new \DateTime('midnight +1 year');
        } else {
            $to = new \DateTime($input->getArgument('end'));
        }

        $force = $input->getOption('force');
        $search = $container->get('mbh.package.search_simple');

        if ($force) {
            $output->writeln("<error>!!! Force == true !!!</error>\n");
        }

        $output->writeln(sprintf("<comment>Dates: %s-%s</comment>\n", $from->format('d.m.Y'), $to->format('d.m.Y')));

        $packages = $dm->getRepository('MBHPackageBundle:Package')
            ->createQueryBuilder()
            ->field('begin')->gte($from)
            ->field('begin')->lte($to)
            ->field('virtualRoom')->equals(null)
            ->getQuery()
            ->execute()
        ;

        $output->writeln(sprintf("<info>Packages: %d</info>\n", count($packages)));

        foreach ($packages as $package) {

            $result = $search->setVirtualRoom(
                $package, $dm->getRepository('MBHPriceBundle:Tariff')->fetchBaseTariff($package->getRoomType()->getHotel())
            );

            if (!$result) {
                $output->writeln(sprintf("<error>Room not found: #%s</error>\n", $package->getNumberWithPrefix()));
            }
            if (!$force) {
                $output->writeln(sprintf("<info>Room found: #%s</info>\n", $package->getVirtualRoom()));
            } else {
                $dm->persist($package);
                $dm->flush();
            }
        }

        $time = $start->diff(new \DateTime());
        $output->writeln('Process complete. Elapsed time: ' . $time->format('%H:%I:%S'));
    }

}