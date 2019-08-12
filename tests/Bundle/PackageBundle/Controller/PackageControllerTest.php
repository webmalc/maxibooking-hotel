<?php


namespace Tests\Bundle\PackageBundle\Controller;


use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\PackageBundle\Document\PackageSource;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\UserBundle\DataFixtures\MongoDB\UserData;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DomCrawler\Crawler;

class PackageControllerTest extends WebTestCase

{
    const TOURIST_DATA = [
        'ilya' => ['name' => 'Илья', 'lastName' => 'Габонов', 'patronymic' => 'Иванович'],
        'pablo' => ['name' => 'Пабло', 'lastName' => 'Хосе', 'patronymic' => 'Васильевич'],
    ];

    const FIXTURE_DATA = [
        [
            'number' => '18',
            'adults' => 1,
            'children' => 1,
            'price' => 2000,
            'paid' => 0,
            'regDayAgo' => 5,
            'beginAfter' => -3,
            'length' => 8,
            'owner' => UserData::USER_MANAGER,
            'isCheckIn' => true,
            'status' => 'channel_manager',
            'confirmed' => true,
            'tourist' => 'ilya',
            'rooms' => 1
        ],
        [
            'number' => '19',
            'adults' => 1,
            'children' => 0,
            'price' => 800,
            'paid' => 400,
            'regDayAgo' => 4,
            'beginAfter' => -2,
            'length' => 5,
            'owner' => UserData::USER_MANAGER,
            'isCheckIn' => true,
            'status' => 'channel_manager',
            'confirmed' => true,
            'tourist' => 'pablo',
            'rooms' => 3
        ],

    ];

    const REQUEST_DATA_COMMON = [];

    const FILTERS_DATA = [
        'date' => [
            [
                'expected' => [
                    'recordsTotal' => 17,
                    'package_summary_total' => 130455,
                    'package_summary_paid' => 400,
                    'package_summary_debt' => 2400,
                    'package_summary_nights' => 90,
                    'package_summary_guests' => 38
                ],
                'beginDaysDiff' => -100,
                'endDaysDiff' => '',
                'criteria' => [],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" minus 100 days to "today".'
            ],
            [
                'expected' => [
                    'recordsTotal' => 15,
                    'package_summary_total' => 127655,
                    'package_summary_paid' => 0,
                    'package_summary_debt' => 0,
                    'package_summary_nights' => 77,
                    'package_summary_guests' => 35
                ],
                'beginDaysDiff' => '',
                'endDaysDiff' => '',
                'criteria' => [],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" equals to "today".'
            ],
            [
                'expected' => [
                    'recordsTotal' => 11,
                    'package_summary_total' => 77024,
                    'package_summary_paid' => 0,
                    'package_summary_debt' => 0,
                    'package_summary_nights' => 48,
                    'package_summary_guests' => 22
                ],
                'beginDaysDiff' => '',
                'endDaysDiff' => 4,
                'criteria' => [],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" equals to "today", "end date" plus 4 days to "today".'
            ],
            [
                'expected' => [
                    'recordsTotal' => 7,
                    'package_summary_total' => 30824,
                    'package_summary_paid' => 400,
                    'package_summary_debt' => 400,
                    'package_summary_nights' => 22,
                    'package_summary_guests' => 10
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
                    'package_summary_total' => 100655,
                    'package_summary_paid' => 0,
                    'package_summary_debt' => 0,
                    'package_summary_nights' => 64,
                    'package_summary_guests' => 26
                ],
                'beginDaysDiff' => -100,
                'endDaysDiff' => '',
                'criteria' => ['status' => 'offline'],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" minus 100 days to "today", booking type of "status" equals to "offline".'
            ],
            [
                'expected' => [
                    'recordsTotal' => 2,
                    'package_summary_total' => 27000,
                    'package_summary_paid' => 0,
                    'package_summary_debt' => 0,
                    'package_summary_nights' => 13,
                    'package_summary_guests' => 9
                ],
                'beginDaysDiff' => -100,
                'endDaysDiff' => '',
                'criteria' => ['status' => 'online'],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" minus 100 days to "today", booking type of "status" equals to "online".'
            ],
            [
                'expected' => [
                    'recordsTotal' => 2,
                    'package_summary_total' => 2800,
                    'package_summary_paid' => 400,
                    'package_summary_debt' => 2400,
                    'package_summary_nights' => 13,
                    'package_summary_guests' => 3
                ],
                'beginDaysDiff' => -100,
                'endDaysDiff' => '',
                'criteria' => ['status' => 'channel_manager'],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" minus 100 days to "today", booking type of "status" equals to "channel_manager".'
            ],

        ],

        'paidType' => [
            [
                'expected' => [
                    'recordsTotal' => 5,
                    'package_summary_total' => 38860,
                    'package_summary_paid' => 0,
                    'package_summary_debt' => 0,
                    'package_summary_nights' => 19,
                    'package_summary_guests' => 13
                ],
                'beginDaysDiff' => -100,
                'endDaysDiff' => '',
                'criteria' => ['paid' => 'paid'],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" minus 100 days to "today", type of "paid" equals to "paid".'
            ],
            [
                'expected' => [
                    'recordsTotal' => 9,
                    'package_summary_total' => 71231,
                    'package_summary_paid' => 400,
                    'package_summary_debt' => 400,
                    'package_summary_nights' => 54,
                    'package_summary_guests' => 20
                ],
                'beginDaysDiff' => -100,
                'endDaysDiff' => '',
                'criteria' => ['paid' => 'part'],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" minus 100 days to "today", type of "paid" equals to "part".'
            ],
            [
                'expected' => [
                    'recordsTotal' => 3,
                    'package_summary_total' => 20364,
                    'package_summary_paid' => 0,
                    'package_summary_debt' => 2000,
                    'package_summary_nights' => 17,
                    'package_summary_guests' => 5
                ],
                'beginDaysDiff' => -100,
                'endDaysDiff' => '',
                'criteria' => ['paid' => 'not_paid'],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" minus 100 days to "today", type of "paid" equals to "not_paid".'
            ],

        ],

        'confirmed' => [
            [
                'expected' => [
                    'recordsTotal' => 17,
                    'package_summary_total' => 130455,
                    'package_summary_paid' => 400,
                    'package_summary_debt' => 2400,
                    'package_summary_nights' => 90,
                    'package_summary_guests' => 38
                ],
                'beginDaysDiff' => -100,
                'endDaysDiff' => '',
                'criteria' => ['confirmed' => ''],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" minus 100 days to "today", type of "confirmed" equals to "".'
            ],
            [
                'expected' => [
                    'recordsTotal' => 15,
                    'package_summary_total' => 127655,
                    'package_summary_paid' => 0,
                    'package_summary_debt' => 0,
                    'package_summary_nights' => 77,
                    'package_summary_guests' => 35
                ],
                'beginDaysDiff' => -100,
                'endDaysDiff' => '',
                'criteria' => ['confirmed' => 0],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" minus 100 days to "today", type of "confirmed" equals to "false".'
            ],
            [
                'expected' => [
                    'recordsTotal' => 2,
                    'package_summary_total' => 2800,
                    'package_summary_paid' => 400,
                    'package_summary_debt' => 2400,
                    'package_summary_nights' => 13,
                    'package_summary_guests' => 3
                ],
                'beginDaysDiff' => -100,
                'endDaysDiff' => '',
                'criteria' => ['confirmed' => 1],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" minus 100 days to "today", type of "confirmed" equals to "true".'
            ],
        ],

        'deleted' => [
            [
                'expected' => [
                    'recordsTotal' => 17,
                    'package_summary_total' => 130455,
                    'package_summary_paid' => 400,
                    'package_summary_debt' => 2400,
                    'package_summary_nights' => 90,
                    'package_summary_guests' => 38
                ],
                'beginDaysDiff' => -100,
                'endDaysDiff' => '',
                'criteria' => ['deleted' => 0],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" minus 100 days to "today", type of "deleted" equals to "disable".'
            ],
            [
                'expected' => [
                    'recordsTotal' => 19,
                    'package_summary_total' => 138385,
                    'package_summary_paid' => 400,
                    'package_summary_debt' => 2400,
                    'package_summary_nights' => 99,
                    'package_summary_guests' => 42
                ],
                'beginDaysDiff' => -100,
                'endDaysDiff' => '',
                'criteria' => ['deleted' => 1],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" minus 100 days to "today", type of "deleted" equals to "enable".'
            ],
        ],

        'begin-today' => [
            [
                'expected' => [
                    'recordsTotal' => 7,
                    'package_summary_total' => 45224,
                    'package_summary_paid' => 0,
                    'package_summary_debt' => 0,
                    'package_summary_nights' => 27,
                    'package_summary_guests' => 12
                ],
                'beginDaysDiff' => -100,
                'endDaysDiff' => '',
                'criteria' => ['quick_link' => 'begin-today'],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" minus 100 days to "today", type of "quick_link" equals to "begin-today".'
            ],
        ],

        'begin-tomorrow' => [
            [
                'expected' => [
                    'recordsTotal' => 1,
                    'package_summary_total' => 7000,
                    'package_summary_paid' => 0,
                    'package_summary_debt' => 0,
                    'package_summary_nights' => 5,
                    'package_summary_guests' => 4
                ],
                'beginDaysDiff' => -100,
                'endDaysDiff' => '',
                'criteria' => ['quick_link' => 'begin-tomorrow'],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" minus 100 days to "today", type of "quick_link" equals to "begin-tomorrow".'
            ],
        ],

        'live-now' => [
            [
                'expected' => [
                    'recordsTotal' => 2,
                    'package_summary_total' => 2800,
                    'package_summary_paid' => 400,
                    'package_summary_debt' => 2400,
                    'package_summary_nights' => 13,
                    'package_summary_guests' => 3
                ],
                'beginDaysDiff' => -100,
                'endDaysDiff' => '',
                'criteria' => ['quick_link' => 'live-now'],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" minus 100 days to "today", type of "quick_link" equals to "live-now".'
            ],
        ],

        'without-approval' => [
            [
                'expected' => [
                    'recordsTotal' => 15,
                    'package_summary_total' => 127655,
                    'package_summary_paid' => 0,
                    'package_summary_debt' => 0,
                    'package_summary_nights' => 77,
                    'package_summary_guests' => 35
                ],
                'beginDaysDiff' => -100,
                'endDaysDiff' => '',
                'criteria' => ['quick_link' => 'without-approval'],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" minus 100 days to "today", type of "quick_link" equals to "without-approval".'
            ],
        ],

        'without-accommodation' => [
            [
                'expected' => [
                    'recordsTotal' => 9,
                    'package_summary_total' => 48024,
                    'package_summary_paid' => 400,
                    'package_summary_debt' => 2400,
                    'package_summary_nights' => 40,
                    'package_summary_guests' => 15
                ],
                'beginDaysDiff' => -100,
                'endDaysDiff' => '',
                'criteria' => ['quick_link' => 'without-accommodation'],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" minus 100 days to "today", type of "quick_link" equals to "without-accommodation".'
            ],
        ],

        'not-paid' => [
            [
                'expected' => [
                    'recordsTotal' => 3,
                    'package_summary_total' => 20364,
                    'package_summary_paid' => 0,
                    'package_summary_debt' => 2000,
                    'package_summary_nights' => 17,
                    'package_summary_guests' => 5
                ],
                'beginDaysDiff' => -100,
                'endDaysDiff' => '',
                'criteria' => ['quick_link' => 'not-paid'],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" minus 100 days to "today", type of "quick_link" equals to "not-paid".'
            ],
        ],

        'not-paid-time' => [
            [
                'expected' => [
                    'recordsTotal' => 3,
                    'package_summary_total' => 20364,
                    'package_summary_paid' => 0,
                    'package_summary_debt' => 2000,
                    'package_summary_nights' => 17,
                    'package_summary_guests' => 5
                ],
                'beginDaysDiff' => -100,
                'endDaysDiff' => '',
                'criteria' => ['quick_link' => 'not-paid-time'],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" minus 100 days to "today", type of "quick_link" equals to "not-paid-time".'
            ],
        ],

        'not-check-in' => [
            [
                'expected' => [
                    'recordsTotal' => 7,
                    'package_summary_total' => 45224,
                    'package_summary_paid' => 0,
                    'package_summary_debt' => 0,
                    'package_summary_nights' => 27,
                    'package_summary_guests' => 12
                ],
                'beginDaysDiff' => -100,
                'endDaysDiff' => '',
                'criteria' => ['quick_link' => 'not-check-in'],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" minus 100 days to "today", type of "quick_link" equals to "not-check-in".'
            ],
        ],

        'created-by' => [
            [
                'expected' => [
                    'recordsTotal' => 8,
                    'package_summary_total' => 72224,
                    'package_summary_paid' => 0,
                    'package_summary_debt' => 0,
                    'package_summary_nights' => 38,
                    'package_summary_guests' => 19
                ],
                'beginDaysDiff' => -100,
                'endDaysDiff' => '',
                'criteria' => ['quick_link' => 'created-by'],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" minus 100 days to "today", type of "quick_link" equals to "created-by" ("My bookings").'
            ],
        ],

        'records quantity' => [
            [
                'expected' => [
                    'data' => ['type' => 'count', 'val' => 10],
                ],
                'criteria' => [
                    'start' => 0,
                    'length' => 10,
                ],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" equals to "today", quantity of records 10, page 1.'
            ],
            [
                'expected' => [
                    'data' => ['type' => 'count', 'val' => 5],
                ],
                'criteria' => [
                    'start' => 10,
                    'length' => 10,
                ],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" equals to "today", quantity of records 10, page 2.'
            ],
        ],


    ];

    const SEARCH_DATA = [

        'search field' => [
            [
                'expected' => [
                    'data' => ['type' => 'count', 'val' => 1],
                ],
                'criteria' => [
                    'search' => 'Илья',
                ],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" equals to "today", search field value equals to "Илья".'
            ],
            [
                'expected' => [
                    'data' => ['type' => 'count', 'val' => 1],
                ],
                'criteria' => [
                    'search' => 'Габонов',
                ],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" equals to "today", search field value equals to "Габонов".'
            ],
            [
                'expected' => [
                    'data' => ['type' => 'count', 'val' => 1],
                ],
                'criteria' => [
                    'search' => 'Пабло',
                ],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" equals to "today", search field value equals to "Пабло".'
            ],
            [
                'expected' => [
                    'data' => ['type' => 'count', 'val' => 1],
                ],
                'criteria' => [
                    'search' => 'Хосе',
                ],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" equals to "today", search field value equals to "Хосе".'
            ],
            [
                'expected' => [
                    'data' => ['type' => 'count', 'val' => 1],
                ],
                'criteria' => [
                    'search' => 18,
                ],
                'errorMessage' => 'Expected the package list to be filtered by "begin date" equals to "today", search field value equals to "18".'
            ],
        ],
    ];

    const SORT_DATA = [

        'sort by order number' => [
            [
                'expected' => [
                    'record1' => [
                        'type' => 'value',
                        'elemNumber' => 0,
                        'paramValue' => '1/1',
                        'expectedVal' => 1
                    ],
                    'record2' => [
                        'type' => 'value',
                        'elemNumber' => 1,
                        'paramValue' => '2/1',
                        'expectedVal' => 1
                    ],

                ],
                'criteria' => [
                    'orderColumn' => 1,
                    'dir' => 'asc'
                ],
                'errorMessage' => 'Expected the package list to be sorted by "order number" in ASC direction.'
            ],
            [
                'expected' => [
                    'record1' => [
                        'type' => 'value',
                        'elemNumber' => 0,
                        'paramValue' => '19/1',
                        'expectedVal' => 1
                    ],
                    'record2' => [
                        'type' => 'value',
                        'elemNumber' => 1,
                        'paramValue' => '18/1',
                        'expectedVal' => 1
                    ],

                ],
                'criteria' => [
                    'orderColumn' => 1,
                    'dir' => 'desc'
                ],
                'errorMessage' => 'Expected the package list to be sorted by "order number" in DESC direction.'
            ],
        ],

        'sort by dates' => [
            [
                'expected' => [
                    'record1' => [
                        'type' => 'value',
                        'elemNumber' => 0,
                        'paramValue' => '18/1',
                        'expectedVal' => 1
                    ],
                    'record2' => [
                        'type' => 'value',
                        'elemNumber' => 1,
                        'paramValue' => '19/1',
                        'expectedVal' => 1
                    ],

                ],
                'criteria' => [
                    'orderColumn' => 2,
                    'dir' => 'asc'
                ],
                'errorMessage' => 'Expected the package list to be sorted by "dates" in ASC direction.'
            ],
            [
                'expected' => [
                    'record1' => [
                        'type' => 'value',
                        'elemNumber' => 0,
                        'paramValue' => '4/1',
                        'expectedVal' => 1
                    ],
                    'record2' => [
                        'type' => 'value',
                        'elemNumber' => 1,
                        'paramValue' => '5/1',
                        'expectedVal' => 1
                    ],

                ],
                'criteria' => [
                    'orderColumn' => 2,
                    'dir' => 'desc'
                ],
                'errorMessage' => 'Expected the package list to be sorted by "dates" in DESC direction.'
            ],
        ],

        'sort by rooms' => [
            [
                'expected' => [
                    'record1' => [
                        'type' => 'value',
                        'elemNumber' => 0,
                        'paramValue' => '18/1',
                        'expectedVal' => 1
                    ],
                    'record2' => [
                        'type' => 'value',
                        'elemNumber' => 1,
                        'paramValue' => '1/1',
                        'expectedVal' => 1
                    ],

                ],
                'criteria' => [
                    'orderColumn' => 3,
                    'dir' => 'asc'
                ],
                'errorMessage' => 'Expected the package list to be sorted by "room type" in ASC direction.'
            ],
            [
                'expected' => [
                    'record1' => [
                        'type' => 'value',
                        'elemNumber' => 0,
                        'paramValue' => '19/1',
                        'expectedVal' => 1
                    ],
                    'record2' => [
                        'type' => 'value',
                        'elemNumber' => 1,
                        'paramValue' => '1/1',
                        'expectedVal' => 1
                    ],

                ],
                'criteria' => [
                    'orderColumn' => 3,
                    'dir' => 'desc'
                ],
                'errorMessage' => 'Expected the package list to be sorted by "room type" in DESC direction.'
            ],
        ],

        'sort by price' => [
            [
                'expected' => [
                    'record1' => [
                        'type' => 'value',
                        'elemNumber' => 0,
                        'paramValue' => '7/1',
                        'expectedVal' => 1
                    ],
                    'record2' => [
                        'type' => 'value',
                        'elemNumber' => 1,
                        'paramValue' => '2/1',
                        'expectedVal' => 1
                    ],

                ],
                'criteria' => [
                    'orderColumn' => 5,
                    'dir' => 'asc'
                ],
                'errorMessage' => 'Expected the package list to be sorted by "price" in ASC direction.'
            ],
            [
                'expected' => [
                    'record1' => [
                        'type' => 'value',
                        'elemNumber' => 0,
                        'paramValue' => '12/1',
                        'expectedVal' => 1
                    ],
                    'record2' => [
                        'type' => 'value',
                        'elemNumber' => 1,
                        'paramValue' => '13/1',
                        'expectedVal' => 1
                    ],

                ],
                'criteria' => [
                    'orderColumn' => 5,
                    'dir' => 'desc'
                ],
                'errorMessage' => 'Expected the package list to be sorted by "price" in DESC direction.'
            ],
        ],

        'sort by created at' => [
            [
                'expected' => [
                    'record1' => [
                        'type' => 'value',
                        'elemNumber' => 0,
                        'paramValue' => '15/1',
                        'expectedVal' => 1
                    ],
                    'record2' => [
                        'type' => 'value',
                        'elemNumber' => 1,
                        'paramValue' => '12/1',
                        'expectedVal' => 1
                    ],

                ],
                'criteria' => [
                    'orderColumn' => 6,
                    'dir' => 'asc'
                ],
                'errorMessage' => 'Expected the package list to be sorted by "created at" in ASC direction.'
            ],
            [
                'expected' => [
                    'record1' => [
                        'type' => 'value',
                        'elemNumber' => 0,
                        'paramValue' => '2/1',
                        'expectedVal' => 1
                    ],
                    'record2' => [
                        'type' => 'value',
                        'elemNumber' => 1,
                        'paramValue' => '1/1',
                        'expectedVal' => 1
                    ],

                ],
                'criteria' => [
                    'orderColumn' => 6,
                    'dir' => 'desc'
                ],
                'errorMessage' => 'Expected the package list to be sorted by "created at" in DESC direction.'
            ],
        ]
    ];

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

    protected $newRecords = [];

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

    public function tearDown()
    {
        foreach ($this->newRecords as $record) {
            $this->dm->remove($record);
            $this->dm->flush();
        }
        $this->newRecords = [];
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

    protected function executeTest($testData)
    {
        $requestParams = self::REQUEST_DATA_COMMON;
        $requestParams = $this->getParameters($requestParams, $testData);
        $this->getRequest($requestParams);
        $this->assertRecords($testData['expected'], $testData['errorMessage']);
    }

    public function testIndexAction()
    {
        foreach (self::FILTERS_DATA as $test) {
            foreach ($test as $testData) {
                $this->executeTest($testData);
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
            'recordsTotal' => 1,
            'package_summary_total' => 2000,
            'package_summary_paid' => 0,
            'package_summary_debt' => 2000,
            'package_summary_nights' => 8,
            'package_summary_guests' => 2
        ];
        $this->assertRecords($expected, $errorMessage);

        $requestParams['roomType'] = $doubleRoom->getId();
        $this->getRequest($requestParams);
        $errorMessage = 'Expected the package list to be filtered by "begin date" minus 100 days to "today", "roomType" equals to "double room".';
        $expected = [
            'recordsTotal' => 15,
            'package_summary_total' => 127655,
            'package_summary_paid' => 0,
            'package_summary_debt' => 0,
            'package_summary_nights' => 77,
            'package_summary_guests' => 35
        ];
        $this->assertRecords($expected, $errorMessage);

    }

    public function testSellingSource()
    {
        $requestParams = self::REQUEST_DATA_COMMON;
        $requestParams['begin'] = new \DateTime('midnight');
        /**
         * @var PackageSource $online
         */
        $online = $this->dm->getRepository(PackageSource::class)->findOneBy([
            'code' => 'online',
        ]);

        /**
         * @var PackageSource $offline
         */
        $offline = $this->dm->getRepository(PackageSource::class)->findOneBy([
            'code' => 'offline',
        ]);

        $requestParams['source'] = $online->getId();
        $this->getRequest($requestParams);
        $errorMessage = 'Expected the package list to be filtered by "begin date" equals to "today", "source" equals to "online".';
        $expected = [
            'recordsTotal' => 2,
            'package_summary_total' => 27000,
            'package_summary_paid' => 0,
            'package_summary_debt' => 0,
            'package_summary_nights' => 13,
            'package_summary_guests' => 9
        ];
        $this->assertRecords($expected, $errorMessage);

        $requestParams['source'] = $offline->getId();
        $this->getRequest($requestParams);
        $errorMessage = 'Expected the package list to be filtered by "begin date" equals to "today", "source" equals to "manager".';
        $expected = [
            'recordsTotal' => 13,
            'package_summary_total' => 100655,
            'package_summary_paid' => 0,
            'package_summary_debt' => 0,
            'package_summary_nights' => 64,
            'package_summary_guests' => 26
        ];
        $this->assertRecords($expected, $errorMessage);

    }

    public function testSearching()
    {
        foreach (self::SEARCH_DATA as $test) {
            foreach ($test as $testData) {
                $requestParams = self::REQUEST_DATA_COMMON;
                $requestParams['begin'] = new \DateTime('-' . 100 . 'days');
                $requestParams['search']['value'] = $testData['criteria']['search'];
                $expected = $testData['expected'];
                $errorMessage = $testData['errorMessage'];
                $this->getRequest($requestParams);
                $this->assertRecords($expected, $errorMessage);
            }
        }
    }

    public function testSorting()
    {
        foreach (self::SORT_DATA as $test) {
            foreach ($test as $testData) {
                $requestParams = self::REQUEST_DATA_COMMON;
                $requestParams['order'][0]['column'] = $testData['criteria']['orderColumn'];
                $requestParams['order'][0]['dir'] = $testData['criteria']['dir'];
                $expected = $testData['expected'];
                $errorMessage = $testData['errorMessage'];
                $this->getRequest($requestParams);
                $this->assertRecords($expected, $errorMessage);
            }
        }
    }

    protected function assertRecords($expected, $errorMessage = "")
    {

        foreach ($expected as $key => $value) {
            if (is_array($value)) {
                switch ($value['type']) {
                    case 'value':
                        $response = json_decode($this->client->getResponse()->getContent(), true);
                        $expectedValue = $value['expectedVal'];
                        $assertParams['elemNumber'] = $value['elemNumber'];
                        $assertParams['paramValue'] = $value['paramValue'];
                        $actualValue = $this->getActualDataFromJson($response, $assertParams);
                        break;

                    case 'count':
                        $response = json_decode($this->client->getResponse()->getContent());
                        $expectedValue = $value['val'];
                        $actualValue = count($response->$key);
                        break;


                }
            } else {
                $response = json_decode($this->client->getResponse()->getContent());
                $expectedValue = $value;
                $actualValue = $this->clearNumber($response->$key);
            }
            $this->assertEquals(
                $expectedValue,
                $actualValue,
                $errorMessage
            );
        }
    }

    protected function getActualDataFromJson($response, $assertParams)
    {
        $crawler = new Crawler(implode($response['data'][$assertParams['elemNumber']]));
        return $crawler->filter('html:contains("' . $assertParams["paramValue"] . '")')->count();
    }

    protected function clearNumber($value)
    {
        if (!is_string($value)) {
            return $value;
        }
        return preg_replace('/,/', '', $value);
    }

    /**
     * @param ObjectManager $manager
     * @param $data
     * @return Order
     */
    public
    function persistOrder(ObjectManager $manager, $data)
    {
        $owner = $this->dm->getRepository(User::class)->findOneBy(['username' => $data['owner']]);

        $order = (new Order())
            ->setPrice($data['price'])
            ->setPaid($data['paid'])
            ->setStatus($data['status'] ?? 'offline')
            ->setTotalOverwrite($data['price'])
            ->setCreatedBy($owner)
            ->setCreatedAt((new \DateTime())->modify('-' . $data['regDayAgo'] . 'days'))
            ->setConfirmed($data['confirmed']);

        if ($owner) {
            $order->setOwner($owner);

        }
        $order->checkPaid();
        $manager->persist($order);
        $this->newRecords[] = $order;
        $manager->flush();

        return $order;
    }


    /**
     * @param ObjectManager $manager
     */
    public
    function persistPackage(ObjectManager $manager)
    {
        /** @var Tariff $tariff */
        $tariff = $this->dm->getRepository(Tariff::class)->findOneBy([
            'hotel' => $this->hotel,
            'isDefault' => true
        ]);


        foreach (self::FIXTURE_DATA as $packageData) {
            /** @var RoomType $roomType */
            $roomType = $this->dm->getRepository(RoomType::class)->findOneBy([
                'hotel' => $this->hotel,
                'places' => $packageData['rooms'],
            ]);
            $owner = $this->getOwner($packageData['owner'] ?? null);
            $order = $this->persistOrder($manager, $packageData);
            $tourist = $this->persistTourist($manager, $packageData);
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

            $package->addTourist($tourist);
            $tourist->addPackage($package);
            $tourist->addOrder($order);
            $order->addPackage($package);
            $order->setMainTourist($tourist);
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
//            $this->newRecords[] = $package;
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

    /**
     * @param ObjectManager $manager
     * @param $data
     * @return Tourist
     */
    private function persistTourist(ObjectManager $manager, $data)
    {
        $touristData = self::TOURIST_DATA[$data['tourist']];
        $locale = $this->container->getParameter('locale') === 'ru' ? 'ru' : 'en';

        $tourist = new Tourist();
        $tourist
            ->setFirstName($touristData['name'])
            ->setLastName($touristData['lastName'])
            ->setSex('male')
            ->setCommunicationLanguage($locale);

        if ($locale === 'ru') {
            $tourist->setPatronymic($touristData['patronymic']);
        }

        if (isset($touristData['email'])) {
            $tourist->setEmail($touristData['email']);
        }
        $manager->persist($tourist);
        $this->newRecords[] = $tourist;
        $manager->flush();
        return $tourist;
    }

}