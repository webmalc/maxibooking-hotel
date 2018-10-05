<?php

namespace Tests\Bundle\BaseBundle\Lib\Normalization;

use MBH\Bundle\BaseBundle\Lib\Normalization\IntegerFieldType;
use MBH\Bundle\BaseBundle\Lib\Normalization\NormalizationException;
use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;

class IntegerFieldTypeTest extends UnitTestCase
{
    /**
     * @throws NormalizationException
     */
    public function testNormalize()
    {
        $fieldType = new IntegerFieldType();
        $this->assertEquals(123, $fieldType->normalize('123.123'));

        $this->expectException(NormalizationException::class);
        $this->expectExceptionMessage('Not int string can not be casted to int type');
        $fieldType->normalize('Not int string');
    }

    /**
     * @throws NormalizationException
     */
    public function testDenormalize()
    {
        $fieldType = new IntegerFieldType();
        $this->assertEquals(444, $fieldType->denormalize('444.1231'));

        $this->expectException(NormalizationException::class);
        $this->expectExceptionMessage('211,123123 can not be casted to int type');
        $fieldType->normalize('211,123123');
    }
}