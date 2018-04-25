<?php


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException;
use MBH\Bundle\SearchBundle\Services\SearchRequestReceiver;

class SearchRequestReceiverTest extends WebTestCase
{
    /** @var SearchRequestReceiver */
    private $receiver;
    /** @var DocumentManager */
    private $dm;

    public function setUp()
    {
        $this->receiver = $this->getContainer()->get('mbh_search.search_request_receiver');
        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
        parent::setUp(); // TODO: Change the autogenerated stub
    }


    public function testHandleSuccessData1(): void
    {

        $roomTypes = $this->getRoomTypes();
        $roomIds = array_keys($roomTypes);
        $tariffs = $this->getTariffs();
        $tariffIds = array_keys($tariffs);
        $hotels = $this->getAllHotels();
        $hotelIds = array_keys($hotels);
        $data = [
            'begin' => '21.04.2018',
            'end' => '22.04.2018',
            'adults' => 3,
            'children' => 3,
            'roomTypes' => $roomIds,
            'tariffs' => $tariffIds,
            'hotels' => $hotelIds,
            'childrenAges' => ['3', 7, '12'],
            'isOnline' => true
        ];
        $result = $this->receiver->handleData($data);
        $object = (new SearchConditions())
            ->setBegin(new \DateTime('2018-04-21 midnight'))
            ->setEnd(new \DateTime('2018-04-22 midnight'))
            ->setAdults(3)
            ->setChildren(3)
            ->setChildrenAges([3, 7, 12])
            ->setRoomTypes(new ArrayCollection(array_values($roomTypes)))
            ->setTariffs(new ArrayCollection(array_values($tariffs)))
            ->setHotels(new ArrayCollection(array_values($hotels)))
            ->setIsOnline(true)
        ;

        $this->assertEquals($object, $result, 'Data from form is not equal expected');

    }

    /**
     * @dataProvider successDataProvider
     */
    public function testHandleSuccessData($data): void
    {
        $result = $this->receiver->handleData($data['raw']);

        $this->assertEquals($data['object'], $result, 'Data from form is not equal expected');

    }

    public function testHandleAdditionalEndMethod(): void
    {
        $data = [
            'begin' => '21.04.2018',
            'end' => '22.04.2018',
            'adults' => 3,
            'additionalBegin' => 2
        ];

        $object = (new SearchConditions())
            ->setBegin(new \DateTime('2018-04-21 midnight'))
            ->setEnd(new \DateTime('2018-04-22 midnight'))
            ->setAdults(3)
            ->setAdditionalBegin(2)
            ->setAdditionalEnd(2);

        $result = $this->receiver->handleData($data);
        $this->assertSame($object->getAdditionalEnd(), $result->getAdditionalEnd(), 'Data from form is not equal expected');

    }


    /**
     * @throws SearchConditionException
     * @dataProvider failDataProvider
     */
    public function testHandleFail($data): void
    {
        if (isset($data['tariffs'])) {
            $data['tariffs'] = array_merge(
                $data['tariffs'],
                array_keys($this->getTariffs()),
                ['wrong tariff name']
            );
        }

        if (isset($data['roomTypes'])) {
            $data['roomTypes'] = array_merge(
                $data['roomTypes'],
                array_keys($this->getRoomTypes()),
                ['wrong roomType name']
            );
        }

        if (isset($data['hotels'])) {
            $data['hotels'] = array_merge(
                $data['hotels'],
                array_keys($this->getAllHotels()),
                ['wrong hotel name']
            );
        }

        $this->expectException(SearchConditionException::class);
        $this->receiver->handleData($data);
    }


    private function getTariffs()
    {
        return $this->getAllFromRepo(Tariff::class);
    }

    private function getRoomTypes(): ?array
    {
        return $this->getAllFromRepo(RoomType::class);
    }

    private function getAllHotels(): ?array
    {
        return $this->getAllFromRepo(Hotel::class);
    }

    private function getAllFromRepo(string $reponame)
    {
        $qb = $this->dm->createQueryBuilder($reponame);

        /** @var \Doctrine\MongoDB\ArrayIterator $ids */
        return $qb->find()->getQuery()->execute()->toArray();
    }

    public function successDataProvider(): array
    {
        return [
            [
                [
                    'raw' => [
                        'begin' => '21.04.2018',
                        'end' => '22.04.2018',
                        'adults' => 3,
                    ],
                    'object' => (new SearchConditions())
                        ->setBegin(new \DateTime('2018-04-21 midnight'))
                        ->setEnd(new \DateTime('2018-04-22 midnight'))
                        ->setAdults(3),
                ],
            ],
            [
                [
                    'raw' => [
                        'begin' => '21.04.2018',
                        'end' => '22.04.2018',
                        'adults' => 3,
                        'additionalBegin' => 2,
                        'additionalEnd' => 3,
                    ],
                    'object' => (new SearchConditions())
                        ->setBegin(new \DateTime('2018-04-21 midnight'))
                        ->setEnd(new \DateTime('2018-04-22 midnight'))
                        ->setAdults(3)
                        ->setAdditionalBegin(2)
                        ->setAdditionalEnd(3),

                ],
            ],
        ];
    }


    public function failDataProvider(): array
    {
        return [
            [
                'begin more than end' => [
                    'begin' => '23.04.2018',
                    'end' => '22.04.2018',
                    'adults' => 3,
                    'children' => 3,
                    'childrenAges' => [3, 4, 5],
                ],

            ],
            [
                'wrong date data' =>
                    [
                        'begin' => 'wrong ',
                        'end' => 'data',
                        'adults' => 3,
                        'children' => 3,
                        'childrenAges' => [3, 4, 5],
                    ],
            ],
            [
                'wrong tariff data' =>
                    [
                        'begin' => '21.04.2018',
                        'end' => '22.04.2018',
                        'adults' => 3,
                        'children' => 3,
                        'childrenAges' => [3, 4, 5],
                        'tariffs' => [],
                    ],
            ],
            [
                'wrong roomType data' =>
                    [
                        'begin' => '21.04.2018',
                        'end' => '22.04.2018',
                        'adults' => 3,
                        'children' => 3,
                        'childrenAges' => [3, 4, 5],
                        'roomTypes' => [],
                    ],
            ],
            [
                'wrong additional data' =>
                    [
                        'begin' => '21.04.2018',
                        'end' => '22.04.2018',
                        'adults' => 3,
                        'children' => 3,
                        'childrenAges' => [3, 4, 5],
                        'additionalBefore' => -1,
                    ],
            ],
            [
                'wrong additional data' =>
                    [
                        'begin' => '21.04.2018',
                        'end' => '22.04.2018',
                        'adults' => 3,
                        'children' => 3,
                        'childrenAges' => [3, 4, 5, 6],
                    ],
            ],
            [
                'wrong hotel data' =>
                    [
                        'begin' => '21.04.2018',
                        'end' => '22.04.2018',
                        'adults' => 3,
                        'children' => 3,
                        'childrenAges' => [3, 4, 5, 6],
                        'hotels' => []
                    ],
            ]

        ];
    }

}