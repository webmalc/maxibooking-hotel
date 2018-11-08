<?php


namespace Tests\Bundle\SearchBundle\Services\Data;


use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Document\ClientConfigRepository;
use MBH\Bundle\HotelBundle\DataFixtures\MongoDB\AdditionalRoomTypeData;
use MBH\Bundle\HotelBundle\DataFixtures\MongoDB\RoomTypeCategoryData;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use MBH\Bundle\PriceBundle\DataFixtures\MongoDB\AdditionalTariffData;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\Data\PriceCacheFetchQuery;
use MBH\Bundle\SearchBundle\Services\Calc\CalcQuery;
use Tests\Bundle\SearchBundle\NamesLibrary;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class PriceCacheFetcherTest extends SearchWebTestCase
{

    /**
     * @param $data
     * @dataProvider priceCachesDataProvider
     */
    public function testFetchNecessaryDataSet($data): void
    {


        $searchQuery = $this->createSearchQuery($data);
        $searchQuery->setAdults(1);

        if ($data['isCategory']) {
            $clientConfigRepo = $this->createMock(ClientConfigRepository::class);
            $clientConfig = $this->createMock(ClientConfig::class);
            $clientConfig->expects($this->any())->method('getUseRoomTypeCategory')->willReturn(true);
            $clientConfigRepo->expects($this->any())->method('fetchConfig')->willReturn($clientConfig);
            $this->getContainer()->set('mbh_search.client_config_repository', $clientConfigRepo);
        }

        $conditions = $searchQuery->getSearchConditions();
        $calcQuery = new CalcQuery();
        $tariff = $this->dm->find(Tariff::class, $searchQuery->getTariffId());
        $roomType = $this->dm->find(RoomType::class, $searchQuery->getRoomTypeId());
        $isCategory = $data['isCategory'];
        $conditionRoomType = new ArrayCollection();
        $isCategory ? $conditionRoomType->add($roomType->getCategory()) : $conditionRoomType->add($roomType);
        $occupancies = $this->getContainer()->get('mbh_search.occupancy_determiner')->determine($searchQuery);

        $calcQuery
            ->setSearchBegin($searchQuery->getBegin())
            ->setSearchEnd($searchQuery->getEnd())
            ->setActualAdults($occupancies->getAdults())
            ->setActualChildren($occupancies->getChildren())
            ->setIsUseCategory($isCategory)
            ->setTariff($tariff)
            ->setRoomType($roomType)
            ->setConditionHash($searchQuery->getSearchHash())
            ->setConditionMaxBegin($conditions->getMaxBegin())
            ->setConditionMaxEnd($conditions->getMaxEnd())
            //** TODO: Уточнить по поводу Promotion */
            /*->setPromotion()*/
        ;


        $fetchQuery = PriceCacheFetchQuery::createInstanceFromCalcQuery($calcQuery);
        $actual = $this->getContainer()->get('mbh_search.price_cache_fetcher')->fetchNecessaryDataSet($fetchQuery);

        $expected = $data['expected'];
        $expectedCount = $expected['count'];
        $expectedRoomTypeName = $expected['RoomType'];
        $expectedTariffName = $expected['TariffName'];
        $expectedCategoryName = $expected['RoomTypeCategory'];

        if (null !== $expectedRoomTypeName) {
            $roomTypeId = $this->dm->getRepository(RoomType::class)->findOneBy(['fullTitle' => $expectedRoomTypeName])->getId();
        } else {
            $roomTypeId = null;
        }

        if (null !== $expectedCategoryName) {
            $categoryId = $this->dm->getRepository(RoomTypeCategory::class)->findOneBy(['fullTitle' => $expectedCategoryName])->getId();
        } else {
            $categoryId = null;
        }

        $this->assertCount($expectedCount, $actual);

        if ($roomTypeId) {
            $actualRoomTypeArray = array_map('\strval', array_column(array_column($actual, 'roomType'), '$id'));
            $diff = array_diff([$roomTypeId], $actualRoomTypeArray);
            $this->assertCount(0, $diff);
        }

        if ($categoryId) {
            $actualCategoryArray = array_map('\strval', array_column(array_column($actual, 'roomTypeCategory'), '$id'));
            $diff = array_diff([$categoryId], $actualCategoryArray);
            $this->assertCount(0, $diff);
        }

        $tariffId = $this->dm->getRepository(Tariff::class)->findOneBy(['fullTitle' => $expectedTariffName])->getId();
        $actualTariffArray = array_map('\strval', array_column(array_column($actual, 'tariff'), '$id'));
        $diff = array_diff([$tariffId], $actualTariffArray);
        $this->assertCount(0, $diff);

    }

    public function priceCachesDataProvider(): iterable
    {
        yield [
            [
                'isCategory' => false,
                'beginOffset' => 0,
                'endOffset' => 26,
                'tariffFullTitle' => NamesLibrary::CHILD_UP_TARIFF_NAME,
                'roomTypeFullTitle' => NamesLibrary::TWO_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'],
                'hotelFullTitle' => 'Отель Волга',
                'expected' => [
                    'count' => 18,
                    'TariffName' => NamesLibrary::UP_TARIFF_NAME,
                    'RoomType' => NamesLibrary::TWO_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'],
                    'RoomTypeCategory' => null
                ]
            ]
        ];
        yield [
            [
                'isCategory' => true,
                'beginOffset' => 0,
                'endOffset' => 26,
                'tariffFullTitle' => NamesLibrary::CHILD_UP_TARIFF_NAME,
                'roomTypeFullTitle' => NamesLibrary::TWO_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'],
                'hotelFullTitle' => 'Отель Волга',
                'expected' => [
                    'count' => 18,
                    'TariffName' => NamesLibrary::UP_TARIFF_NAME,
                    'RoomType' => null,
                    'RoomTypeCategory' => NamesLibrary::ADDITIONAL_PLACES_CATEGORY['fullTitle']
                ]
            ]
        ];
    }

}