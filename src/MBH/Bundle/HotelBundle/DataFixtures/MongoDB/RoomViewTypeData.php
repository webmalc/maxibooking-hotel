<?php

namespace MBH\Bundle\HotelBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\HotelBundle\Document\RoomViewType;

class RoomViewTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    public static  function getRoomViewTypes()
    {
        return [
            new RoomViewType('room_view_types.airport_view', 1, 'Airport view'),
            new RoomViewType('room_view_types.bay_view', 2, 'Bay view'),
            new RoomViewType('room_view_types.city_view', 3, 'City view'),
            new RoomViewType('room_view_types.courtyard_view', 4, 'Courtyard view'),
            new RoomViewType('room_view_types.golf_view', 5, 'Golf view'),
            new RoomViewType('room_view_types.harbor_view', 6, 'Harbor view'),
            new RoomViewType('room_view_types.intercoastal_view', 7, 'Intercoastal view'),
            new RoomViewType('room_view_types.lake_view', 8, 'Lake view'),
            new RoomViewType('room_view_types.marina_view', 9, 'Marina view'),
            new RoomViewType('room_view_types.mountain_view', 10, 'Mountain view'),
            new RoomViewType('room_view_types.ocean_view', 11, 'Ocean view'),
            new RoomViewType('room_view_types.pool_view', 12, 'Pool view'),
            new RoomViewType('room_view_types.river_view', 13, 'River view'),
            new RoomViewType('room_view_types.water_view', 14, 'Water view'),
            new RoomViewType('room_view_types.beach_view', 15, 'Beach view'),
            new RoomViewType('room_view_types.garden_view', 16, 'Garden view'),
            new RoomViewType('room_view_types.park_view', 17, 'Park view'),
            new RoomViewType('room_view_types.forest_view', 18, 'Forest view'),
            new RoomViewType('room_view_types.rain_forest_view', 19, 'Rain forest view'),
            new RoomViewType('room_view_types.various_views', 20, 'Various views'),
            new RoomViewType('room_view_types.limited_view', 21, 'Limited view'),
            new RoomViewType('room_view_types.slope_view', 22, 'Slope view'),
            new RoomViewType('room_view_types.strip_view', 23, 'Strip view'),
            new RoomViewType('room_view_types.countryside_view', 24, 'Countryside view'),
            new RoomViewType('room_view_types.sea_view', 25, 'Sea view')
        ];
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach (self::getRoomViewTypes() as $roomViewType) {
            /** @var RoomViewType $roomViewType */
            $manager->persist($roomViewType);
            $this->setReference('room_view_' . $roomViewType->getOpenTravelCode(), $roomViewType);
        }
        $manager->flush();
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

    public static function getOpentravelCodes()
    {
        $codes = [];
        foreach (self::getRoomViewTypes() as $roomViewType) {
            /** @var RoomViewType $roomViewType */
            $codes[] = $roomViewType->getOpenTravelCode();
        }

        return $codes;
    }

    public static function getOpenTravelCodeNames()
    {
        $codeNames = [];
        foreach (self::getRoomViewTypes() as $roomViewType) {
            /** @var RoomViewType $roomViewType */
            $codeNames[] = $roomViewType->getCodeName();
        }

        return $codeNames;
    }
}