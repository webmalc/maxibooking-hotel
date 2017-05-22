<?php


namespace MBH\Bundle\PriceBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SpecialDisplayDatesMassChanger extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:special:begin-display-changer')
            ->setDescription('Specials mass display date changer with period')
            ->addOption('period', null, InputOption::VALUE_OPTIONAL, 'Period in Days')
            ->addOption('begin', null, InputOption::VALUE_OPTIONAL, 'Recalculate from (date - d.m.Y)')
            ->addOption('end', null, InputOption::VALUE_OPTIONAL, 'Recalculate to (date - d.m.Y)')
            ->addOption('roomType', null, InputOption::VALUE_OPTIONAL, 'RoomType id')

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime();

        $changer = $this->getContainer()->get('mbh.special_display_dates_mass_changer');
        $logOutput = function ($message) use ($output) {
            $output->writeln($message);
        };
        $helper = $this->getContainer()->get('mbh.helper');
        $begin = null;
        $end = null;
        $period = null;
        $roomType = null;
        if ($input->getOption('begin')) {
            $begin = $helper->getDateFromString($input->getOption('begin'));
        }

        if ($input->getOption('end')) {
            $end = $helper->getDateFromString($input->getOption('end'));
        }
        if ($input->getOption('period')) {
            $period = $input->getOption('pediod');
        }
        if ($input->getOption('roomType')) {
            $roomType = $input->getOption('roomType');
        }
        $changer->changeDates($period, $begin, $end, $roomType, $input->getOption('verbose')?$logOutput:null);

        $time = $start->diff(new \DateTime());
        $output->writeln(
            sprintf('Recalculate complete. Elapsed time: %s', $time->format('%H:%I:%S'))
        );
    }
}