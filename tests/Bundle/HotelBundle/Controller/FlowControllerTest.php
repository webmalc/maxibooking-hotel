<?php

namespace Tests\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\FlowConfig;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Form\RoomTypeFlow\RoomTypeFlow;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use Symfony\Component\DomCrawler\Crawler;

class FlowControllerTest extends WebTestCase
{
    const FLOW_BASE_URL = '/management/hotel/flow';
    const ROOM_TYPE_FLOW_FORM_NAME = 'mbhhotel_bundle_room_type_flow';
    const ROOM_TYPE_FLOW_URL = self::FLOW_BASE_URL . '/roomType';

    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    public function testRoomTypeFlow()
    {
        $notDefaultHotel = $this->getNotDefaultHotel();
        $crawler = $this->client->request('GET', self::ROOM_TYPE_FLOW_URL);

        $this->isSuccessfulResponse(self::ROOM_TYPE_FLOW_URL, $crawler);
        $this->assertFieldByIdExists($crawler, self::ROOM_TYPE_FLOW_FORM_NAME . '_hotel', 'select');

        $this->checkRoomTypeStep($crawler);

        /** @var RoomType $managedRoomType */
        $managedRoomType = $this->getNotDefaultHotel()->getRoomTypes()->first();
        $urlFromRoomTypeStep = self::ROOM_TYPE_FLOW_URL . '?' . http_build_query([
                RoomTypeFlow::PREV_STEP_NAME => RoomTypeFlow::ROOM_TYPE_STEP,
                RoomTypeFlow::HOTEL_ID_PARAM => $notDefaultHotel->getId(),
            ]);
        $crawler = $this->submitFlowForm($urlFromRoomTypeStep, $crawler, ['roomType' => $managedRoomType->getId()], 'back');

        $this->isSuccessfulResponse($urlFromRoomTypeStep, $crawler);
        $this->assertFieldByIdExists($crawler, self::ROOM_TYPE_FLOW_FORM_NAME . '_hotel', 'select');

        $this->checkRoomTypeStep($crawler);

        $urlWithFlowId = self::ROOM_TYPE_FLOW_URL . '/' . $managedRoomType->getId();

        $this->submitFlowForm(
            $urlFromRoomTypeStep,
            $crawler,
            ['roomType' => $managedRoomType->getId()]
        );
        $crawler = $this->client->followRedirect();

        $this->isSuccessfulResponse($urlWithFlowId, $crawler);
        $this->assertFieldByIdExists($crawler, self::ROOM_TYPE_FLOW_FORM_NAME . '_description', 'textarea');
        $this->assertFieldByIdExists($crawler, self::ROOM_TYPE_FLOW_FORM_NAME . '_roomSpace', 'input');
        $this->assertFieldByIdExists($crawler, self::ROOM_TYPE_FLOW_FORM_NAME . '_facilities', 'select');

        /** @var FlowConfig $flowConfig */
        $flowConfig = $this
            ->getDm()
            ->getRepository(FlowConfig::class)
            ->findOneBy([
                'flowType' => RoomTypeFlow::FLOW_TYPE,
                'flowId' => $managedRoomType->getId()
            ]);

        $description = 'some description';
        $roomSpace = 123;
        $crawler = $this->submitFlowForm($urlWithFlowId, $crawler, [
            'description' => $description,
            'roomSpace' => $roomSpace
        ]);
        $this->isSuccessfulResponse($urlWithFlowId, $crawler, 'image');

        $this->getDm()->refresh($managedRoomType);
        $this->assertEquals($description, $managedRoomType->getDescription());
        $this->assertEquals($roomSpace, $managedRoomType->getRoomSpace());
        $this->assertEquals(1, $crawler->filter('input#image_imageFile')->count());
        $this->assertEquals(1, $crawler->filter('input#image_isDefault')->count());

        $crawler = $this->submitFlowForm($urlWithFlowId, $crawler, [], 'next', 'image');

        //        $file = new UploadedFile($this->getContainer()->get('kernel')->getRootDir() . '/../web/apple-touch-icon.png', 'somename');
//        $this->client->request('POST', $urlWithFlowId, ['image' => ['_token' => $this->getFormToken($crawler, 'image')]], [$file]);
        $this->isSuccessfulResponse($urlWithFlowId, $crawler);
        $this->assertEquals(1, $crawler->filter('#' . self::ROOM_TYPE_FLOW_FORM_NAME . '_places')->count());
        $this->assertEquals(1, $crawler->filter('#' . self::ROOM_TYPE_FLOW_FORM_NAME . '_additionalPlaces')->count());

        $crawler = $this->submitFlowForm($urlWithFlowId, $crawler, [
            'places' => 2,
            'additionalPlaces' => 1
        ]);
        $this->isSuccessfulResponse($urlWithFlowId, $crawler);
        $this->getDm()->refresh($managedRoomType);
        $this->assertEquals(2, $managedRoomType->getPlaces());
        $this->assertEquals(1, $managedRoomType->getAdditionalPlaces());
        $this->assertEquals(1, $crawler->filter('#' . self::ROOM_TYPE_FLOW_FORM_NAME . '_rooms')->count());

        $numberOfRooms = 10;
        $crawler = $this->submitFlowForm($urlWithFlowId, $crawler, [
            'rooms' => $numberOfRooms
        ]);
        $this->isSuccessfulResponse($urlWithFlowId, $crawler);
        $this->assertFieldByIdExists($crawler, self::ROOM_TYPE_FLOW_FORM_NAME . '_begin');
        $this->assertFieldByIdExists($crawler, self::ROOM_TYPE_FLOW_FORM_NAME . '_end');

        $this->getDm()->refresh($flowConfig);
        $this->assertTrue(isset($flowConfig->getFlowData()['rooms']));

        $begin = (new \DateTime('midnight'));
        $end = new \DateTime('midnight +10 days');
        $crawler = $this->submitFlowForm($urlWithFlowId, $crawler, [
            'begin' => $begin->format('d.m.Y'),
            'end' => $end->format('d.m.Y'),
        ]);

        $this->isSuccessfulResponse($urlWithFlowId, $crawler);
        $this->assertFieldByIdExists($crawler, self::ROOM_TYPE_FLOW_FORM_NAME . '_tariff', 'select');

        $this->getDm()->refresh($flowConfig);
        $flowData = $flowConfig->getFlowData();
        $this->assertTrue(isset($flowData['begin']));
        $this->assertEquals($begin->format('d.m.Y H:i:s'), $flowData['begin']);
        $this->assertTrue(isset($flowData['end']));
        $this->assertEquals($end->format('d.m.Y H:i:s'), $flowData['end']);

        $crawler = $this->submitFlowForm($urlWithFlowId, $crawler, [
            'tariff' => $notDefaultHotel->getBaseTariff()->getId()
        ]);

        $this->isSuccessfulResponse($urlWithFlowId, $crawler);
        $this->assertFieldByIdExists($crawler, self::ROOM_TYPE_FLOW_FORM_NAME . '_price');

        $this->getDm()->refresh($flowConfig);
        $this->assertTrue(isset($flowConfig->getFlowData()['pricesTariffId']));
        $this->assertEquals($notDefaultHotel->getBaseTariff()->getId(), $flowConfig->getFlowData()['pricesTariffId']);

        $price = 1234;
        $this->submitFlowForm($urlWithFlowId, $crawler, [
            'price' => $price
        ], 'finish');
        $crawler = $this->client->followRedirect();
        $this->isSuccessfulResponse(self::ROOM_TYPE_FLOW_URL, $crawler);
        //TODO: Проверить созданные рум кеши и прайс кеши

        $createdPriceCaches = $this
            ->getDm()
            ->getRepository('MBHPriceBundle:PriceCache')
            ->findBy([
                'tariff.id' => $notDefaultHotel->getBaseTariff()->getId(),
                'roomType.id' => $managedRoomType->getId(),
                'date' => ['$gte' => $begin, '$lte' => $end],
                'isEnabled' => true
            ], ['date' => 1]);

        $firstPriceCache = current($createdPriceCaches);
        $this->assertEquals($price, $firstPriceCache->getPrice());
        $this->assertEquals($begin, $firstPriceCache->getDate());

        $lastPriceCache = end($createdPriceCaches);
        $this->assertEquals($price, $lastPriceCache->getPrice());
        $this->assertEquals($end, $lastPriceCache->getDate());

        /** @var RoomCache[] $createdRoomCaches */
        $createdRoomCaches = $this
            ->getDm()
            ->getRepository('MBHPriceBundle:RoomCache')
            ->findBy([
                'roomType.id' => $managedRoomType->getId(),
                'date' => ['$gte' => $begin, '$lte' => $end],
            ], ['date' => 1]);

        $firstRoomCache = current($createdRoomCaches);
        $this->assertEquals($numberOfRooms, $firstRoomCache->getTotalRooms());
        $this->assertEquals($begin, $firstRoomCache->getDate());

        $lastRoomCache = end($createdRoomCaches);
        $this->assertEquals($numberOfRooms, $lastRoomCache->getTotalRooms());
        $this->assertEquals($end, $lastRoomCache->getDate());
    }

    /**
     * @param Crawler $crawler
     * @param string $formName
     * @return string
     */
    private function getFormToken(Crawler $crawler, $formName = self::ROOM_TYPE_FLOW_FORM_NAME)
    {
        $formTokenId = $formName . '__token';
        $formToken = $crawler->filter('#' . $formTokenId)->attr('value');

        return $formToken;
    }

    /**
     * @param $url
     * @param Crawler $crawler
     * @param string $formName
     */
    private function isSuccessfulResponse($url, Crawler $crawler, $formName = self::ROOM_TYPE_FLOW_FORM_NAME): void
    {
        $this->isSuccessful($this->client->getResponse());
        $this->assertEquals('http://localhost' . $url, $crawler->getUri());
        $this->assertEquals(1, $crawler->filter('#' . $formName)->count());
        $this->assertEquals(0, $crawler->filter('#' . $formName . '>div.alert-danger')->count());
    }

    /**
     * @param $crawler
     * @return Crawler
     */
    private function checkRoomTypeStep($crawler): Crawler
    {
        $notDefaultHotel = $this->getNotDefaultHotel();
        $crawler = $this->submitFlowForm(self::ROOM_TYPE_FLOW_URL, $crawler, ['hotel' => $notDefaultHotel->getId()]);

        $this->isSuccessfulResponse(self::ROOM_TYPE_FLOW_URL, $crawler);

        $numberOfRoomTypes = 3;
        $roomTypeRadioInputs = $crawler->filter('input#mbhhotel_bundle_room_type_flow_roomType');
        $this->assertEquals($numberOfRoomTypes, $roomTypeRadioInputs->count());

        $radioButtonValues = array_map(function (\DOMElement $roomTypeInput) {
            return $roomTypeInput->getAttribute('value');
        }, iterator_to_array($roomTypeRadioInputs->getIterator()));

        $this->assertEquals(array_reverse($this->getNotDefaultHotelRoomTypeIds($notDefaultHotel)), $radioButtonValues);

        return $crawler;
    }

    /**
     * @return Hotel|null|object
     */
    private function getNotDefaultHotel()
    {
        return $this->getDm()->getRepository(Hotel::class)->findOneBy(['isDefault' => false]);
    }

    /**
     * @param $notDefaultHotel
     * @return array
     */
    private function getNotDefaultHotelRoomTypeIds(Hotel $notDefaultHotel): array
    {
        $roomTypeIds = $this
            ->getContainer()
            ->get('mbh.helper')->toIds($notDefaultHotel->getRoomTypes()->toArray());

        return $roomTypeIds;
    }

    /**
     * @param string $url
     * @param Crawler $crawler
     * @param array $formData
     * @param string $button
     * @param string $formName
     * @return Crawler
     */
    private function submitFlowForm(string $url, Crawler $crawler, array $formData, $button = 'next', $formName = self::ROOM_TYPE_FLOW_FORM_NAME): Crawler
    {
        $crawler = $this->client->request('POST', $url, [
            $button => '',
            $formName => array_merge($formData, [
                '_token' => $this->getFormToken($crawler, $formName)
            ])
        ]);

        return $crawler;
    }

    /**
     * @param Crawler $crawler
     * @param string $fieldId
     * @param string $fieldType
     */
    private function assertFieldByIdExists(Crawler $crawler, string $fieldId, $fieldType = 'input'): void
    {
        $this->assertEquals(1, $crawler->filter($fieldType . '#' . $fieldId)->count());
    }
}