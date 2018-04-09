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
            ->setDescription('Drop template db, load test fixtures');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start loading of fixtures');
        $this->runCommand('doctrine:mongodb:fixtures:load');
        $output->writeln('Cancel loading of fixtures');
        $output->writeln('Start creation of packages');
        $this->runCommand('mbhbase:load_packages_to_example_dbcommand');
        $output->writeln('End creation of packages');
    }

    private function runCommand(string $command, $paramsString = null)
    {
        $process = new Process(
            'nohup php ' . $this->getContainer()->get('kernel')->getRootDir() . '/../bin/console ' . $command . ' --no-debug --env=sandbox' . ($paramsString ? ' ' . $paramsString : ''),
            null,
            ['SANDBOX_LOGIN' => getenv('SANDBOX_LOGIN'), 'SANDBOX_PASSWORD' => getenv('SANDBOX_PASSWORD')]
        );
        $process->setTimeout(0);
        $process->mustRun();
    }
}
