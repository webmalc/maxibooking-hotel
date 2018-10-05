<?php

namespace Tests\Bundle\BaseBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;
use MBH\Bundle\BaseBundle\Service\Utils;
use MBH\Bundle\HotelBundle\Document\Hotel;

class UtilsTest extends UnitTestCase
{
    public function testStartsWith()
    {
        $this->assertTrue(Utils::startsWith('some_string_123123', 'some_string'));
        $this->assertFalse(Utils::startsWith('some_string_for_false_condition', 'false_cond'));
    }

    public function testGetFromArrayByKeys()
    {
        $array = [
            'first' => 'first key val',
            'second' => 'second key val',
            'third' => 'third key val',
            'fourth' => 'fourth key val',
            'fifth' => 'fifth key val',
            'sixth' => 'sixth key val',
        ];

        $this->assertEquals(
            ['first' => 'first key val', 'fourth' => 'fourth key val'],
            Utils::getFromArrayByKeys($array, ['first', 'fourth'])
        );
    }

    public function testGetDifferenceInDaysWithSign()
    {
        $this->assertEquals(5, Utils::getDifferenceInDaysWithSign(new \DateTime(), new \DateTime('+5 days')));
        $this->assertEquals(-15, Utils::getDifferenceInDaysWithSign(new \DateTime(), new \DateTime('-15 days')));
    }

    public function testCanBeCastedToBool()
    {
        $this->assertTrue(Utils::canBeCastedToBool(true));
        $this->assertTrue(Utils::canBeCastedToBool(false));
        $this->assertTrue(Utils::canBeCastedToBool('true'));
        $this->assertTrue(Utils::canBeCastedToBool('false'));
        $this->assertFalse(Utils::canBeCastedToBool('not_bool'));
        $this->assertFalse(Utils::canBeCastedToBool(['true']));
        $this->assertFalse(Utils::canBeCastedToBool([true]));
    }

    public function testCanConvertToString()
    {
        $this->assertTrue(Utils::canConvertToString('string'));
        $this->assertTrue(Utils::canConvertToString(123123));
        $this->assertTrue(Utils::canConvertToString(123213.123));
        $this->assertTrue(Utils::canConvertToString(new Hotel()));

        $this->assertFalse(Utils::canConvertToString(['string']));
        $this->assertFalse(Utils::canConvertToString(new \DateTime()));
    }

    public function testGetStringValueOrType()
    {
        $this->assertEquals('string', Utils::getStringValueOrType('string'));
        $this->assertEquals('123123', Utils::getStringValueOrType(123123));
        $this->assertEquals('123213.123', Utils::getStringValueOrType(123213.123));
        $this->assertEquals('title', Utils::getStringValueOrType((new Hotel())->setFullTitle('title')));

        $this->assertEquals('array', Utils::getStringValueOrType(['string']));
        $this->assertEquals('DateTime', Utils::getStringValueOrType(new \DateTime()));
    }

    public function testCastToIterable()
    {
        $this->assertEquals('array', gettype(Utils::castIterableToArray(new ArrayCollection())));
        $this->assertEquals('array', gettype(Utils::castIterableToArray([213])));
        $this->expectException(\InvalidArgumentException::class);
        Utils::castIterableToArray('string text');
    }
}