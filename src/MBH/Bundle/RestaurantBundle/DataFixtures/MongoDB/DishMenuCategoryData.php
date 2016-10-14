<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 28.06.16
 * Time: 14:39
 */

namespace MBH\Bundle\RestaurantBundle\DataFixtures\MongoDB;


use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\RestaurantBundle\Document\DishMenuCategory;

class DishMenuCategoryData implements FixtureInterface
{

    public function load(ObjectManager $manager)
    {
        $hotels = $manager->getRepository('MBHHotelBundle:Hotel')->findAll();

        if ($hotels) {
            foreach ($hotels as $hotel) {

                foreach ($this->getCategories() as $categoryFullTitle) {
                    if ($manager->getRepository('MBHRestaurantBundle:DishMenuCategory')->findOneBy(['fullTitle'=>$categoryFullTitle])) {
                        continue;
                    }

                    $category = new DishMenuCategory();
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
            'Горячие блюда',
            'Напитки',
            'Салаты',
            'Бар',
            'Хлеб'
        ];
    }

}
