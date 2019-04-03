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
            ->setDescription('Add isSinglePrice option to all RoomTypes')
            ->addOption(
                'singlePlacement',
                null,
                InputOption::VALUE_REQUIRED,
                '$roomType->setIsSinglePlacement() value'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
//        if (!$input->getOption('singlePlacement')) {
//            throw new \Exception('singlePlacement value required');
//        }

        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $booking = $this->getContainer()->get('mbh.channelmanager.booking');

        /** @var BookingConfig $config */
        $count = 0;
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

        $output->writeln(sprintf('Set singlePlacement %s times', $count));

        $allRoomTypes = $dm->getRepository('MBHHotelBundle:RoomType')->findBy(['isSinglePlacement' => null]);

        $updatedIds = [];
        if (\count($allRoomTypes)) {
            /** @var RoomType $roomType */
            foreach ($allRoomTypes as $roomType) {
                $roomType->setIsSinglePlacement(false);
                $dm->persist($roomType);
                $updatedIds[] = $roomType->getId();
            }
            $dm->flush();
        }

        $output->writeln('Updated from Booking done');
        foreach ($updatedIds as $id) {
            $output->writeln($id);
        }


    }
}