<?php
/**
 * Created by PhpStorm.
 * User: mb3
 * Date: 27.03.18
 * Time: 10:24
 */

namespace Tests\Bundle\PriceBundle\Controller;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use Symfony\Component\DomCrawler\Crawler;

class RoomCacheControllerTest extends WebTestCase
{
    private $currentDateForSearchRooms;
    private $hotelId;
    private $roomTypeCache;

    private const BASE_URL = '/price/room_cache/';
    private const NAME_TEST_HOTEL = 'Мой отель #1';
    private const SPECIAL_TARIFFS = 'Special tariff';

    private const FORM_NAME_GENERATION = 'mbh_bundle_pricebundle_room_cache_generator_type';

    private const SUNDAY = 0;
    private const TUESDAY = 2;
    private const THURSDAY = 4;

    private const TRIPLE_ROOM = 3;
    private const TWIN_ROOM = 2;

    private const NAME_FOR_UPDATE_ROOM_CACHES = 'updateRoomCaches';
    private const NAME_FOR_NEW_ROOM_CACHES = 'newRoomCaches';

    private const AMOUNT_ROOM_CACHE_DEFAULT = 60;

    /**
     * Количество записей в таблице после теста testGeneration, расчет:
     * первоначально 60 на два отеля.
     * в testGeneration создаётся 48 записей (поровну для двух- и трехместных номеров)
     * (60/2)+48
     */
    private const AMOUNT_ROOM_CACHE = self::AMOUNT_ROOM_CACHE_DEFAULT / 2 + 48;

    public static function setUpBeforeClass()
    {
        self::baseFixtures();
        self::command('mbh:cache:recalculate');
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
        $this->assertEquals(['7', '35%', '13', '0%', '5'], $this->getResultFromTable());
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

        $this->assertEquals(['7', '14%', '43', '0%', '5'], $this->getResultFromTable());

        $this->assertCount($amountRooms, $roomCache->findAll());
    }

    public function testAddRoomCache()
    {
        $roomType = $this->getRoomType(self::TWIN_ROOM);

        $date = new \DateTime('noon +20 days');

        $roomCache = $this->getRoomCache();

        $amountRooms = count($roomCache->findAll());

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

        $this->assertEquals(['0%', '60'], $this->getResultFromTable($date));

        $this->assertCount($amountRooms + 1, $roomCache->findAll());
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

        $this->assertEquals(['7', '20%', '28', '0%', '35'], $this->getResultFromTable());
    }

    /**
     * @depends testAddRoomCache
     * @depends testGeneration
     */
    public function testTableAfterGeneration()
    {
        $roomCache = $this->getRoomCache();

        $this->assertCount(self::AMOUNT_ROOM_CACHE, $roomCache->findAll());
        $this->assertEquals(['0%', '35', '0%', '35'], $this->getResultFromTable(new \DateTime('noon +20 days')));
        $this->assertEquals([], $this->getResultFromTable(new \DateTime('noon -3 days')));
        $this->assertEquals([], $this->getResultFromTable(new \DateTime('noon +22 days')));
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

        $this->assertEquals(['7', '0%', '-7'], $this->getResultFromTable());
        $this->assertEquals(['8', '22.86%', '27', '0%', '35'], $this->getResultFromTable(new \DateTime('noon +1 day')));
        $this->assertCount(self::AMOUNT_ROOM_CACHE - 1, $roomCache->findAll());
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
                $this->assertEquals(['0%', '15'], $this->getResultFromTable($date, self::TRIPLE_ROOM));
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

        $this->assertEquals([], $this->getResultFromTable(null, self::TRIPLE_ROOM));
        $this->assertEquals(['33', '0%', '33'], $this->getResultFromTable(null, self::TRIPLE_ROOM, [$this->getIdSpecialTariff()]));
    }

    /**
     * @return string
     */
    private function getIdSpecialTariff(): string
    {
        $dm = $this->getDocumentManager();

        return $dm->getRepository('MBHPriceBundle:Tariff')
            ->findOneBy(
                [
                    'hotel.id'  => $this->getHotelId(),
                    'fullTitle' => self::SPECIAL_TARIFFS,
                ]
            )->getId();
    }

    private function removeViaGenerationForRoomType(int $places)
    {
        $roomCache = $this->getRoomCache();

        $form = $this->getGenerationFormWithValues('-1', $places);

        $this->client->submit($form);

        switch ($places) {
            case self::TWIN_ROOM:
                $amountRoom = self::AMOUNT_ROOM_CACHE_DEFAULT - 15;
                break;
            case self::TRIPLE_ROOM:
                $amountRoom = self::AMOUNT_ROOM_CACHE - 24;
                break;
        }

        $this->assertEquals(['8', '22.86%', '27'], $this->getResultFromTable(new \DateTime('noon +1 day')));
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

        /* для отладки */
//        self::putInFile($table, __METHOD__);

        $td = $table->filter($selector);

        $result = [];
        foreach ($td->getIterator() as $element) {
            if (!empty(trim($element->nodeValue))) {
                $result[] = trim($element->nodeValue);
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

    private static function putInFile(Crawler $node, $method)
    {
        $rawName = explode('::', $method);

        $name = isset($rawName[1]) ? $rawName[1] : $method;

        $fileName = '/var/www/mbh/';
        $fileName .= time() . '_' . $name;
        $fileName .= '.html';
        file_put_contents($fileName, $node->html());
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
     * @return mixed
     */
    private function getHotelId(): string
    {
        if (empty($this->hotelId)) {
            $dm = $this->getDocumentManager();
            $this->hotelId = $dm->getRepository('MBHHotelBundle:Hotel')
                ->findOneBy(['fullTitle' => self::NAME_TEST_HOTEL])
                ->getId();
        }
        return $this->hotelId;
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
}