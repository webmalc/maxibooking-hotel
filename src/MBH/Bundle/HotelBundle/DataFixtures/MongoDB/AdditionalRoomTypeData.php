<?php
namespace MBH\Bundle\HotelBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class TaskData
 */
class AdditionalRoomTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    public const ONE_PLACE_ROOM_TYPE = [
        'fullTitle' => 'OnePlace',
        'places' => 1,
        'additionalPlaces' => 0,
        'color' => '#008000',
        'isHostel' => false,
        'isChildPrice' => false,
        'isIndividualAddPrice' => false,
        'category' => RoomTypeCategoryData::NO_ADDITIONAL_PLACES_CATEGORY['fullTitle']
    ];

    public const THREE_PLACE_ROOM_TYPE = [
        'fullTitle' => 'ThreePlace',
        'places' => 3,
        'additionalPlaces' => 0,
        'color' => '#008000',
        'isHostel' => false,
        'isChildPrice' => false,
        'isIndividualAddPrice' => false,
        'category' => RoomTypeCategoryData::NO_ADDITIONAL_PLACES_CATEGORY['fullTitle']
    ];

    public const TWO_PLUS_TWO_PLACE_ROOM_TYPE = [
        'fullTitle' => 'TwoPlusTwoPlace',
        'places' => 2,
        'additionalPlaces' => 2,
        'color' => '#008000',
        'isHostel' => false,
        'isChildPrice' => true,
        'isIndividualAddPrice' => true,
        'category' => RoomTypeCategoryData::ADDITIONAL_PLACES_CATEGORY['fullTitle']
    ];
    public const THREE_PLUS_TWO_PLACE_ROOM_TYPE = [
        'fullTitle' => 'ThreePlusTwoPlace',
        'places' => 3,
        'additionalPlaces' => 2,
        'color' => '#008000',
        'isHostel' => false,
        'isChildPrice' => false,
        'isIndividualAddPrice' => false,
        'category' => RoomTypeCategoryData::ADDITIONAL_PLACES_CATEGORY['fullTitle']
    ];

    public const ROOM_TYPES = [
        self::ONE_PLACE_ROOM_TYPE,
        self::THREE_PLACE_ROOM_TYPE,
        self::TWO_PLUS_TWO_PLACE_ROOM_TYPE,
        self::THREE_PLUS_TWO_PLACE_ROOM_TYPE
    ];





    use ContainerAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $hotels = $manager->getRepository('MBHHotelBundle:Hotel')->findAll();

        foreach ($hotels as $hotelNumber => $hotel) {
            foreach (self::ROOM_TYPES as $roomTypeData) {
                $roomType = new RoomType();
                $roomType
                    ->setHotel($hotel)
                    ->setFullTitle($roomTypeData['fullTitle'])
                    ->setPlaces($roomTypeData['places'])
                    ->setAdditionalPlaces($roomTypeData['additionalPlaces'])
                ;
                if (false === $roomTypeData['isHostel']) {
                    $roomType
                        ->setIsHostel(false)
                        ->setIsChildPrices($roomTypeData['isChildPrice'])
                        ->setIsIndividualAdditionalPrices($roomTypeData['isIndividualAddPrice'])
                    ;
                } else {
                    $roomType->setIsHostel(true);
                }

                $categoryName = $roomTypeData['category'] ?? null;
                if ($categoryName) {
                    /** @var RoomTypeCategory $category */
                    $category = $this->getReference($roomTypeData['category'] . '/' . $hotelNumber);
                    $roomType->setCategory($category);
                }


                $manager->persist($roomType);
                $manager->flush();

                for ($i = 1; $i <= 10; $i ++) {
                    $room = new Room();
                    $room
                        ->setRoomType($roomType)
                        ->setHotel($hotel)
                        ->setFullTitle($i)
                    ;
                    $manager->persist($room);
                }
                $manager->flush();

                $roomTypeReference = $roomTypeData['fullTitle'] . '/' . $hotelNumber;
                $this->setReference($roomTypeReference, $roomType);
            }
        }
    }

    public function getEnvs(): array
    {
        return ['test', 'dev'];
    }

    public function getOrder()
    {
        return 220;
    }
}