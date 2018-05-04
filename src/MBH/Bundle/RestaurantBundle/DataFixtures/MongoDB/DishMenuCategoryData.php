<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 28.06.16
 * Time: 14:39
 */

namespace MBH\Bundle\RestaurantBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use MBH\Bundle\RestaurantBundle\Document\DishMenuCategory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class DishMenuCategoryData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function doLoad(ObjectManager $manager)
    {
        $hotels = $manager->getRepository('MBHHotelBundle:Hotel')->findAll();

        if ($hotels) {
            foreach ($hotels as $hotel) {

                foreach ($this->getCategories() as $categoryFullTitleId) {
                    $categoryFullTitle = $this->container->get('translator')->trans($categoryFullTitleId);
                    if ($manager->getRepository('MBHRestaurantBundle:DishMenuCategory')->findOneBy(['fullTitle' => $categoryFullTitle])) {
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
            'fixtures.dish_menu_category_data.categories.hot_dishes',
            'fixtures.dish_menu_category_data.categories.beverages',
            'fixtures.dish_menu_category_data.categories.salads',
            'fixtures.dish_menu_category_data.categories.bar',
            'fixtures.dish_menu_category_data.categories.bread'
        ];
    }

    public function getOrder()
    {
        return 9995;
    }

    /**
     * {@inheritDoc}
     */
    protected function getEnvs(): array
    {
        return ['test', 'dev', 'sandbox'];
    }
}
