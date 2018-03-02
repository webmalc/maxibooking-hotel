<?php

namespace MBH\Bundle\BaseBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class CreateTemplateTestDBCommand extends ContainerAwareCommand
{
    const CLIENT_NAME_FOR_CREATION_OF_TEMPLATE_TEST_DB = 'template_db_for_tests';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mbh:create_template_test_dbcommand')
            ->setDescription('Create template database used for cloning by tests');
    }

    private function runCommand(string $command, $paramsString = null)
    {
        $rootDir = $this->getContainer()->get('kernel')->getRootDir();
        $process = new Process(
            'nohup php ' . $rootDir . '/../bin/console ' . $command . ' --env=dev ' . ($paramsString ?? ''),
            null,
            [\AppKernel::CLIENT_VARIABLE => self::CLIENT_NAME_FOR_CREATION_OF_TEMPLATE_TEST_DB]
        );
        $process->mustRun();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->runCommand('doctrine:mongodb:schema:drop');
        $dbName = getenv('MONGODB_DATABASE');
        $this->getContainer()->get('mbh.mongo.maintenance')->createDBUser($dbName, self::CLIENT_NAME_FOR_CREATION_OF_TEMPLATE_TEST_DB);
        $this->runCommand('doctrine:mongodb:fixtures:load --append');

        $paramsString = '--collections=LogEntry';
        $this->runCommand('mbh:drop_collection_command', $paramsString);
    }
}