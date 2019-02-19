<?php
namespace MBH\Bundle\PriceBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Document\TariffChildOptions;

/**
 * Class TariffData
 * @package MBH\Bundle\PriceBundle\DataFixtures\MongoDB
 */
class AdditionalTariffData extends AbstractFixture implements OrderedFixtureInterface
{

    public const DOWN_TARIFF_NAME = 'DownTariff';
    public const UP_TARIFF_NAME = 'UpTariff';
    public const CHILD_UP_TARIFF_NAME = 'ChildUpTariff';

    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $hotels = $manager->getRepository('MBHHotelBundle:Hotel')->findAll();

        foreach ($hotels as $hotelNumber => $hotel) {

            if ($this->getEnv() !== 'prod') {

                $downTariff = new Tariff();
                $downTariff->setFullTitle(self::DOWN_TARIFF_NAME)
                    ->setIsDefault(false)
                    ->setIsOnline(true)
                    ->setHotel($hotel);
                $manager->persist($downTariff);

                $manager->flush();
                $this->setReference(self::DOWN_TARIFF_NAME.'-tariff/' . $hotelNumber, $downTariff);


                $upTariff = new Tariff();
                $upTariff->setFullTitle(self::UP_TARIFF_NAME)
                ->setIsDefault(false)
                ->setIsOnline(true)
                ->setMinPerPrepay(55)
                ->setMergingTariff($downTariff)
                ->setHotel($hotel);
                $manager->persist($upTariff);

                $manager->flush();
                $this->setReference(self::UP_TARIFF_NAME.'-tariff/' . $hotelNumber, $upTariff);


                $childOptions = new TariffChildOptions();
                $childUpTariff = new Tariff();
                $childUpTariff->setFullTitle(self::CHILD_UP_TARIFF_NAME)
                    ->setIsDefault(false)
                    ->setIsOnline(true)
                    ->setParent($upTariff)
                    ->setChildOptions($childOptions)
                    ->setHotel($hotel);

                $manager->persist($childUpTariff);

                $manager->flush();
                $this->setReference(self::CHILD_UP_TARIFF_NAME.'-tariff/' . $hotelNumber, $upTariff);
            }
        }
    }

    public function getOrder()
    {
        return 190;
    }

    /**
     * get environments for fixture
     *
     * @return array
     */
    protected function getEnvs(): array
    {
        return ['test', 'dev'];
    }
}
