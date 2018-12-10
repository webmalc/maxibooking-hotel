<?php
namespace MBH\Bundle\PriceBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Document\Restriction;

/**
 * Class RestrictionData
 */
class RestrictionData extends AbstractFixture implements OrderedFixtureInterface
{
    const PERIOD_LENGTH_STR = 'midnight +6 month';
    const DATA = [
        'main-tariff' => [
            'roomtype-double' => 3,
            'hotel-triple' => 2,
        ],
        'special-tariff' => [
            'single' => 3,
            'roomtype-double' => 5,
            'hotel-triple' => 6,
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $hotels = $manager->getRepository('MBHHotelBundle:Hotel')->findAll();
        $begin = new \DateTime('midnight');
        $end = new \DateTime(self::PERIOD_LENGTH_STR);
        $period = new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end);

        foreach ($hotels as $hotelNumber => $hotel) {
            foreach (self::DATA as $tariffKey => $dataByRoomTypes) {
                /** @var Tariff $tariff */
                $tariff = $this->getReference($tariffKey . '/' . $hotelNumber);
                foreach ($dataByRoomTypes as $roomTypeKey => $minStay) {
                    /** @var RoomType $roomType */
                    $roomType = $this->getReference($roomTypeKey . '/' . $hotelNumber);
                    foreach ($period as $day) {
                        $cache = new Restriction();
                        $cache->setRoomType($roomType)
                            ->setHotel($hotel)
                            ->setTariff($tariff)
                            ->setMinStay($minStay)
                            ->setDate($day);
                        $manager->persist($cache);
                    }
                }
            }
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 40;
    }

    /**
     * {@inheritDoc}
     */
    protected function getEnvs(): array
    {
        return ['test', 'dev', 'sandbox'];
    }
}
