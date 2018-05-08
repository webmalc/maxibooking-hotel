<?php

namespace MBH\Bundle\RestaurantBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use MBH\Bundle\RestaurantBundle\Document\TableType;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class TableTypeData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function doLoad(ObjectManager $manager)
    {
        $hotels = $manager->getRepository('MBHHotelBundle:Hotel')->findAll();

        if ($hotels) {
            foreach ($hotels as $hotel) {

                foreach ($this->getCategories() as $categoryFullTitleId) {
                    $categoryFullTitle = $this->container->get('translator')->trans($categoryFullTitleId);
                    if ($manager->getRepository('MBHRestaurantBundle:TableType')->findOneBy(['fullTitle'=>$categoryFullTitle])) {
                        continue;
                    }

                    $category = new TableType();
                    $category
                        ->setHotel($hotel)
                        ->setFullTitle($categoryFullTitle);

                    $manager->persist($category);
                    $manager->flush();
                }
            }
        }
    }

    private function getCategories(): array
    {
        return [
            'fixtures.table_type_data.categories.main',
        ];
    }

    public function getOrder()
    {
        return 9996;
    }

    /**
     * {@inheritDoc}
     */
    protected function getEnvs(): array
    {
        return ['test', 'dev', 'sandbox'];
    }
}