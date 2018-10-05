<?php

namespace Tests\Bundle\BaseBundle\Lib\Normalization;

use MBH\Bundle\BaseBundle\Lib\Normalization\FloatFieldType;
use MBH\Bundle\BaseBundle\Lib\Normalization\NormalizationException;
use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;

class FloatFieldTypeTest extends UnitTestCase
{
    public function testNormalize()
    {
        $fieldType = new FloatFieldType(3);
        $this->assertEquals(541.132, $fieldType->normalize('541.13234123'));
        $this->assertEquals(1234.000, $fieldType->normalize('1234'));

        $this->expectException(NormalizationException::class);
        $this->expectExceptionMessage('123 incorrect num can not be casted to float type');
        $fieldType->normalize('123 incorrect num');
    }

    public function testDenormalize()
    {
        $fieldType = new FloatFieldType(1);
        $this->assertEquals(1111.0, $fieldType->denormalize('1111'));
        $this->assertEquals(123.1, $fieldType->normalize('123.13234123'));

        $this->expectException(NormalizationException::class);
        $this->expectExceptionMessage('123,123 can not be casted to float type');
        $fieldType->normalize('123,123');
    }
}