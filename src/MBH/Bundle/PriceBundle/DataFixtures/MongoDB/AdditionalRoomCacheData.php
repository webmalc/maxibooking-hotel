<?php
namespace MBH\Bundle\PriceBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\HotelBundle\DataFixtures\MongoDB\AdditionalRoomTypeData;
use MBH\Bundle\HotelBundle\DataFixtures\MongoDB\HotelData;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use MBH\Bundle\PriceBundle\Document\RoomCache;

/**
 * Class RoomCacheData

 */
class AdditionalRoomCacheData extends AbstractFixture implements OrderedFixtureInterface
{

    const DATA = [
        AdditionalRoomTypeData::ONE_PLACE_ROOM_TYPE['fullTitle'] => 6,
        AdditionalRoomTypeData::THREE_PLACE_ROOM_TYPE['fullTitle'] => 4,
        AdditionalRoomTypeData::TWO_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'] => 5,
        AdditionalRoomTypeData::THREE_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'] => 12
    ];

    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $begin = new \DateTime('midnight +2 days');
        $end = new \DateTime('midnight +1 month -2 days');
        $period = new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end);
        $hotels = $manager->getRepository('MBHHotelBundle:Hotel')->findAll();

        foreach ($hotels as $hotelNumber => $hotel) {

            foreach (self::DATA as $roomKey => $numberOfRooms) {
                /** @var RoomType $roomType */
                $roomType = $this->getReference($roomKey . '/' . $hotelNumber);
                foreach ($period as $day) {
                    $cache = new RoomCache();
                    $cache->setRoomType($roomType)
                        ->setHotel($hotel)
                        ->setDate($day)
                        ->setTotalRooms($numberOfRooms);
                    $manager->persist($cache);
                }
            }
        }

        $manager->flush();

        /** Quotas */
        /** @var Hotel $hotel */
        $hotel = $manager->getRepository(Hotel::class)->findOneBy(['fullTitle' => 'Отель Волга']);
        /** @var RoomType $roomType */
        $roomType = $manager
            ->getRepository(RoomType::class)
            ->createQueryBuilder()
            ->field('fullTitle')->equals(AdditionalRoomTypeData::THREE_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'])
            ->field('hotel')->references($hotel)
            ->getQuery()
            ->getSingleResult()
        ;
        /** @var Tariff $tariff */
        $tariff = $manager
            ->getRepository(Tariff::class)
            ->createQueryBuilder()
            ->field('fullTitle')->equals(AdditionalTariffData::UP_TARIFF_NAME)
            ->field('hotel')->references($hotel)
            ->getQuery()
            ->getSingleResult()
        ;

        $begin = new \DateTime('midnight +2 days');
        $end = new \DateTime('midnight +4 days');
        $period = new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end);
        foreach ($period as $day) {
            $cache = new RoomCache();
            $cache->setRoomType($roomType)
                ->setHotel($hotel)
                ->setDate($day)
                ->setTariff($tariff)
                ->setTotalRooms(8)
            ;

            $manager->persist($cache);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 510;
    }

    /**
     * {@inheritDoc}
     */
    protected function getEnvs(): array
    {
        return ['test', 'dev'];
    }
}
