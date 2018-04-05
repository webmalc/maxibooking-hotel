<?php

namespace MBH\Bundle\BaseBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class RestoreSandboxDBCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mbhbase:restore_sandbox_dbcommand')
            ->setDescription('Drop sandbox db and recreate by copying of template db');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->runCommand('doctrine:mongodb:schema:drop');
        $container = $this->getContainer();
        $dbName = 'mbh_' . $this->getContainer()->getParameter('sandbox_client');
        $container->get('mbh.billing_mongo_client')->copyDatabase('db_test', $dbName);
    }

    private function runCommand(string $command)
    {
        $client = $this->getContainer()->getParameter('sandbox_client');
        $process = new Process(
            'nohup php ' . $this->getContainer()->get('kernel')->getRootDir() . '/../bin/console ' . $command . ' --no-debug --env=prod',
            null,
            [\AppKernel::CLIENT_VARIABLE => $client
        ]);
        $process->mustRun();
    }
}
