<?php

namespace Tests\Bundle\BaseBundle\Lib\Normalization;

use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\BaseBundle\Lib\Normalization\BooleanFieldType;
use MBH\Bundle\BaseBundle\Lib\Normalization\CollectionFieldType;
use MBH\Bundle\BaseBundle\Lib\Normalization\DateTimeFieldType;
use MBH\Bundle\BaseBundle\Lib\Normalization\NormalizationException;
use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;

class CollectionFieldTypeTest extends UnitTestCase
{
    /**
     * @throws NormalizationException
     */
    public function testNormalizeSimpleCollection()
    {
        $fieldType = new CollectionFieldType();
        $this->assertEquals(['1232', 'sdaf'], $fieldType->normalize(['1232', 'sdaf']));
        $this->assertEquals([123, 444], $fieldType->normalize(new ArrayCollection([123, 444])));

        $this->expectException(NormalizationException::class);
        $this->expectExceptionMessage('Passed value is not iterable');
        $fieldType->normalize('string');
    }

    /**
     * @throws NormalizationException
     */
    public function testNormalizeDocumentsCollection()
    {
        $fieldType =  new CollectionFieldType(new BooleanFieldType());
        $this->assertEquals([true, false], $fieldType->normalize(new ArrayCollection(['true', false])));

        $this->expectException(NormalizationException::class);
        $this->expectExceptionMessage('Can not normalize not-bool to boolean value');
        $fieldType->normalize([true, false, 'not-bool']);
    }

    /**
     * @throws NormalizationException
     */
    public function testDenormalizeSimpleCollection()
    {
        $fieldType = new CollectionFieldType();
        $this->assertEquals(['333', 'aaaa'], $fieldType->denormalize(['333', 'aaaa']));
        $this->assertEquals([444, 333], $fieldType->denormalize(new ArrayCollection([444, 333])));

        $this->expectException(NormalizationException::class);
        $fieldType->denormalize('string');
    }

    /**
     * @throws NormalizationException
     */
    public function testDenormalizeDocumentsCollection()
    {
        $fieldType = new CollectionFieldType(new DateTimeFieldType());

        $firstDate = new \DateTime('midnight');
        $secondDate = new \DateTime('midnight + 10 days');

        $expected = [$firstDate, $secondDate];
        $actual = $fieldType->denormalize([$firstDate->format('d.m.Y'), $secondDate->format('d.m.Y')]);
        $this->assertEquals($expected, $actual);
    }
}