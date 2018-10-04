<?php

namespace Tests\Bundle\BaseBundle\Service;

use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;
use MBH\Bundle\BaseBundle\Service\Utils;

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
}