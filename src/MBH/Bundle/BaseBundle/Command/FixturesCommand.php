<?php

namespace MBH\Bundle\BaseBundle\Command;

use MBH\Bundle\BaseBundle\Lib\Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class FixturesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:base:fixtures')
            ->setDescription('Install project fixtures')
            ->addOption('cities', null, InputOption::VALUE_NONE, 'with cities?')
        ;
    }

    private function runCommand(string $command)
    {
//        $env = $this->getContainer()->get('kernel')->getEnvironment();
//        $process = new Process(
//            'nohup php ' . $this->getContainer()->get('kernel')->getRootDir() . '/../bin/console ' . $command . ' --no-debug --env=' . $env
//        );
//        $process->run();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime();
//        $this->runCommand('doctrine:mongodb:schema:drop');
        $this->runCommand('doctrine:mongodb:fixtures:load --append');

        if ($input->getOption('cities')) {
            $this->runCommand('mbh:city:load');
        }
        $time = $start->diff(new \DateTime());
        $output->writeln('Installing complete. Elapsed time: ' . $time->format('%H:%I:%S'));
    }
}