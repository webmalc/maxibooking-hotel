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
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');


        if ($dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $dm->getFilterCollection()->disable('softdeleteable');
        }

        if ($dm->getFilterCollection()->isEnabled('disableable')) {
            $dm->getFilterCollection()->disable('disableable');
        }

        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $booking = $this->getContainer()->get('mbh.channelmanager.booking');

        $count = 0;
        /** @var BookingConfig $config */
        foreach ($booking->getConfig() as $config) {
            $roomTypes = $config->getRooms();

            foreach ($roomTypes as $roomTypeId => $roomTypeInfo) {
                $roomType = $roomTypeInfo->getRoomType();
                $roomType->setIsSinglePlacement($roomTypeInfo->isUploadSinglePrices());
                $dm->persist($roomType);
                $count++;
            }
        }
        $dm->flush();
        $output->writeln(sprintf('Updated %s roomTypes', $count));

        $allRoomTypes = $dm->getRepository('MBHHotelBundle:RoomType')->findBy(['isSinglePlacement' => null]);
        if (!count($allRoomTypes)) {
            $output->writeln('All roomTypes updated. Update ended');
            return;
        }

        $updatedIds = [];
        /** @var RoomType $roomType */
        foreach ($allRoomTypes as $roomType) {
            $roomType->setIsSinglePlacement(true);
            $dm->persist($roomType);
            $updatedIds[] = $roomType->getId();
        }
        $dm->flush();
        $output->writeln('Updated %s', implode(' ', $updatedIds));

        if (!$dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $dm->getFilterCollection()->enable('softdeleteable');
        }

        if (!$dm->getFilterCollection()->isEnabled('disableable')) {
            $dm->getFilterCollection()->enable('disableable');
        }
    }
}