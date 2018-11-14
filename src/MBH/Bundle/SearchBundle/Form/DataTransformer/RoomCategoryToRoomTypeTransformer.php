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
        $result = [];
        if ($value->count()) {
            foreach ($value as $roomTypeOrCategory) {
                if ($roomTypeOrCategory instanceof RoomTypeCategory) {
                    $result[] = $roomTypeOrCategory->getTypes()->toArray();
                }
            }
            $result = array_merge(...$result);
        }

        return new ArrayCollection($result);
    }

}