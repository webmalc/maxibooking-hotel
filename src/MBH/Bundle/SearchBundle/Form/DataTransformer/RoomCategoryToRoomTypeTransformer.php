<?php


namespace MBH\Bundle\SearchBundle\Form\DataTransformer;


use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use Symfony\Component\Form\DataTransformerInterface;

class RoomCategoryToRoomTypeTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        return $value;
    }

    public function reverseTransform($value)
    {
        $roomTypeFromCategories = [];
        $roomTypesNoCategory = [];
        if ($value->count()) {
            foreach ($value as $roomTypeOrCategory) {
                if ($roomTypeOrCategory instanceof RoomTypeCategory) {
                    $roomTypeFromCategories[] = $roomTypeOrCategory->getTypes()->toArray();
                } else {
                    $roomTypesNoCategory[] = $roomTypeOrCategory;
                }
            }
            if (\count($roomTypeFromCategories)) {
                $roomTypeFromCategories = array_merge(...$roomTypeFromCategories);
            }
        }

        $roomTypes = new ArrayCollection(array_merge($roomTypeFromCategories, $roomTypesNoCategory));

        return $roomTypes;
    }

}