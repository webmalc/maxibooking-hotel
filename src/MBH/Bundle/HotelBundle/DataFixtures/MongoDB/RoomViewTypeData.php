<?php

namespace MBH\Bundle\HotelBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\HotelBundle\Document\RoomViewType;
use Symfony\Component\Yaml\Yaml;

class RoomViewTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $existingRoomViews = $manager->getRepository('MBHHotelBundle:RoomViewType')->findAll();
        $translationRepository = $manager->getRepository('GedmoTranslatable:Translation');

        foreach ($this->getRoomViewData() as $code => $roomViewTypeData) {
            $roomViewType = new RoomViewType();
            $roomViewType->setOpenTravelCode($code);
            $roomViewType->setCodeName(RoomViewType::getRoomViewTypes()[$code]);
            if (!$this->isRoomViewTypeExists($existingRoomViews, $roomViewType)) {
                $roomViewType->setTitle($roomViewTypeData['ru']);
                $roomViewType->setLocale('ru_RU');
                $translationRepository
                    ->translate($roomViewType, 'title', 'ru_RU', $roomViewTypeData['ru'])
                    ->translate($roomViewType, 'title', 'en_EN', $roomViewTypeData['en']);

                $manager->persist($roomViewType);

                $this->setReference('room_view_' . $code, $roomViewType);
            }
        }

        $manager->flush();
    }

    /**
     * Checks whether the specified type of room type exists in the database
     *
     * @param $existingRoomViews
     * @param RoomViewType $roomViewType
     * @return bool
     */
    private function isRoomViewTypeExists($existingRoomViews, RoomViewType $roomViewType)
    {
        foreach ($existingRoomViews as $existingRoomView) {
            /** @var RoomViewType $existingRoomView */
            if ($roomViewType->getOpenTravelCode() == $existingRoomView->getOpenTravelCode()) {
                return true;
            }
        }

        return false;
    }

    private function getRoomViewData()
    {
        $value = Yaml::parse(file_get_contents(__DIR__.'/../../../BaseBundle/Resources/translations/fixtures_data.yml'));

        return $value['room_view_types'];
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 9996;
    }
}