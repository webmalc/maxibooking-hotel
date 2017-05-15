<?php

namespace MBH\Bundle\PackageBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PriceBundle\Document\Tariff;

/**
 * Class OrderData
 */
class OrderData extends AbstractFixture implements OrderedFixtureInterface
{
    const DATA = [
        [
            'number' => '1',
            'adults' => 1,
            'children' => 1,
            'price' => 2000,
            'paid' => 2001,
            'regDayAgo' => 1,
            'beginAfter' => 0,
            'length' => 3
        ],
        [
            'number' => '2',
            'adults' => 1,
            'children' => 0,
            'price' => 800,
            'paid' => 10,
            'regDayAgo' => 1,
            'beginAfter' => 2,
            'length' => 2
        ],
        [
            'number' => '3',
            'adults' => 3,
            'children' => 1,
            'price' => 7000,
            'paid' => 1000,
            'regDayAgo' => 2,
            'beginAfter' => 1,
            'length' => 5
        ],
        [
            'number' => '4',
            'adults' => 2,
            'children' => 0,
            'price' => 4631,
            'paid' => 276,
            'regDayAgo' => 2,
            'beginAfter' => 10,
            'length' => 6
        ],
        [
            'number' => '5',
            'adults' => '3',
            'children' => '2',
            'price' => '8000.0',
            'paid' => '8000',
            'regDayAgo' => 5,
            'beginAfter' => 8,
            'length' => 3
        ],
        [
            'number' => '6',
            'adults' => 2,
            'children' => 0,
            'price' => 9364,
            'paid' => 0,
            'regDayAgo' => 5,
            'beginAfter' => 0,
            'length' => 3
        ],
        [
            'number' => '7',
            'adults' => 1,
            'children' => 0,
            'price' => 430,
            'paid' => 560,
            'regDayAgo' => 6,
            'beginAfter' => 0,
            'length' => 3
        ],
        [
            'number' => '8',
            'adults' => 1,
            'children' => 0,
            'price' => 3000,
            'paid' => 750,
            'regDayAgo' => 6,
            'beginAfter' => 0,
            'length' => 3
        ],
        [
            'number' => '9',
            'adults' => 1,
            'children' => 0,
            'price' => 7000,
            'paid' => 50,
            'regDayAgo' => 6,
            'beginAfter' => 0,
            'length' => 6
        ],
        [
            'number' => '10',
            'adults' => 2,
            'children' => 1,
            'price' => 7500,
            'paid' => 7500,
            'regDayAgo' => 7,
            'beginAfter' => 7,
            'length' => 6
        ],
        [
            'number' => 11,
            'adults' => 1,
            'children' => 0,
            'price' => 9000,
            'paid' => 0,
            'regDayAgo' => 7,
            'beginAfter' => 0,
            'length' => 6
        ],
        [
            'number' => 12,
            'adults' => 3,
            'children' => 1,
            'price' => 19000,
            'paid' => 7000,
            'regDayAgo' => 9,
            'beginAfter' => 8,
            'length' => 10
        ],
        [
            'number' => 13,
            'adults' => 2,
            'children' => 0,
            'price' => 19000,
            'paid' => 7000,
            'regDayAgo' => 9,
            'beginAfter' => 8,
            'length' => 10
        ],
        [
            'number' => 14,
            'adults' => 2,
            'children' => 0,
            'price' => 10000,
            'paid' => 6000,
            'regDayAgo' => 9,
            'beginAfter' => 4,
            'length' => 7
        ],
        [
            'number' => 15,
            'adults' => 3,
            'children' => 0,
            'price' => 14000,
            'paid' => 14000,
            'regDayAgo' => 11,
            'beginAfter' => 4,
            'length' => 7
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $this->persistPackage($manager);
    }

    /**
     * @param ObjectManager $manager
     * @param $data
     * @return Order
     */
    public function persistOrder(ObjectManager $manager, $data)
    {
        $touristKeys = array_keys(TouristData::TOURIST_DATA);
        $tourist = $this->getReference($touristKeys[array_rand($touristKeys, 1)]);
        $order = (new Order())
            ->setPaid($data['paid'])
            ->setStatus('offline')
            ->setTotalOverwrite($data['price'])
            ->setSource($this->getReference('Booking.com'))
            ->setMainTourist($tourist)
            ->setCreatedAt((new \DateTime())->modify('-' . $data['regDayAgo'] . 'days'));

        $this->setReference('order' . $data['number'], $order);
        $manager->persist($order);
        $manager->flush();

        return $order;
    }

    /**
     * @param ObjectManager $manager
     */
    public function persistPackage(ObjectManager $manager)
    {
        /** @var Tariff $tariff */
        $tariff = $this->getReference('main-tariff');
        $roomType = $this->getReference('roomtype-double');

        foreach (self::DATA as $packageData) {
            $order = $this->persistOrder($manager, $packageData);
            $beginDate = new \DateTime('midnight +' . $packageData['beginAfter'] . 'days');
            $endDate = (clone  $beginDate)->modify('+' . $packageData['length'] . 'days');
            $dateOfCreation = new \DateTime('-' . $packageData['regDayAgo'] . 'days');

            $package = new Package();
            /** @var RoomType $roomType */
            $package
                ->setAdults($packageData['adults'])
                ->setNumber($packageData['number'] . '/1')
                ->setChildren($packageData['children'])
                ->setPrice($packageData['price'])
                ->setOrder($order)
                ->setTariff($tariff)
                ->setRoomType($roomType)
                ->setBegin($beginDate)
                ->setCreatedAt($dateOfCreation)
                ->setEnd($endDate);

            $manager->persist($package);
            $manager->flush();
        }
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 5;
    }

    /**
     * {@inheritDoc}
     */
    protected function getEnvs(): array
    {
        return ['test'];
    }
}
