<?php

namespace Tests\Bundle\BaseBundle\Lib\Normalization;

use MBH\Bundle\BaseBundle\Lib\Normalization\NormalizationException;
use MBH\Bundle\BaseBundle\Lib\Normalization\StringFieldType;
use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;

class StringFieldTypeTest extends UnitTestCase
{
    /**
     * @throws \MBH\Bundle\BaseBundle\Lib\Normalization\NormalizationException
     */
    public function testNormalize()
    {
        $fieldType = new StringFieldType();
        $this->assertEquals('sdfsdf', $fieldType->normalize('sdfsdf'));
        $this->assertEquals('fulltitle', (new Hotel())->setFullTitle('fulltitle'));

        $this->expectExceptionMessage('array can not be casted to string');
        $this->expectException(NormalizationException::class);
        $fieldType->normalize(['sdf']);
    }

    /**
     * @throws NormalizationException
     */
    public function testDenormalize()
    {
        $fieldType = new StringFieldType();

        $this->assertEquals('12333', $fieldType->denormalize('12333'));
        $this->assertEquals('title', (new RoomType())->setTitle('title'));

        $this->expectExceptionMessage('DateTime can not be casted to string');
        $this->expectException(NormalizationException::class);
        $fieldType->normalize(new \DateTime());
    }
}