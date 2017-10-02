<?php

namespace MBH\Bundle\ClientBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SearchCacheCommand extends ContainerAwareCommand
{
    /**
     * @var ContainerInterface
     */
    private $container;

    protected function configure()
    {
        $this
            ->setName('mbh:dashboard')
            ->setDescription('Dashboard management command')
            ->addOption('clear', null, InputOption::VALUE_NONE)
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $start = new \DateTime();
        $this->container = $this->getContainer();
        $dashboard = $this->container->get('mbh.dashboard');
        
        if ($input->getOption('clear')) {
            $dashboard->clear();
        }
        $dashboard->notify();
        $time = $start->diff(new \DateTime());
        $output->writeln('Complete. Elapsed time: ' . $time->format('%H:%I:%S'));
    }
}
