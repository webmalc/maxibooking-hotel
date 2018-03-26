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
        $translator = $this->container->get('translator');
        $translationRepository = $manager->getRepository('GedmoTranslatable:Translation');
        $locales = $this->container->getParameter('mbh.languages');

        $repairStatusList = [];
        foreach ($this->getRoomStatuses() as $roomStatusData) {
            $roomStatus = new RoomStatus();
            $roomStatus
                ->setCode($roomStatusData['code'])
                ->setTitle($translator->trans($roomStatusData['title']))
                ->setHotel($hotel);
            $isNotExists = $roomStatusRepository->createQueryBuilder()
                    ->field('code')->equals($roomStatus->getCode())
                    ->field('hotel.id')->equals($hotel->getId())
                    ->getQuery()->count() == 0;

            if ($isNotExists) {
                foreach ($locales as $locale) {
                    $translationRepository
                        ->translate($roomStatus, 'title', $locale, $translator->trans($roomStatusData['title'], [], null, $locale));
                }
                $manager->persist($roomStatus);
                $repairStatusList[$roomStatus->getCode()] = $roomStatus;
            }
        }

        $category = new TaskTypeCategory();
        $category->setIsSystem(true)
            ->setCode('clean')
            ->setTitle($translator->trans('mbhhotelbundle.taskData.cleaning'))
            ->setFullTitle($translator->trans('mbhhotelbundle.taskData.place_cleaning'))
            ->setHotel($hotel);

        $taskTypeCategoryRepository = $manager->getRepository('MBHHotelBundle:TaskTypeCategory');
        if ($taskTypeCategoryRepository->createQueryBuilder()
                ->field('code')->equals($category->getCode())
                ->field('hotel.id')->equals($hotel->getId())
                ->getQuery()->count() == 0
        ) {
            foreach ($locales as $locale) {
                $translationRepository
                    ->translate($category, 'title', $locale, $translator->trans('mbhhotelbundle.taskData.cleaning', [], null, $locale))
                    ->translate($category, 'fullTitle', $locale, $translator->trans('mbhhotelbundle.taskData.place_cleaning', [], null, $locale));
            }
            $manager->persist($category);
        }

        $taskType = new TaskType();
        $staff = $manager->getRepository('MBHUserBundle:Group')->findOneBy(['code' => 'staff']);
        $taskType->setIsSystem(true)
            ->setCode('clean_room')
            ->setTitle($translator->trans('mbhhotelbundle.taskData.clean_room'))
            ->setCategory($category)
            ->setDefaultUserGroup($staff)
            ->setHotel($hotel);
        if (isset($repairStatusList['cleaning'])) {
            $taskType->setRoomStatus($repairStatusList['cleaning']);
        }

        $taskTypeRepository = $manager->getRepository('MBHHotelBundle:TaskType');


        if ($taskTypeRepository->createQueryBuilder()
                ->field('code')->equals($taskType->getCode())
                ->field('hotel.id')->equals($hotel->getId())
                ->getQuery()->count() == 0
        ) {
            foreach ($locales as $locale) {
                $translationRepository
                    ->translate($taskType, 'title', $locale, $translator->trans('mbhhotelbundle.taskData.clean_room', [], null, $locale));
            }
            $manager->persist($taskType);
        }
        $manager->flush();
    }

    public function getOrder()
    {
        return 9999;
    }
}