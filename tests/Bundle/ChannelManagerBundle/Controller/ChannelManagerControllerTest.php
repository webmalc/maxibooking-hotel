<?php

namespace Tests\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\ChannelManagerBundle\Form\BookingRoomsType;
use MBH\Bundle\ChannelManagerBundle\Services\Airbnb\Airbnb;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\UserBundle\DataFixtures\MongoDB\UserData;
use Symfony\Component\DomCrawler\Crawler;
use Tests\Bundle\ChannelManagerBundle\Services\ChannelManagerServiceMock;

class ChannelManagerControllerTest extends WebTestCase
{
    const NUMBER_OF_ROOMS_IN_MOCK_CM = 2;
    const NUMBER_OF_TARIFFS_IN_MOCK_CM = 1;

    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    /**
     * @dataProvider channelManagersProvider
     * @param string $serviceName
     * @param $hasForm
     */
    public function testWizardInfoAction(string $serviceName, bool $hasForm)
    {
        $crawler = $this->client->request('GET', '/management/channelmanager/' . $serviceName . '/wizard_info');
        $this->assertEquals($this->client->getResponse()->getStatusCode(), 200);

        $instructionBlock = $crawler->filter('#channel-manager-instruction');
        $this->assertEquals(1, $instructionBlock->count());

        $this->assertEquals(0, $crawler->filter('#connection-request-sent-message')->count());
        $expectedMessage = $serviceName !== Airbnb::NAME
            ? 'Чтобы получить разрешение от системы бронирования'
            : 'Для настройки взаимодействия AirBnb и программы Максибукин';
        $this->assertContains($expectedMessage, $instructionBlock->text());

        $serviceHumanName = $this->getContainer()->get('mbh.channelmanager')->getServiceHumanName($serviceName);
        $this->assertEquals('1. Разрешение от "' . $serviceHumanName . '"', $this->getActiveTabCrawler($crawler)->text());

        $introFormCrawler = $crawler->filter('form[name="mbhchannel_manager_bundle_intro_type"]');
        $this->assertEquals((int) $hasForm, $introFormCrawler->count());

        if ($hasForm) {
            $introForm = $introFormCrawler->form(['mbhchannel_manager_bundle_intro_type[hotelId]' => 123]);
            $crawler = $this->client->submit($introForm);
            $this->assertEquals(0, $crawler->filter('form[name="mbhchannel_manager_bundle_intro_type"]')->count());
            $requestSentMessageBlock = $crawler->filter('#connection-request-sent-message');
            $this->assertContains(
                'Заявка отправлена, ожидайте сообщения технической поддержки',
                $requestSentMessageBlock->text()
            );
        } else {
            $nextStepButton = $crawler->filter('#next-step');
            $this->assertEquals(1, $nextStepButton->count());
            $this->client->click($nextStepButton->link());
            $indexCrawler = $this->client->followRedirect();

            if ($serviceName !== Airbnb::NAME) {
                $formName = $this->getIndexFormName($serviceName);
            } else {
                $indexCrawler = $this->client->followRedirect();
                $formName = 'mbhchannel_manager_bundle_airbnb_room_form';
            }

            $this->assertEquals(1, $indexCrawler->filter('form[name="' . $formName . '"]')->count());
        }
    }

    /**
     * @depends      testWizardInfoAction
     * @dataProvider channelManagersProvider
     * @param string $serviceName
     */
    public function testIndexAction(string $serviceName)
    {
        $crawler = $this->client->request('GET', '/management/channelmanager/' . $serviceName . '/');

        if ($serviceName !== Airbnb::NAME) {
            $this->assertEquals($this->client->getResponse()->getStatusCode(), 200);

            $formName = $this->getIndexFormName($serviceName);
            $indexFormCrawler = $crawler
                ->filter('form[name="' . $formName . '"]');
            $this->assertEquals(1, $indexFormCrawler->count());
            $indexForm = $indexFormCrawler
                ->form($this->getIndexFormData($serviceName, $formName));

            $this->client->submit($indexForm);

            if ($serviceName === 'myallocator') {
                $indexCrawler = $this->client->followRedirect();
                $indexForm = $indexCrawler
                    ->filter('form[name="' . $formName . '"]')
                    ->form([$formName . '[hotelId]' => 'ID1']);
                $this->client->submit($indexForm);
            }

            $this->client->followRedirect();
            $this->assertEquals('2. Основные настройки', $this->getActiveTabCrawler($crawler)->text());
        }

        $roomsCrawler = $this->client->followRedirect();
        $this->assertEquals(
            'http://localhost/management/channelmanager/' . $serviceName . '/' . 'room',
            $roomsCrawler->getUri()
        );
    }

    /**
     * @depends      testIndexAction
     * @dataProvider channelManagersProvider
     * @param string $serviceName
     */
    public function testRoomAction(string $serviceName)
    {
        $crawler = $this->client->request('GET', '/management/channelmanager/' . $serviceName . '/room');
        $roomsFormName = $this->getRoomsFormName($serviceName);
        $roomsFormCrawler = $crawler->filter('form[name="' . $roomsFormName . '"]');

        /** @var Hotel $hotel */
        $hotel = $this->getDefaultHotel();

        $roomTypes = $hotel->getRoomTypes();

        if ($serviceName !== Airbnb::NAME) {
            $roomsSelectsCrawler = $roomsFormCrawler->filter('select');
            $this->assertEquals(self::NUMBER_OF_ROOMS_IN_MOCK_CM, $roomsSelectsCrawler->count());
        } else {
            $roomInputsCrawler = $roomsFormCrawler->filter('input:not([type="hidden"])');
            $this->assertEquals(count($roomTypes), $roomInputsCrawler->count());
        }

        if ($serviceName !== Airbnb::NAME) {
            $roomsForm = $roomsFormCrawler->form(
                [
                    $this->getRoomFormName(
                        $roomsFormName,
                        ChannelManagerServiceMock::FIRST_ROOM_ID,
                        $serviceName
                    ) => $roomTypes[0]->getId(),
                    $this->getRoomFormName(
                        $roomsFormName,
                        ChannelManagerServiceMock::SECOND_ROOM_ID,
                        $serviceName
                    ) => $roomTypes[1]->getId(),
                ]
            );

            $activeTabText = '3. Типы номеров';
        } else {
            $roomsFormData = [];
            foreach ($roomTypes as $roomType) {
                $inputName = $this->getRoomFormName($roomsFormName, $roomType->getId(), $serviceName);
                $roomsFormData[$inputName] = Airbnb::SYNC_URL_BEGIN . $roomType->getId();
            }

            $roomsForm = $roomsFormCrawler->form($roomsFormData);
            $activeTabText = '2. Объекты размещения';
        }

        $this->assertEquals($activeTabText, $this->getActiveTabCrawler($crawler)->text());
        $this->client->submit($roomsForm);

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $tariffsCrawler = $this->client->followRedirect();
        $this->assertEquals(
            'http://localhost/management/channelmanager/' . $serviceName . '/' . ($serviceName !== Airbnb::NAME ? 'tariff' : 'room_links'),
            $tariffsCrawler->getUri()
        );
    }

    /**
     * @depends testRoomAction
     */
    public function testRoomLinksAction()
    {
        $crawler = $this->client->request('GET', '/management/channelmanager/airbnb/room_links');
        $roomLinksInstructionsCrawler = $crawler->filter('#room-links-instruction');
        $this->assertEquals(1, $roomLinksInstructionsCrawler->count());
        $this->assertContains('Для того, чтобы на AirBnb отображалась', $roomLinksInstructionsCrawler->text());
        $this->assertContains('3. Ссылки для Airbnb', $this->getActiveTabCrawler($crawler)->text());

        $this->client->submit($crawler->filter('#room-links-form')->form());
        $tariffsCrawler = $this->client->followRedirect();
        $this->assertEquals('http://localhost/management/channelmanager/airbnb/tariff', $tariffsCrawler->getUri());
    }

    /**
     * @depends      testRoomAction
     * @dataProvider channelManagersProvider
     * @param string $serviceName
     */
    public function testTariffAction(string $serviceName)
    {
        $crawler = $this->client->request('GET', '/management/channelmanager/' . $serviceName . '/tariff');
        $tariffsFormName = $this->getTariffsFormName($serviceName);
        $tariffsFormCrawler = $crawler->filter('form[name="' . $tariffsFormName . '"]');
        $tariffsSelectsCrawler = $tariffsFormCrawler->filter('select');
        $this->assertEquals(self::NUMBER_OF_TARIFFS_IN_MOCK_CM, $tariffsSelectsCrawler->count());
        $this->assertEquals('4. Тарифы', $this->getActiveTabCrawler($crawler)->text());

        /** @var Hotel $hotel */
        $hotel = $this->getDefaultHotel();

        $baseTariffId = $hotel->getBaseTariff()->getId();
        if ($serviceName !== Airbnb::NAME) {
            $roomsForm = $tariffsFormCrawler->form(
                [
                    $tariffsFormName . '[' . ChannelManagerServiceMock::FIRST_TARIFF_ID . ']' => $baseTariffId,
                ]
            );
        } else {
            $roomsForm = $tariffsFormCrawler->form(
                [
                    $tariffsFormName . '[tariff]' => $baseTariffId,
                ]
            );
        }
        $this->client->submit($roomsForm);

        $dataWarningsCrawler = $this->client->followRedirect();
        $this->assertEquals(
            'http://localhost/management/channelmanager/' . $serviceName . '/' . 'data_warnings',
            $dataWarningsCrawler->getUri()
        );
    }

    /**
     * @depends      testTariffAction
     * @dataProvider channelManagersProvider
     * @param string $serviceName
     */
    public function testDataWarnings(string $serviceName)
    {
        $crawler = $this->client->request('GET', '/management/channelmanager/' . $serviceName . '/data_warnings');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('5. Проверка данных', $this->getActiveTabCrawler($crawler)->text());

        $hotel = $this->getDefaultHotel();
        $lastCachesTableCrawler = $crawler->filter('#last-cashes-table');
        $this->assertEquals(1, $lastCachesTableCrawler->count());

        $numberOfConfiguredRooms = $serviceName === Airbnb::NAME
            ? $hotel->getRoomTypes()->count()
            : self::NUMBER_OF_ROOMS_IN_MOCK_CM;
        $this->assertEquals($numberOfConfiguredRooms, $lastCachesTableCrawler->filter('li.last-room-caches')->count());
        $this->assertEquals($numberOfConfiguredRooms, $lastCachesTableCrawler->filter('li.last-price-caches')->count());
    }

    private function getTariffsFormName(string $serviceName)
    {
        return $serviceName === Airbnb::NAME
            ? 'mbhchannel_manager_bundle_airbnb_tariff_type'
            : 'mbh_bundle_channelmanagerbundle_tariffs_type';
    }

    /**
     * @return iterable
     */
    public function channelManagersProvider(): iterable
    {
        $channelManagers = array_keys($this->getContainer()->getParameter('mbh.channelmanager.services'));
        $wizard = $this->getContainer()->get('mbh.cm_wizard_manager');

        foreach ($channelManagers as $cmName) {
            yield $cmName => [$cmName, $wizard->isConfiguredByTechSupport($cmName)];
        }
    }

    private function getRoomFormName(string $roomsFormName, string $id, string $serviceName)
    {
        return $roomsFormName . '['
            . ($serviceName === 'booking' ? BookingRoomsType::ROOM_TYPE_FIELD_PREFIX : '')
            . $id . ']';
    }

    private function getRoomsFormName(string $serviceName)
    {
        switch ($serviceName) {
            case 'booking':
                return 'mbh_bundle_channelmanagerbundle_booking_rooms_type';
            case Airbnb::NAME:
                return 'mbhchannel_manager_bundle_airbnb_room_form';

        }

        return 'mbh_bundle_channelmanagerbundle_rooms_type';
    }

    private function getIndexFormName(string $serviceName)
    {
        if (in_array($serviceName, ['expedia', 'ostrovok'])) {
            return 'mbhchannel_manager_bundle_channel_manager_config_type';
        }

        return "mbh_bundle_channelmanagerbundle_" . $serviceName . "_type";
    }

    /**
     * @param string $serviceName
     * @param string $formName
     * @return array
     */
    private function getIndexFormData(string $serviceName, string $formName)
    {
        switch ($serviceName) {
            case 'hundred_one_hotels':
                return [
                    $formName . '[apiKey]' => 1234,
                    $formName . '[hotelId]' => 125324,
                ];
            case 'vashotel':
                return [
                    $formName . '[password]' => 1234,
                    $formName . '[hotelId]' => 125324,
                ];
            case 'myallocator':
                return [
                    $formName . '[username]' => 'valera',
                    $formName . '[password]' => '1234123',
                ];
                break;
            case 'expedia':
            case 'booking':
            case 'ostrovok':
                return [
                    $formName . '[hotelId]' => 125324,
                ];

            default:
                throw new \InvalidArgumentException('Incorrect service name: ' . $serviceName);
        }
    }

    /**
     * @param Crawler $crawler
     * @return Crawler
     */
    private function getActiveTabCrawler(Crawler $crawler)
    {
        return $crawler->filter('div.nav-tabs-custom > ul.nav.nav-tabs > li.active');
    }

    /**
     * @return Hotel
     */
    private function getDefaultHotel()
    {
        return $this->getContainer()
            ->get('doctrine.odm.mongodb.document_manager')
            ->getRepository('MBHHotelBundle:Hotel')
            ->findOneBy(['isDefault' => true]);
    }
}