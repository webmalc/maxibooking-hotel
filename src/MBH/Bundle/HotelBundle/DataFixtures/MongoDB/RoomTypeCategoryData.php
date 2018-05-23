<?php
namespace MBH\Bundle\HotelBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;

/**
 * Class TaskData
 */
class RoomTypeCategoryData extends AbstractFixture implements OrderedFixtureInterface
{
    public const TWO_ONLY_ROOM_TYPE = 'zero';
    /**
     * Get roomType data
     *
     * @return array
     */
    public function data()
    {
        return [
            'categoryOne' => [
                'title' => 'CategoryOne',
                'isChildPrice' => false,
                'isIndividualAddPrice' => false
            ],
            'categoryTwo' => [
                'title' => 'categoryTwo',
                'isChildPrice' => true,
                'isIndividualAddPrice' => false
            ],
            'categoryThree' => [
                'title' => 'categoryThree',
                'isChildPrice' => false,
                'isIndividualAddPrice' => true
            ],

        ];
    }

    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $hotels = $manager->getRepository('MBHHotelBundle:Hotel')->findAll();

        foreach ($hotels as $hotelNumber => $hotel) {
            foreach ($this->data() as $key => $data) {
                $category = new RoomTypeCategory();
                $category
                    ->setHotel($hotel)
                    ->setFullTitle($data['title'])
                ;
                $category
                    ->setIsChildPrices($data['isChildPrice'])
                    ->setIsIndividualAdditionalPrices($data['isIndividualAddPrice']);

                $manager->persist($category);
                $manager->flush();

                $roomCategoryReference = $key . '/' . $hotelNumber;
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