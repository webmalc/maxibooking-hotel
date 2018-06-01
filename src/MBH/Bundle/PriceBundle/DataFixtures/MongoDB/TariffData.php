<?php
namespace MBH\Bundle\PriceBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\PriceBundle\Document\Tariff;

/**
 * Class TariffData
 * @package MBH\Bundle\PriceBundle\DataFixtures\MongoDB
 */
class TariffData extends AbstractFixture implements OrderedFixtureInterface
{


    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $hotels = $manager->getRepository('MBHHotelBundle:Hotel')->findAll();

        foreach ($hotels as $hotelNumber => $hotel) {
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
            $this->setReference('main-tariff/' . $hotelNumber, $tariff);

            if ($this->getEnv() !== 'prod') {
                $special = new Tariff();
                $special->setFullTitle('Special tariff')
                ->setIsDefault(false)
                ->setIsOnline(true)
                ->setMinPerPrepay(55)
                ->setHotel($hotel);
                $manager->persist($special);

                $manager->flush();
                $this->setReference('special-tariff/' . $hotelNumber, $special);
            }
        }
    }

    public function getOrder()
    {
        return 180;
    }
}
