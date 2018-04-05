<?php

namespace MBH\Bundle\BaseBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class FillExampleDBCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mbhbase:load_example_db_data_command')
            ->setDescription('Drop template db, load test fixtu');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->runCommand('doctrine:mongodb:fixtures:load');
        $this->runCommand('mbhbase:load_packages_to_example_dbcommand');
        $this->runCommand('mbh:drop_collection_command', '--collections=LogEntry');
    }

    private function runCommand(string $command, $paramsString = null)
    {
        $process = new Process(
            'nohup php ' . $this->getContainer()->get('kernel')->getRootDir() . '/../bin/console ' . $command . ' --no-debug --env=test' . ($paramsString ? ' ' . $paramsString : ''));
        $process->setTimeout(0);
        $process->mustRun();
    }
}
