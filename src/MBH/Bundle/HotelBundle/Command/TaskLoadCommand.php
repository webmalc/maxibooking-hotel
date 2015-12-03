<?php

namespace MBH\Bundle\HotelBundle\Command;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\RoomStatus;
use MBH\Bundle\HotelBundle\Document\TaskType;
use MBH\Bundle\HotelBundle\Document\TaskTypeCategory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @todo move to DataFixtures class
 * Class TaskLoadCommand
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class TaskLoadCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:task:load')
            ->setDescription('Loading system tasks')
            ->addOption('force');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var DocumentManager $dm */
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $roomStatusRepository = $dm->getRepository('MBHHotelBundle:RoomStatus');
        $taskTypeCategoryRepository = $dm->getRepository('MBHHotelBundle:TaskTypeCategory');
        $taskTypeRepository = $dm->getRepository('MBHHotelBundle:TaskType');


        if (!$input->getOption('force')) {
            if ($roomStatusRepository->createQueryBuilder()->getQuery()->count() > 0) {
                throw new \Exception('RoomStatus Repository has some documents. Use --force');
            }
            if ($taskTypeCategoryRepository->createQueryBuilder()->getQuery()->count() > 0) {
                throw new \Exception('Task Repository has some documents. Use --force');
            }
            if ($taskTypeRepository->createQueryBuilder()->getQuery()->count() > 0) {
                throw new \Exception('TaskType Repository has some documents. Use --force');
            }
        }

        $roomStatusRepository->createQueryBuilder()->remove()->getQuery()->execute();
        $taskTypeCategoryRepository->createQueryBuilder()->remove()->getQuery()->execute();
        $taskTypeRepository->createQueryBuilder()->remove()->getQuery()->execute();

        $hotels = $dm->getRepository('MBHHotelBundle:Hotel')->findAll();

        foreach ($hotels as $hotel) {
            $repairStatus = new RoomStatus();
            $repairStatus->setTitle('Ремонт')->setCode('repair')->setHotel($hotel);
            if ($roomStatusRepository->createQueryBuilder()
                    ->field('code')->equals($repairStatus->getCode())
                    ->field('hotel.id')->equals($hotel->getId())
                    ->getQuery()->count() == 0
            ) {
                $dm->persist($repairStatus);
            }

            $cleaningStatus = new RoomStatus();
            $cleaningStatus->setTitle('Уборка')->setCode('cleaning')->setHotel($hotel);
            if ($roomStatusRepository->createQueryBuilder()
                    ->field('code')->equals($cleaningStatus->getCode())
                    ->field('hotel.id')->equals($hotel->getId())
                    ->getQuery()->count() == 0
            ) {
                $dm->persist($cleaningStatus);
            }

            $reserveStatus = new RoomStatus();
            $reserveStatus->setTitle('Резерв')->setCode('reserve')->setHotel($hotel);
            if ($roomStatusRepository->createQueryBuilder()
                    ->field('code')->equals($reserveStatus->getCode())
                    ->field('hotel.id')->equals($hotel->getId())
                    ->getQuery()->count() == 0
            ) {
                $dm->persist($reserveStatus);
            }

            $otherStatus = new RoomStatus();
            $otherStatus->setTitle('Другое')->setCode('other')->setHotel($hotel);
            if ($roomStatusRepository->createQueryBuilder()
                    ->field('code')->equals($otherStatus->getCode())
                    ->field('hotel.id')->equals($hotel->getId())
                    ->getQuery()->count() == 0
            ) {
                $dm->persist($otherStatus);
            }

            $taskTypeRepository = $dm->getRepository('MBHHotelBundle:TaskType');

            $category = new TaskTypeCategory();
            $category->setIsSystem(true)
                ->setCode('clean')
                ->setTitle('Уборка')
                ->setFullTitle('Уборка помещений')
                ->setHotel($hotel);

            $taskType = new TaskType();
            $taskType->setIsSystem(true)
                ->setCode('clean_room')
                ->setTitle('Убрать комнату')
                ->setCategory($category)
                ->setRoomStatus($cleaningStatus)
                ->setHotel($hotel);

            if ($taskTypeCategoryRepository->createQueryBuilder()
                    ->field('code')->equals($category->getCode())
                    ->field('hotel.id')->equals($hotel->getId())
                    ->getQuery()->count() == 0
            ) {
                $dm->persist($category);
            }
            if ($taskTypeRepository->createQueryBuilder()
                    ->field('code')->equals($taskType->getCode())
                    ->field('hotel.id')->equals($hotel->getId())
                    ->getQuery()->count() == 0
            ) {
                $dm->persist($taskType);
            }

            $dm->flush();
        }
        $output->writeln('Done');
    }
}