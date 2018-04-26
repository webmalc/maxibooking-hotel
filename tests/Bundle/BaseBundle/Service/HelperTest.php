<?php

namespace Tests\Bundle\BaseBundle\Service;

use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;
use MBH\Bundle\BaseBundle\Service\Helper;

class HelperTest extends UnitTestCase
{
    /** @var Helper */
    private $helper;

    public function setUp()
    {
        parent::setUp();

        self::bootKernel();
        $this->helper = (self::getContainerStat())->get('mbh.helper');
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
            $this->helper->getFromArrayByKeys($array, ['first', 'fourth'])
        );
    }
}