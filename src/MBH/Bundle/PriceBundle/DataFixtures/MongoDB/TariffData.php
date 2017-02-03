<?php
namespace MBH\Bundle\PriceBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class PriceData

 */
class TariffData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $hotels = $manager->getRepository('MBHHotelBundle:Hotel')->findAll();

        foreach ($hotels as $hotel) {
            $baseTariff = $manager->getRepository('MBHPriceBundle:Tariff')->fetchBaseTariff($hotel);

            if ($baseTariff) {
                return $baseTariff;
            }

            $tariff = new Tariff();
            $tariff->setFullTitle('Основной тариф')
                ->setIsDefault(true)
                ->setIsOnline(true)
                ->setHotel($hotel);
            $manager->persist($tariff);
            $manager->flush();
        }
    }

    public function getOrder()
    {
        return 3;
    }
}