<?php

namespace MBH\Bundle\PackageBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Class VirtualRoomMovingCommand
 * @package MBH\Bundle\PackageBundle\Command
 */
class VirtualRoomMovingCommand extends ContainerAwareCommand
{
    const HANDLED_PACKAGES_COUNT = 500;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mbh:virtual_rooms:move')
            ->setDescription('Move virtual room')
            ->addOption('begin', null, InputOption::VALUE_OPTIONAL, 'Begin (date - d.m.Y)')
            ->addOption('end', null, InputOption::VALUE_OPTIONAL, 'End (date - d.m.Y)');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $helper = $container->get('mbh.helper');
        $beginDate = $helper->getDateFromString($input->getOption('begin')) ?? new \DateTime('midnight');
        $monthCount = $container->getParameter('packaging.month_count');
        $endDate = $helper->getDateFromString($input->getOption('end')) ?? new \DateTime('+' . $monthCount . ' month');

        $handledPackagesCount = $container
            ->get('doctrine.odm.mongodb.document_manager')
            ->getRepository('MBHPackageBundle:Package')
            ->getFetchWithVirtualRoomQB($beginDate, $endDate)
            ->getQuery()
            ->count();

        $startOffset = 0;
        $limitedCommandResultCode = $this->callLimitedCommand($beginDate, $endDate,
            self::HANDLED_PACKAGES_COUNT, $startOffset, $handledPackagesCount, $output);

        if ($limitedCommandResultCode != 0) {
            $output->writeln('An error with code ' . $limitedCommandResultCode . ' occurred');
        }

        $output->writeln('Completed');
    }

    /**
     * @param \DateTime $beginDate
     * @param \DateTime $endDate
     * @param $limit
     * @param $offset
     * @param $handledPackagesCount
     * @param OutputInterface $output
     * @param int $result
     * @return int
     */
    private function callLimitedCommand(
        \DateTime $beginDate,
        \DateTime $endDate,
        $limit,
        $offset,
        $handledPackagesCount,
        OutputInterface $output,
        $result = 0
    ) {
        if ($offset < $handledPackagesCount) {
            $console = $this->getContainer()->get('kernel')->getRootDir() . '/../bin/console ';
            $command = 'nohup php ' . $console . 'mbh:limited_virtual_room_moving_command'
                .' --begin='. $beginDate->format('d.m.Y') .' --end='. $endDate->format('d.m.Y')
                . ' --limit=' . $limit . ' --offset=' . $offset;
            $process = new Process($command);
            $process->setTimeout(null)->setIdleTimeout(null)->run();

            $currentIterationRightEdge = $offset + $limit;
            $output->writeln("Packages between $offset and $currentIterationRightEdge handled");
            $output->writeln((memory_get_usage() / 1024) . " KB");

            return $this->callLimitedCommand($beginDate, $endDate, $limit, $offset + $limit,
                $handledPackagesCount, $output, $result);
        }

        return $result;
    }
}