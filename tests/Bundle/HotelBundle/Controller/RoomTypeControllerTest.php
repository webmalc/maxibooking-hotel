<?php
/**
 * Created by PhpStorm.
 * User: mb3
 * Date: 02.03.18
 * Time: 16:13
 */

namespace Tests\Bundle\HotelBundle\Controller;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Test\Traits\HotelIdTestTrait;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\Room;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @property Crawler $crawler
 */
class RoomTypeControllerTest extends WebTestCase
{
    use HotelIdTestTrait;

    private const URL_INDEX = '/management/hotel/roomtype/';
    private const TABS_ITEM = 'ul[role="tablist"] > li';

    private const ROOM_TYPE_TAB_NAME_NUM2 = 'Стандартный двухместный';
    private const ROOM_TYPE_TAB_NAME_NUM3 = 'Люкс';
    private const ROOM_TYPE_TAB_NAME_ADD_NEW = 'Новый тип номера';

    private const ROOM_TYPE_NEW_FULL_TITLE = 'RoomType_FullTitle';
    private const ROOM_TYPE_NEW_TITLE = 'RoomType_Title';

    private const ROOM_GENERATE_FROM = 39;
    private const ROOM_GENERATE_TO = 46;
    private const ROOM_GENERATE_PREFIX = 'TSTRM';

    private const LINK_EDIT_ROOM_TYPE = 0;
    private const LINK_ADD_ROOM = 1;
    private const LINK_GENERATE_ROOM = 2;
    private const LINK_DELETE_ROOM_TYPE = 3;

    private const LINK_NAME = [
        self::LINK_EDIT_ROOM_TYPE   => 'Редактировать',
        self::LINK_ADD_ROOM         => 'Добавить Номер',
        self::LINK_GENERATE_ROOM    => 'Сгенерировать Номера',
        self::LINK_DELETE_ROOM_TYPE => 'Удалить',
    ];

    private const FORM_NAME_HOTEL_ROOM_TYPE_TYPE = 'mbh_bundle_hotelbundle_room_type_type';
    private const FORM_NAME_HOTEL_ROOM_TYPE = 'mbh_bundle_hotelbundle_room_type';
    private const FORM_NAME_HOTEL_ROOMS_GENERATE = 'mbh_bundle_hotelbundle_room_type_generate_rooms_type';

    private const BUTTON_NAME_SAVE = 'button[name="save"]';
    private const BUTTON_NAME_SAVE_CLOSE = 'button[name="save_close"]';

    private $facilities;


    /** Число записей возращаемые getTableWithRooms() */
    private const DEFAULT_RECORDS_TOTAL = 10;

    /** @var DocumentManager */
    private $dm;

    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public function setUp()
    {
        parent::setUp();

        $this->dm = $this->getDocumentManager();
        $this->client->followRedirects();
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    /**
     * проверяем статус ответа
     */
    public function testStatusCode()
    {
        $this->getListCrawler(self::URL_INDEX);

        $this->assertStatusCode(
            200, // or Symfony\Component\HttpFoundation\Response::HTTP_OK
            $this->client
        );

    }

    /**
     * Тест страницы management/hotel/roomtype/
     * и наименования вкладок (tabs)
     */
    public function testIndexRoomType()
    {
        $crawler = $this->getListCrawler(self::URL_INDEX);

        $tabs = $crawler->filter(self::TABS_ITEM);

        $this->assertCount(
            4, // кол-во вкладок с типами номеров
            $tabs
        );

        $roomTypeIdNum2 = $this->getRoomType();
        $roomTypeIdNum3 = $this->getRoomType(self::ROOM_TYPE_TAB_NAME_NUM3);

        $linkNum2 = $tabs->eq(1)->filter('a');
        $linkNum3 = $tabs->eq(0)->filter('a');
        $linkAdd = $tabs->eq(3)->filter('a');


        $this->linkTest($linkNum2, self::ROOM_TYPE_TAB_NAME_NUM2, '#' . $roomTypeIdNum2);

        $this->linkTest($linkNum3, self::ROOM_TYPE_TAB_NAME_NUM3, '#' . $roomTypeIdNum3);

        $this->linkTest($linkAdd, self::ROOM_TYPE_TAB_NAME_ADD_NEW, self::URL_INDEX . 'new');
    }

    /**
     *  Вкладка Двухместный, ссылки
     */
    public function testLinkTabNum2()
    {
        $this->tabLinksTest($this->getRoomType());
    }

    /**
     *  Вкладка Трехместный, ссылки
     */
    public function testLinkTabNum3()
    {
        $this->tabLinksTest($this->getRoomType(self::ROOM_TYPE_TAB_NAME_NUM3));
    }

    /**
     * тест формата ответа (json)
     */
    public function testResponseTableWithRooms()
    {
        $roomTypeId = $this->getRoomType();
        $this->getTableWithRooms($roomTypeId);

        $this->assertTrue(
            $this->client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
    }

    /**
     * данные двухместных номеров
     */
    public function testDataNum2()
    {
        $this->diffArrayAjaxVsArrayDbTest($this->getRoomType());
    }

    /**
     * данные трехместных номеров
     */
    public function testDataNum3()
    {
        $this->diffArrayAjaxVsArrayDbTest($this->getRoomType(self::ROOM_TYPE_TAB_NAME_NUM3));
    }

    /**
     * удаление комнаты
     */
    public function testTableRoomDelete()
    {
        $roomTypeId = $this->getRoomType();

        $list = $this->getTableWithRooms($roomTypeId);

        $this->assertEquals(
            self::DEFAULT_RECORDS_TOTAL,
            $list['recordsTotal']
        );
        /** порядковый номер записи для удаления*/
        $key = 5;
        $node = new Crawler($list['data'][$key][5]);
        $link = $node->filter('a.delete-link')->attr('href');
        $result = $this->client->request('GET', $link);

        $this->alertMsgTest('Запись успешно удалена', $result);

        $list = $this->getTableWithRooms($roomTypeId);

        $this->assertEquals(
            self::DEFAULT_RECORDS_TOTAL - 1,
            $list['recordsTotal']
        );
    }

    /**
     * редактирование записи комнаты
     *
     * @depends testTableRoomDelete
     */
    public function testTableRoomEdit()
    {
        $roomTypeId = $this->getRoomType();

        $list = $this->getTableWithRooms($roomTypeId);

        /** порядковый номер записи для редактирования*/
        $key = 8;

        $node = new Crawler($list['data'][$key][1]);
        $nameOld = $node->filter('a')->text();
        $link = $node->filter('a')->attr('href');
        $formEdit = $this->client->request('GET', $link);

        $nameNew = time();
        $form = $formEdit
            ->filter(self::BUTTON_NAME_SAVE)
            ->form(
                [
                    self::FORM_NAME_HOTEL_ROOM_TYPE . '[fullTitle]' => $nameNew,
                ]
            );

        /** нажато Сохранить */
        $result = $this->client->submit($form);
        $this->alertMsgTest('Запись успешно отредактирована', $result);

        $form = $result->filter(self::BUTTON_NAME_SAVE_CLOSE)->form();

        $redirect = $this->client->submit($form);

        /** нажато Сохранить и Закрыть */
        $this->assertTrue(
            $redirect->getUri() == $this
                ->client
                ->getRequest()
                ->getUriForPath(self::URL_INDEX) . '?tab=' . $roomTypeId
        );

        $list = $this->getTableWithRooms($roomTypeId);

        $node = new Crawler($list['data'][$key][1]);

        $this->assertNotEquals(
            $nameOld,
            $node->filter('a')->text()
        );

        $this->linkTest($node->filter('a'), $nameNew, $link);
    }

    /**
     * Создание новой комнаты
     *
     * @depends testTableRoomEdit
     */
    public function testAddNewRoom()
    {
        $viewsType = $this->dm->getRepository('MBHHotelBundle:RoomViewType')->findAll();
        $amountItems = count($viewsType);

        $roomTypeId = $this->getRoomType();

        $crawler = $this->getListCrawler($this->getLinkAction($roomTypeId, self::LINK_ADD_ROOM));

        $nameNew = time();
        $nameInside = 'InsideName_' . $nameNew;
        $form = $crawler
            ->filter(self::BUTTON_NAME_SAVE)
            ->form(
                [
                    self::FORM_NAME_HOTEL_ROOM_TYPE . '[fullTitle]' => $nameNew,
                    self::FORM_NAME_HOTEL_ROOM_TYPE . '[title]'     => $nameInside,
                    self::FORM_NAME_HOTEL_ROOM_TYPE . '[floor]'     => 100500,
                    self::FORM_NAME_HOTEL_ROOM_TYPE . '[isSmoking]' => true,
                ]
            );
        $form->setValues(
            [
                self::FORM_NAME_HOTEL_ROOM_TYPE . '[roomViewsTypes]' => [
                    $viewsType[mt_rand(0, $amountItems - 1)]->getId(),
                    $viewsType[mt_rand(0, $amountItems - 1)]->getId(),
                ],
            ]
        );
        $result = $this->client->submit($form);

        /** нажато Сохранить */
        $this->alertMsgTest('Комната успешно добавлена', $result);

        $form = $result->filter(self::BUTTON_NAME_SAVE_CLOSE)->form();

        $redirect = $this->client->submit($form);

        /** нажато Сохранить и Закрыть */
        $this->assertTrue(
            $redirect->getUri() == $this
                ->client
                ->getRequest()
                ->getUriForPath(self::URL_INDEX) . '?tab=' . $roomTypeId
        );

        $list = $this->getTableWithRooms($roomTypeId);

        $this->assertEquals(
            self::DEFAULT_RECORDS_TOTAL,
            $list['recordsTotal']
        );

        /** проверяем в последнем элементе имя */
        $item = new Crawler(array_pop($list['data'])[1]);

        $this->assertContains(
            $nameInside,
            $item->filter('a')->text()
        );
    }

    /**
     * создани номеров через генератор
     *
     * @depends testAddNewRoom
     */
    public function testGenerateRoomsAdd()
    {
        $roomTypeId = $this->getRoomType();

        $crawler = $this->getListCrawler($this->getLinkAction($roomTypeId, self::LINK_GENERATE_ROOM));

        /* invalid form */
        $form = $crawler
            ->filter(self::BUTTON_NAME_SAVE_CLOSE)
            ->form(
                [
                    self::FORM_NAME_HOTEL_ROOMS_GENERATE . '[from]' => self::ROOM_GENERATE_TO,
                    self::FORM_NAME_HOTEL_ROOMS_GENERATE . '[to]'   => self::ROOM_GENERATE_FROM,
                ]
            );

        $result = $this->client->submit($form);
        $this->assertValidationErrors(['data'], $this->client->getContainer());

        /* valid form */
        $form = $result
            ->filter(self::BUTTON_NAME_SAVE_CLOSE)
            ->form(
                [
                    self::FORM_NAME_HOTEL_ROOMS_GENERATE . '[from]'   => self::ROOM_GENERATE_FROM,
                    self::FORM_NAME_HOTEL_ROOMS_GENERATE . '[to]'     => self::ROOM_GENERATE_TO,
                    self::FORM_NAME_HOTEL_ROOMS_GENERATE . '[prefix]' => self::ROOM_GENERATE_PREFIX,
                ]
            );

        $result = $this->client->submit($form);
        $this->alertMsgTest('Номера успешно сгенерированы', $result);

        $list = $this->getTableWithRooms($roomTypeId);

        $this->assertEquals(
            self::DEFAULT_RECORDS_TOTAL + (self::ROOM_GENERATE_TO - self::ROOM_GENERATE_FROM + 1),
            $list['recordsTotal']
        );
    }

    /**
     * повтроное создание с такими же данными
     * проверка на отутствие перезаписи
     *
     * @depends testGenerateRoomsAdd
     */
    public function testGenerateRoomAgain()
    {
        $roomTypeId = $this->getRoomType();

        $arrayDbBefore = $this->getRoomsAsArray($roomTypeId);

        $crawler = $this->getListCrawler($this->getLinkAction($roomTypeId, self::LINK_GENERATE_ROOM));

        $form = $crawler
            ->filter(self::BUTTON_NAME_SAVE_CLOSE)
            ->form(
                [
                    self::FORM_NAME_HOTEL_ROOMS_GENERATE . '[from]'   => self::ROOM_GENERATE_FROM,
                    self::FORM_NAME_HOTEL_ROOMS_GENERATE . '[to]'     => self::ROOM_GENERATE_TO,
                    self::FORM_NAME_HOTEL_ROOMS_GENERATE . '[prefix]' => self::ROOM_GENERATE_PREFIX,
                ]
            );

        $this->client->submit($form);

        $arrayDbAfter = $this->getRoomsAsArray($roomTypeId);

        $this->assertCount(
            0,
            array_diff_assoc($arrayDbBefore, $arrayDbAfter)
        );
    }

    public function testInvalidDataForAddNewRoom()
    {
        $crawler = $this->getListCrawler(self::URL_INDEX . 'new');

        $form = $crawler
            ->filter(self::BUTTON_NAME_SAVE)
            ->form();

        $result = $this->client->submit($form);

        $this->assertStatusCode(200, $this->client);
        $this->assertValidationErrors(['data.fullTitle'], $this->client->getContainer());
    }

    /**
     * Добавление нового типа
     */
    public function testAddNewRoomType()
    {
        $crawler = $this->getListCrawler(self::URL_INDEX . 'new');

        $nameNew = self::ROOM_TYPE_NEW_FULL_TITLE;

        $form = $crawler
            ->filter(self::BUTTON_NAME_SAVE)
            ->form(
                [
                    self::FORM_NAME_HOTEL_ROOM_TYPE_TYPE . '[fullTitle]' => $nameNew,
                ]
            );

        /** нажато Сохранить */
        $result = $this->client->submit($form);
        $this->alertMsgTest('Новый тип номера успешно создан', $result);

        $crawler = $this->getListCrawler(self::URL_INDEX);
        $newTab = false;
        $tabs = $crawler->filter(self::TABS_ITEM);

        if ($tabs->filter('a:contains("' . $nameNew . '")')->count() > 0) {
            $newTab = true;
        }

        $this->assertTrue($newTab, 'not found new tab');
    }

    public function getDataForEditRoomType(): array
    {
        $test_1 = self::ROOM_TYPE_NEW_FULL_TITLE . '_editFullTitle';
        $test_2 = self::ROOM_TYPE_NEW_TITLE . '_editTitle';

        $data = [
            'edit fullTitle'                                        =>
                [
                    [
                        'forUrl'  => self::ROOM_TYPE_NEW_FULL_TITLE,
                        'tabName' => $test_1,
                        'form'    => [
                            self::FORM_NAME_HOTEL_ROOM_TYPE_TYPE . '[fullTitle]' => $test_1,
                        ],
                    ],
                ],
            'change fullTitle on default, add title'                =>
                [
                    [
                        'forUrl'  => $test_1,
                        'tabName' => $test_2,
                        'form'    => [
                            self::FORM_NAME_HOTEL_ROOM_TYPE_TYPE . '[fullTitle]' => self::ROOM_TYPE_NEW_FULL_TITLE,
                            self::FORM_NAME_HOTEL_ROOM_TYPE_TYPE . '[title]'     => $test_2,
                        ],
                    ],
                ],
            'change title on default'                               =>
                [
                    [
                        'forUrl'  => self::ROOM_TYPE_NEW_FULL_TITLE,
                        'tabName' => self::ROOM_TYPE_NEW_TITLE,
                        'form'    => [
                            self::FORM_NAME_HOTEL_ROOM_TYPE_TYPE . '[title]' => self::ROOM_TYPE_NEW_TITLE,
                        ],
                    ],
                ],
            'edit: internationalTitle; add: description, roomSpace' =>
                [
                    [
                        'forUrl'  => self::ROOM_TYPE_NEW_FULL_TITLE,
                        'tabName' => self::ROOM_TYPE_NEW_TITLE,
                        'form'    => [
                            self::FORM_NAME_HOTEL_ROOM_TYPE_TYPE . '[internationalTitle]' => 'Intourist',
                            self::FORM_NAME_HOTEL_ROOM_TYPE_TYPE . '[description]'        => 'hello world!',
                            self::FORM_NAME_HOTEL_ROOM_TYPE_TYPE . '[roomSpace]'          => 100500,
                        ],
                    ],
                ],
        ];

        return $data;
    }

    /**
     *  редактирование
     *
     * @depends      testAddNewRoomType
     * @dataProvider getDataForEditRoomType
     */
    public function testEditRoomType(array $data)
    {
        $crawler = $this->getListCrawler(
            $this->getLinkAction(
                $this->getRoomType($data['forUrl']),
                self::LINK_EDIT_ROOM_TYPE
            )
        );

        $form = $crawler
            ->filter(self::BUTTON_NAME_SAVE)
            ->form($data['form']);

        /** нажато Сохранить */
        $result = $this->client->submit($form);
        $this->alertMsgTest('Запись успешно отредактирована.', $result);

        /**
         * проверим что все новые значения формы сохранились
         */
        foreach ($data['form'] as $name => $value) {
            $formValue = $result->filter('[name="' . $name . '"]')->attr('value');
            if ($formValue === null) {
                /** проверка textarea */
                $formValue = $result->filter('[name="' . $name . '"]')->text();
            }
            $this->assertEquals($value, $formValue, 'Fields "' . $name . '" - not save new value');
        }

        /**
         * проверим что название таба равно:
         *  если есть title то title иначе fullTitle
         */
        $crawler = $this->getListCrawler(self::URL_INDEX);
        $tabs = $crawler->filter(self::TABS_ITEM);

        $this->assertGreaterThan(
            0,
            $tabs->filter('a:contains("' . $data['tabName'] . '")')->count(),
            sprintf('Not found tab with name "%s"', $data['tabName'])
        );
    }

    /**
     * @depends testEditRoomType
     */
    public function testAddFacilities()
    {
        $srcFacilities = $this->getFacilities();

        $crawler = $this->getListCrawler(
            $this->getLinkAction(
                $this->getRoomType(self::ROOM_TYPE_NEW_FULL_TITLE),
                self::LINK_EDIT_ROOM_TYPE
            )
        );

        $form = $crawler->filter(self::BUTTON_NAME_SAVE)->form();

        $form->setValues(
            [
                self::FORM_NAME_HOTEL_ROOM_TYPE_TYPE . '[facilities]' => $srcFacilities,
            ]
        );

        $result = $this->client->submit($form);

        $this->alertMsgTest('Запись успешно отредактирована.', $result);

        $rawFacilities = $result->filter('[name="' . self::FORM_NAME_HOTEL_ROOM_TYPE_TYPE . '[facilities][]"] option:selected');
        $facilities = [];

        foreach ($rawFacilities as $node) {
            $facilities[] = $node->getAttribute('value');
        }

        $this->assertEquals(
            $facilities,
            $srcFacilities,
            'Amount saved facilities not equals facilities amount from source'
        );
    }

    /**
     * показывать отключенные ввиды комнат
     *
     * @depends testEditRoomType
     */
    public function testEnabledRoomType()
    {
        $roomType = $this->getRoomType(self::ROOM_TYPE_NEW_FULL_TITLE, false);

        $roomType->setIsEnabled(false);

        $this->getContainer()->get('mbh.client_config_manager')->changeDisableableMode(true);
        $this->dm->flush($roomType);

        $crawler = $this->getListCrawler(self::URL_INDEX);
        $tabs = $crawler->filter(self::TABS_ITEM);

        $disabledTab = true;

        if ($tabs->filter('a:contains("' . self::ROOM_TYPE_NEW_TITLE . '_edit' . '")')->count() > 0) {
            $disabledTab = false;
        }

        $this->assertTrue($disabledTab, 'you can see tabs with attribute IsEnabled set in False');

        $roomType->setIsEnabled(true);
        $this->dm->flush($roomType);
    }

    /**
     * удаление созданного RoomType
     *
     * @depends testAddNewRoomType
     */
    public function testDeleteRoomType()
    {
        $crawler = $this->getListCrawler(
            $this->getLinkAction(
                $this->getRoomType(self::ROOM_TYPE_NEW_FULL_TITLE),
                self::LINK_DELETE_ROOM_TYPE
            )
        );

        $tabs = $crawler->filter(self::TABS_ITEM);

        $deleteRoomType = true;

        if ($tabs->filter('a:contains("' . self::ROOM_TYPE_NEW_TITLE . '_edit' . '")')->count() > 0) {
            $deleteRoomType = false;
        }

        if (!empty($this->getRoomType(self::ROOM_TYPE_NEW_FULL_TITLE, false))) {
            $deleteRoomType = false;
        }

        $this->assertTrue($deleteRoomType, 'no remove RoomType');
    }

    public function testShortCreateAction()
    {
        $this->getListCrawler(self::URL_INDEX . 'short_create/' . $this->getHotelId());
        $this->isSuccessful($this->client->getResponse(), true, 'application/json');
        $decodedResponse = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($decodedResponse['status']);
    }

    private function getDocumentManager()
    {
        return $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
    }

    /**
     * @param $roomTypeId
     * @return array|Room[]
     */
    private function getRooms($roomTypeId)
    {
        return $this->dm->getRepository('MBHHotelBundle:Room')
            ->findBy(
                [
                    'roomType.id' => $roomTypeId,
                    'hotel.id'    => $this->getHotelId(),
                ]
            );
    }

    /**
     * @param $roomTypeId
     * @return array
     */
    private function getRoomsAsArray($roomTypeId)
    {
        $arr = [];

        /** @var Room $room */
        foreach ($this->getRooms($roomTypeId) as $room) {
            $arr[$room->getId()] = $room->getFullTitle();
        }

        return $arr;
    }

    /**
     * Случайные удобства
     *
     * @return array
     */
    private function getFacilities()
    {
        if (empty($this->facilities)) {
            $facilities = $this->getContainer()->get('mbh.facility_repository')->getAllGrouped();

            $selectFacilities = [];

            /** выбор случайных значений */
            foreach ($facilities as $group => $facility) {
                $num = mt_rand(0, count($facility) - 1);
                $nameKey = array_keys($facility);
                $selectFacilities[] = $nameKey[$num];
            }

            $this->facilities = $selectFacilities;
        }

        return $this->facilities;
    }

    /**
     * Получение ссылки
     *
     * @param $id
     * @return string|bool
     */
    private function getLinkAction($id, $linkType)
    {
        switch ($linkType) {
            case self::LINK_EDIT_ROOM_TYPE:
                return self::URL_INDEX . $id . '/edit';
            case self::LINK_ADD_ROOM:
                return '/management/hotel/room/' . $id . '/new/';
            case self::LINK_GENERATE_ROOM:
                return '/management/hotel/room/' . $id . '/generate/';
            case self::LINK_DELETE_ROOM_TYPE:
                return self::URL_INDEX . $id . '/delete';
        }

        return false;
    }

    /**
     * @param $roomTypeId
     * @return string
     */
    private function getLinkRoom($roomTypeId)
    {
        return '/management/hotel/room/' . $roomTypeId . '/room/';
    }

    /**
     * @param string $fullTitle
     * @param bool $returnId
     * @return \MBH\Bundle\HotelBundle\Document\RoomType|null|object
     */
    private function getRoomType($fullTitle = self::ROOM_TYPE_TAB_NAME_NUM2, $returnId = true)
    {
        $roomTypeId = $this->dm->getRepository('MBHHotelBundle:RoomType')
            ->findOneBy(
                [
                    'fullTitle' => $fullTitle,
                    'hotel.id'  => $this->getHotelId(),
                ]
            );

        if ($returnId) {
            return $roomTypeId->getId();
        }

        return $roomTypeId;
    }


    /**
     * Сообщение о результате действия (flash)
     *
     * @param $msg
     * @param Crawler $element
     */
    private function alertMsgTest($msg, Crawler $element)
    {
        $this->assertContains(
            $msg,
            $element->filter('#messages')->text(),
            sprintf('Message "%s" - not found', $msg)
        );
    }

    /**
     * Проверяем ссылки имя и адрес
     *
     * @param Crawler $link
     * @param $name
     * @param $href
     */
    private function linkTest(Crawler $link, $name, $href)
    {
        $this->assertContains(
            (string)$name,
            $link->text(),
            'link name is not ' . $name
        );

        $this->assertContains(
            $href,
            $link->attr('href'),
            'link href is not ' . $href
        );
    }

    /**
     *  тест для ссылок:
     *  - Редактировать
     *  - Добавить Номер
     *  - Сгенерировать Номера
     *  - Удалить
     */
    private function tabLinksTest($roomTypeId)
    {
        $tab = $this->getListCrawler(self::URL_INDEX)->filter('#' . $roomTypeId);

        $this->assertContains(
            $roomTypeId,
            $tab->attr('id')
        );

        $links = $tab->filter('a');

        $this->assertCount(
            4,
            $links
        );

        $this->linkTest(
            $links->eq(self::LINK_EDIT_ROOM_TYPE),
            self::LINK_NAME[self::LINK_EDIT_ROOM_TYPE],
            $this->getLinkAction($roomTypeId, self::LINK_EDIT_ROOM_TYPE)
        );

        $this->linkTest(
            $links->eq(self::LINK_ADD_ROOM),
            self::LINK_NAME[self::LINK_ADD_ROOM],
            $this->getLinkAction($roomTypeId, self::LINK_ADD_ROOM)
        );

        $this->linkTest(
            $links->eq(self::LINK_GENERATE_ROOM),
            self::LINK_NAME[self::LINK_GENERATE_ROOM],
            $this->getLinkAction($roomTypeId, self::LINK_GENERATE_ROOM)
        );

        $this->linkTest(
            $links->eq(self::LINK_DELETE_ROOM_TYPE),
            self::LINK_NAME[self::LINK_DELETE_ROOM_TYPE],
            $this->getLinkAction($roomTypeId, self::LINK_DELETE_ROOM_TYPE)
        );
    }

    /**
     * таблица с номерами
     *
     * @param $roomTypeId
     * @return mixed
     */
    private function getTableWithRooms($roomTypeId)
    {
        $this->getListCrawler($this->getLinkRoom($roomTypeId));

        return json_decode($this->client->getResponse()->getContent(), true);
    }


    /**
     * сравниваем данные из аякс запроса (получение номеров) и ДБ
     *
     * @param $roomTypeId
     */
    private function diffArrayAjaxVsArrayDbTest($roomTypeId)
    {
        $list = $this->getTableWithRooms($roomTypeId);

        $arrayTable = [];

        foreach ($list['data'] as $value) {
            if (preg_match('@<a href=\'/management/hotel/room/(.*)/edit\'.*>(.*)</a>@', $value[1], $matches)) {
                $arrayTable[$matches[1]] = $matches[2];
            }
        }

        $this->assertCount(
            0,
            array_diff_assoc($this->getRoomsAsArray($roomTypeId), $arrayTable)
        );
    }
}