<?php

namespace MBH\Bundle\HotelBundle\Service;

use MBH\Bundle\HotelBundle\Document\TaskType;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Lib\QueryBuilder;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeRepository;
use MBH\Bundle\HotelBundle\Document\TaskRepository;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageRepository;
use MBH\Bundle\HotelBundle\Document\Task;

/**
 * Class AutoTaskCreator
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class AutoTaskCreator
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return int
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function createDailyTasks()
    {
        /** @var DocumentManager $dm */
        $dm = $this->container->get('doctrine_mongodb')->getManager();
        $dm->getConnection()->getConfiguration()->setLoggerCallable(null);

        $now = new \DateTime();
        $tomorrow = new \DateTime('+1 day');
        /** @var DocumentRepository $hotelRepository */
        $hotelRepository = $dm->getRepository('MBHHotelBundle:Hotel');
        /** @var RoomTypeRepository $roomTypeRepository */
        $roomTypeRepository = $dm->getRepository('MBHHotelBundle:RoomType');
        /** @var PackageRepository $packageRepository */
        $packageRepository = $dm->getRepository('MBHPackageBundle:Package');
        /** @var TaskRepository $taskRepository */
        $taskRepository = $dm->getRepository('MBHHotelBundle:Task');

        $hotels = $hotelRepository->createQueryBuilder()
            ->field('isEnabled')->equals(true)
            ->select('_id')
            ->getQuery()->toArray();

        $hotelIDs = [];//array_column($hotels, '_id');
        foreach ($hotels as $hotel) {
            $hotelIDs[] = $hotel->getId();
        }

        if (!$hotelIDs) {
            return 0;
        }

        $inc = 0;

        $queryBuilder = $roomTypeRepository->createQueryBuilder();
        $queryBuilder
            ->field('taskSettings.daily')->exists(true)
            ->field('hotel.id')->in($hotelIDs)
            ->field('taskSettings.daily')->not($queryBuilder->expr()->size(0));

        /** @var RoomType[] $roomTypes */
        $roomTypes = $queryBuilder->getQuery()->execute();

        foreach ($roomTypes as $roomType) {
            //$output->writeln("Room Type: " . $roomType->getId() . ' ' . $roomType->getTitle());
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
            foreach ($packages as $package) {
                if ($roomType->getTaskSettings() && $roomType->getTaskSettings()->getDaily()) {
                    foreach ($roomType->getTaskSettings()->getDaily() as $dailyTaskSetting) {
                        $taskType = $dailyTaskSetting->getTaskType();
                        if ($taskType->getDefaultRole()) {
                            $arrival = $package->getArrivalTime()->modify('midnight');
                            $interval = $arrival->diff($now)->d;
                            if ($interval % $dailyTaskSetting->getDay() === 0) { //condition
                                $count = $taskRepository->createQueryBuilder()
                                    ->field('type.id')->equals($taskType->getId())
                                    ->field('role')->equals($taskType->getDefaultRole())
                                    ->field('room.id')->equals($package->getAccommodation()->getId())
                                    //->field('status')->equals(Task::STATUS_OPEN)
                                    //->field('priority')->equals(Task::PRIORITY_AVERAGE)
                                    ->field('createdBy')->equals(null)
                                    ->field('createdAt')->gte($now)->lte($tomorrow)
                                    ->getQuery()->count();
                                if($count == 0) {
                                    $task = new Task();
                                    $task->setType($taskType)
                                        ->setRole($taskType->getDefaultRole())
                                        ->setRoom($package->getAccommodation())
                                        ->setStatus(Task::STATUS_OPEN)
                                        ->setPriority(Task::PRIORITY_AVERAGE);

                                    $dm->persist($task);
                                    ++$inc;
                                }
                            };
                        }
                    }
                }
                $dm->detach($package);
            }
            $dm->detach($roomType);
        }

        $dm->flush();

        return $inc;
    }

    public function createCheckInTasks(Package $package)
    {
        $this->createCheck('In', $package);
    }

    public function createCheckOutTasks(Package $package)
    {
        $this->createCheck('Out', $package);
    }

    protected function createCheck($check, Package $package)
    {
        $type = $package->getRoomType();
        $dm = $this->container->get('doctrine_mongodb')->getManager();
        $settings = $type->getTaskSettings();
        if(!$settings) {
            return;
        }
        /** @var TaskType[] $taskTypes */
        $taskTypes = call_user_func([$settings, 'getCheck'.$check]);
        foreach($taskTypes as $type) {
            if($type->getDefaultRole()) {
                $task = new Task();
                $task
                    ->setType($type)
                    ->setRole($type->getDefaultRole())
                    ->setRoom($package->getAccommodation())
                    ->setStatus(Task::STATUS_OPEN)
                    ->setPriority(Task::PRIORITY_AVERAGE);

                $dm->persist($task);
            }
        }
        $dm->flush();
    }
}