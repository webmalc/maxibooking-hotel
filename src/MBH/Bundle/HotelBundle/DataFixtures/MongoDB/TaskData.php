<?php
namespace MBH\Bundle\HotelBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomStatus;
use MBH\Bundle\HotelBundle\Document\TaskType;
use MBH\Bundle\HotelBundle\Document\TaskTypeCategory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class TaskData

 */
class TaskData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected function getRoomStatuses()
    {
        return [
            [
                'code' => 'repair',
                'title' => 'mbhhotelbundle.taskData.repair',
            ],
            [
                'code' => 'cleaning',
                'title' => 'mbhhotelbundle.taskData.cleaning',
            ],
            [
                'code' => 'reserve',
                'title' => 'mbhhotelbundle.taskData.reserve',
            ],
            [
                'code' => 'other',
                'title' => 'mbhhotelbundle.taskData.other',
            ]
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $hotels = $manager->getRepository('MBHHotelBundle:Hotel')->findAll();

        foreach ($hotels as $hotel) {
            $this->persistForHotel($manager, $hotel);
        }
    }

    public function persistForHotel(ObjectManager $manager, Hotel $hotel)
    {
        /** @var DocumentRepository $roomStatusRepository */
        $roomStatusRepository = $manager->getRepository('MBHHotelBundle:RoomStatus');
        $taskTypeCategoryRepository = $manager->getRepository('MBHHotelBundle:TaskTypeCategory');
        $taskTypeRepository = $manager->getRepository('MBHHotelBundle:TaskType');
        $translator = $this->container->get('translator');

        $repairStatusList = [];
        foreach($this->getRoomStatuses() as $roomStatus) {
            $repairStatus = new RoomStatus();
            $repairStatus
                ->setCode($roomStatus['code'])
                ->setTitle($this->container->get('translator')->trans($roomStatus['title']))
                ->setHotel($hotel);
            $isNotExists = $roomStatusRepository->createQueryBuilder()
                    ->field('code')->equals($repairStatus->getCode())
                    ->field('hotel.id')->equals($hotel->getId())
                    ->getQuery()->count() == 0;

            if ($isNotExists) {
                $manager->persist($repairStatus);
                $repairStatusList[$repairStatus->getCode()] = $repairStatus;
            }
        }

        $category = new TaskTypeCategory();

        $category->setIsSystem(true)
            ->setCode('clean')
            ->setTitle($translator->trans('mbhhotelbundle.taskData.cleaning'))
            ->setFullTitle($translator->trans('mbhhotelbundle.taskData.place_cleaning'))
            ->setHotel($hotel);

        $taskType = new TaskType();
        $staff = $manager->getRepository('MBHUserBundle:Group')->findOneBy(['code' => 'staff']);
        $taskType->setIsSystem(true)
            ->setCode('clean_room')
            ->setTitle($translator->trans('mbhhotelbundle.taskData.clean_room'))
            ->setCategory($category)
            ->setDefaultUserGroup($staff)
            ->setHotel($hotel);
        if(isset($repairStatusList['cleaning'])) {
            $taskType->setRoomStatus($repairStatusList['cleaning']);
        }

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

    public function getOrder()
    {
        return 9999;
    }
}