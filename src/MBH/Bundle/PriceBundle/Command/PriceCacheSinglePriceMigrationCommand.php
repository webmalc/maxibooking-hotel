<?php

namespace MBH\Bundle\PriceBundle\Command;


use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PriceCacheSinglePriceMigrationCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('mbh:price_cache:single_price:migration')
            ->setDescription('Add isSinglePrice option to all RoomTypes');

        $this->addOption(
            'singlePlacement',
            null,
            InputOption::VALUE_REQUIRED,
            '$roomType->setIsSinglePlacement() value'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');

        $roomTypeRepository = $dm->getRepository('MBHHotelBundle:RoomType');
        $allRoomTypes = $roomTypeRepository->findBy(['isSinglePlacement' => null]);

        if (!count($allRoomTypes)) {
            $output->writeln('Nothing to update');
            return;
        }

        $updatedIds = [];
        /** @var RoomType $roomType */
        foreach ($allRoomTypes as $roomType) {
            $roomType->setIsSinglePlacement($input->getOption('singlePlacement'));
            $dm->persist($roomType);
            $updatedIds[] = $roomType->getId();
        }
        $dm->flush();

        $output->writeln('Updated following RoomTypes - ' . implode(', ', $updatedIds));
    }
}