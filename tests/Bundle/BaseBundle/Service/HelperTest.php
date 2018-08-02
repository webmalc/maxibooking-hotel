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


    /**
     * @throws \Exception
     */
    public function testGetDatePeriodsGeneratorWithBeginDateMoreThanEnd()
    {
        $this->expectException(\Exception::class);
        $this->helper
            ->getDatePeriodsGenerator(new \DateTime('midnight'), new \DateTime('midnight -10 days'), 100)
            ->current();
    }

    /**
     * @throws \Exception
     */
    public function testGetDatePeriodsGeneratorWithMinPeriodMoreThanPassedPeriod()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->helper
            ->getDatePeriodsGenerator(new \DateTime('midnight'), new \DateTime('midnight +5 days'), 100, 6)
            ->current();
    }

    /**
     * @throws \Exception
     */
    public function testGetDatePeriodsGenerator()
    {
        $oneDayInterval = new \DateInterval('P1D');
        $periodGenerator =$this->helper
            ->getDatePeriodsGenerator(new \DateTime('midnight'), new \DateTime('midnight +5 days'), 5);
        $periods = iterator_to_array($periodGenerator);
        $expectedFirstPeriod = new \DatePeriod(new \DateTime('midnight'), $oneDayInterval, new \DateTime('midnight +1 day'));
        $this->assertEquals($expectedFirstPeriod, $periods[0]);

        $expectedSecondPeriod = new \DatePeriod(new \DateTime('midnight'), $oneDayInterval, new \DateTime('midnight +2 day'));
        $this->assertEquals($expectedSecondPeriod, $periods[1]);

        $expectedLastPeriod = new \DatePeriod(new \DateTime('midnight +4 days'), $oneDayInterval, new \DateTime('midnight +5 day'));
        $actualLastPeriod = $periods[count($periods) - 1];
        $this->assertEquals($expectedLastPeriod, $actualLastPeriod);
    }
}