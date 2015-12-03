<?php
namespace MBH\Bundle\HotelBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\HotelBundle\Document\RoomStatus;
use MBH\Bundle\HotelBundle\Document\TaskType;
use MBH\Bundle\HotelBundle\Document\TaskTypeCategory;
use MBH\Bundle\UserBundle\Document\Group;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class TaskData
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class TaskData implements FixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $roomStatusRepository = $manager->getRepository('MBHHotelBundle:RoomStatus');
        $taskTypeCategoryRepository = $manager->getRepository('MBHHotelBundle:TaskTypeCategory');
        $taskTypeRepository = $manager->getRepository('MBHHotelBundle:TaskType');

        $roomStatusRepository->createQueryBuilder()->remove()->getQuery()->execute();
        $taskTypeCategoryRepository->createQueryBuilder()->remove()->getQuery()->execute();
        $taskTypeRepository->createQueryBuilder()->remove()->getQuery()->execute();

        $hotels = $manager->getRepository('MBHHotelBundle:Hotel')->findAll();

        foreach ($hotels as $hotel) {
            $repairStatus = new RoomStatus();
            $repairStatus->setTitle('Ремонт')->setCode('repair')->setHotel($hotel);
            if ($roomStatusRepository->createQueryBuilder()
                    ->field('code')->equals($repairStatus->getCode())
                    ->field('hotel.id')->equals($hotel->getId())
                    ->getQuery()->count() == 0
            ) {
                $manager->persist($repairStatus);
            }

            $cleaningStatus = new RoomStatus();
            $cleaningStatus->setTitle('Уборка')->setCode('cleaning')->setHotel($hotel);
            if ($roomStatusRepository->createQueryBuilder()
                    ->field('code')->equals($cleaningStatus->getCode())
                    ->field('hotel.id')->equals($hotel->getId())
                    ->getQuery()->count() == 0
            ) {
                $manager->persist($cleaningStatus);
            }

            $reserveStatus = new RoomStatus();
            $reserveStatus->setTitle('Резерв')->setCode('reserve')->setHotel($hotel);
            if ($roomStatusRepository->createQueryBuilder()
                    ->field('code')->equals($reserveStatus->getCode())
                    ->field('hotel.id')->equals($hotel->getId())
                    ->getQuery()->count() == 0
            ) {
                $manager->persist($reserveStatus);
            }

            $otherStatus = new RoomStatus();
            $otherStatus->setTitle('Другое')->setCode('other')->setHotel($hotel);
            if ($roomStatusRepository->createQueryBuilder()
                    ->field('code')->equals($otherStatus->getCode())
                    ->field('hotel.id')->equals($hotel->getId())
                    ->getQuery()->count() == 0
            ) {
                $manager->persist($otherStatus);
            }

            $taskTypeRepository = $manager->getRepository('MBHHotelBundle:TaskType');

            $category = new TaskTypeCategory();

            $category->setIsSystem(true)
                ->setCode('clean')
                ->setTitle('Уборка')
                ->setFullTitle('Уборка помещений')
                ->setHotel($hotel);

            $taskType = new TaskType();

            $staff = $manager->getRepository('MBHUserBundle:Group')->findOneBy(['code' => 'staff']);
            $taskType->setIsSystem(true)
                ->setCode('clean_room')
                ->setTitle('Убрать комнату')
                ->setCategory($category)
                ->setRoomStatus($cleaningStatus)
                ->setDefaultUserGroup($staff)
                ->setHotel($hotel);

            if ($taskTypeCategoryRepository->createQueryBuilder()
                    ->field('code')->equals($category->getCode())
                    ->field('hotel.id')->equals($hotel->getId())
                    ->getQuery()->count() == 0
            ) {
                $manager->persist($category);
            }
            if ($taskTypeRepository->createQueryBuilder()
                    ->field('code')->equals($taskType->getCode())
                    ->field('hotel.id')->equals($hotel->getId())
                    ->getQuery()->count() == 0
            ) {
                $manager->persist($taskType);
            }

            $manager->flush();
        }
    }
}