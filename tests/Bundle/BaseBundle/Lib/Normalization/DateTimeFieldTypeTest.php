<?php

namespace Tests\Bundle\BaseBundle\Lib\Normalization;

use MBH\Bundle\BaseBundle\Lib\Normalization\DateTimeFieldType;
use MBH\Bundle\BaseBundle\Lib\Normalization\NormalizationException;
use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;

class DateTimeFieldTypeTest extends UnitTestCase
{
    /**
     * @throws \MBH\Bundle\BaseBundle\Lib\Normalization\NormalizationException
     */
    public function testNormalize()
    {
        $fieldType = new DateTimeFieldType();
        $this->assertEquals('22.02.1345', $fieldType->normalize(\DateTime::createFromFormat('d.m.Y', '22.02.1345')));

        $this->expectException(NormalizationException::class);
        $this->expectExceptionMessage('Can not normalize Hotel 1 because it\'s not an instance of DateTime');
        $fieldType->normalize((new Hotel())->setFullTitle('Hotel 1'));
    }

    public function testDenormalize()
    {
        $format = 'd.m.Y H:i';
        $fieldType = new DateTimeFieldType($format);
        $this->assertEquals(\DateTime::createFromFormat($format, '31.01.1999 23:45'), $fieldType->denormalize('31.01.1999 23:45'));
    }

    /**
     * @throws NormalizationException
     */
    public function testDenormalizeByDateFormat()
    {
        $fieldType = new DateTimeFieldType();
        $this->assertEquals(new \DateTime('midnight'), $fieldType->denormalize((new \DateTime())->format('d.m.Y')));

        $this->expectException(NormalizationException::class);
        $this->expectExceptionMessage('Can not denormalize 04.05.1982 23:43 to datetime by format "d.m.Y"');
        $fieldType->denormalize('04.05.1982 23:43');
    }
}