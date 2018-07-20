<?php

namespace MBH\Bundle\PriceBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RoomCacheRecalculateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:cache:recalculate')
            ->setDescription('Recalculate RoomCaches package count')
            ->addOption('roomTypes', null, InputOption::VALUE_REQUIRED, 'RoomTypes ids (comma-separated)')
            ->addOption('begin', null, InputOption::VALUE_REQUIRED, 'Recalculate from (date - d.m.Y)')
            ->addOption('end', null, InputOption::VALUE_REQUIRED, 'Recalculate to (date - d.m.Y)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getContainer()->get('mbh.helper');
        $start = new \DateTime();
        $num = 0;

        if ($input->getOption('roomTypes')) {
            $roomTypes = explode(',', trim($input->getOption('roomTypes'), ','));
        } else {
            $roomTypes = $helper
                ->toIds($this->getContainer()
                    ->get('doctrine.odm.mongodb.document_manager')
                    ->getRepository('MBHHotelBundle:RoomType')
                    ->findAll());
        }

        $recalculationResult = $this->getContainer()->get('mbh.room.cache')->recalculateByPackages(
            $helper->getDateFromString($input->getOption('begin')),
            $helper->getDateFromString($input->getOption('end')),
            isset($roomTypes) ? $roomTypes : []
        );

        $num += $recalculationResult['total'];
        $numberOfInconsistencies = $recalculationResult['numberOfInconsistencies'];

        $time = $start->diff(new \DateTime());
        $output->writeln(
            sprintf('Recalculate complete. Entries: %s. Elapsed time: %s', number_format($num), $time->format('%H:%I:%S'))
        );
        $output->writeln($numberOfInconsistencies == 0 ? 'Inconsistencies not found' : 'Number of inconsistencies: ' . $numberOfInconsistencies);

        if ($numberOfInconsistencies > 0) {
            list($minDate, $maxDate) = $this->getContainer()->get('mbh.helper')->getMinAndMaxDates($recalculationResult['inconsistentDates']);
            $this->getContainer()->get('mbh.channelmanager')->updatePricesInBackground($minDate, $maxDate);
            $notifier = $this->getContainer()->get('exception_notifier');
            $message = $notifier::createMessage();
            $message
                ->setType('danger')
                ->setText('Inconsistencies of room caches are found. Client:' . $this->getContainer()->getParameter('client'));
            $notifier
                ->setMessage($message)
                ->notify();;
        }
    }
}
