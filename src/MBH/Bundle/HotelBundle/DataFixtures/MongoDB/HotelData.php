<?php

namespace MBH\Bundle\HotelBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;

/**
 * Class HotelData
 */
class HotelData extends AbstractFixture implements OrderedFixtureInterface
{
    public const HOTELS_DATA_KEY_ONE = 'hotel-one';
    public const HOTELS_DATA_KEY_TWO = 'hotel-two';

    /**
     * Get hotel data
     *
     * @return array
     */
    const HOTELS_DATA = [
         self::HOTELS_DATA_KEY_ONE => [
            'title' => 'mbhhotelbundle.hotelData.hotelOne',
            'default' => true
        ],
        self::HOTELS_DATA_KEY_TWO => [
            'title' => 'mbhhotelbundle.hotelData.hotelTwo',
            'default' => false
        ]
    ];

    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $repo = $manager->getRepository('MBHHotelBundle:Hotel');

        if (!count($repo->findAll())) {
            foreach (self::HOTELS_DATA as $key => $hotelData) {
                $hotel = new Hotel();
                $hotel
                    ->setFullTitle($this->container->get('translator')->trans($hotelData['title']))
                    ->setIsDefault($hotelData['default']);

                $manager->persist($hotel);
                $manager->flush();

                $this->setReference($key, $hotel);
            }
        }
    }

    public function getOrder()
    {
        return -9999;
    }

    public function getEnvs(): array
    {
        return ['test', 'dev', 'sandbox'];
    }
}
