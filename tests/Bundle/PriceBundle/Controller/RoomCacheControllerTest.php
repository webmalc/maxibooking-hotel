<?php
/**
 * Created by PhpStorm.
 * User: mb3
 * Date: 27.03.18
 * Time: 10:24
 */

namespace Tests\Bundle\PriceBundle\Controller;


use MBH\Bundle\BaseBundle\Lib\Test\Traits\HotelIdTestTrait;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\DataFixtures\MongoDB\RoomCacheData;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use Symfony\Component\DomCrawler\Crawler;

class RoomCacheControllerTest extends WebTestCase
{
    use HotelIdTestTrait;

    private $currentDateForSearchRooms;
    private $roomTypeCache;
    private $tariffs;

    private static $amountRoomCacheDefault;

    private const BASE_URL = '/price/room_cache/';
    private const SPECIAL_TARIFFS = 'Special tariff';

    private const FORM_NAME_GENERATION = 'mbh_bundle_pricebundle_room_cache_generator_type';

    private const SUNDAY = 0;
    private const TUESDAY = 2;
    private const THURSDAY = 4;

    private const TRIPLE_ROOM = 3;
    private const TWIN_ROOM = 2;

    private const NAME_FOR_UPDATE_ROOM_CACHES = 'updateRoomCaches';
    private const NAME_FOR_NEW_ROOM_CACHES = 'newRoomCaches';

    private const AMOUNT_TWIN_ROOMS = 10;

    private const AMOUNT_TRIPLE_ROOMS = 10;

    public static function setUpBeforeClass()
    {
        self::baseFixtures();
        self::command('mbh:cache:recalculate');
        self::setDefaultAmountRooms();
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    public function testStatusCode()
    {
        $this->getListCrawler(self::BASE_URL);

        $this->assertStatusCode(
            200, // or Symfony\Component\HttpFoundation\Response::HTTP_OK
            $this->client
        );
    }

    public function testStatusCodeTable()
    {
        $this->getTable();

        $this->assertStatusCode(
            200, // or Symfony\Component\HttpFoundation\Response::HTTP_OK
            $this->client
        );
    }

    /**
     * @depends testStatusCodeTable
     */
    public function testDefaultTable()
    {
        $this->assertEquals(['0', '0%', '10', '7', '70%', '3', '0', '0%', '10'], $this->getResultFromTable());
    }

    /**
     * @depends testStatusCodeTable
     */
    public function testChangeTable()
    {
        $roomCache = $this->getRoomCache();

        /** @var RoomCache $room */
        $room = $roomCache
            ->findOneByDate(
                $this->getCurrentDateForSearchRooms(),
                $this->getRoomType(self::TWIN_ROOM, true)
            );

        $amountRooms = count($roomCache->findAll());

        $this->client->request(
            'POST',
            self::BASE_URL . 'save',
            [
                self::NAME_FOR_UPDATE_ROOM_CACHES => [
                    $room->getId() => [
                        'rooms' => 50,
                    ],
                ],
            ]
        );

        $this->assertEquals(['0', '0%', '10', '7', '14%', '43', '0', '0%', '10'], $this->getResultFromTable());

        $this->assertCount($amountRooms, $roomCache->findAll());
    }

    public function testAddRoomCache()
    {
        $roomType = $this->getRoomType(self::TWIN_ROOM);

        $date = new \DateTime('noon -3 days');

        $roomCache = $this->getRoomCache();

        $this->client->request(
            'POST',
            self::BASE_URL . 'save',
            [
                self::NAME_FOR_NEW_ROOM_CACHES => [
                    $roomType => [
                        0 => [
                            $date->format('d.m.Y') => [
                                'rooms' => 60,
                            ],
                        ],
                    ],
                ],
            ]
        );

        $this->assertEquals(['0', '0%', '60',], $this->getResultFromTable($date));

        $this->assertCount($this->getDefaultAmountRoomCache() + 1, $roomCache->findAll());
    }

    public function testInvalidDateInGeneration()
    {
        $form = $this->getGenerationFormWithValues(1, null, [], new \DateTime(), new \DateTime('-10 day'));

        $this->client->submit($form);

        $this->assertValidationErrors(['data'], $this->client->getContainer());
    }

    public function testInvalidRoomsGeneration()
    {
        $form = $this->getGenerationFormWithValues(500);

        $result = $this->client->submit($form);

        $this->assertGreaterThan(0, $result->filter('#messages')->count());
    }

    public function testGeneration()
    {
        $form = $this->getGenerationFormWithValues(35);

        $this->client->submit($form);

        $this->assertEquals(['0', '0%', '35', '7', '20%', '28', '0', '0%', '35'], $this->getResultFromTable());
    }

    /**
     * @depends testAddRoomCache
     * @depends testGeneration
     */
    public function testTableAfterGeneration()
    {
        $roomCache = $this->getRoomCache();

        $this->assertCount($this->getAmountRoomCache(), $roomCache->findAll());
        $this->assertEquals(
            ['0', '0%', '35', '0', '0%', '35', '0', '0%', '35'],
            $this->getResultFromTable(new \DateTime('noon -1 days'))
        );
        $this->assertEquals([], $this->getResultFromTable(new \DateTime('noon -4 days')));
        $this->assertEquals([], $this->getResultFromTable(new \DateTime('noon +7 month')));
    }

    /**
     * @depends testTableAfterGeneration
     */
    public function testRemoveSingle()
    {
        $roomCache = $this->getRoomCache();

        /** @var $twigRoomToday $room */
        $twigRoomToday = $roomCache
            ->findOneBy(
                [
                    'roomType.id' => $this->getRoomType(self::TWIN_ROOM),
                    'date'        => $this->getCurrentDateForSearchRooms(),
                ]
            );

        /** @var $tripleRoomToday $room */
        $tripleRoomToday = $roomCache
            ->findOneBy(
                [
                    'roomType.id' => $this->getRoomType(self::TRIPLE_ROOM),
                    'date'        => $this->getCurrentDateForSearchRooms(),
                ]
            );
        $dateTomorrow = (clone $this->getCurrentDateForSearchRooms())->modify('+1 day');

        /** @var $twigRoomTomorrow $room */
        $twigRoomTomorrow = $roomCache
            ->findOneBy(
                [
                    'roomType.id' => $this->getRoomType(self::TWIN_ROOM),
                    'date'        => $dateTomorrow,
                ]
            );

        $this->client->request(
            'POST',
            self::BASE_URL . 'save',
            [
                self::NAME_FOR_UPDATE_ROOM_CACHES => [
                    $twigRoomToday->getId()    => [
                        'rooms' => '0',
                    ],
                    $twigRoomTomorrow->getId() => [
                        'rooms' => '',
                    ],
                    $tripleRoomToday->getId()  => [
                        'rooms' => '',
                    ],
                ],
            ]
        );

        $this->assertEquals(['7', '0%', '-7', '0', '0%', '35'], $this->getResultFromTable());
        $this->assertEquals(
            ['0', '0%', '35', '8', '22.86%', '27', '0', '0%', '35'],
            $this->getResultFromTable(new \DateTime('noon +1 day'))
        );
        $this->assertCount($this->getAmountRoomCache() - 1, $roomCache->findAll());
    }

    /**
     * @depends testRemoveSingle
     */
    public function testRemoveViaGenerationTripleRooms()
    {
        $this->removeViaGenerationForRoomType(self::TRIPLE_ROOM);
    }

    /**
     * @depends testRemoveViaGenerationTripleRooms
     */
    public function testRemoveViaGenerationTwigRooms()
    {
        $this->removeViaGenerationForRoomType(self::TWIN_ROOM);
    }

    public function testGenerationWeekdays()
    {
        $begin = new \DateTime('noon -3 days');
        $end = new \DateTime('noon +3 days');

        $dateRange = new \DatePeriod($begin, new \DateInterval('P1D'), (clone $end)->modify('+1 day'));

        $form = $this->getGenerationFormWithValues(
            15,
            self::TRIPLE_ROOM,
            [self::SUNDAY, self::TUESDAY, self::THURSDAY],
            $begin,
            $end
        );

        $this->client->submit($form);

        $count = 0;
        /** @var \DateTime $date */
        foreach ($dateRange as $date) {
            if (in_array((int)$date->format('w'), [self::SUNDAY, self::TUESDAY, self::THURSDAY], true)) {
                $this->assertEquals(['0', '0%', '15'], $this->getResultFromTable($date, self::TRIPLE_ROOM));
                $count++;
            }
        }

        $this->assertEquals(3, $count);
    }

    /**
     * @depends testRemoveViaGenerationTripleRooms
     */
    public function testGenerationQuotas()
    {
        $form = $this->getGenerationFormWithValues(
            33,
            self::TRIPLE_ROOM,
            [],
            null,
            null,
            [$this->getIdSpecialTariff()]
        );

        $this->client->submit($form);

        $today = new \DateTime();
        /** эта проверка нужна т.к. в тесте testGenerationWeekdays генерятся данные на вт, чт, вс */
        if (in_array((int)$today->format('w'), [self::SUNDAY, self::TUESDAY, self::THURSDAY], true)) {
            $this->assertEquals(['0', '0%', '15'], $this->getResultFromTable(null, self::TRIPLE_ROOM));
            $this->assertEquals(
                ['0', '0%', '33'],
                $this->getResultFromTable(null, self::TRIPLE_ROOM, [$this->getIdSpecialTariff()])
            );
        } else {
            $this->assertEquals([], $this->getResultFromTable(null, self::TRIPLE_ROOM));
            $this->assertEquals(
                ['33', '0', '0%', '33'],
                $this->getResultFromTable(null, self::TRIPLE_ROOM, [$this->getIdSpecialTariff()])
            );
        }
    }

    public function testGraphActionStatusCode()
    {
        self::setUpBeforeClass();

        $this->getListCrawler($this->getUrlGraphAction());

        $this->assertStatusCode(
            200, // or Symfony\Component\HttpFoundation\Response::HTTP_OK
            $this->client
        );
    }

    /**
     * @depends testGraphActionStatusCode
     */
    public function testGraphActionEmptyData()
    {
        $this->assertEquals(['0', '0', '0', '0', '0', '0',], $this->getResultGraphAction(new \DateTime('noon -1 days')));
        $this->assertEquals(
            ['0', '0', '0', '0', '0', '0',],
            $this->getResultGraphAction(new \DateTime(sprintf('noon +%s month', RoomCacheData::AMOUNT_MONTH + 1)))
        );
    }

    /**
     * @depends testGraphActionStatusCode
     */
    public function testGraphActionCurrentDate()
    {
        $this->assertEquals(
            ['3', '3', '0', '3', '0', '3',],
            $this->getResultGraphAction(null, $this->getTariffs(), self::TWIN_ROOM, true)
        );

        $this->assertEquals(
            ['10', '10', '0', '10', '0', '10',],
            $this->getResultGraphAction(null, $this->getTariffs(), self::TRIPLE_ROOM, true)
        );
    }

    /**
     * @depends testGraphActionStatusCode
     */
    public function testGraphActionPlusFourDays()
    {
        $this->assertEquals(
            ['5', '-1', '5', '0', '8', '-3',],
            $this->getResultGraphAction(
                new \DateTime('noon +4 days'),
                $this->getTariffs(),
                self::TWIN_ROOM,
                true
            )
        );

        $this->assertEquals(
            ['10', '0', '10', '0',],
            $this->getResultGraphAction(
                new \DateTime('noon +4 days'),
                [$this->getIdSpecialTariff()],
                self::TRIPLE_ROOM,
                true
            )
        );
    }

    /**
     * @depends testGraphActionStatusCode
     */
    public function testGraphActionPlusSevenDays()
    {
        $this->assertEquals(
            ['8', '0', '3', '5', '8', '0',],
            $this->getResultGraphAction(
                new \DateTime('noon +7 days'),
                $this->getTariffs(),
                self::TWIN_ROOM,
                true
            )
        );

        $this->assertEquals(
            ['10', '0', '0', '10',],
            $this->getResultGraphAction(
                new \DateTime('noon +7 days'),
                [$this->getIdSpecialTariff()],
                self::TRIPLE_ROOM,
                true
            )
        );
    }

    /**
     * @param \DateTime|null $date
     * @param array $tariff
     * @param int|null $places
     * @param bool $onlyAnalytics
     * @return array
     */
    private function getResultGraphAction(
        \DateTime $date = null,
        array $tariff = [],
        int $places = null,
        $onlyAnalytics = false
    ): array
    {
        $date = $date === null ? new \DateTime('noon') : $date;

        $this->client->request('GET', self::BASE_URL);

        $crawler = $this->getListCrawler($this->getUrlGraphAction($date, $date, [], $tariff));

        $selector = '';

        if ($places !== null) {
            $selector .= $selectorTable = 'table[data-room-type-id="' . $this->getRoomType($places) . '"] ';
        }
        $selector .= $selectorDate = 'td[data-date="' . $date->format('d.m.Y') . '"]';
        $conditionTariff = $tariff !== [] && count($tariff) == 1;
        if ($conditionTariff) {
            $selector .= '[data-tariff-id="' . array_values($tariff)[0] . '"]';
            if ($onlyAnalytics) {
                $selector = $selectorTable . $selectorDate . '[data-tariff-id="common"] , ' . $selector;
            }
        }

        $td = $crawler->filter($selector);

        $result = $this->getResultFromIterator($td);

        if ($onlyAnalytics && !$conditionTariff) {
            if ($places === self::TWIN_ROOM) {
                $result = array_slice($result, self::AMOUNT_TWIN_ROOMS);
            } elseif ($places === self::TRIPLE_ROOM) {
                $result = array_slice($result, self::AMOUNT_TRIPLE_ROOMS);
            }
        }
        return $result;
    }

    /**
     * @param \DateTime|null $begin
     * @param \DateTime|null $end
     * @param array $roomTypes
     * @param array $tariffs
     * @return string
     */
    private function getUrlGraphAction(
        \DateTime $begin = null,
        \DateTime $end = null,
        array $roomTypes = [],
        array $tariffs = []
    ): string
    {
        $begin = $begin === null ? new \DateTime('noon -2 day') : $begin;
        $end = $end === null ? new \DateTime('noon + 16 days') : $end;

        $url = '/price/room_cache/graph?';
        $url .= 'begin=' . $begin->format('d.m.Y');
        $url .= '&end=' . $end->format('d.m.Y');

        if ($roomTypes !== []) {
            foreach ($roomTypes as $roomType) {
                $url .= '&roomTypes[]=' . $roomType;
            }
        }

        if ($tariffs !== []) {
            foreach ($tariffs as $tariff) {
                $url .= '&tariffs[]=' . $tariff;
            }
        }

        return $url;
    }

    /**
     * @return string
     */
    private function getIdSpecialTariff(): string
    {
        return $this->getTariffs()[self::SPECIAL_TARIFFS];
    }

    /**
     * @return array
     */
    private function getTariffs(): array
    {
        if (empty($this->tariffs)) {
            $dm = $this->getDocumentManager();
            $tariffs = $dm->getRepository('MBHPriceBundle:Tariff')
                ->findBy(['hotel.id' => $this->getHotelId()]);
            foreach ($tariffs as $tariff) {
                $this->tariffs[$tariff->getFullTitle()] = $tariff->getId();
            }
        }
        return $this->tariffs;
    }

    private function removeViaGenerationForRoomType(int $places)
    {
        $date = new \DateTime('noon +1 day');

        $roomCache = $this->getRoomCache();

        $form = $this->getGenerationFormWithValues('-1', $places);

        $this->client->submit($form);

        switch ($places) {
            case self::TWIN_ROOM:
                $amountRoom = $this->getDefaultAmountRoomCache() - 23;
                break;
            case self::TRIPLE_ROOM:
                $amountRoom = $this->getDefaultAmountRoomCache() - 17;
                break;
        }

        $this->assertEquals(['8', '22.86%', '27', '0', '0%', '35'], $this->getResultFromTable($date));
        $this->assertCount($amountRoom, $roomCache->findAll());
    }

    /**
     * @param int|null $places
     * @param bool $returnObject
     * @return array|RoomType|string
     */
    private function getRoomType(int $places = null, $returnObject = false)
    {
        if ($places !== null && !in_array($places, [self::TWIN_ROOM, self::TRIPLE_ROOM], true)) {
            throw new \LogicException('Needed true roomType');
        }

        $typeRooms = [];

        /** @var RoomType $typeRoom */
        foreach ($this->getRoomTypeCache() as $typeRoom) {
            if ($places === null) {
                if ($returnObject) {
                    $typeRooms[] = $typeRoom;
                } else {
                    $typeRooms[] = $typeRoom->getId();
                }
            } else {
                if ($typeRoom->getPlaces() == $places) {
                    if ($returnObject) {
                        $typeRooms = $typeRoom;
                    } else {
                        $typeRooms = $typeRoom->getId();
                    }
                }
            }
        }

        return $typeRooms;
    }

    /**
     * @return \Symfony\Component\DomCrawler\Form
     */
    private function getGenerationForm(): \Symfony\Component\DomCrawler\Form
    {
        $crawler = $this->getListCrawler(self::BASE_URL . 'generator');

        return $crawler->filter('button[name="save_close"]')->form();
    }

    /**
     * @return \Doctrine\ODM\MongoDB\DocumentManager
     */
    private function getDocumentManager(): \Doctrine\ODM\MongoDB\DocumentManager
    {
        return $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
    }

    /**
     * @param \DateTime|null $date
     * @param null|int $place
     * @param array $tariffs
     * @return array
     */
    private function getResultFromTable(\DateTime $date = null, int $places = null, array $tariffs = []): array
    {
        $date = $date !== null ? $date : new \DateTime();

        $selector = 'td[data-id';
        if ($places === null) {
            $selector .= '$="';
        } else {
            $selector .= '="' . $this->getRoomType($places);
        }
        $selector .= '_' . $date->format('d.m.Y') . '"]';

        $table = $this->getTable(null, null, $tariffs);

        $td = $table->filter($selector);

        return $this->getResultFromIterator($td);
    }

    /**
     * @param Crawler $td
     * @return array
     */
    private function getResultFromIterator(Crawler $td): array
    {
        $result = [];
        foreach ($td->getIterator() as $element) {
            $value = trim($element->nodeValue);
            if ($value != '') {
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * @param \DateTime|null $begin
     * @param \DateTime|null $end
     * @param array $tariffs
     * @return Crawler
     */
    private function getTable(\DateTime $begin = null, \DateTime $end = null, array $tariffs = []): Crawler
    {
        if ($begin === null) {
            $begin = new \DateTime('noon -25 day');
        }
        if ($end === null) {
            $end = new \DateTime('noon +25 day');
        }

        $url = self::BASE_URL . 'table?';
        $url .= 'begin=' . $begin->format('d.m.Y');
        $url .= '&end=' . $end->format('d.m.Y');
        $url .= '&roomTypes=';

        if ($tariffs !== []) {
            foreach ($tariffs as $tariff) {
                $url .= '&tariffs[]=' . $tariff;
            }
        }

        return $this->getListCrawler($url);
    }

    /**
     * Текущее время для поиска номеров в БД
     *
     * @return \DateTime
     */
    private function getCurrentDateForSearchRooms(): \DateTime
    {
        if (empty($this->currentDateForSearchRooms)) {
            $this->currentDateForSearchRooms = new \DateTime('21:0:0 -1 day', new \DateTimeZone('UTC'));
        }
        return $this->currentDateForSearchRooms;
    }

    /**
     * @param $rooms
     * @param null|int $places
     * @param array $weekdays
     * @param \DateTime|null $dateBegin
     * @param \DateTime|null $dateEnd
     * @param array $tariffs
     * @return \Symfony\Component\DomCrawler\Form
     */
    private function getGenerationFormWithValues(
        $rooms,
        int $places = null,
        array $weekdays = [],
        \DateTime $dateBegin = null,
        \DateTime $dateEnd = null,
        array $tariffs = []
    ): \Symfony\Component\DomCrawler\Form
    {
        if ($weekdays === []) {
            $weekdays = range(self::SUNDAY, 6);
        }

        if ($dateBegin === null) {
            $dateBegin = new \DateTime('noon -2 day');
        }

        if ($dateEnd === null) {
            $dateEnd = new \DateTime('noon +21 day');
        }


        $form = $this->getGenerationForm();

        $form->setValues(
            [
                self::FORM_NAME_GENERATION . '[begin]'     => $dateBegin->format('d.m.Y'),
                self::FORM_NAME_GENERATION . '[end]'       => $dateEnd->format('d.m.Y'),
                self::FORM_NAME_GENERATION . '[rooms]'     => $rooms,
                self::FORM_NAME_GENERATION . '[weekdays]'  => $weekdays,
                self::FORM_NAME_GENERATION . '[roomTypes]' => $this->getRoomType($places),
                self::FORM_NAME_GENERATION . '[tariffs]'   => $tariffs,
            ]
        );
        return $form;
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository|\MBH\Bundle\PriceBundle\Document\RoomCacheRepository
     */
    private function getRoomCache()
    {
        $dm = $this->getDocumentManager();

        return $dm->getRepository('MBHPriceBundle:RoomCache');
    }

    /**
     * @return array
     */
    private function getRoomTypeCache(): array
    {
        if (empty($this->roomTypeCache)) {
            $dm = $this->getDocumentManager();
            $this->roomTypeCache = $dm
                ->getRepository('MBHHotelBundle:RoomType')
                ->findBy(['hotel.id' => $this->getHotelId()]);
        }
        return $this->roomTypeCache;
    }

    private static function setDefaultAmountRooms(): void
    {
        $container = self::getContainerStat();

        $dm = $container->get('doctrine.odm.mongodb.document_manager');

        $dm->getRepository('MBHPriceBundle:RoomCache');

        $roomCache = $dm->getRepository('MBHPriceBundle:RoomCache');
        self::$amountRoomCacheDefault = count($roomCache->findAll());
    }

    /**
     * @return int
     */
    private function getDefaultAmountRoomCache(): int
    {
        return self::$amountRoomCacheDefault;
    }

    /**
     * +1 in testAddRoomCache
     * +6 это комнаты созданые в testGeneration
     *
     * @return int
     */
    private function getAmountRoomCache(): int
    {
        return $this->getDefaultAmountRoomCache() + 1 + 6;
    }
}