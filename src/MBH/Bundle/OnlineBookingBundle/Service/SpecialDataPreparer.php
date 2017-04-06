<?php

namespace MBH\Bundle\OnlineBookingBundle\Service;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Document\Image;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeImage;
use MBH\Bundle\PriceBundle\Document\Special;

class SpecialDataPreparer
{

    const NO_IMAGE_PATH = 'noimage.png';

    /** @var  DocumentManager */
    private $dm;

    /**
     * SpecialDataPreparer constructor.
     * @param DocumentManager $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function getPreparedData(array $specials): array
    {
        $result = [];
        foreach ($specials as $special) {
            if (!$special instanceof Special) {
                continue;
            }
            $this->mustHaveRoomTypes($special);
            $result = array_merge($this->prepareDataToTwig($special));
        }

        return $result;
    }

    private function mustHaveRoomTypes(Special $special): void
    {
        if (count($special->getRoomTypes()) == 0) {
            $hotel = $special->getHotel();
            $roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')->fetch($hotel)->toArray();
            foreach ($roomTypes as $roomType) {
                $special->addRoomType($roomType);
            }
        }
    }

    private function prepareDataToTwig(Special $special): array
    {
        $result = [];
        foreach ($special->getRoomTypes() as $roomType) {
            /** @var RoomType $roomType */
            $result[] = [
                'image' => $this->getImage($roomType),
                'hotelName' => $roomType->getHotel()->getName(),
                'roomTypeName' => $roomType->getName(),
                'eat' => '',
                'dates' => [
                    'begin' => $special->getBegin(),
                    'end' => $special->getEnd(),
                    'days' => $special->getDays(),
                    'nights' => $special->getNights(),
                ],
                'discount' => $special->getDiscount(),
                'prices' => $special->getPrices()
            ];
        }

        return $result;
    }

    private function getImage(RoomType $roomType): ?string
    {
        $roomImage = $roomType->getMainImage()??$roomType->getImages()->first()??null;
        $hotelImage = $roomType->getHotel()->getImages()->first()??null;
        $image = $roomImage??$hotelImage;
        if ($image instanceof Image || $image instanceof RoomTypeImage) {
            $result = $image->getPath();
        } else {
            $result = self::NO_IMAGE_PATH;
        }

        return $result;
    }

}