<?php
namespace MBH\Bundle\HotelBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class TaskData
 */
class RoomTypeData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * Get roomType data
     *
     * @return array
     */
    public function data()
    {
        return [
            'single' => [
                'title' => 'mbhhotelbundle.roomTypeData.single.place',
                'places' => 1,
                'additionalPlaces' => 1,
                'color' => '#008000'
            ],
            'roomtype-double' => [
                'title' => 'mbhhotelbundle.roomTypeData.two.place',
                'places' => 2,
                'additionalPlaces' => 1,
                'color' => '#b50e2c'
            ],
            'hotel-triple' => [
                'title' => 'mbhhotelbundle.roomTypeData.three.place',
                'places' => 3,
                'additionalPlaces' => 2,
                'color' => '#008000'
            ]
        ];
    }

    use ContainerAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        if (count($manager->getRepository('MBHHotelBundle:RoomType')->findAll())) {
            return;
        }
        $hotels = $manager->getRepository('MBHHotelBundle:Hotel')->findAll();

        foreach ($hotels as $hotelNumber => $hotel) {
            foreach ($this->data() as $key => $data) {
                $roomType = new RoomType();
                $roomType
                    ->setHotel($hotel)
                    ->setFullTitle($this->container->get('translator')->trans($data['title']))
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

                $roomTypeReference = $key . '/' . $hotelNumber;
                $this->setReference($roomTypeReference, $roomType);
            }
        }
    }

    public function getEnvs(): array
    {
        return ['test', 'dev', 'sandbox'];
    }

    public function getOrder()
    {
        return 4;
    }
}