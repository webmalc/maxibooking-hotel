<?php

namespace MBH\Bundle\PriceBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SpecialPriceRecalculateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:special:recalculate')
            ->addOption('specialIds', null, InputOption::VALUE_OPTIONAL, 'SpecialIds comma separated ')
            ->setDescription('Recalculate Special prices by search');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime();
        $logOutput = function ($message, $context) use ($output) {
            $output->writeln($message);
        };

        if ($input->getOption('specialIds')) {
            $specialIds = explode(',', trim($input->getOption('specialIds'), ','));
        }

        $this
            ->getContainer()
            ->get('mbh.special_handler')
            ->calculatePrices(
                isset($specialIds) ? $specialIds : [],
                [],
                $input->getOption('verbose') ? $logOutput : null
            );

        $time = $start->diff(new \DateTime());
        $output->writeln(
            sprintf('Recalculate complete. Elapsed time: %s', $time->format('%H:%I:%S'))
        );
    }


}