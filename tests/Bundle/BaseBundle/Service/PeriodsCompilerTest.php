<?php

namespace Tests\Bundle\BaseBundle\Service;

use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;
use MBH\Bundle\BaseBundle\Service\PeriodsCompiler;
use MBH\Bundle\PriceBundle\Document\RoomCache;

class PeriodsCompilerTest extends UnitTestCase
{
    /** @var PeriodsCompiler */
    private static $periodsCompiler;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        static::$periodsCompiler = self::getContainerStat()->get('mbh.periods_compiler');
    }

    /**
     * @throws \Exception
     */
    public function testGetPeriodsByDocName()
    {
        $dataByDates = [
            '22.02.1991' => (new RoomCache())->setLeftRooms(10),
            '23.02.1991' => (new RoomCache())->setLeftRooms(10),
            '24.02.1991' => (new RoomCache())->setLeftRooms(10),
            '28.02.1991' => (new RoomCache())->setLeftRooms(11),
            '01.03.1991' => (new RoomCache())->setLeftRooms(12),
            '02.03.1991' => (new RoomCache())->setLeftRooms(12),
            '03.03.1991' => (new RoomCache())->setLeftRooms(11),
            '04.03.1991' => (new RoomCache())->setLeftRooms(12),
        ];

        $begin = \DateTime::createFromFormat('d.m.Y', '18.02.1991');
        $end = \DateTime::createFromFormat('d.m.Y', '10.03.1991');

        $periods = $this->getPeriodsCompiler()->getPeriodsByFieldNames($begin, $end, $dataByDates, ['leftRooms']);
        $expectedPeriods = [
            [
                'begin' => $begin = \DateTime::createFromFormat('d.m.Y', '18.02.1991'),
                'end' => $begin = \DateTime::createFromFormat('d.m.Y', '21.02.1991'),
                'data' => null
            ],
            [
                'begin' => $begin = \DateTime::createFromFormat('d.m.Y', '22.02.1991'),
                'end' => $begin = \DateTime::createFromFormat('d.m.Y', '24.02.1991'),
                'data' => (new RoomCache())->setLeftRooms(10)
            ],
            [
                'begin' => $begin = \DateTime::createFromFormat('d.m.Y', '25.02.1991'),
                'end' => $begin = \DateTime::createFromFormat('d.m.Y', '27.02.1991'),
                'data' => null
            ],
            [
                'begin' => $begin = \DateTime::createFromFormat('d.m.Y', '28.02.1991'),
                'end' => $begin = \DateTime::createFromFormat('d.m.Y', '28.02.1991'),
                'data' => (new RoomCache())->setLeftRooms(11)
            ],
            [
                'begin' => $begin = \DateTime::createFromFormat('d.m.Y', '01.03.1991'),
                'end' => $begin = \DateTime::createFromFormat('d.m.Y', '02.03.1991'),
                'data' => (new RoomCache())->setLeftRooms(12)
            ],
            [
                'begin' => $begin = \DateTime::createFromFormat('d.m.Y', '03.03.1991'),
                'end' => $begin = \DateTime::createFromFormat('d.m.Y', '03.03.1991'),
                'data' => (new RoomCache())->setLeftRooms(11)
            ],
            [
                'begin' => $begin = \DateTime::createFromFormat('d.m.Y', '04.03.1991'),
                'end' => $begin = \DateTime::createFromFormat('d.m.Y', '04.03.1991'),
                'data' => (new RoomCache())->setLeftRooms(12)
            ],
            [
                'begin' => $begin = \DateTime::createFromFormat('d.m.Y', '05.03.1991'),
                'end' => $begin = \DateTime::createFromFormat('d.m.Y', '10.03.1991'),
                'data' => null
            ]
        ];

        $this->assertEquals($expectedPeriods, $periods);
    }

    /**
     * @throws \Exception
     */
    public function testGetPeriodByFieldNames()
    {
        $data = [
            '22.02.1995' => ['fieldName' => 12],
            '23.02.1995' => ['fieldName' => 12],
            '24.02.1995' => ['fieldName' => 13],
            '28.02.1995' => ['fieldName' => 13],
            '01.03.1995' => ['fieldName' => 5],
            '02.03.1995' => ['fieldName' => 5],
            '03.03.1995' => ['fieldName' => 5],
            '04.03.1995' => ['fieldName' => 11],
        ];

        $begin = \DateTime::createFromFormat('d.m.Y', '20.02.1995');
        $end = \DateTime::createFromFormat('d.m.Y', '10.03.1995');

        $periods = $this->getPeriodsCompiler()->getPeriodsByFieldNames($begin, $end, $data, ['fieldName'], 'd.m.Y', true);
        $expectedPeriods = [
            [
                'begin' => $begin = \DateTime::createFromFormat('d.m.Y', '20.02.1995'),
                'end' => $begin = \DateTime::createFromFormat('d.m.Y', '21.02.1995'),
                'data' => null
            ],
            [
                'begin' => $begin = \DateTime::createFromFormat('d.m.Y', '22.02.1995'),
                'end' => $begin = \DateTime::createFromFormat('d.m.Y', '23.02.1995'),
                'data' => ['fieldName' => 12]
            ],
            [
                'begin' => $begin = \DateTime::createFromFormat('d.m.Y', '24.02.1995'),
                'end' => $begin = \DateTime::createFromFormat('d.m.Y', '24.02.1995'),
                'data' => ['fieldName' => 13]
            ],
            [
                'begin' => $begin = \DateTime::createFromFormat('d.m.Y', '25.02.1995'),
                'end' => $begin = \DateTime::createFromFormat('d.m.Y', '27.02.1995'),
                'data' => null
            ],
            [
                'begin' => $begin = \DateTime::createFromFormat('d.m.Y', '28.02.1995'),
                'end' => $begin = \DateTime::createFromFormat('d.m.Y', '28.02.1995'),
                'data' => ['fieldName' => 13]
            ],
            [
                'begin' => $begin = \DateTime::createFromFormat('d.m.Y', '01.03.1995'),
                'end' => $begin = \DateTime::createFromFormat('d.m.Y', '03.03.1995'),
                'data' => ['fieldName' => 5]
            ],
            [
                'begin' => $begin = \DateTime::createFromFormat('d.m.Y', '04.03.1995'),
                'end' => $begin = \DateTime::createFromFormat('d.m.Y', '04.03.1995'),
                'data' => ['fieldName' => 11]
            ],
            [
                'begin' => $begin = \DateTime::createFromFormat('d.m.Y', '05.03.1995'),
                'end' => $begin = \DateTime::createFromFormat('d.m.Y', '10.03.1995'),
                'data' => null
            ]
        ];
        $this->assertEquals($expectedPeriods, $periods);
    }

    public function testCombineIntersectedPeriods()
    {
        $periods = [
            ['begin' => '23.02.2018', 'end' => '25.02.2018'],
            ['begin' => '24.02.2018', 'end' => '26.02.2018'],
            ['begin' => '10.03.2018', 'end' => '15.03.2018'],
            ['begin' => '10.03.2018', 'end' => '13.03.2018'],
            ['begin' => '10.02.2018', 'end' => '15.02.2018'],
        ];
        $periods = $this->convertPeriodDatesFromStringToDateTime($periods);
        $combinedPeriods = $this->getPeriodsCompiler()->combineIntersectedPeriods($periods);

        $expected = $this->convertPeriodDatesFromStringToDateTime([
            ['begin' => '10.02.2018', 'end' => '15.02.2018'],
            ['begin' => '23.02.2018', 'end' => '26.02.2018'],
            ['begin' => '10.03.2018', 'end' => '15.03.2018'],
        ]);
        $this->assertEquals($expected, $combinedPeriods);
    }

    private function convertPeriodDatesFromStringToDateTime(array $periods)
    {
        foreach ($periods as $key => $period) {
            $period['begin'] = \DateTime::createFromFormat('d.m.Y H:i:s', $period['begin'] . ' 00:00:00');
            $period['end'] = \DateTime::createFromFormat('d.m.Y H:i:s', $period['end'] . ' 00:00:00');
            $periods[$key] = $period;
        }

        return $periods;
    }
    
    private function getPeriodsCompiler(): PeriodsCompiler
    {
        return static::$periodsCompiler;
    }
}