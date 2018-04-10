<?php

namespace MBH\Bundle\BaseBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class CreateTemplateTestDBCommand extends ContainerAwareCommand
{
    public const MB_CLIENT_TEST_USER = 'test';

    /**
     * {@inheritdoc}
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this
            ->setName('mbh:create_template_test_db')
            ->setDescription('Create template database used for cloning by tests');
    }

    /**
     * {@inheritdoc}
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
//        $this->getContainer()->get('mbh.mongo.maintenance')->createDBUser(
//            'template_test',
//            self::MB_CLIENT_TEST_USER
//        );

        $this->runCommand('doctrine:mongodb:schema:drop');

        $this->runCommand('doctrine:mongodb:fixtures:load --append');

        $paramsString = '--collections=LogEntry';
        $this->runCommand('mbh:drop_collection_command', $paramsString);
    }

    /**
     * @param string $command
     * @param null $paramsString
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     */
    private function runCommand(string $command, $paramsString = null): void
    {
        $rootDir = $this->getContainer()->get('kernel')->getRootDir();
        $process = new Process(
            'nohup php '.$rootDir.'/../bin/console '.$command.' --env=dev '.($paramsString ?? ''),
            null,
            [\AppKernel::CLIENT_VARIABLE => self::MB_CLIENT_TEST_USER]
        );
        $process->mustRun();
    }
}