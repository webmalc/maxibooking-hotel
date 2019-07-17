<?php

namespace Tests\Bundle\SearchBundle\Services;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use MBH\Bundle\SearchBundle\Services\AdditionalDatesGenerator;

class AdditionalDatesGeneratorTest extends WebTestCase
{


    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param int|null $rangeBegin
     * @param int|null $rangeEnd
     * @param int $countExpected
     * @param array $dataExpected
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConfigException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException
     * @dataProvider datesProvider
     */
    public function testGenerate(
        \DateTime $begin,
        \DateTime $end,
        ?int $rangeBegin = null,
        ?int $rangeEnd = null,
        int $countExpected,
        array $dataExpected
    ): void {

        $generator = $this->getContainer()->get('mbh_search.additional_days_generator');

        $actual = $generator->generate($begin, $end, $rangeBegin, $rangeEnd);

        $this->assertCount($countExpected, $actual);
        if (\count($dataExpected)) {
            $this->assertEquals($dataExpected, $actual, 'The array of dates is wrong');
        }


    }

    public function datesProvider(): array
    {
        return [
            [
                new \DateTime('21.04.2018 midnight'),
                new \DateTime('22.04.2018 midnight'),
                0,
                null,
                1,
                [
                    '21-04-2018_22-04-2018' => [
                        'begin' => new \DateTime('21.04.2018 midnight'),
                        'end' => new \DateTime('22.04.2018 midnight'),
                    ],
                ],
            ],
            [
                new \DateTime('10.01.2018 midnight'),
                new \DateTime('15.01.2018 midnight'),
                1,
                1,
                9,
                [],
            ]
        ];
    }
}