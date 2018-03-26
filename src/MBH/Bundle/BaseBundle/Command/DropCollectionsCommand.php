<?php

namespace MBH\Bundle\BaseBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DropCollectionsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mbh:drop_collection_command')
            ->addOption('collections', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'dropped collections names', [])
            ->setDescription('Drop specified collections');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $collections = $input->getOption('collections');
        foreach ($collections as $collection) {
            $this->getContainer()->get('mbh.mongo')->dropCollection($collection);
        }

    }
}