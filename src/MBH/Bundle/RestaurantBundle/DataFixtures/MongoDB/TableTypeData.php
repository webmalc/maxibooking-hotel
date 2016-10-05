<?php

namespace MBH\Bundle\RestaurantBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\RestaurantBundle\Document\TableType;

class TableTypeData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $hotels = $manager->getRepository('MBHHotelBundle:Hotel')->findAll();

        if ($hotels) {
            foreach ($hotels as $hotel) {

                foreach ($this->getCategories() as $categoryFullTitle) {
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
            'Основной',
        ];
    }
}