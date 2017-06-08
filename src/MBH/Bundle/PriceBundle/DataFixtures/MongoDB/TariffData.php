<?php
namespace MBH\Bundle\PriceBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class PriceData

 */
class TariffData extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $hotels = $manager->getRepository('MBHHotelBundle:Hotel')->findAll();

        foreach ($hotels as $hotel) {
            $baseTariff = $manager->getRepository('MBHPriceBundle:Tariff')->fetchBaseTariff($hotel);

            if ($baseTariff) {
                continue;
            }

            $tariff = new Tariff();
            $tariff->setFullTitle($this->container->get('translator')->trans('price.datafixtures.mongodb.servicedata.default_tariff'))
                ->setIsDefault(true)
                ->setIsOnline(true)
                ->setMinPerPrepay(25)
                ->setHotel($hotel);
            $manager->persist($tariff);
            
            $manager->flush();
            $this->setReference('main-tariff', $tariff);

            if ($this->getEnv() == 'test') {
                $special = new Tariff();
                $special->setFullTitle('Special tariff')
                ->setIsDefault(false)
                ->setIsOnline(true)
                ->setMinPerPrepay(55)
                ->setHotel($hotel);
                $manager->persist($special);

                $manager->flush();
                $this->setReference('special-tariff', $special);
            }
        }
    }

    public function getOrder()
    {
        return 3;
    }
}
