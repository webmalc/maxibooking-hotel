<?php

namespace MBH\Bundle\DemoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class APCChangeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:demo:apc')
            ->setDescription('Change APC in project')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'APC name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {



        if (empty($input->getOption('name'))) {
            $output->writeln("<error>--name = false</error>");
            return false;
        }

        $name = $input->getOption('name');

        $path = $this->getContainer()->get('kernel')->getRootDir() . '/../web/app.php';

        file_put_contents($path, preg_replace('/ApcClassLoader\((.*)\,/', "ApcClassLoader('" . $name . "',", file_get_contents($path)));

        $output->writeln('Complete. New ACP name: '. $name);
    }
}