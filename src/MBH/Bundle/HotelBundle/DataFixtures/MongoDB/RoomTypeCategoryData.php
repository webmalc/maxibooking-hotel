<?php

namespace MBH\Bundle\HotelBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;

/**
 * Class TaskData
 */
class RoomTypeCategoryData extends AbstractFixture implements OrderedFixtureInterface
{
    public const CATEGORY_ONE = [
        'fullTitle' => 'CategoryOne',
        'isChildPrice' => false,
        'isIndividualAddPrice' => false
    ];

    public const NO_ADDITIONAL_PLACES_CATEGORY = [
        'fullTitle' => 'NoAdditionalPlacesCategoryOne',
        'isChildPrice' => false,
        'isIndividualAddPrice' => false
    ];
    public const ADDITIONAL_PLACES_CATEGORY = [
        'fullTitle' => 'AdditionalPlacesCategory',
        'isChildPrice' => false,
        'isIndividualAddPrice' => true
    ];

    public const CATEGORIES = [
        self::CATEGORY_ONE,
        self::NO_ADDITIONAL_PLACES_CATEGORY,
        self::ADDITIONAL_PLACES_CATEGORY
    ];

    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $hotels = $manager->getRepository('MBHHotelBundle:Hotel')->findAll();

        foreach ($hotels as $hotelNumber => $hotel) {
            foreach (self::CATEGORIES as $catgoryData) {
                $category = new RoomTypeCategory();
                $category
                    ->setHotel($hotel)
                    ->setFullTitle($catgoryData['fullTitle']);
                $category
                    ->setIsChildPrices($catgoryData['isChildPrice'])
                    ->setIsIndividualAdditionalPrices($catgoryData['isIndividualAddPrice']);

                $manager->persist($category);
                $manager->flush();

                $roomCategoryReference = $catgoryData['fullTitle'] . '/' . $hotelNumber;
                $this->setReference($roomCategoryReference, $category);
            }
        }
    }

    public function getEnvs(): array
    {
        return ['test', 'dev'];
    }

    public function getOrder()
    {
        return 190;
    }
}