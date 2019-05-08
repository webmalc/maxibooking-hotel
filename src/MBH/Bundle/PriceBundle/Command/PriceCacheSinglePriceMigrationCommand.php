<?php

namespace MBH\Bundle\PriceBundle\Command;


use MBH\Bundle\ChannelManagerBundle\Document\BookingConfig;
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
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $softDisablable = false;
        $enablable = false;
        if ($this->getContainer()->get('doctrine.odm.mongodb.document_manager')
            ->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->getContainer()->get('doctrine.odm.mongodb.document_manager')
                ->getFilterCollection()->disable('softdeleteable');
            $softDisablable = !$softDisablable;
        }
        if ($this->getContainer()->get('doctrine.odm.mongodb.document_manager')
            ->getFilterCollection()->isEnabled('disableable')) {
            $this->getContainer()->get('doctrine.odm.mongodb.document_manager')
                ->getFilterCollection()->disable('disableable');
            $enablable = !$enablable;
        }

        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $booking = $this->getContainer()->get('mbh.channelmanager.booking');

        $i = 0;
        /** @var BookingConfig $config */
        foreach ($booking->getConfig() as $config) {
            $roomTypes = $config->getRooms();

            foreach ($roomTypes as $roomTypeId => $roomTypeInfo) {
                $roomType = $roomTypeInfo->getRoomType();
                $roomType->setIsSinglePlacement($roomTypeInfo->isUploadSinglePrices());
                $dm->persist($roomType);
                $i++;
            }
        }
        $dm->flush();
        $output->writeln('Updated '.$i.' roomTypes from Booking config');

        $allRoomTypes = $dm->getRepository('MBHHotelBundle:RoomType')->findBy(['isSinglePlacement' => null]);
        if (!count($allRoomTypes)) {
            $output->writeln('Already updated. Nothing more to update.');
            return;
        }
        $j = 0;
        /** @var RoomType $roomType */
        foreach ($allRoomTypes as $roomType) {
            $roomType->setIsSinglePlacement(true);
            $dm->persist($roomType);
            $updatedIds[] = $roomType->getId();
            $j++;
        }
        $dm->flush();

        $output->writeln('Updated '.$j.' more roomTypes, with default true isSinglePlacement');

        if ($softDisablable) {
            $this->getContainer()->get('doctrine.odm.mongodb.document_manager')
                ->getFilterCollection()->enable('softdeleteable');
        }
        if ($enablable) {
            $this->getContainer()->get('doctrine.odm.mongodb.document_manager')
                ->getFilterCollection()->enable('disableable');
        }
    }
}
