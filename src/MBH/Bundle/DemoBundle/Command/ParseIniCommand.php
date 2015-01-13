<?php

namespace MBH\Bundle\DemoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ParseIniCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:demo:params')
            ->setDescription('Change mongodb db/user in project')
            ->addOption('db', null, InputOption::VALUE_REQUIRED, 'DB name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (empty($input->getOption('db'))) {
            $output->writeln("<error>--db = false</error>");
            return false;
        }

        $dbName = $input->getOption('db');
        $path = $this->getContainer()->get('kernel')->getRootDir() . '/config/parameters.yml';

        file_put_contents($path, preg_replace('/mongodb_database\:(.*)$/im', 'mongodb_database: ' . $dbName, file_get_contents($path)));
        file_put_contents($path, preg_replace('/mbh_environment\:(.*)$/im', 'mbh_environment: test', file_get_contents($path)));

        $output->writeln('Complete. New db name: '.$input->getOption('db'));
    }
}