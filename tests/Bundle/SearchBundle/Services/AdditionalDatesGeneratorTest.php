<?php


use Liip\FunctionalTestBundle\Test\WebTestCase;

class AdditionalDatesGeneratorTest extends WebTestCase
{


    /**
     * @param DateTime $begin
     * @param DateTime $end
     * @param int|null $rangeBegin
     * @param int|null $rangeEnd
     * @param int $countExpected
     * @param array $dataExpected
     * @dataProvider datesProvider
     */
    public function testGenerate(
        DateTime $begin,
        DateTime $end,
        ?int $rangeBegin = null,
        ?int $rangeEnd = null,
        int $countExpected,
        array $dataExpected
    ): void {
        $generator = $this->getContainer()->get('mbh_search.additional_days_generator');
        $actual = $generator->generate($begin, $end, $rangeBegin, $rangeEnd, [], []);

        $this->assertCount($countExpected, $actual);
        $this->assertEquals($dataExpected, $actual, 'The array of dates is wrong');

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
        ];
    }
}