<?php
namespace MBH\Bundle\PriceBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use MBH\Bundle\PriceBundle\Document\Special;

/**
 * Class SpecialData
 */
class SpecialData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * number of entries for generation
     */
    const NUM_OF_ENTRIES = 3;

    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $hotels = $manager->getRepository('MBHHotelBundle:Hotel')->findAll();
        $begin = new \DateTime('midnight -3 months');
        $end = new \DateTime('midnight +3 months');

        for ($i = 0; $i < self::NUM_OF_ENTRIES; $i++) {
            foreach ($hotels as $hotel) {
                $special = new Special();
                $special->setHotel($hotel)
                    ->setTitle('Test special #' . $i)
                    ->setFullTitle('Test special #' . $i)
                    ->setDescription('Test special #' . $i .  'description')
                    ->setBegin($begin)
                    ->setEnd($end)
                    ->setDiscount(random_int(10, 90))
                    ->setLimit(random_int(1, 20))
                    ->setDisplayFrom($begin)
                    ->setDisplayTo($end)
                ;
                $manager->persist($special);
                $manager->flush();
            }
        }
    }

    public function getOrder()
    {
        return 999;
    }
    
    /**
     * {@inheritDoc}
     */
    protected function getEnvs(): array
    {
        return ['test', 'dev', 'sandbox'];
    }
}
