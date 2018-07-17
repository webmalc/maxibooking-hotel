<?php

namespace MBH\Bundle\ChannelManagerBundle\Command;

use MBH\Bundle\ChannelManagerBundle\Services\ChannelManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PullCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:channelmanager:pull')
            ->addOption('service', null, InputOption::VALUE_OPTIONAL, 'Service name (booking, ostrovok, etc)')
            ->addOption('old', true, InputOption::VALUE_NONE, 'Get old reservations?')
            ->setDescription('Pull orders from channel manager services')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime();
        $logger = $this->getContainer()->get('mbh.channelmanager.logger');
        $logger->addInfo(
            'ChannelManager Pull command started'
        );

        $oldPackageStatus = $input->getOption('old') === false
            ? ChannelManager::OLD_PACKAGES_PULLING_NOT_STATUS
            : ChannelManager::OLD_PACKAGES_PULLING_ALL_STATUS;
        $this->getContainer()->get('mbh.channelmanager')->pullOrders(
            $input->getOption('service'),
            $oldPackageStatus
        );

        $time = $start->diff(new \DateTime());
        $message = 'Command complete. Elapsed time: '.$time->format('%H:%I:%S');
        $logger->addInfo(
            'ChannelManager Pull command ended. '.$message
        );
        $output->writeln($message);
    }
}
