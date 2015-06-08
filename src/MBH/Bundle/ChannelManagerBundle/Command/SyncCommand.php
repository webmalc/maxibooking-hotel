<?php

namespace MBH\Bundle\ChannelManagerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:channelmanager:sync')
            ->setDescription('Sync channel manager services')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Service name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime();

        $this->getContainer()->get('mbh.channelmanager')->sync($input->getOption('name'));
        
        $time = $start->diff(new \DateTime());
        $output->writeln('Command complete. Elapsed time: ' . $time->format('%H:%I:%S'));
    }
}