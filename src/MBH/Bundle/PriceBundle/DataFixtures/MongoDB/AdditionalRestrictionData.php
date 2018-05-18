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
class AdditionalRestrictionData extends AbstractFixture implements OrderedFixtureInterface
{
    public const DATA = [
        'zero' => [
            'main-tariff' => [
                'minStayArrival' => [],
                'maxStayArrival' => [],
                'minStay' => [],
                'maxStay' => [],
                'minBeforeArrival' => [],
                'maxBeforeArrival' => [],
                'minGuest' => [],
                'maxGuest' => [],
                'closedOnArrival' => [],
                'closedOnDeparture' => [],
                'closed' => [],

            ],
            'downTariff-tariff' => [
                'minStayArrival' => [],
                'maxStayArrival' => [],
                'minStay' => [],
                'maxStay' => [],
                'minBeforeArrival' => [],
                'maxBeforeArrival' => [],
                'minGuest' => [],
                'maxGuest' => [],
                'closedOnArrival' => [],
                'closedOnDeparture' => [],
                'closed' => [],

            ],
            'upTariff-tariff' => [
                'minStayArrival' => [],
                'maxStayArrival' => [],
                'minStay' => [],
                'maxStay' => [],
                'minBeforeArrival' => [],
                'maxBeforeArrival' => [],
                'minGuest' => [],
                'maxGuest' => [],
                'closedOnArrival' => [],
                'closedOnDeparture' => [],
                'closed' => [],

            ],
        ],
        'one' => [
            'main-tariff' => [
                'minStayArrival' => [],
                'maxStayArrival' => [],
                'minStay' => [],
                'maxStay' => [],
                'minBeforeArrival' => [],
                'maxBeforeArrival' => [],
                'minGuest' => [],
                'maxGuest' => [],
                'closedOnArrival' => [],
                'closedOnDeparture' => [],
                'closed' => [],

            ],
            'downTariff-tariff' => [
                'minStayArrival' => [],
                'maxStayArrival' => [],
                'minStay' => [],
                'maxStay' => [],
                'minBeforeArrival' => [],
                'maxBeforeArrival' => [],
                'minGuest' => [],
                'maxGuest' => [],
                'closedOnArrival' => [],
                'closedOnDeparture' => [],
                'closed' => [],

            ],
            'upTariff-tariff' => [
                'minStayArrival' => [],
                'maxStayArrival' => [],
                'minStay' => [],
                'maxStay' => [],
                'minBeforeArrival' => [],
                'maxBeforeArrival' => [],
                'minGuest' => [],
                'maxGuest' => [],
                'closedOnArrival' => [],
                'closedOnDeparture' => [],
                'closed' => [],

            ],
        ],
        'two' => [
            'main-tariff' => [
                'minStayArrival' => [],
                'maxStayArrival' => [],
                'minStay' => [],
                'maxStay' => [],
                'minBeforeArrival' => [],
                'maxBeforeArrival' => [],
                'minGuest' => [],
                'maxGuest' => [],
                'closedOnArrival' => [],
                'closedOnDeparture' => [],
                'closed' => [],

            ],
            'downTariff-tariff' => [
                'minStayArrival' => [],
                'maxStayArrival' => [],
                'minStay' => [],
                'maxStay' => [],
                'minBeforeArrival' => [],
                'maxBeforeArrival' => [],
                'minGuest' => [],
                'maxGuest' => [],
                'closedOnArrival' => [],
                'closedOnDeparture' => [],
                'closed' => [],

            ],
            'upTariff-tariff' => [
                'minStayArrival' => [],
                'maxStayArrival' => [],
                'minStay' => [],
                'maxStay' => [],
                'minBeforeArrival' => [],
                'maxBeforeArrival' => [],
                'minGuest' => [],
                'maxGuest' => [],
                'closedOnArrival' => [],
                'closedOnDeparture' => [],
                'closed' => [],

            ],
        ],
        'three' => [
            'main-tariff' => [
                'minStayArrival' => [],
                'maxStayArrival' => [],
                'minStay' => [],
                'maxStay' => [],
                'minBeforeArrival' => [],
                'maxBeforeArrival' => [],
                'minGuest' => [],
                'maxGuest' => [],
                'closedOnArrival' => [],
                'closedOnDeparture' => [],
                'closed' => [],

            ],
            'downTariff-tariff' => [
                'minStayArrival' => [],
                'maxStayArrival' => [],
                'minStay' => [],
                'maxStay' => [],
                'minBeforeArrival' => [],
                'maxBeforeArrival' => [],
                'minGuest' => [],
                'maxGuest' => [],
                'closedOnArrival' => [],
                'closedOnDeparture' => [],
                'closed' => [],

            ],
            'upTariff-tariff' => [
                'minStayArrival' => [],
                'maxStayArrival' => [],
                'minStay' => [],
                'maxStay' => [],
                'minBeforeArrival' => [],
                'maxBeforeArrival' => [],
                'minGuest' => [],
                'maxGuest' => [],
                'closedOnArrival' => [],
                'closedOnDeparture' => [],
                'closed' => [],

            ],
        ],
        'four' => [
            'main-tariff' => [
                'minStayArrival' => [],
                'maxStayArrival' => [],
                'minStay' => [],
                'maxStay' => [],
                'minBeforeArrival' => [],
                'maxBeforeArrival' => [],
                'minGuest' => [],
                'maxGuest' => [],
                'closedOnArrival' => [],
                'closedOnDeparture' => [],
                'closed' => [],

            ],
            'downTariff-tariff' => [
                'minStayArrival' => [],
                'maxStayArrival' => [],
                'minStay' => [],
                'maxStay' => [],
                'minBeforeArrival' => [],
                'maxBeforeArrival' => [],
                'minGuest' => [],
                'maxGuest' => [],
                'closedOnArrival' => [],
                'closedOnDeparture' => [],
                'closed' => [],

            ],
            'upTariff-tariff' => [
                'minStayArrival' => [],
                'maxStayArrival' => [],
                'minStay' => [],
                'maxStay' => [],
                'minBeforeArrival' => [],
                'maxBeforeArrival' => [],
                'minGuest' => [],
                'maxGuest' => [],
                'closedOnArrival' => [],
                'closedOnDeparture' => [],
                'closed' => [],

            ],
        ],
        'hostel' => [
            'main-tariff' => [
                'minStayArrival' => [],
                'maxStayArrival' => [],
                'minStay' => [],
                'maxStay' => [],
                'minBeforeArrival' => [],
                'maxBeforeArrival' => [],
                'minGuest' => [],
                'maxGuest' => [],
                'closedOnArrival' => [],
                'closedOnDeparture' => [],
                'closed' => [],

            ],
            'downTariff-tariff' => [
                'minStayArrival' => [],
                'maxStayArrival' => [],
                'minStay' => [],
                'maxStay' => [],
                'minBeforeArrival' => [],
                'maxBeforeArrival' => [],
                'minGuest' => [],
                'maxGuest' => [],
                'closedOnArrival' => [],
                'closedOnDeparture' => [],
                'closed' => [],

            ],
            'upTariff-tariff' => [
                'minStayArrival' => [],
                'maxStayArrival' => [],
                'minStay' => [],
                'maxStay' => [],
                'minBeforeArrival' => [],
                'maxBeforeArrival' => [],
                'minGuest' => [],
                'maxGuest' => [],
                'closedOnArrival' => [],
                'closedOnDeparture' => [],
                'closed' => [],

            ],
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $hotels = $manager->getRepository('MBHHotelBundle:Hotel')->findAll();
        $begin = new \DateTime('midnight');
        $end = new \DateTime('midnight +1 month -1 day');
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
        return 610;
    }

    /**
     * {@inheritDoc}
     */
    protected function getEnvs(): array
    {
        return ['test', 'dev'];
    }
}
