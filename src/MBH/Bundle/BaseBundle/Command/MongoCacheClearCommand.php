<?php

namespace MBH\Bundle\BaseBundle\Command;

use MBH\Bundle\BaseBundle\Lib\Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class MongoCacheClearCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:cache:mongo-clear')
            ->setDescription('Clear expired mongo cache')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime();
        $time = $start->diff(new \DateTime());
        $total = $this->getContainer()->get('mbh.cache')->clearExpiredItems();
        $output->writeln('Completed. Total: ' . $total. '. Time elapsed: ' . $time->format('%H:%I:%S'));
    }
}
