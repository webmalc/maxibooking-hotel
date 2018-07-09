<?php

namespace Tests\Bundle\BaseBundle\Service;

use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;
use MBH\Bundle\BaseBundle\Service\PeriodsCompiler;
use MBH\Bundle\PriceBundle\Document\RoomCache;

class PeriodsCompilerTest extends UnitTestCase
{
    /** @var PeriodsCompiler */
    static $periodsCompiler;

    public static function setUpBeforeClass()
    {
        self::$periodsCompiler = self::getContainerStat()->get('mbh.periods_compiler');
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

        $periods = self::$periodsCompiler->getPeriodsByFieldNames($begin, $end, $dataByDates, ['leftRooms']);
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

        $periods = self::$periodsCompiler->getPeriodsByFieldNames($begin, $end, $data, ['fieldName'], 'd.m.Y', true);
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
}