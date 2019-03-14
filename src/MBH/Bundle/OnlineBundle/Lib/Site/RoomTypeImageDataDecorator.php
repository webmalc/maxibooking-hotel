<?php
/**
 * Created by PhpStorm.
 * Date: 18.01.19
 */

namespace MBH\Bundle\OnlineBundle\Lib\Site;

use MBH\Bundle\HotelBundle\Document\RoomType;

class RoomTypeImageDataDecorator extends ImageDataDecorator
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
            'hotel' => $this->roomType->getHotel()->getId(),
            'title' => $this->roomType->getFullTitle() ? $this->roomType->getFullTitle() : $this->roomType->getInternationalTitle(),
            'internalTitle' => $this->roomType->getTitle(),
            'description' => $this->roomType->getDescription() ?? '',
            'numberOfPlaces' => $this->roomType->getPlaces(),
            'numberOfAdditionalPlaces' => $this->roomType->getAdditionalPlaces(),
            'places' => $this->roomType->getPlaces(),
            'additionalPlaces' => $this->roomType->getAdditionalPlaces()
        ];
        if ($isFull) {
            $comprehensiveData = [
                'isSmoking' => $this->roomType->isIsSmoking(),
                'isHostel' => $this->roomType->getIsHostel(),
                'facilities' => $this->roomType->getFacilities(),
            ];
            if ($this->roomType->getRoomSpace()) {
                $comprehensiveData['roomSpace'] = $this->roomType->getRoomSpace();
            }
            if ($this->uploaderHelper !== null) {
                $comprehensiveData['photos'] = $this->getImagesData($this->roomType->getOnlineImages()->toArray());
            }
            $data = array_merge($data, $comprehensiveData);
        }

        return $data;
    }
}