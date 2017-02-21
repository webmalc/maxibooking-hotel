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

        foreach ($this->getRoomViewData() as $code => $roomViewTypeData) {
            $roomViewType = new RoomViewType();
            $roomViewType->setOpenTravelCode($code);
            $roomViewType->setCodeName(RoomViewType::getRoomViewTypes()[$code]);
            if (!$this->isRoomViewTypeExists($existingRoomViews, $roomViewType)) {
                foreach ($roomViewTypeData as $locale => $titleValue) {
                    $roomViewType->setTitle($titleValue);
                    $roomViewType->setLocale($locale);
                    $manager->persist($roomViewType);
                    $manager->flush();
                }
                $this->setReference('room_view_' . $code, $roomViewType);
            }
        }
    }

    /**
     * Проверяет существует ли в базе данных указанный тип вида комнаты
     *
     * @param $existingRoomViews Массив существующих типов видов комнат
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