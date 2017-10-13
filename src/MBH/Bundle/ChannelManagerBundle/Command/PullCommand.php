<?php

namespace MBH\Bundle\ChannelManagerBundle\Command;

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
        $this->getContainer()->get('mbh.channelmanager')->pullOrders(
            $input->getOption('service'),
            $input->getOption('old')
        );

        $time = $start->diff(new \DateTime());
        $output->writeln('Command complete. Elapsed time: ' . $time->format('%H:%I:%S'));
    }
}
