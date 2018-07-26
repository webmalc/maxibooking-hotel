<?php

namespace Tests\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\ChannelManagerBundle\Form\BookingRoomsType;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\UserBundle\DataFixtures\MongoDB\UserData;
use Tests\Bundle\ChannelManagerBundle\Services\ChannelManagerServiceMock;

class ChannelManagerControllerTest extends WebTestCase
{
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
        $this->client = self::makeClient(
            [
                'username' => UserData::MB_USER_USERNAME,
                'password' => $this->getContainer()->getParameter('mb_user_pwd'),
            ]
        );
    }

    /**
     * @dataProvider channelManagersProvider
     * @param string $serviceName
     * @param $hasForm
     */
    public function testWizardInfoAction(string $serviceName, bool $hasForm)
    {
        $crawler = $this->client->request('GET', '/management/channelmanager/'.$serviceName.'/wizard_info');
        $this->assertEquals($this->client->getResponse()->getStatusCode(), 200);

        $instructionBlock = $crawler->filter('#channel-manager-instruction');
        $this->assertEquals(1, $instructionBlock->count());

        $this->assertEquals(0, $crawler->filter('#connection-request-sent-message')->count());
        $expectedMessage = $this
            ->getContainer()
            ->get('translator')
            ->trans('cm_connection_instructions.part1.header');
        $this->assertContains($expectedMessage, $instructionBlock->text());

        $introFormCrawler = $crawler->filter('form[name="mbhchannel_manager_bundle_intro_type"]');
        $this->assertEquals(intval($hasForm), $introFormCrawler->count());

        if ($hasForm) {
            if ($serviceName === 'hundred_one_hotels') {
                $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
                $hotel = $dm->getRepository('MBHHotelBundle:Hotel')->findOneBy(['isDefault' => true]);
                $hotel->setCityId(12);
                $hotel->setRegionId(13);
                $hotel->setCountryTld('ru');
                $dm->flush();
            }

            $introForm = $introFormCrawler->form(['mbhchannel_manager_bundle_intro_type[hotelId]' => 123]);
            $crawler = $this->client->submit($introForm);
            $this->assertEquals(0, $crawler->filter('form[name="mbhchannel_manager_bundle_intro_type"]')->count());
            $requestSentMessageBlock = $crawler->filter('#connection-request-sent-message');
            $this->assertContains(
                'Заявка отправлена, ожидайте сообщения тех. поддержки',
                $requestSentMessageBlock->text()
            );
        } else {
            $confirmButton = $crawler->filter('#confirm-config-button');
            $this->assertEquals(1, $confirmButton->count());
            $this->client->click($confirmButton->link());
            $indexCrawler = $this->client->followRedirect();

            $formName = $this->getIndexFormName($serviceName);
            $this->assertEquals(1, $indexCrawler->filter('form[name="'.$formName.'"]')->count());
        }
    }

    /**
     * @depends      testWizardInfoAction
     * @dataProvider channelManagersProvider
     * @param string $serviceName
     * @param bool $hasForm
     */
    public function testIndexAction(string $serviceName, bool $hasForm)
    {
        $crawler = $this->client->request('GET', '/management/channelmanager/'.$serviceName.'/');

        $this->assertEquals($this->client->getResponse()->getStatusCode(), 200);

        $formName = $this->getIndexFormName($serviceName);
        $indexFormCrawler = $crawler
            ->filter('form[name="'.$formName.'"]');
        $this->assertEquals(1, $indexFormCrawler->count());
        $indexForm = $indexFormCrawler
            ->form($this->getIndexFormData($serviceName, $formName));

        $this->client->submit($indexForm);
        if ($hasForm) {
            $crawler = $this->client->followRedirect();
            $confirmButton = $crawler->filter('#confirm-config-button');
            $this->client->click($confirmButton->link());
        }

        $this->client->followRedirect();

        $roomsCrawler = $this->client->followRedirect();

        $this->assertEquals(
            'http://localhost/management/channelmanager/'.$serviceName.'/'.'room',
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
        $crawler = $this->client->request('GET', '/management/channelmanager/'.$serviceName.'/room');
        $roomsFormName = $this->getRoomsFormName($serviceName);
        $roomsFormCrawler = $crawler->filter('form[name="'.$roomsFormName.'"]');
        $roomsSelectsCrawler = $roomsFormCrawler->filter('select');
        $this->assertEquals(2, $roomsSelectsCrawler->count());

        $hotel = $this->getContainer()
            ->get('doctrine.odm.mongodb.document_manager')
            ->getRepository('MBHHotelBundle:Hotel')
            ->findOneBy(['isDefault' => true]);

        $roomTypes = $hotel->getRoomTypes();

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
        $this->client->submit($roomsForm);

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $tariffsCrawler = $this->client->followRedirect();
        $this->assertEquals(
            'http://localhost/management/channelmanager/'.$serviceName.'/'.'tariff',
            $tariffsCrawler->getUri()
        );
    }

    /**
     * @depends      testRoomAction
     * @dataProvider channelManagersProvider
     * @param string $serviceName
     */
    public function testTariffAction(string $serviceName)
    {
        $crawler = $this->client->request('GET', '/management/channelmanager/'.$serviceName.'/tariff');
        $tariffsFormName = 'mbh_bundle_channelmanagerbundle_tariffs_type';
        $tariffsFormCrawler = $crawler->filter('form[name="'.$tariffsFormName.'"]');
        $tariffsSelectsCrawler = $tariffsFormCrawler->filter('select');
        $this->assertEquals(1, $tariffsSelectsCrawler->count());

        /** @var Hotel $hotel */
        $hotel = $this->getContainer()
            ->get('doctrine.odm.mongodb.document_manager')
            ->getRepository('MBHHotelBundle:Hotel')
            ->findOneBy(['isDefault' => true]);

        $tariffs = $hotel->getTariffs();

        $roomsForm = $tariffsFormCrawler->form(
            [
                $tariffsFormName.'['.ChannelManagerServiceMock::FIRST_TARIFF_ID.']' => $tariffs[0]->getId(),
            ]
        );
        $this->client->submit($roomsForm);
    }

    /**
     * @return array
     */
    public function channelManagersProvider()
    {
        $channelManagers = array_keys($this->getContainer()->getParameter('mbh.channelmanager.services'));

        return array_map(
            function (string $cmName) {
                return [
                    $cmName,
                    $this->getContainer()->get('mbh.cm_wizard_manager')->isConfiguredByTechSupport($cmName),
                ];
            },
            $channelManagers
        );
    }

    private function getRoomFormName(string $roomsFormName, string $id, string $serviceName)
    {
        return $roomsFormName.'['
            .($serviceName === 'booking' ? BookingRoomsType::ROOM_TYPE_FIELD_PREFIX : '')
            .$id.']';
    }

    private function getRoomsFormName(string $serviceName)
    {
        return $serviceName === 'booking' ? 'mbh_bundle_channelmanagerbundle_booking_rooms_type' : 'mbh_bundle_channelmanagerbundle_rooms_type';
    }

    private function getIndexFormName(string $serviceName)
    {
        if (in_array($serviceName, ['expedia', 'ostrovok'])) {
            return 'mbhchannel_manager_bundle_channel_manager_config_type';
        }

        return "mbh_bundle_channelmanagerbundle_".$serviceName."_type";
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
                    $formName.'[apiKey]' => 1234,
                    $formName.'[hotelId]' => 125324,
                ];
            case 'vashotel':
                return [
                    $formName.'[password]' => 1234,
                    $formName.'[hotelId]' => 125324,
                ];
            case 'myallocator':
                return [
                    $formName.'[username]' => 'valera',
                    $formName.'[password]' => '1234123',
                ];
                break;
            case 'expedia':
            case 'booking':
            case 'ostrovok':
                return [
                    $formName.'[hotelId]' => 125324,
                ];

            default:
                throw new \InvalidArgumentException('Incorrect service name: '.$serviceName);
        }
    }
}