<?php
namespace MBH\Bundle\HotelBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;

/**
 * Class HotelData

 */
class HotelData extends AbstractFixture implements OrderedFixtureInterface
{
    const HOTELS = [
        'hotel-one' => [
            'title' => 'Мой отель #1',
            'default' => true
        ],
        'hotel-two' => [
            'title' => 'Мой отель #2',
            'default' => false
        ]
    ];

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $repo = $manager->getRepository('MBHHotelBundle:Hotel');

        if (!count($repo->findAll())) {
            foreach (self::HOTELS as $key => $hotelData) {
                $hotel = new Hotel();
                $hotel
                    ->setFullTitle($hotelData['title'])
                    ->setIsDefault($hotelData['default'])
                ;

                $manager->persist($hotel);
                $manager->flush();

                $this->setReference($key, $hotel);

                if ($this->getEnv() != 'test') {
                    break;
                }
            }
        }
    }

    public function getOrder()
    {
        return -9999;
    }
}
