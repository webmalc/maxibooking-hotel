<?php

namespace MBH\Bundle\BaseBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class CreateTemplateTestDBCommand extends ContainerAwareCommand
{
    public const CLIENT_NAME_FOR_CREATION_OF_TEMPLATE_TEST_DB = 'client_for_create_tm_db';

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

        $env = $input->getOption('env');

        $output->writeln('Dropping mongodb schema.');
        $this->runCommand('doctrine:mongodb:schema:drop', $output, $env);
        $output->writeln('Mongodb Schema was dropped.');

        $output->writeln('Start loading fixtures.');
        $this->runCommand('doctrine:mongodb:fixtures:load --append', $output,  $env);

        $paramsString = '--collections=LogEntry';
        $this->runCommand('mbh:drop_collection_command', $output, $env, $paramsString);
    }

    /**
     * @param string $command
     * @param string $env
     * @param string $paramsString
     */
    private function runCommand(string $command, OutputInterface $output, $env = 'dev', $paramsString = null): void
    {

        $rootDir = $this->getContainer()->get('kernel')->getRootDir();

        $process = new Process(
            'php '.$rootDir.'/../bin/console '.$command.' --env='. $env .' '.($paramsString ?? ''),
            null,
            [\AppKernel::CLIENT_VARIABLE => self::CLIENT_NAME_FOR_CREATION_OF_TEMPLATE_TEST_DB]
        );

        try {
            $process->mustRun(static function ($type, $buffer) use($output) {
                if ($output) {
                    if (Process::ERR === $type) {
                        $output->writeln("ERR>>> $buffer");
                    } else {
                        $output->writeln("OUT>>> $buffer");
                    }
                }

            });
        } catch (ProcessFailedException $exception) {
            if ($output) {
                $output->writeln($exception->getMessage());
            }
        }

    }
}
