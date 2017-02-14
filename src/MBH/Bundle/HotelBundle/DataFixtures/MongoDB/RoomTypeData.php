<?php
namespace MBH\Bundle\HotelBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class TaskData

 */
class RoomTypeData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    const DATA = [
        'roomtype-double' => [
            'title' => 'Двухместный',
            'places' => 2,
            'additionalPlaces' => 1,
            'color' => '#b50e2c'
        ],
        'hotel-triple' => [
            'title' => 'Трехместный',
            'places' => 3,
            'additionalPlaces' => 2,
            'color' => '#008000'
        ]
    ];
    
    use ContainerAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        if (count($manager->getRepository('MBHHotelBundle:RoomType')->findAll())) {
            return;
        }
        $hotels = $manager->getRepository('MBHHotelBundle:Hotel')->findAll();

        foreach ($hotels as $hotel) {
            foreach (self::DATA as $key => $data) {
                $roomType = new RoomType();
                $roomType
                    ->setHotel($hotel)
                    ->setFullTitle($data['title'])
                    ->setPlaces($data['places'])
                    ->setAdditionalPlaces($data['additionalPlaces'])
                ;

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

                $this->setReference($key, $roomType);
            }
        }
    }

    public function getOrder()
    {
        return 4;
    }
}