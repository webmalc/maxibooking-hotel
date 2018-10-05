<?php

namespace Tests\Bundle\BaseBundle\Lib\Normalization;

use MBH\Bundle\BaseBundle\Lib\Normalization\BooleanFieldType;
use MBH\Bundle\BaseBundle\Lib\Normalization\NormalizationException;
use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;

class BooleanFieldTypeTest extends UnitTestCase
{
    /**
     * @throws NormalizationException
     */
    public function testNormalize()
    {
        $fieldType = new BooleanFieldType();
        $this->assertEquals(true, $fieldType->normalize('true'));
        $this->assertEquals(false, $fieldType->normalize('false'));
        $this->assertEquals(false, $fieldType->normalize(false));
        $this->assertNotEquals(true, $fieldType->normalize('false'));

        $this->expectException(NormalizationException::class);
        $this->expectExceptionMessage('Can not normalize incorrect_type to boolean value');
        $fieldType->normalize('incorrect_type');

    }

    /**
     * @throws NormalizationException
     */
    public function testDenormalize()
    {
        $fieldType = new BooleanFieldType();
        $this->assertEquals(true, $fieldType->denormalize('true'));
        $this->assertEquals(false, $fieldType->denormalize('false'));
        $this->assertEquals(false, $fieldType->denormalize(false));
        $this->assertNotEquals(true, $fieldType->denormalize('false'));

        $this->expectException(NormalizationException::class);
        $this->expectExceptionMessage('Can not denormalize array to boolean value');
        $fieldType->denormalize(['true']);
    }
}