<?php


namespace Tests\Bundle\SearchBundle\Lib;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\DataFixtures\MongoDB\AdditionalRoomTypeData;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PriceBundle\DataFixtures\MongoDB\AdditionalTariffData;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\DataHolder;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class DataHolderTest extends SearchWebTestCase
{

    /** @var DataHolder */
    private $dataHolder;

    /** @var DocumentManager */
    private $dm;

    public function setUp()
    {
        parent::setUp();
//        self::baseFixtures();
        $this->dataHolder = $this->getContainer()->get('mbh_search.data_holder');
        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
    }

    /** @dataProvider roomCacheDataProvider */
    public function testGetNecessaryRoomCaches($data): void
    {
        $searchQuery = $this->createSearchQuery($data);
        $actual = $this->dataHolder->getNecessaryRoomCaches($searchQuery);
        $expectedData = $data['expected'];



    }

    /** @dataProvider restrictionDataProvider */
    public function testGetCheckNecessaryRestrictions($data): void
    {
        $searchQuery = $this->createSearchQuery($data);
        $actual = $this->dataHolder->getCheckNecessaryRestrictions($searchQuery);
        $expectedData = $data['expected'];
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($expectedData as $restrictionName => $offsets) {
            foreach ($offsets as $offsetIndex => $expectedValue) {
                $currentRestriction = $actual[$offsetIndex];
                $actualValue = $accessor->getValue($currentRestriction, "[{$restrictionName}]");
                $this->assertEquals($expectedValue, $actualValue);
            }
        }


    }

    private function createSearchQuery(array $data): SearchQuery
    {

        /** @var Hotel $hotel */
        $hotel = $this->dm->getRepository(Hotel::class)->findOneBy(['fullTitle' => $data['hotelFullTitle']]);
        $roomTypes = $hotel->getRoomTypes()->toArray();
        $searchRoomType = $this->getDocumentFromArrayByFullTitle($roomTypes, $data['roomTypeFullTitle']);
        $hotelTariffs = $hotel->getTariffs()->toArray();
        $searchTariff = $this->getDocumentFromArrayByFullTitle($hotelTariffs, $data['tariffFullTitle']);
        $begin = new \DateTime("midnight +{$data['beginOffset']} days");
        $end = new \DateTime("midnight +{$data['endOffset']} days");

        $searchHash = uniqid(gethostname(), true);
        $conditions = new SearchConditions();
        $conditions
            ->setBegin($begin)
            ->setEnd($end)
            ->setAdditionalBegin(0)
            ->setAdditionalEnd(0)
            ->setSearchHash($searchHash)
            ->addTariff($searchTariff);

        /** @var Tariff $searchTariff */
        if ($searchTariff->getParent() && $searchTariff->getChildOptions() && $searchTariff->getChildOptions()->isInheritRestrictions()) {
            $restrictionTariffId = $searchTariff->getParent()->getId();
        } else {
            $restrictionTariffId = $searchTariff->getId();
        }


        $searchQuery = new SearchQuery();
        $searchQuery
            ->setBegin($begin)
            ->setEnd($end)
            ->setSearchHash($searchHash)
            ->setRoomTypeId($searchRoomType->getId())
            ->setRestrictionTariffId($restrictionTariffId);

        $searchQuery->setSearchConditions($conditions);

        return $searchQuery;
    }

    public function roomCacheDataProvider(): iterable
    {
        yield [
            [
                'beginOffset' => 0,
                'endOffset' => 5,
                'tariffFullTitle' => AdditionalTariffData::UP_TARIFF_NAME,
                'roomTypeFullTitle' => AdditionalRoomTypeData::TWO_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'],
                'hotelFullTitle' => 'Отель Волга',
                'expected' => [
                    0 => null,
                    1 => null,
                    2 => 5,
                    3 => 5,
                    4 => 5
                ],
            ]
        ];
    }

    public function restrictionDataProvider(): iterable
    {
        yield [
            [
                'beginOffset' => 0,
                'endOffset' => 5,
                'tariffFullTitle' => AdditionalTariffData::UP_TARIFF_NAME,
                'roomTypeFullTitle' => AdditionalRoomTypeData::TWO_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'],
                'hotelFullTitle' => 'Отель Волга',
                'expected' => [
                    'minStayArrival' => [
                        0 => null,
                        1 => null,
                        2 => null,
                        3 => 5,
                        4 => 5,
                        5 => 5
                    ],
                    'close' => [
                        0 => null
                    ],

                ]
            ]
        ];

        yield [
            [
                'beginOffset' => 0,
                'endOffset' => 5,
                'tariffFullTitle' => AdditionalTariffData::CHILD_UP_TARIFF_NAME,
                'roomTypeFullTitle' => AdditionalRoomTypeData::TWO_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'],
                'hotelFullTitle' => 'Отель Волга',
                'expected' => [
                    'minStayArrival' => [
                        0 => null,
                        1 => null,
                        2 => null,
                        3 => 5,
                        4 => 5,
                        5 => 5
                    ],
                    'close' => [
                        0 => null
                    ],

                ]
            ]
        ];

    }
}