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
    private const BASE_URL = '/price/room_cache/';
    private const NAME_TEST_HOTEL = 'Мой отель #1';

    private const FORM_NAME_GENERATION = 'mbh_bundle_pricebundle_room_cache_generator_type';

    /**
     * Количество записей в таблице после теста testGeneration, расчет:
     * первоначально 60 на два отеля.
     * в testGeneration создаётся 48 записей (поровну для двух- и трехместных номеров)
     * (60/2)+48
     */
    private const AMOUNT_ROOM_CACHE = 78;

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
        $dm = $this->getDocumentManager();

        $roomCache = $dm->getRepository('MBHPriceBundle:RoomCache');

        /** @var RoomCache $room */
        $room = $roomCache
            ->findOneByDate(
            /* this is current date  */
                new \DateTime('21:0:0 -1 day', new \DateTimeZone('UTC')),
                $this->getRoomType(2, true)
            );

        $amountRooms = count($roomCache->findAll());

        $this->client->request(
            'POST',
            self::BASE_URL . 'save',
            [
                'updateRoomCaches' => [
                    $room->getId() => [
                        'rooms' => 50,
                    ]
                ]
            ]);

        $this->assertEquals(['7', '14%', '43', '0%', '5'], $this->getResultFromTable());

        $this->assertCount($amountRooms, $roomCache->findAll());
    }

    public function testAddRoomCache()
    {
        $roomType = $this->getRoomType(2);

        $date = new \DateTime('noon +20 days');

        $dm = $this->getDocumentManager();

        $roomCache = $dm->getRepository('MBHPriceBundle:RoomCache');

        $amountRooms = count($roomCache->findAll());

        $this->client->request(
            'POST',
            self::BASE_URL . 'save',
            [
                'newRoomCaches' => [
                    $roomType => [
                        0 => [
                            $date->format('d.m.Y') => [
                                'rooms' => 60,
                            ]
                        ]
                    ]
                ]
            ]);

        $this->assertEquals(['0%', '60'], $this->getResultFromTable($date));

        $this->assertCount($amountRooms+1, $roomCache->findAll());
    }

    public function testInvalidDateInGeneration()
    {
        $form = $this->getGenerationForm();

        $form->setValues(
            [
                self::FORM_NAME_GENERATION . '[begin]' => (new \DateTime())->format('d.m.Y'),
                self::FORM_NAME_GENERATION . '[end]'   => (new \DateTime('-10 day'))->format('d.m.Y'),
                self::FORM_NAME_GENERATION . '[rooms]' => 1,
            ]
        );

        $this->client->submit($form);

        $this->assertValidationErrors(['data'], $this->client->getContainer());
    }

    public function testInvalidRoomsGeneration()
    {
        $form = $this->getGenerationForm();

        $form->setValues(
            [
                self::FORM_NAME_GENERATION . '[begin]'     => (new \DateTime('noon'))->modify('-2 day')->format('d.m.Y'),
                self::FORM_NAME_GENERATION . '[end]'       => (new \DateTime('noon'))->modify('+2 day')->format('d.m.Y'),
                self::FORM_NAME_GENERATION . '[rooms]'     => 201,
                self::FORM_NAME_GENERATION . '[weekdays]'  => range(0, 6),
                self::FORM_NAME_GENERATION . '[roomTypes]' => $this->getRoomType(),
            ]
        );

        $result = $this->client->submit($form);

        $this->assertGreaterThan(0, $result->filter('#messages')->count());
    }

    public function testGeneration()
    {
        $form = $this->getGenerationForm();

        $dateBegin = (new \DateTime('noon -2 day'))->format('d.m.Y');
        $dateEnd = (new \DateTime('noon +21 day'))->format('d.m.Y');

        $form->setValues(
            [
                self::FORM_NAME_GENERATION . '[begin]'     => $dateBegin,
                self::FORM_NAME_GENERATION . '[end]'       => $dateEnd,
                self::FORM_NAME_GENERATION . '[rooms]'     => 35,
                self::FORM_NAME_GENERATION . '[weekdays]'  => range(0, 6),
                self::FORM_NAME_GENERATION . '[roomTypes]' => $this->getRoomType(),
            ]
        );

        $result = $this->client->submit($form);

        $this->assertEquals(['7', '20%', '28', '0%', '35'], $this->getResultFromTable());
    }

    /**
     * @depends testAddRoomCache
     * @depends testGeneration
     */
    public function testTableAfterGeneration()
    {
        $dm = $this->getDocumentManager();

        $roomCache = $dm->getRepository('MBHPriceBundle:RoomCache');

        $this->assertCount(self::AMOUNT_ROOM_CACHE, $roomCache->findAll());
        $this->assertEquals(['0%', '35','0%','35'], $this->getResultFromTable(new \DateTime('noon +20 days')));
        $this->assertEquals([], $this->getResultFromTable(new \DateTime('noon -3 days')));
        $this->assertEquals([], $this->getResultFromTable(new \DateTime('noon +22 days')));
    }

    /**
     * @param null $places
     * @param bool $object
     * @return array|integer|RoomType
     */
    private function getRoomType($places = null, $retunObject = false)
    {
        $dm = $this->getDocumentManager();

        $hotelId = $dm->getRepository('MBHHotelBundle:Hotel')
            ->findOneBy(['fullTitle' => self::NAME_TEST_HOTEL])
            ->getId();

        $typeRooms = [];

        /** @var RoomType $typeRoom */
        foreach ($dm->getRepository('MBHHotelBundle:RoomType')->findBy(['hotel.id' => $hotelId]) as $typeRoom) {
            if ($places === null) {
                if ($retunObject) {
                    $typeRooms[] = $typeRoom;
                } else {
                    $typeRooms[] = $typeRoom->getId();
                }
            } else {
                if ($typeRoom->getPlaces() == $places) {
                    if ($retunObject) {
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
    private function getGenerationForm()
    {
        $crawler = $this->getListCrawler(self::BASE_URL . 'generator');

        return $crawler->filter('button[name="save_close"]')->form();
    }

    private function getDocumentManager()
    {
        return $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
    }


    /**
     * @param \DateTime|null $date
     * @return array
     */
    private function getResultFromTable(\DateTime $date = null)
    {
        $date = $date !== null ? $date : new \DateTime();

        $table = $this->getTable();

        /* для отладки */
//        self::putInFile($table, __METHOD__);
//        file_put_contents('/var/www/mbh/table_from_test_' . time() . '.html', $table->html());

        $td = $table->filter('td[data-id$="_' . $date->format('d.m.Y') . '"]');

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
     * @return Crawler
     */
    private function getTable(\DateTime $begin = null, \DateTime $end = null)
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
        $url .= '&roomTypes=&tariffs=';

        return $this->getListCrawler($url);
    }

    private static function putInFile($node, $method)
    {
        $rawName = explode('::', $method);

        $name = isset($rawName[1]) ? $rawName[1] : $method;

        $fileName = '/var/www/mbh/';
        $fileName .= time() . '_' . $name;
        $fileName .= '.html';
        file_put_contents($fileName, $node->html());
    }
}