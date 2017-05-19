<?php

namespace MBH\Bundle\PackageBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PackageRelocateBackCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mbhpackage:package_relocate_back_command');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $backMovedPackagesData = [
            ["oldRoomTypeId" => "5703c37674eb53676e8b458b", 'packageId' => "584680dd84919e579f42aedb"],
            ["oldRoomTypeId" => "5703c5ba74eb53f36e8b459c", 'packageId' => "58fefedccd57227d5b34fba4"],
            ["oldRoomTypeId" => "5703c5ba74eb53f36e8b459c", 'packageId' => "5857cd4b84919e58d73c451b"],
            ["oldRoomTypeId" => "5703c5ba74eb53f36e8b459c", 'packageId' => "5857cd4684919e58d73c4512"],
            ["oldRoomTypeId" => "5703c5ba74eb53f36e8b459c", 'packageId' => "5857cd4184919e58d73c4509"],
            ["oldRoomTypeId" => "5703c5ba74eb53f36e8b459c", 'packageId' => "5857cd5084919e58d73c4524"],
            ["oldRoomTypeId" => "5703c67374eb532c6f8b4570", 'packageId' => "58591cde84919e48535322a6"],
            ["oldRoomTypeId" => "57051f9b74eb53441c8b4852", 'packageId' => "587225fe84919e073429bd54"],
            ["oldRoomTypeId" => "5703c67374eb532c6f8b4570", 'packageId' => "58a32aa984919e295243d4fe"],
            ["oldRoomTypeId" => "5703b46d74eb53ba6b8b45db", 'packageId' => "58a6ce5a84919e270c3fade9"],
            ["oldRoomTypeId" => "5703b46d74eb53ba6b8b45db", 'packageId' => "58a6ce8984919e279f152b16"],
            ["oldRoomTypeId" => "5703c5ba74eb53f36e8b459c", 'packageId' => "58b014c684919e3d546042e6"],
            ["oldRoomTypeId" => "57051f9b74eb53441c8b4852", 'packageId' => "58b57a8384919e72c63aa197"],
            ["oldRoomTypeId" => "5703c67374eb532c6f8b4570", 'packageId' => "58baf84a84919e146e699c98"],
            ["oldRoomTypeId" => "5703c5ba74eb53f36e8b459c", 'packageId' => "58c6ae3e9c117d3df82648fb"],
            ["oldRoomTypeId" => "5703c37674eb53676e8b458b", 'packageId' => "58cbf2ed9c117d6292117bbc"],
            ["oldRoomTypeId" => "5703c37674eb53676e8b458b", 'packageId' => "58cf75ac9c117d245e23ee7d"],
            ["oldRoomTypeId" => "5703b46d74eb53ba6b8b45db", 'packageId' => "58d8d5ca9c117d44b76f4344"],
            ["oldRoomTypeId" => "5703c37674eb53676e8b458b", 'packageId' => "58e4a579cd57224758779ada"],
            ["oldRoomTypeId" => "5703c37674eb53676e8b458b", 'packageId' => "58e766a3cd57226b6019fd4a"],
            ["oldRoomTypeId" => "5703c37674eb53676e8b458b", 'packageId' => "58e76f1dcd57227a77690c5b"],
            ["oldRoomTypeId" => "5703c67374eb532c6f8b4570", 'packageId' => "58eb802acd572274744b3a79"],
            ["oldRoomTypeId" => "5703c5ba74eb53f36e8b459c", 'packageId' => "58ee59cbcd5722107847b9f2"],
            ["oldRoomTypeId" => "57051f9b74eb53441c8b4852", 'packageId' => "58ef5051cd572255531708e1"],
            ["oldRoomTypeId" => "5703c37674eb53676e8b458b", 'packageId' => "58fd94e6cd5722713b5b844b"],
            ["oldRoomTypeId" => "5703c5ba74eb53f36e8b459c", 'packageId' => "590ba139cd572269026c90f7"],
            ["oldRoomTypeId" => "5703c37674eb53676e8b458b", 'packageId' => "590df446cd572262405cd346"],
            ["oldRoomTypeId" => "5703c5ba74eb53f36e8b459c", 'packageId' => "59100f38cd572260a222952f"],
            ["oldRoomTypeId" => "5703c67374eb532c6f8b4570", 'packageId' => "5911e316cd5722106820098e"],
            ["oldRoomTypeId" => "5705206874eb53a51f8b456a", 'packageId' => "5912faebcd57225a667b2603"],
            ["oldRoomTypeId" => "5703c37674eb53676e8b458b", 'packageId' => "5915473bcd572232374a8c8d"],
            ["oldRoomTypeId" => "5705206874eb53a51f8b456a", 'packageId' => "59170cf2cd5722445b0961e0"],
            ["oldRoomTypeId" => "57177c9574eb53e82f8b4568", 'packageId' => "5918212ccd5722099d0022af"],
            ['packageId' => '591d4ceacd572251801420a2', 'oldRoomTypeId' => '5703c37674eb53676e8b458b'],
            ['packageId' => '591d88b3cd572238ca058216', 'oldRoomTypeId' => '5705206874eb53a51f8b456a'],
            ['packageId' => '591d8778cd572236b538311d', 'oldRoomTypeId' => '5703c5ba74eb53f36e8b459c'],
        ];

        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $orderManager = $this->getContainer()->get('mbh.order_manager');
        $logger = $this->getContainer()->get('mbh.packagezip.logger');
        $output->writeln("BEGIN MOVING BACK");
        $logger->alert("BEGIN MOVING BACK");
        foreach ($backMovedPackagesData as $backMovedPackagesDatum) {
            $package = $dm->find('MBHPackageBundle:Package', $backMovedPackagesDatum['packageId']);
            $roomType = $dm->find('MBHHotelBundle:RoomType', $backMovedPackagesDatum['oldRoomTypeId']);
            if (!is_null($package)) {
                $logger->alert("Change room type for package with id = '{$backMovedPackagesDatum['packageId']}' from room with ID = '{$package->getRoomType()->getId()}' and name '{$package->getRoomType()->getName()}' in room with ID = '{$roomType->getId()}' and name '{$roomType->getName()}'");
                $result = $orderManager->changeRoomType($package, $roomType);
                if ($result) {
                    $logger->alert("Moved back package with ID = {$package->getId()}");
                } else {
                    $logger->alert("Package with id = {$package->getId()} not moved");
                }
                $dm->clear();

            } else {
                $logger->alert('PACKAGE WITH ID = ' . $backMovedPackagesDatum['packageId'] . ' NOT FOUND');
            }
        }
        $output->writeln("COMPLETED");
    }
}
