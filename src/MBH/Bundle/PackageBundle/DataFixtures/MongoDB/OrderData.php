<?php

namespace MBH\Bundle\PackageBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\UserBundle\DataFixtures\MongoDB\UserData;
use MBH\Bundle\UserBundle\Document\User;

/**
 * Class OrderData
 */
class OrderData extends AbstractFixture implements OrderedFixtureInterface
{
    public const ORDER_DATA_4_NUMBER = '4';
    public const ORDER_DATA_4_PRICE = 4631;
    public const ORDER_DATA_4_PAID = 276;

    const DATA = [
        [
            'number' => '1',
            'adults' => 1,
            'children' => 1,
            'price' => 2000,
            'paid' => 2001,
            'regDayAgo' => 1,
            'beginAfter' => 0,
            'length' => 3,
            'owner' => UserData::USER_MANAGER
        ],
        [
            'number' => '2',
            'adults' => 1,
            'children' => 0,
            'price' => 800,
            'paid' => 10,
            'regDayAgo' => 1,
            'beginAfter' => 2,
            'length' => 2,
            'owner' => UserData::USER_MANAGER
        ],
        [
            'number' => '3',
            'adults' => 3,
            'children' => 1,
            'price' => 7000,
            'paid' => 1000,
            'regDayAgo' => 2,
            'beginAfter' => 1,
            'length' => 5,
            'owner' => UserData::USER_L_MANAGER
        ],
        [
            'number'     => self::ORDER_DATA_4_NUMBER,
            'adults'     => 2,
            'children'   => 0,
            'price'      => self::ORDER_DATA_4_PRICE,
            'paid'       => self::ORDER_DATA_4_PAID,
            'regDayAgo'  => 2,
            'beginAfter' => 10,
            'length'     => 6,
            'owner' => UserData::USER_MANAGER

        ],
        [
            'number' => '5',
            'adults' => 3,
            'children' => 2,
            'price' => '8000.0',
            'paid' => '8000',
            'regDayAgo' => 5,
            'beginAfter' => 8,
            'length' => 3,
            'status' => 'online'
        ],
        [
            'number' => '6',
            'adults' => 2,
            'children' => 0,
            'price' => 9364,
            'paid' => 0,
            'regDayAgo' => 5,
            'beginAfter' => 0,
            'length' => 3,
            'owner' => UserData::USER_ADMIN

        ],
        [
            'number'       => '16',
            'adults'       => 1,
            'children'     => 0,
            'price'        => 430,
            'paid'         => 560,
            'regDayAgo'    => 5,
            'beginAfter'   => 0,
            'length'       => 3,
            'cancelledAgo' => 6,
            'owner' => UserData::USER_L_MANAGER
        ],
        [
            'number' => '17',
            'adults' => 2,
            'children' => 0,
            'price' => 14430,
            'paid' => 14430,
            'regDayAgo' => 5,
            'beginAfter' => 0,
            'length' => 3,
            'owner' => UserData::USER_ADMIN
        ],
        [
            'number' => '7',
            'adults' => 1,
            'children' => 0,
            'price' => 430,
            'paid' => 560,
            'regDayAgo' => 6,
            'beginAfter' => 0,
            'length' => 3,
            'owner' => UserData::USER_ADMIN
        ],
        [
            'number' => '8',
            'adults' => 1,
            'children' => 0,
            'price' => 3000,
            'paid' => 750,
            'regDayAgo' => 6,
            'beginAfter' => 0,
            'length' => 3,
            'owner' => UserData::USER_L_MANAGER
        ],
        [
            'number' => '9',
            'adults' => 2,
            'children' => 1,
            'price' => 7000,
            'paid' => 50,
            'regDayAgo' => 6,
            'beginAfter' => 0,
            'length' => 6,
            'owner' => UserData::USER_ADMIN
        ],
        [
            'number' => '10',
            'adults' => 2,
            'children' => 1,
            'price' => 7500,
            'paid' => 7500,
            'regDayAgo' => 7,
            'beginAfter' => 7,
            'length' => 6,
            'cancelledAgo' => 7,
            'owner' => UserData::USER_MANAGER
        ],
        [
            'number' => 11,
            'adults' => 1,
            'children' => 0,
            'price' => 9000,
            'paid' => 0,
            'regDayAgo' => 7,
            'beginAfter' => 0,
            'length' => 6,
            'owner' => UserData::USER_ADMIN

        ],
        [
            'number' => 12,
            'adults' => 3,
            'children' => 1,
            'price' => 19000,
            'paid' => 7000,
            'regDayAgo' => 9,
            'beginAfter' => 8,
            'length' => 10,
            'status' => 'online',
            'owner' => UserData::USER_MANAGER
        ],
        [
            'number' => 13,
            'adults' => 2,
            'children' => 0,
            'price' => 19000,
            'paid' => 7000,
            'regDayAgo' => 9,
            'beginAfter' => 8,
            'length' => 10,
            'owner' => UserData::USER_L_MANAGER
        ],
        [
            'number' => 14,
            'adults' => 2,
            'children' => 0,
            'price' => 10000,
            'paid' => 6000,
            'regDayAgo' => 9,
            'beginAfter' => 4,
            'length' => 7,
            'owner' => UserData::USER_ADMIN
        ],
        [
            'number' => 15,
            'adults' => 3,
            'children' => 0,
            'price' => 14000,
            'paid' => 14000,
            'regDayAgo' => 11,
            'beginAfter' => 4,
            'length' => 7,
            'owner' => UserData::USER_ADMIN
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
        $locale = $this->container->getParameter('locale') === 'ru' ? 'ru' : 'en';
        $touristKeys = array_keys(TouristData::TOURIST_DATA[$locale]);

        if ($data['number'] === self::ORDER_DATA_4_NUMBER) {
            /** @var Tourist $tourist */
            $tourist = $this->getReference(TouristData::TOURIST_RICK_KEY);
        } else {
            $tourist = $this->getReference($touristKeys[array_rand($touristKeys, 1)]);
        }

        $owner = $this->getOwner($data['owner'] ?? null);
        $order = (new Order())
            ->setPrice($data['price'])
            ->setPaid($data['paid'])
            ->setStatus($data['status'] ?? 'offline')
            ->setTotalOverwrite($data['price'])
//            ->setSource($this->getReference('Booking.com'))
            ->setMainTourist($tourist)
            ->setCreatedBy($owner)
            ->setCreatedAt((new \DateTime())->modify('-' . $data['regDayAgo'] . 'days'));
        if ($owner) {
            $order->setOwner($owner);

        }
        $order->checkPaid();

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
        $tariff = $this->getReference('main-tariff/0');
        /** @var RoomType $roomType */
        $roomType = $this->getReference('roomtype-double/0');


        foreach (self::DATA as $packageData) {
            $owner = $this->getOwner($packageData['owner'] ?? null);
            $order = $this->persistOrder($manager, $packageData);
            $beginDate = new \DateTime('midnight +' . $packageData['beginAfter'] . 'days');
            $endDate = (clone  $beginDate)->modify('+' . $packageData['length'] . 'days');
            $dateOfCreation = new \DateTime('-' . $packageData['regDayAgo'] . 'days');

            $package = new Package();
            $package
                ->setAdults($packageData['adults'])
                ->setNumber(1)
                ->setNumberWithPrefix($packageData['number'] . '/1')
                ->setChildren($packageData['children'])
                ->setPrice($packageData['price'])
                ->setOrder($order)
                ->setTariff($tariff)
                ->setRoomType($roomType)
                ->setBegin($beginDate)
                ->setCreatedAt($dateOfCreation)
                ->setCreatedBy($owner)
                ->setEnd($endDate);
            if ($owner) {
                $package->setOwner($owner);
            }

            if (isset($packageData['cancelledAgo'])) {
                $cancellationDate = (new \DateTime())->modify('-' . $packageData['cancelledAgo'] . 'days');
                $package->setDeletedAt($cancellationDate);
                $order->setDeletedAt($cancellationDate);
            }

            $prices = [];
            for ($i = 0; $i < $package->getNights(); $i++) {
                $date = (clone $package->getBegin())->modify('+' . $i . 'days');
                $price = $package->getPrice() / $package->getNights();
                $prices[] = new PackagePrice($date, $price, $package->getTariff());
            }
            $package->setPrices($prices);

            $manager->persist($package);
            $manager->flush();
        }
    }

    private function getOwner($name): ?User
    {
        if (null !== $name) {
            /** @noinspection PhpIncompatibleReturnTypeInspection */
            return $this->getReference("user-${name}");
        }

        return null;
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
        return ['test', 'dev'];
    }
}
