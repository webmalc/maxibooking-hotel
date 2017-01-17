<?php

namespace MBH\Bundle\HotelBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\UnitOfWork;
use MBH\Bundle\BaseBundle\Lib\QueryBuilder;

use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeRepository;
use MBH\Bundle\HotelBundle\Document\Task;
use MBH\Bundle\HotelBundle\Document\TaskRepository;
use MBH\Bundle\HotelBundle\Document\TaskType;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageAccommodation;
use MBH\Bundle\PackageBundle\Document\PackageRepository;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AutoTaskCreator
 */
class AutoTaskCreator
{
    protected $container;

    /**
     * @var DocumentManager
     */
    protected $dm;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $this->container->get('doctrine_mongodb')->getManager();
    }

    /**
     * @param OutputInterface $output
     * @return int
     */
    public function createDailyTasks()
    {
        $this->dm->getConnection()->getConfiguration()->setLoggerCallable(null);

        /** @var DocumentRepository $hotelRepository */
        $hotelRepository = $this->dm->getRepository('MBHHotelBundle:Hotel');
        /** @var RoomTypeRepository $roomTypeRepository */
        $roomTypeRepository = $this->dm->getRepository('MBHHotelBundle:RoomType');
        /** @var PackageRepository $packageRepository */
        $packageRepository = $this->dm->getRepository('MBHPackageBundle:Package');

        $hotels = $hotelRepository->createQueryBuilder()
            ->field('isEnabled')->equals(true)
            ->select('_id')
            ->getQuery()->toArray();

        $helper = $this->container->get('mbh.helper');
        $hotelIDs = $helper->toIds($hotels);

        if (!$hotelIDs) {
            return 0;
        }

        $inc = 0;

        $queryBuilder = $roomTypeRepository->createQueryBuilder();
        $queryBuilder
            ->field('taskSettings.daily')->exists(true)
            ->field('hotel.id')->in($hotelIDs)
            ->field('taskSettings.daily')->not($queryBuilder->expr()->size(0));

        $now = new \DateTime();
        $midnight = new \DateTime('midnight');

        /** @var RoomType[] $roomTypes */
        $roomTypes = $queryBuilder->getQuery()->execute();

        //Get current Accommodations
        $packageAccommodationRepository = $this->dm->getRepository('MBHPackageBundle:PackageAccommodation');
        $packageAccommodations = $packageAccommodationRepository->getAccommodationByDate($now);
        $helper = $this->container->get('mbh.helper');
        $pIDs = $helper->toIds($packageAccommodations);

        foreach ($roomTypes as $roomType) {
            //$output->writeln("Room Type: " . $roomType->getId() . ' ' . $roomType->getTitle());
            //$roomType->getHotel() is enable task module

            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = $packageRepository->createQueryBuilder()
                ->field('isCheckIn')->equals(true)
                ->field('isCheckOut')->equals(false)
                ->field('accommodations.id')->in($pIDs)
                ->field('begin')->lte($now)
                ->field('end')->gte($now)
                ->field('roomType.id')->equals($roomType->getId())
                ->field('arrivalTime')->exists(true)//->gte($lastDay)->lte($now)
            ;
            /** @var Package[] $packages */
            $packages = $queryBuilder->getQuery()->execute();


            foreach ($packages as $package) {
                if ($roomType->getTaskSettings() && $roomType->getTaskSettings()->getDaily()) {
                    foreach ($roomType->getTaskSettings()->getDaily() as $dailyTaskSetting) {
                        $taskType = $dailyTaskSetting->getTaskType();
                        if ($taskType->getDefaultUserGroup()) {
                            $arrival = $package->getArrivalTime()->modify('midnight');
                            $interval = $arrival->diff($midnight)->d;
                            if ($interval % $dailyTaskSetting->getDay() === 0) { //condition
                                $task = new Task();
                                $task
                                    ->setType($taskType)
                                    ->setUserGroup($taskType->getDefaultUserGroup())
                                    ->setRoom($package->getAccommodationByDate($now)->getAccommodation())
                                    ->setStatus(Task::STATUS_OPEN)
                                    ->setPriority(Task::PRIORITY_AVERAGE)
                                    ->setDescription(Task::AUTO_CREATE)
                                    ->setHotel($package->getAccommodationByDate($now)->getAccommodation()->getHotel());

                                if ($this->getCountSameTasks($task) === 0) {
                                    $this->dm->persist($task);
                                    ++$inc;

                                    $this->dm->flush();
                                }
                            };
                        }
                    }
                }
            }
        }

        return $inc;
    }

    private function getCountSameTasks(Task $task)
    {
        /** @var TaskRepository $taskRepository */
        $taskRepository = $this->dm->getRepository('MBHHotelBundle:Task');

        $midnight = new \DateTime('midnight');
        $tomorrow =  new \DateTime('midnight tomorrow -1 minute');

        $queryBuilder = $taskRepository->createQueryBuilder();
        $queryBuilder
            ->field('type')->equals($task->getType())
            ->field('userGroup')->equals($task->getUserGroup())
            ->field('room')->equals($task->getRoom())
            ->field('createdBy')->exists(false)
            ->field('createdAt')->gte($midnight)->lte($tomorrow);
        ;
        $query = $queryBuilder->getQuery();
        $count = $query->count();

        return $count;
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

        $packageAccommodation = call_user_func([$package, 'getAccommodationCheck' . $check]);

        if (!$packageAccommodation && !$packageAccommodation->getAccommodation()) {
            return;
        }
        /** @var RoomType $roomType */
        $roomType = $packageAccommodation->getAccommodation()->getRoomType();
        $settings = $roomType->getTaskSettings();
        if (!$settings) {
            return;
        }

        /** @var TaskType[] $taskTypes */
        $taskTypes = call_user_func([$settings, 'getCheck' . $check]);
        foreach ($taskTypes as $roomType) {
            if ($roomType->getDefaultUserGroup()) {
                $task = new Task();
                $task
                    ->setType($roomType)
                    ->setUserGroup($roomType->getDefaultUserGroup())
                    ->setRoom($packageAccommodation->getAccommodation())
                    ->setStatus(Task::STATUS_OPEN)
                    ->setPriority(Task::PRIORITY_AVERAGE)
                    ->setHotel($package->getRoomType()->getHotel());

                if ($this->getCountSameTasks($task) == 0) {
                    $this->dm->persist($task);
                }
            }
        }
        $this->dm->flush();
    }
}