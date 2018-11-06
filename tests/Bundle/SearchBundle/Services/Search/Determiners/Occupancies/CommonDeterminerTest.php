<?php


namespace Tests\Bundle\SearchBundle\Services\Search\Determiners\Occupancies;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\Exceptions\OccupancyDeterminerException;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies\Occupancy;

class CommonDeterminerTest extends WebTestCase
{
    /** @dataProvider dataProvider */
    public function testDetermine($data)
    {
        $service = $this->getContainer()->get('mbh_search.occupancy_determiner_common');
        $roomType = new RoomType();
        $roomType->setMaxInfants($data['roomType']);

        $tariff = new Tariff();

        $tariff
            ->setInfantAge($data['tariff'][0])
            ->setChildAge($data['tariff'][1]);

        $occupancy = $data['occupancy'];
        [$adults, $children, $childrenAges] = $occupancy;
        $occupancies = new Occupancy($adults, $children, 0, $childrenAges);

        $expected = $data['expected'];
        if ($expected === 'exception') {
            $this->expectException(OccupancyDeterminerException::class);
        }
        $actual = $service->determine($occupancies, $tariff, $roomType);


        $this->assertSame($expected[0], $actual->getAdults());
        $this->assertSame($expected[1], $actual->getChildren());
        $this->assertSame($expected[2], $actual->getInfants());
        $this->assertSame($expected[3], $actual->getChildrenAges());
    }

    public function dataProvider(): iterable
    {

        foreach ([
                     [
                         'occupancy' => [1, 2, [1, 1]],
                         'expected' => [1, 2, 0, [1, 1]],
                         'tariff' => [null, 14],
                         'roomType' => 1
                     ],
                     [
                         'occupancy' => [1, 5, [1, 2, 3, 4, 5]],
                         'expected' => [1, 5, 0, [1, 2, 3, 4, 5]],
                         'tariff' => [null, 14],
                         'roomType' => 1
                     ],
                     [
                         'occupancy' => [1, 5, [1, 2, 3, 4, 5]],
                         'expected' => [1, 3, 2, [1, 2, 4, 4, 5]],
                         'tariff' => [3, 14],
                         'roomType' => 2
                     ],
                     [
                         'occupancy' => [1, 2, [2, 2]],
                         'expected' => [1, 1, 1, [2, 3]],
                         'tariff' => [2, 14],
                         'roomType' => 1
                     ],
                     [
                         'occupancy' => [1, 0, []],
                         'expected' => [1, 0, 0, []],
                         'tariff' => [2, 14],
                         'roomType' => 2
                     ],
                     [
                         'occupancy' => [1, 1, [0]],
                         'expected' => [1, 0, 1, [0]],
                         'tariff' => [2, 14],
                         'roomType' => 2
                     ],
                     [
                         'occupancy' => [1, 2, [1, 13]],
                         'expected' => [1, 1, 1, [1, 13]],
                         'tariff' => [2, 14],
                         'roomType' => 2
                     ],
                     [
                         'occupancy' => [1, 2, [6, 16]],
                         'expected' => [2, 1, 0, [6, 16]],
                         'tariff' => [2, 14],
                         'roomType' => 2
                     ],
                     [
                         'occupancy' => [1, 2, [6]],
                         'expected' => 'exception',
                         'tariff' => [2, 14],
                         'roomType' => 2
                     ]


                 ] as $data) {
            yield [$data];
        }


    }
}