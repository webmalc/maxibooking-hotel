<?php
/**
 * Created by PhpStorm.
 * User: mb3
 * Date: 02.03.18
 * Time: 16:13
 */

namespace Tests\Bundle\HotelBundle\Controller;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\Room;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @property Crawler $crawler
 */
class RoomTypeControllerTest extends WebTestCase
{
    private const NAME_TEST_HOTEL = 'Мой отель #1';

    private const URL_INDEX = '/management/hotel/roomtype';
    private const TABS_ITEM = 'ul[role="tablist"] > li';

    private const ROOM_TYPE_TAB_NAME_NUM2 = 'Двухместный';
    private const ROOM_TYPE_TAB_NAME_NUM3 = 'Трехместный';
    private const ROOM_TYPE_TAB_NAME_ADD_NEW = 'Новый тип номера';

    private const ROOM_TYPE_NEW_FULL_TITLE = 'RoomType_Name';
    private const ROOM_TYPE_NEW_TITLE = 'RoomType_InsideName';

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

    private const BUTTON_NAME_SAVE = 'button[name="save"]';

    private $facilities;
    private $hotelId;

    /** @var DocumentManager */
    private $dm;

    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->dm = $this->getDocumentManager();
        $this->facilities = $this->getRandomFacilities();
        $this->hotelId = $this->getHotelId();
    }

    private function getDocumentManager()
    {
        return $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
    }

    /**
     * @return mixed
     */
    private function getHotelId()
    {
        return $this->dm->getRepository('MBHHotelBundle:Hotel')
            ->findOneBy(['fullTitle' => self::NAME_TEST_HOTEL])
            ->getId();
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
                    'hotel.id'    => $this->hotelId,
                ]
            );
    }

    /**
     * Случайные удобства
     *
     * @return array
     */
    private function getRandomFacilities()
    {
        $facilities = $this->getContainer()->get('mbh.facility_repository')->getAllByGroup();

        $selectFacilities = [];

        /** выбор случайных значений */
        foreach ($facilities as $group => $facility) {
            $num = mt_rand(0, count($facility) - 1);
            $nameKey = array_keys($facility);
            $selectFacilities[] = $nameKey[$num];
        }

        return $selectFacilities;
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
                return self::URL_INDEX . '/' . $id . '/edit';
            case self::LINK_ADD_ROOM:
                return '/management/hotel/room/' . $id . '/new/';
            case self::LINK_GENERATE_ROOM:
                return '/management/hotel/room/' . $id . '/generate/';
            case self::LINK_DELETE_ROOM_TYPE:
                return self::URL_INDEX . '/' . $id . '/delete';
        }
        return false;
    }

    /**
     * @param $roomTypeId
     * @return string
     */
    private function getLinkRoom($roomTypeId)
    {
        return '/management/hotel/room/' . $roomTypeId . '/room';
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
                    'hotel.id'  => $this->hotelId,
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
            'no message: ' . $msg
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
        $this->client->followRedirects(true);

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
        $this->client->followRedirects(true);

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

        $arrayDb = [];
        /** @var Room $room */
        foreach ($this->getRooms($roomTypeId) as $room) {
            $arrayDb[$room->getId()] = $room->getFullTitle();
        }

        $this->assertCount(
            0,
            array_diff_assoc($arrayDb, $arrayTable)
        );
    }

    /**
     * проверяем статус ответа
     */
    public function testStatusCode()
    {
        $this->client->followRedirects(true);

        $this->getListCrawler(self::URL_INDEX);

        $this->assertEquals(
            200, // or Symfony\Component\HttpFoundation\Response::HTTP_OK
            $this->client->getResponse()->getStatusCode()
        );

    }

    /**
     * Тест страницы management/hotel/roomtype/
     * и наименования вкладок (tabs)
     */
    public function testIndexRoomType()
    {
        $this->client->followRedirects(true);

        $crawler = $this->getListCrawler(self::URL_INDEX);

        $tabs = $crawler->filter(self::TABS_ITEM);

        $this->assertCount(
            3,
            $tabs
        );

        $roomTypeIdNum2 = $this->getRoomType();
        $roomTypeIdNum3 = $this->getRoomType(self::ROOM_TYPE_TAB_NAME_NUM3);

        $linkNum2 = $tabs->eq(0)->filter('a');
        $linkNum3 = $tabs->eq(1)->filter('a');
        $linkAdd = $tabs->eq(2)->filter('a');


        $this->linkTest($linkNum2, self::ROOM_TYPE_TAB_NAME_NUM2, '#' . $roomTypeIdNum2);

        $this->linkTest($linkNum3, self::ROOM_TYPE_TAB_NAME_NUM3, '#' . $roomTypeIdNum3);

        $this->linkTest($linkAdd, self::ROOM_TYPE_TAB_NAME_ADD_NEW, self::URL_INDEX . '/new');
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

        $this->assertContains(
            (string)10,
            $list['recordsTotal']
        );
        /** порядковый номер записи для удаления*/
        $key = 5;
        $node = new Crawler($list['data'][$key][5]);
        $link = $node->filter('a.delete-link')->attr('href');
        $result = $this->client->request('GET', $link);

        $this->alertMsgTest('Запись успешно удалена', $result);

        $list = $this->getTableWithRooms($roomTypeId);

        $this->assertContains(
            (string)9,
            $list['recordsTotal']
        );
    }

    /**
     * редактирование записи комнаты
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

        $form = $result->filter('button[name="save_close"]')->form();

        $redirect = $this->client->submit($form);

        /** нажато Сохранить и Закрыть */
        $this->assertTrue(
            $redirect->getUri() == $this
                ->client
                ->getRequest()
                ->getUriForPath(self::URL_INDEX) . '/?tab=' . $roomTypeId
        );

        $list = $this->getTableWithRooms($roomTypeId);

        $node = new Crawler($list['data'][$key][1]);

        $this->assertNotContains(
            $nameOld,
            $node->filter('a')->text()
        );

        $this->linkTest($node->filter('a'), $nameNew, $link);
    }

    /**
     * Создание новой комнаты
     */
    public function testAddNewRoom()
    {
        $roomTypeId = $this->getRoomType();

        $this->client->followRedirects(true);

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
                    '5a99322bcb5f740de15d9b02',
                    '5a99322bcb5f740de15d9b0e',
                ],
            ]
        );
        $result = $this->client->submit($form);

        /** нажато Сохранить */
        $this->alertMsgTest('Комната успешно добавлена', $result);

        $form = $result->filter('button[name="save_close"]')->form();

        $redirect = $this->client->submit($form);

        /** нажато Сохранить и Закрыть */
        $this->assertTrue(
            $redirect->getUri() == $this
                ->client
                ->getRequest()
                ->getUriForPath(self::URL_INDEX) . '/?tab=' . $roomTypeId
        );

        $list = $this->getTableWithRooms($roomTypeId);

        $this->assertContains(
            (string)10,
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
     * Добавление нового типа
     */
    public function testAddNewRoomType()
    {
        $this->client->followRedirects(true);

        $crawler = $this->getListCrawler(self::URL_INDEX . '/new');

        $nameNew = self::ROOM_TYPE_NEW_FULL_TITLE;
        $nameInside = self::ROOM_TYPE_NEW_TITLE;
        $nameInternational = 'InternationalNameRoomType_' . $nameNew;

        $form = $crawler
            ->filter(self::BUTTON_NAME_SAVE)
            ->form(
                [
                    self::FORM_NAME_HOTEL_ROOM_TYPE_TYPE . '[fullTitle]'          => $nameNew,
                    self::FORM_NAME_HOTEL_ROOM_TYPE_TYPE . '[title]'              => $nameInside,
                    self::FORM_NAME_HOTEL_ROOM_TYPE_TYPE . '[internationalTitle]' => $nameInternational,
                    self::FORM_NAME_HOTEL_ROOM_TYPE_TYPE . '[description]'        => 'test',
                    self::FORM_NAME_HOTEL_ROOM_TYPE_TYPE . '[roomSpace]'          => 100500,
                ]
            );

        $form->setValues(
            [
                self::FORM_NAME_HOTEL_ROOM_TYPE_TYPE . '[facilities]' => $this->facilities,
            ]
        );

        /** нажато Сохранить */
        $result = $this->client->submit($form);
        $this->alertMsgTest('Новый тип номера успешно создан', $result);

        foreach ($result->filter('input[name="' . self::FORM_NAME_HOTEL_ROOM_TYPE_TYPE . '[facilities][]"]') as $input) {
            if (!in_array($input->nodeValue, $this->facilities)) {
                $this->assertTrue(false, 'No facilities');
            }
        }

        $crawler = $this->getListCrawler(self::URL_INDEX);

        $newTab = false;

        $tabs = $crawler->filter(self::TABS_ITEM);

        if ($tabs->filter('a:contains("' . $nameInside . '")')->count() > 0) {
            $newTab = true;
        }

        $this->assertTrue($newTab, 'no new tab');
    }


    /**
     *  редактирование нового типа
     */
    public function testEditRoomType()
    {
        $crawler = $this->getListCrawler(
            $this->getLinkAction(
                $this->getRoomType(self::ROOM_TYPE_NEW_FULL_TITLE),
                self::LINK_EDIT_ROOM_TYPE
            )
        );

        $newInsideName = self::ROOM_TYPE_NEW_TITLE . '_edit';

        $form = $crawler
            ->filter(self::BUTTON_NAME_SAVE)
            ->form(
                [
                    self::FORM_NAME_HOTEL_ROOM_TYPE_TYPE . '[title]' => $newInsideName,
                ]
            );

        $this->client->followRedirects(true);
        /** нажато Сохранить */
        $result = $this->client->submit($form);

        $this->alertMsgTest('Запись успешно отредактирована.', $result);

        $crawler = $this->getListCrawler(self::URL_INDEX);

        $newTab = false;

        $tabs = $crawler->filter(self::TABS_ITEM);

        if ($tabs->filter('a:contains("' . $newInsideName . '")')->count() > 0) {
            $newTab = true;
        }

        $this->assertTrue($newTab, 'no edit tab');
    }


    /**
     * показывать отключенные ввиды комнат
     */
    public function testEnabledRoomType()
    {
        $roomType = $this->getRoomType(self::ROOM_TYPE_NEW_FULL_TITLE, false);

        $roomType->setIsEnabled(false);
        $this->dm->flush($roomType);

        $crawler = $this->getListCrawler(self::URL_INDEX);
        $tabs = $crawler->filter(self::TABS_ITEM);

        $disabledTab = true;

        if ($tabs->filter('a:contains("' . self::ROOM_TYPE_NEW_TITLE . '_edit' . '")')->count() > 0) {
            $disabledTab = false;
        }

        $this->assertTrue($disabledTab, 'no "isEnabled" roomtype (tab)');

        $roomType->setIsEnabled(true);
        $this->dm->flush($roomType);
    }

    /**
     * удаление созданного RoomType
     */
    public function testDeleteRoomType()
    {
        $this->client->followRedirects(true);
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
}