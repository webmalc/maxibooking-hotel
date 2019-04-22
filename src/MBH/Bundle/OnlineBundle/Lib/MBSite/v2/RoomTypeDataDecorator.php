<?php
/**
 * Created by PhpStorm.
 * Date: 18.01.19
 */

namespace MBH\Bundle\OnlineBundle\Lib\MBSite\v2;

use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\OnlineBundle\Lib\MBSite\ImageDataDecorator;

class RoomTypeDataDecorator extends ImageDataDecorator
{
    /**
     * @var RoomType
     */
    private $roomType;

    /**
     * @param RoomType $roomType
     */
    public function setRoomType(RoomType $roomType): self
    {
        $this->roomType = $roomType;

        return $this;
    }

    /**
     * @param bool $isFull
     * @return array
     */
    public function getJsonSerialized($isFull = false): array
    {
        $data = [
            'id' => $this->roomType->getId(),
            'isEnabled' => $this->roomType->getIsEnabled(),
            'title' => $this->roomType->getFullTitle() ? $this->roomType->getFullTitle() : $this->roomType->getInternationalTitle(),
            'internalTitle' => $this->roomType->getTitle(),
            'description' => $this->roomType->getDescription() ?? '',
            'numberOfPlaces' => $this->roomType->getPlaces(),
            'numberOfAdditionalPlaces' => $this->roomType->getAdditionalPlaces(),
            'places' => $this->roomType->getPlaces(),
            'additionalPlaces' => $this->roomType->getAdditionalPlaces()
        ];
        if ($isFull) {

            $facilities = $this->roomType->getFacilities();

            $comprehensiveData = [
                'isSmoking' => $this->roomType->isIsSmoking(),
                'isHostel' => $this->roomType->getIsHostel(),
                'facilities' => [
                    'amount' => count($facilities),
                    'list'  => $facilities
                ],
            ];
            if ($this->roomType->getRoomSpace()) {
                $comprehensiveData['roomSpace'] = $this->roomType->getRoomSpace();
            }

            $photos = $this->getImagesData($this->roomType->getOnlineImages()->toArray());
            $comprehensiveData['photos'] = [
                'amount' => count($photos),
                'list' => $photos
            ];

            $data = array_merge($data, $comprehensiveData);
        }

        return $data;
    }
}
