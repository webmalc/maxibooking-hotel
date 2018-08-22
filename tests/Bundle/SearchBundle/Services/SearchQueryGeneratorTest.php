<?php

namespace Tests\Bundle\SearchBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException;
use MBH\Bundle\SearchBundle\Lib\Result\GroupSearchQuery;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\SearchQueryGenerator;

class SearchQueryGeneratorTest extends WebTestCase
{
    /** @var DocumentManager */
    private $dm;

    public function setUp()
    {
        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
        parent::setUp();
    }

    public function testGenerate()
    {
        $generator = $this->getContainer()->get('mbh_search.search_query_generator');
        $dateBegin = new \DateTime('2018-04-21 midnight');
        $dateEnd = new \DateTime('2018-04-22 midnight');
        $additionalDays = 10;
        $conditions = new SearchConditions();
        $conditions
            ->setBegin($dateBegin)
            ->setEnd($dateEnd)
            ->setAdults(3)
            ->setChildren(4)
            ->setAdditionalBegin($additionalDays);

        $actual = $generator->generate($conditions, false);
        $expectedCount = $this->getAllExpectedVariants($dateBegin, $dateEnd, $additionalDays);

        $this->assertCount($expectedCount, $actual);
        foreach ($actual as $result) {
            $this->assertInstanceOf(SearchQuery::class, $result);
        }

        $actual = $generator->generate($conditions, true);
        foreach ($actual as $result) {
            $this->assertInstanceOf(GroupSearchQuery::class, $result);
        }

    }

    public function testGetTariffIdsOneTariffNoHotel(): void
    {
        $tariff = $this->dm->getRepository(Tariff::class)->findOneBy([]);

        $generator = $this->getContainer()->get('mbh_search.search_query_generator');
        $method = $this->getPrivateMethod(SearchQueryGenerator::class, 'getTariffs');
        $rawTariffIds = Helper::toIds(new ArrayCollection([$tariff]));
        $actual = $method->invokeArgs($generator, [$rawTariffIds, [], true]);

        $this->assertNotEmpty($actual, 'Result is empty!');
        $this->assertEquals($tariff->getId(), $actual[$tariff->getHotel()->getId()][0]['id']);
        $this->assertArrayHasKey('rawTariff', $actual[$tariff->getHotel()->getId()][0]);
        $this->assertEquals($tariff->getId(), (string)$actual[$tariff->getHotel()->getId()][0]['rawTariff']['_id']);
    }

    public function testGetTariffIdsNoTariffNoHotel(): void
    {
        $tariffs = $this->dm->getRepository(Tariff::class)->findAll();
        $generator = $this->getContainer()->get('mbh_search.search_query_generator');
        $method = $this->getPrivateMethod(SearchQueryGenerator::class, 'getTariffs');
        $rawTariffIds = Helper::toIds(new ArrayCollection());
        $actual = $method->invokeArgs($generator, [$rawTariffIds, [], false]);

        $expected = [];
        foreach ($tariffs as $tariff) {
            $expected[$tariff->getHotel()->getId()][] = [];
        }

        $this->assertNotEmpty($actual, 'Result is empty!');
        $this->assertSameSize($expected, $actual);
    }

    public function testGetTariffIdsOneTariffOneHotel(): void
    {
        $tariff = $this->dm->getRepository(Tariff::class)->findOneBy([]);
        $hotelId = $tariff->getHotel()->getId();
        $strangerHotel = $this->dm->createQueryBuilder(Hotel::class)->field('id')->notEqual($hotelId)->limit(
            1
        )->getQuery()->execute()->toArray();
        $generator = $this->getContainer()->get('mbh_search.search_query_generator');
        $method = $this->getPrivateMethod(SearchQueryGenerator::class, 'getTariffs');
        $rawTariffIds = Helper::toIds(new ArrayCollection([$tariff]));
        $actual = $method->invokeArgs(
            $generator,
            [$rawTariffIds, [array_values($strangerHotel)], false]
        );

        $this->assertNotEmpty($actual, 'Result is empty!');
        $this->assertCount(1, $actual);
        $this->assertCount(1, $actual[$tariff->getHotel()->getId()]);
        $this->assertArrayHasKey('id', $actual[$tariff->getHotel()->getId()][0]);
        $this->assertArrayHasKey('rawTariff', $actual[$tariff->getHotel()->getId()][0]);
        $this->assertEquals($tariff->getId(), (string)$actual[$tariff->getHotel()->getId()][0]['rawTariff']['_id']);


    }

    public function testGetTariffIdsNoTariffOneHotel(): void
    {
        $hotel = $this->dm->getRepository(Hotel::class)->findOneBy([]);
        $tariffs = $this->dm->createQueryBuilder(Tariff::class)->field('hotel.id')->equals($hotel->getId())->getQuery(
        )->execute()->toArray();
        $generator = $this->getContainer()->get('mbh_search.search_query_generator');
        $method = $this->getPrivateMethod(SearchQueryGenerator::class, 'getTariffs');
        $rawTariffIds = Helper::toIds(new ArrayCollection());
        $actual = $method->invokeArgs($generator, [$rawTariffIds, [$hotel->getId()], false]);

        $this->assertNotEmpty($actual, 'Result is empty!');
        $this->assertCount(1, $actual);
        $this->assertCount(\count($tariffs), $actual[$hotel->getId()]);
    }


    public function testGetRoomTypeIdsOneRoomTypeNoHotel(): void
    {
        $roomType = $this->dm->getRepository(RoomType::class)->findOneBy([]);
        $generator = $this->getContainer()->get('mbh_search.search_query_generator');
        $method = $this->getPrivateMethod(SearchQueryGenerator::class, 'getRoomTypeIds');
        $roomTypeIds = Helper::toIds(new ArrayCollection([$roomType]));
        $actual = $method->invokeArgs($generator, [$roomTypeIds, []]);
        $expected = [
            $roomType->getHotel()->getId() => [
                $roomType->getId(),
            ],
        ];

        $this->assertNotEmpty($actual, 'Result is empty!');
        $this->assertEquals($expected, $actual);
    }

    public function testGetRoomTypeIdsOneRoomTypeOneHotel(): void
    {
        $roomType = $this->dm->getRepository(RoomType::class)->findOneBy([]);
        $hotel = $this->dm->createQueryBuilder(Hotel::class)->field('id')->notEqual(
            $roomType->getHotel()->getId()
        )->getQuery()->execute()->toArray();
        $hotelIds = Helper::toIds($hotel);
        $roomTypeIds = Helper::toIds(new ArrayCollection([$roomType]));

        $generator = $this->getContainer()->get('mbh_search.search_query_generator');
        $method = $this->getPrivateMethod(SearchQueryGenerator::class, 'getRoomTypeIds');
        $actual = $method->invokeArgs($generator, [$roomTypeIds, [$hotelIds]]);
        $expected = [
            $roomType->getHotel()->getId() => [
                $roomType->getId(),
            ],
        ];
        $this->assertNotEmpty($actual, 'Result is empty!');
        $this->assertEquals($expected, $actual);
    }

    public function testGetRoomTypeIdsNoRoomTypeNoHotel(): void
    {
        $roomTypes = $this->dm->getRepository(RoomType::class)->findAll();
        $generator = $this->getContainer()->get('mbh_search.search_query_generator');
        $method = $this->getPrivateMethod(SearchQueryGenerator::class, 'getRoomTypeIds');
        $actual = $method->invokeArgs($generator, [[], []]);
        $expected = [];
        foreach ($roomTypes as $roomType) {
            $expected[$roomType->getHotel()->getId()][] = $roomType->getId();
        }

        $this->assertNotEmpty($actual, 'Result is empty!');
        $this->assertArraySimilar($expected, $actual);
    }

    public function testGetRoomTypeIdsNoRoomTypeOneHotel(): void
    {
        $hotel = $this->dm->getRepository(Hotel::class)->findOneBy([]);
        $roomTypes = $this->dm->createQueryBuilder(RoomType::class)->field('hotel.id')->equals(
            $hotel->getId()
        )->getQuery()->execute()->toArray();
        $generator = $this->getContainer()->get('mbh_search.search_query_generator');
        $method = $this->getPrivateMethod(SearchQueryGenerator::class, 'getRoomTypeIds');
        $actual = $method->invokeArgs($generator, [[], [$hotel->getId()]]);
        $expected = [];
        foreach ($roomTypes as $roomType) {
            $expected[$hotel->getId()][] = $roomType->getId();
        }

        $this->assertNotEmpty($actual, 'Result is empty!');
        $this->assertArraySimilar($expected, $actual);
    }

    public function testGetRoomTypeIdsNoRoomTypeTwoHotel(): void
    {
        $hotels = $this->dm->getRepository(Hotel::class)->findAll();
        $hotelsIds = Helper::toIds($hotels);
        $roomTypes = $this->dm->createQueryBuilder(RoomType::class)->field('hotel.id')->in($hotelsIds)->getQuery(
        )->execute()->toArray();
        $generator = $this->getContainer()->get('mbh_search.search_query_generator');
        $method = $this->getPrivateMethod(SearchQueryGenerator::class, 'getRoomTypeIds');
        $actual = $method->invokeArgs($generator, [[], $hotelsIds]);
        $expected = [];
        foreach ($roomTypes as $roomType) {
            $expected[$roomType->getHotel()->getId()][] = $roomType->getId();
        }

        $this->assertNotEmpty($actual, 'Result is empty!');
        $this->assertArraySimilar($expected, $actual);
    }

    /**
     * @dataProvider combineFailIdsProvider
     */
    public function testFailCombineTariffWithRoomType($roomTypeIds, $tariffIds): void
    {
        $generator = $this->getContainer()->get('mbh_search.search_query_generator');
        $method = $this->getPrivateMethod(SearchQueryGenerator::class, 'combineTariffWithRoomType');
        $this->expectException(SearchQueryGeneratorException::class);
        $method->invokeArgs($generator, [$roomTypeIds, $tariffIds]);

    }

    private function getPrivateMethod($className, $methodName)
    {
        $reflector = new \ReflectionClass($className);
        $method = $reflector->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }


    public function combineIdsProvider(): array
    {
        return [
            [
                'roomTypeIds' => [
                    'hotelOne' =>
                        [
                            'roomTypeHotel1Id1',
                            'roomTypeHotel1Id2',
                        ],
                    'hotelTwo' =>
                        [
                            'roomTypeHotel2Id1',
                            'roomTypeHotel2Id2',
                        ],
                ],
                'tariffids' => [
                    'hotelOne' =>
                        [
                            ['id' => 'tariffHotel1Id1', 'rawTariff' => ['_id' => 'tariffHotel1Id1']],
                            ['id' => 'tariffHotel1Id2', 'rawTariff' => ['_id' => 'tariffHotel1Id2']],

                        ],
                    'hotelTwo' =>
                        [
                            ['id' => 'tariffHotel2Id1', 'rawTariff' => ['_id' => 'tariffHotel2Id1']],
                            ['id' => 'tariffHotel2Id2', 'rawTariff' => ['_id' => 'tariffHotel2Id2']],
                        ],
                ],
                'expected' => [
                    ['roomTypeId' => 'roomTypeHotel1Id1', 'tariffId' => 'tariffHotel1Id1', 'restrictionTariffId' => 'tariffHotel1Id1', 'tariff' => ['_id' => 'tariffHotel1Id1']],
                    ['roomTypeId' => 'roomTypeHotel1Id1', 'tariffId' => 'tariffHotel1Id2', 'restrictionTariffId' => 'tariffHotel1Id2', 'tariff' => ['_id' => 'tariffHotel1Id2']],
                    ['roomTypeId' => 'roomTypeHotel1Id2', 'tariffId' => 'tariffHotel1Id1', 'restrictionTariffId' => 'tariffHotel1Id1', 'tariff' => ['_id' => 'tariffHotel1Id1']],
                    ['roomTypeId' => 'roomTypeHotel1Id2', 'tariffId' => 'tariffHotel1Id2', 'restrictionTariffId' => 'tariffHotel1Id2', 'tariff' => ['_id' => 'tariffHotel1Id2']],
                    ['roomTypeId' => 'roomTypeHotel2Id1', 'tariffId' => 'tariffHotel2Id1', 'restrictionTariffId' => 'tariffHotel2Id1', 'tariff' => ['_id' => 'tariffHotel2Id1']],
                    ['roomTypeId' => 'roomTypeHotel2Id1', 'tariffId' => 'tariffHotel2Id2', 'restrictionTariffId' => 'tariffHotel2Id2', 'tariff' => ['_id' => 'tariffHotel2Id2']],
                    ['roomTypeId' => 'roomTypeHotel2Id2', 'tariffId' => 'tariffHotel2Id1', 'restrictionTariffId' => 'tariffHotel2Id1', 'tariff' => ['_id' => 'tariffHotel2Id1']],
                    ['roomTypeId' => 'roomTypeHotel2Id2', 'tariffId' => 'tariffHotel2Id2', 'restrictionTariffId' => 'tariffHotel2Id2', 'tariff' => ['_id' => 'tariffHotel2Id2']],
                ],
            ],
        ];
    }

    public function combineFailIdsProvider(): array
    {
        return [
            [
                'roomTypeIds' => [
                    'hotelOne' =>
                        [
                            'roomTypeHotel1Id1',
                            'roomTypeHotel1Id2',
                        ],
                    'hotelTwo' =>
                        [
                            'roomTypeHotel2Id1',
                            'roomTypeHotel2Id2',
                        ],
                ],
                'tariffids' => [
                    'hotelOneThree' =>
                        [
                            'tariffHotel1Id1',
                            'tariffHotel1Id2',
                        ],
                    'hotelTwoFour' =>
                        [
                            'tariffHotel2Id1',
                            'tariffHotel2Id2',
                        ],
                ],
            ],
            [
                'roomTypeIds' => [
                    'hotelOne' =>
                        [
                            'roomTypeHotel1Id1',
                            'roomTypeHotel1Id2',
                        ],
                    'hotelTwo' =>
                        [
                            'roomTypeHotel2Id1',
                            'roomTypeHotel2Id2',
                        ],
                ],
                'tariffids' => [],
            ],
        ];
    }

    public function addingDatesProvider()
    {
        return [
            [

                new \DateTime('01-05-2018 midnight'),
                new \DateTime('09-05-2018 midnight'),
                5,

            ],
            [

                new \DateTime('01-05-2018 midnight'),
                new \DateTime('02-05-2018 midnight'),
                0,

            ],
        ];
    }

    private function getAllExpectedVariants($dateBegin, $dateEnd, $additionalDays): int
    {
        $roomTypeQb = $this->dm->getRepository(RoomType::class)->createQueryBuilder();
        $tariffQb = $this->dm->getRepository(Tariff::class)->createQueryBuilder();
        $hotels = $this->dm->getRepository(Hotel::class)->findAll();
        $expectedCount = 0;
        /** @var Hotel $hotel */
        foreach ($hotels as $hotel) {
            $roomTypesCount = $roomTypeQb->field('hotel.id')->equals($hotel->getId())->getQuery()->count();
            $tariffCount = $tariffQb->field('hotel.id')->equals($hotel->getId())->getQuery()->count();
            $expectedCount += $roomTypesCount * $tariffCount;
        }
        $expectedCount *= $this->calculateAdditionalDays($dateBegin, $dateEnd, $additionalDays);

        return $expectedCount;
    }

    private function calculateAdditionalDays(\DateTime $begin, \DateTime $end, $range): int
    {
        $dates = \count($this->getContainer()->get('mbh_search.additional_days_generator')->generate($begin, $end, $range, $range));

        return $dates;
    }
}