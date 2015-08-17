<?php

namespace MBH\Bundle\HotelBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Lib\QueryBuilder;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeRepository;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MBH\Bundle\HotelBundle\Document\Task;

/**
 * Class AutoTasksCommand
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class AutoTasksCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:task:auto')
            ->setDescription('Create auto tasks');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var DocumentManager $dm */
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $dm->getConnection()->getConfiguration()->setLoggerCallable(null);

        $now = new \DateTime();
        /** @var DocumentRepository $hotelRepository */
        $hotelRepository = $dm->getRepository('MBHHotelBundle:Hotel');
        /** @var RoomTypeRepository $roomTypeRepository */
        $roomTypeRepository = $dm->getRepository('MBHHotelBundle:RoomType');
        /** @var PackageRepository $packageRepository */
        $packageRepository = $dm->getRepository('MBHPackageBundle:Package');

        $hotels = $hotelRepository->createQueryBuilder()
            ->field('isEnabled')->equals(true)
            ->select('_id')
            ->getQuery()->toArray();

        $hotelIDs = [];//array_column($hotels, '_id');
        foreach ($hotels as $hotel) {
            $hotelIDs[] = $hotel->getId();
        }

        if (!$hotelIDs) {
            return;
        }

        $queryBuilder = $roomTypeRepository->createQueryBuilder();
        $queryBuilder
            ->field('taskSettings.daily')->exists(true)
            ->field('hotel.id')->in($hotelIDs)
            ->field('taskSettings.daily')->not($queryBuilder->expr()->size(0));

        /** @var RoomType[] $roomTypes */
        $roomTypes = $queryBuilder->getQuery()->execute();

        foreach ($roomTypes as $roomType) {
            $output->writeln("Room Type: " . $roomType->getId() . ' ' . $roomType->getTitle());
            //$roomType->getHotel() is enable task module

            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = $packageRepository->createQueryBuilder()
                ->field('isCheckIn')->equals(true)
                ->field('isCheckOut')->equals(false)
                ->field('begin')->lte($now)
                ->field('end')->gte($now)
                ->field('roomType.id')->equals($roomType->getId())
                ->field('arrivalTime')->exists(true)//->gte($lastDay)->lte($now)
            ;
            /** @var Package[] $packages */
            $packages = $queryBuilder->getQuery()->execute();

            $now->modify('midnight');
            $inc = 0;
            foreach ($packages as $package) {
                if ($roomType->getTaskSettings() && $roomType->getTaskSettings()->getDaily()) {
                    foreach ($roomType->getTaskSettings()->getDaily() as $dailyTaskSetting) {
                        $taskType = $dailyTaskSetting->getTaskType();
                        if ($taskType->getDefaultRole()) {
                            $arrival = $package->getArrivalTime()->modify('midnight');
                            $interval = $arrival->diff($now)->d;
                            if ($interval % $dailyTaskSetting->getDay() === 0) { //condition
                                $task = new Task();
                                $task->setType($taskType)
                                    ->setRole($taskType->getDefaultRole())
                                    ->setRoom($package->getAccommodation())
                                    ->setStatus(Task::STATUS_OPEN)
                                    ->setPriority(Task::PRIORITY_AVERAGE);

                                $dm->persist($task);
                                ++$inc;
                            };
                        }
                    }
                }
                $dm->detach($package);
            }
            $dm->detach($roomType);
        }

        $output->writeln("Created task total:" . $inc);
        //$dm->flush();
        $output->writeln("Done");
    }
}