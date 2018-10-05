<?php

namespace Tests\Bundle\BaseBundle\Lib\Normalization;

use MBH\Bundle\BaseBundle\Lib\Normalization\CustomFieldType;
use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;

class CustomFieldTypeTest extends UnitTestCase
{
    /**
     * @throws \MBH\Bundle\BaseBundle\Lib\Exception
     */
    public function testNormalize()
    {
        $fieldType = new CustomFieldType(function (RoomType $roomType) {
            return [
                'fullTitle' => $roomType->getFullTitle(),
                'places' => $roomType->getPlaces(),
                'hotel' => [
                    'fullTitle' => $roomType->getHotel()->getFullTitle()
                ]
            ];
        });

        $roomType = (new RoomType())
            ->setFullTitle('fullTitle')
            ->setPlaces(123)
            ->setHotel((new Hotel())->setFullTitle('hotel fulltitle'));

        $expected = [
            'fullTitle' => 'fullTitle',
            'places' => 123,
            'hotel' => [
                'fullTitle' => 'hotel fulltitle'
            ]
        ];

        $this->assertEquals($expected, $fieldType->normalize($roomType));
    }

    /**
     * @throws \MBH\Bundle\BaseBundle\Lib\Exception
     */
    public function testDenormalize()
    {
        $fieldType = new CustomFieldType(null, function(array $tariffData) {
            return (new Tariff())
                ->setFullTitle($tariffData['fullTitle'])
                ->setUpdatedBy($tariffData['updatedBy'])
                ->setInfantAge($tariffData['infantAge']);
        });
        $expected = (new Tariff())
            ->setFullTitle('fulltitle')
            ->setInfantAge(45)
            ->setUpdatedBy('general alladin');

        $tariffData = [
            'fullTitle' => 'fulltitle',
            'updatedBy' => 'general alladin',
            'infantAge' => 45
        ];

        $this->assertEquals($expected, $fieldType->denormalize($tariffData));
    }
}