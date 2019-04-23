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
        $this->getContainer()->get('doctrine.odm.mongodb.document_manager')
            ->getFilterCollection()->disable('softdeleteable');

        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $booking = $this->getContainer()->get('mbh.channelmanager.booking');

        /** @var BookingConfig $config */
        foreach ($booking->getConfig() as $config) {
            $roomTypes = $config->getRooms();

            foreach ($roomTypes as $roomTypeId => $roomTypeInfo) {
                $roomType = $roomTypeInfo->getRoomType();
                $roomType->setIsSinglePlacement($roomTypeInfo->isUploadSinglePrices());
                $dm->persist($roomType);
            }
        }
        $dm->flush();

        $allRoomTypes = $dm->getRepository('MBHHotelBundle:RoomType')->findBy(['isSinglePlacement' => null]);
        if (!count($allRoomTypes)) {
            $output->writeln('Updated from Booking');
            return;
        }

        /** @var RoomType $roomType */
        foreach ($allRoomTypes as $roomType) {
            $roomType->setIsSinglePlacement(false);
            $dm->persist($roomType);
            $updatedIds[] = $roomType->getId();
        }
        $dm->flush();

        $this->getContainer()->get('doctrine.odm.mongodb.document_manager')
            ->getFilterCollection()->enable('softdeleteable');
    }
}
