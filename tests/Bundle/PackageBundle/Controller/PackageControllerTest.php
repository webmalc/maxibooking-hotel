<?php


namespace Tests\Bundle\PackageBundle\Controller;


use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\UserBundle\DataFixtures\MongoDB\UserData;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PackageControllerTest extends WebTestCase

{
    const FIXTURE_DATA = [
        [
            'number' => '18',
            'adults' => 1,
            'children' => 1,
            'price' => 2000,
            'paid' => 2001,
            'regDayAgo' => 5,
            'beginAfter' => -3,
            'length' => 8,
            'owner' => UserData::USER_MANAGER,
            'isCheckIn' => true,
            'status' => 'channel_manager'
        ],
        [
            'number' => '19',
            'adults' => 1,
            'children' => 0,
            'price' => 800,
            'paid' => 10,
            'regDayAgo' => 4,
            'beginAfter' => -2,
            'length' => 5,
            'owner' => UserData::USER_MANAGER,
            'isCheckIn' => true,
            'status' => 'channel_manager'
        ],

    ];

    const REQUEST_DATA_COMMON = [
        'draw' => 2,
        'columns[0][data]' => 0,
        'columns[0][name]' => '',
        'columns[0][searchable]' => true,
        'columns[0][orderable]' => false,
        'columns[0][search][value]' => '',
        'columns[0][search][regex]' => false,
        'columns[1][data]' => 1,
        'columns[1][name]' => '',
        'columns[1][searchable]' => true,
        'columns[1][orderable]' => true,
        'columns[1][search][value]' => '',
        'columns[1][search][regex]' => false,
        'columns[2][data]' => 2,
        'columns[2][name]' => '',
        'columns[2][searchable]' => true,
        'columns[2][orderable]' => true,
        'columns[2][search][value]' => '',
        'columns[2][search][regex]' => false,
        'columns[3][data]' => 3,
        'columns[3][name]' => '',
        'columns[3][searchable]' => true,
        'columns[3][orderable]' => true,
        'columns[3][search][value]' => '',
        'columns[3][search][regex]' => false,
        'columns[4][data]' => 4,
        'columns[4][name]' => '',
        'columns[4][searchable]' => true,
        'columns[4][orderable]' => true,
        'columns[4][search][value]' => '',
        'columns[4][search][regex]' => false,
        'columns[5][data]' => 5,
        'columns[5][name]' => '',
        'columns[5][searchable]' => true,
        'columns[5][orderable]' => true,
        'columns[5][search][value]' => '',
        'columns[5][search][regex]' => false,
        'columns[6][data]' => 6,
        'columns[6][name]' => '',
        'columns[6][searchable]' => true,
        'columns[6][orderable]' => true,
        'columns[6][search][value]' => '',
        'columns[6][search][regex]' => false,
        'columns[7][data]' => 7,
        'columns[7][name]' => '',
        'columns[7][searchable]' => true,
        'columns[7][orderable]' => false,
        'columns[7][search][value]' => '',
        'columns[7][search][regex]' => false,
        'order[0][column]' => 3,
        'order[0][dir]' => 'desc',
        'start' => 0,
        'length' => 50,
        'search[value]' => '',
        'search[regex]' => false,
        'begin' => '',
        'end' => '',
        'roomType[]' => '',
        'source' => '',
        'status' => '',
        'deleted' => 0,
        'dates' => '',
        'paid' => '',
        'confirmed' => '',
        'quick_link' => '',
    ];

    const TEST_DATA = [
        'date' => [
            [
                'expected' => [
                    'recordsTotal' => 17,
//                    'package_summary_total' => 100655,
//                    'package_summary_paid' => 0,
//                    'package_summary_debt' => 0,
//                    'package_summary_nights' => 64,
//                    'package_summary_guests' => 26
                ],
                'beginDaysDiff' => -100,
                'endDaysDiff' => '',
                'criteria' => [],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" minus 100 days to "today".'
            ],
            [
                'expected' => [
                    'recordsTotal' => 15,
//                    'package_summary_total' => 100655,
//                    'package_summary_paid' => 0,
//                    'package_summary_debt' => 0,
//                    'package_summary_nights' => 64,
//                    'package_summary_guests' => 26
                ],
                'beginDaysDiff' => '',
                'endDaysDiff' => '',
                'criteria' => [],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" equals to "today".'
            ],
            [
                'expected' => [
                    'recordsTotal' => 11,
//                    'package_summary_total' => 100655,
//                    'package_summary_paid' => 0,
//                    'package_summary_debt' => 0,
//                    'package_summary_nights' => 64,
//                    'package_summary_guests' => 26
                ],
                'beginDaysDiff' => '',
                'endDaysDiff' => 4,
                'criteria' => [],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" equals to "today", "end date" plus 4 days to "today".'
            ],
            [
                'expected' => [
                    'recordsTotal' => 7,
//                    'package_summary_total' => 100655,
//                    'package_summary_paid' => 0,
//                    'package_summary_debt' => 0,
//                    'package_summary_nights' => 64,
//                    'package_summary_guests' => 26
                ],
                'beginDaysDiff' => '',
                'endDaysDiff' => 4,
                'criteria' => ['dates' => 'end'],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" equals to "today", "end date" plus 4 days to "today", type of "dates" equals to "end".'
            ],
        ],

        'bookingType' => [
            [
                'expected' => [
                    'recordsTotal' => 13,
//                    'package_summary_total' => 100655,
//                    'package_summary_paid' => 0,
//                    'package_summary_debt' => 0,
//                    'package_summary_nights' => 64,
//                    'package_summary_guests' => 26
                ],
                'beginDaysDiff' => -100,
                'endDaysDiff' => '',
                'criteria' => ['status' => 'offline'],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" minus 100 days to "today", booking type of "status" equals to "offline".'
            ],
            [
                'expected' => [
                    'recordsTotal' => 2,
//                    'package_summary_total' => 100655,
//                    'package_summary_paid' => 0,
//                    'package_summary_debt' => 0,
//                    'package_summary_nights' => 64,
//                    'package_summary_guests' => 26
                ],
                'beginDaysDiff' => -100,
                'endDaysDiff' => '',
                'criteria' => ['status' => 'online'],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" minus 100 days to "today", booking type of "status" equals to "online".'
            ],
            [
                'expected' => [
                    'recordsTotal' => 2,
//                    'package_summary_total' => 100655,
//                    'package_summary_paid' => 0,
//                    'package_summary_debt' => 0,
//                    'package_summary_nights' => 64,
//                    'package_summary_guests' => 26
                ],
                'beginDaysDiff' => -100,
                'endDaysDiff' => '',
                'criteria' => ['status' => 'channel_manager'],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" minus 100 days to "today", booking type of "status" equals to "channel_manager".'
            ],

        ]
    ];

    const TEST_ROOM_TYPE = 'room';
    const TEST_BOOKING_TYPE = 'booking';
    const TEST_SOURCE = 'date';
    const TEST_PAYMENT_STATUS = 'payment';
    const TEST_APPROVAL = 'approval';
    const TEST_DELETED = 'deleted';
    const TEST_BEGIN_TODAY = 'begin-today';
    const TEST_BEGIN_TOMORROW = 'begin-tomorrow';
    const TEST_LIVE_TODAY = 'live-now';
    const TEST_WITHOUT_APPROVAL = 'without-approval';
    const TEST_WITHOUT_ACCOMMODATION = 'without-accommodation';
    const TEST_NOT_PAID = 'not-paid';
    const TEST_NOT_PAID_TIME = 'not-paid-time';
    const TEST_NOT_CHECK_IN = 'not-check-in';
    const TEST_CREATED_BY = 'created-by';

//    const SEARCH_PARAMS = [
//        ['quick_link' => 'begin-today'],
//        ['quick_link' => 'begin-tomorrow'],
//        ['quick_link' => 'live-now'],
//        ['quick_link' => 'without-approval'],
//        ['quick_link' => 'without-accommodation'],
//        ['quick_link' => 'not-paid'],
//        ['quick_link' => 'not-paid-time'],
//        ['quick_link' => 'not-check-in'],
//        ['quick_link' => 'created-by'],
//        ['quick_link' => ''],
//    ];

    /** @var Client */
    protected $client;

    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var object
     */
    protected $hotel;

    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    public function setUp()
    {
        parent::setUp();
        $this->client = self::makeClient(true);
        $this->container = self::getContainer();
        $this->dm = $this->container->get('doctrine.odm.mongodb.document_manager');
        $this->hotel = $this->container->get('mbh.hotel.selector')->getSelected();
        $this->persistPackage($this->dm);

    }

    protected function getRequest($params)
    {
        $request = $this->client->request(
            'GET',
            '/package/json',
            $params,
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ]
        );

        return $request;
    }

    protected function getDates($requestParams, $testData)
    {
        if (!empty($testData['beginDaysDiff'])) {
            $requestParams['begin'] = new \DateTime('midnight' . '+' . $testData['beginDaysDiff'] . 'days');
        } else {
            $requestParams['begin'] = new \DateTime('midnight');
        }
        if (!empty($testData['endDaysDiff'])) {
            $requestParams['end'] = new \DateTime('+' . $testData['endDaysDiff'] . 'days');
        } else {
            $requestParams['end'] = '';
        }

        return $requestParams;
    }

    protected function getParameters($requestParams, $testData)
    {
        $requestParams = $this->getDates($requestParams, $testData);
        if (!empty($testData['criteria'])) {
            foreach ($testData['criteria'] as $key => $value) {
                $requestParams[$key] = $value;
            }
        }

        return $requestParams;
    }

    public function testIndexAction()
    {
        foreach (self::TEST_DATA as $test) {
            foreach ($test as $testData) {
                $requestParams = self::REQUEST_DATA_COMMON;
                $requestParams = $this->getParameters($requestParams, $testData);
                $this->getRequest($requestParams);
                $this->assertRecords($testData['expected'], $testData['errorMessage']);
            }
        }
    }

    public function testRoomType()
    {
        $requestParams = self::REQUEST_DATA_COMMON;
        $requestParams['begin'] = new \DateTime('-' . 100 . 'days');
        /**
         * @var RoomType $singleRoom
         */
        $singleRoom = $this->dm->getRepository(RoomType::class)->findOneBy([
            'hotel' => $this->hotel,
            'places' => 1
        ]);
        /**
         * @var RoomType $doubleRoom
         */
        $doubleRoom = $this->dm->getRepository(RoomType::class)->findOneBy([
            'hotel' => $this->hotel,
            'places' => 2
        ]);

        $requestParams['roomType'] = $singleRoom->getId();
        $this->getRequest($requestParams);
        $errorMessage = 'Expected the package list to be filtered by "begin date" minus 100 days to "today", "roomType" equals to "single room".';
        $expected = [
            'recordsTotal' => 2,
//                    'package_summary_total' => 100655,
//                    'package_summary_paid' => 0,
//                    'package_summary_debt' => 0,
//                    'package_summary_nights' => 64,
//                    'package_summary_guests' => 26
        ];
        $this->assertRecords($expected, $errorMessage);

        $requestParams['roomType'] = $doubleRoom->getId();
        $this->getRequest($requestParams);
        $errorMessage = 'Expected the package list to be filtered by "begin date" minus 100 days to "today", "roomType" equals to "double room".';
        $expected = [
            'recordsTotal' => 15,
//                    'package_summary_total' => 100655,
//                    'package_summary_paid' => 0,
//                    'package_summary_debt' => 0,
//                    'package_summary_nights' => 64,
//                    'package_summary_guests' => 26
        ];
        $this->assertRecords($expected, $errorMessage);

    }

    protected function assertRecords($expected, $errorMessage = "")
    {
        $response = json_decode($this->client->getResponse()->getContent());
        foreach ($expected as $key => $value)
            $this->assertEquals(
                $value,
                $response->$key,
                $errorMessage
            );
    }

    /**
     * @param ObjectManager $manager
     * @param $data
     * @return Order
     */
    public function persistOrder(ObjectManager $manager, $data)
    {
        $owner = $this->dm->getRepository(User::class)->findOneBy(['username' => $data['owner']]);

        $order = (new Order())
            ->setPrice($data['price'])
            ->setPaid($data['paid'])
            ->setStatus($data['status'] ?? 'offline')
            ->setTotalOverwrite($data['price'])
            ->setCreatedBy($owner)
            ->setCreatedAt((new \DateTime())->modify('-' . $data['regDayAgo'] . 'days'));
        if ($owner) {
            $order->setOwner($owner);

        }
        $order->checkPaid();
        $manager->persist($order);
        $manager->flush();

        return $order;
    }


    /**
     * @param ObjectManager $manager
     */
    public function persistPackage(ObjectManager $manager)
    {
        /** @var Tariff $tariff */
        $tariff = $this->dm->getRepository(Tariff::class)->findOneBy([
            'hotel' => $this->hotel,
            'isDefault' => true
        ]);
        /** @var RoomType $roomType */
        $roomType = $this->dm->getRepository(RoomType::class)->findOneBy([
            'hotel' => $this->hotel,
            'places' => 1
        ]);

        foreach (self::FIXTURE_DATA as $packageData) {
            $owner = $this->getOwner($packageData['owner'] ?? null);
            $order = $this->persistOrder($manager, $packageData);
            $beginDate = new \DateTime('midnight +' . $packageData['beginAfter'] . 'days');
            $endDate = (clone $beginDate)->modify('+' . $packageData['length'] . 'days');
            $dateOfCreation = new \DateTime('-' . $packageData['regDayAgo'] . 'days');
            $package = new Package();
            $package
                ->setAdults($packageData['adults'])
                ->setNumber(1)
                ->setNumberWithPrefix($packageData['number'] . '/1')
                ->setChildren($packageData['children'])
                ->setPrice($packageData['price'])
                ->setOrder($order)
                ->setTariff($tariff)
                ->setRoomType($roomType)
                ->setBegin($beginDate)
                ->setCreatedAt($dateOfCreation)
                ->setCreatedBy($owner)
                ->setEnd($endDate)
                ->setIsCheckIn($packageData['isCheckIn']);
            if ($owner) {
                $package->setOwner($owner);
            }

            if (isset($packageData['cancelledAgo'])) {
                $cancellationDate = (new \DateTime())->modify('-' . $packageData['cancelledAgo'] . 'days');
                $package->setDeletedAt($cancellationDate);
                $order->setDeletedAt($cancellationDate);
            }

            $prices = [];
            for ($i = 0; $i < $package->getNights(); $i++) {
                $date = (clone $package->getBegin())->modify('+' . $i . 'days');
                $price = $package->getPrice() / $package->getNights();
                $prices[] = new PackagePrice($date, $price, $package->getTariff());
            }
            $package->setPrices($prices);
            $manager->persist($package);
            $manager->flush();
        }
    }


    private function getOwner($name): ?User
    {
        if (null !== $name) {
            /** @noinspection PhpIncompatibleReturnTypeInspection */
            return $this->dm->getRepository(User::class)->findOneBy(['username' => $name]);
        }

        return null;
    }

}