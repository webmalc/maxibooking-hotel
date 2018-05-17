<?php
namespace MBH\Bundle\HotelBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class TaskData
 */
class AdditionalRoomTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Get roomType data
     *
     * @return array
     */
    public function data()
    {
        return [
            'zero' => [
                'title' => 'TwoOnlyRoomType',
                'places' => 1,
                'additionalPlaces' => 0,
                'color' => '#008000',
                'isHostel' => false,
                'isChildPrice' => false,
                'isIndividualAddPrice' => false
            ],
            'one' => [
                'title' => 'OneAndOneRoomType',
                'places' => 1,
                'additionalPlaces' => 1,
                'color' => '#008000',
                'isHostel' => false,
                'isChildPrice' => false,
                'isIndividualAddPrice' => false
            ],
            'two' => [
                'title' => 'TwoAndOneAndChildPriceRoomType',
                'places' => 2,
                'additionalPlaces' => 1,
                'color' => '#b50e2c',
                'isHostel' => false,
                'isChildPrice' => true,
                'isIndividualAddPrice' => false
            ],
            'three' => [
                'title' => 'ThreeAndThreeAndIndividualPriceRoomType',
                'places' => 3,
                'additionalPlaces' => 3,
                'color' => '#008000',
                'isHostel' => false,
                'isChildPrice' => false,
                'isIndividualAddPrice' => true
            ],
            'four' => [
                'title' => 'TwoAndOneAndChildPriceAndIndividualPriceRoomType',
                'places' => 2,
                'additionalPlaces' => 1,
                'color' => '#b50e2c',
                'isHostel' => false,
                'isChildPrice' => true,
                'isIndividualAddPrice' => true
            ],
            'hostel' => [
                'title' => 'ThreeAndTwoHostel',
                'places' => 3,
                'additionalPlaces' => 2,
                'color' => '#008000',
                'isHostel' => true,
                'isChildPrice' => false,
                'isIndividualAddPrice' => false
            ]
        ];
    }

    use ContainerAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $hotels = $manager->getRepository('MBHHotelBundle:Hotel')->findAll();

        foreach ($hotels as $hotelNumber => $hotel) {
            foreach ($this->data() as $key => $data) {
                $roomType = new RoomType();
                $roomType
                    ->setHotel($hotel)
                    ->setFullTitle($data['title'])
                    ->setPlaces($data['places'])
                    ->setAdditionalPlaces($data['additionalPlaces'])
                ;
                if (false === $data['isHostel']) {
                    $roomType
                        ->setIsHostel(false)
                        ->setIsChildPrices($data['isChildPrice'])
                        ->setIsIndividualAdditionalPrices($data['isIndividualAddPrice'])
                    ;
                } else {
                    $roomType->setIsHostel(true);
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

                $roomTypeReference = $key . '/' . $hotelNumber;
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