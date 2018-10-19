<?php

namespace Tests\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\FlowConfig;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Form\HotelFlow\HotelFlow;
use MBH\Bundle\HotelBundle\Form\RoomTypeFlow\RoomTypeFlow;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use Symfony\Component\DomCrawler\Crawler;

class FlowControllerTest extends WebTestCase
{
    const FLOW_BASE_URL = '/management/hotel/flow';

    const ROOM_TYPE_FLOW_FORM_NAME = 'mbhhotel_bundle_room_type_flow';
    const ROOM_TYPE_FLOW_URL = self::FLOW_BASE_URL . '/roomType';

    const HOTEL_FLOW_URL = self::FLOW_BASE_URL . '/hotel';
    const HOTEL_FLOW_FORM_NAME = 'mbhhotel_bundle_hotel_flow';

    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public static function tearDownAfterClass()
    {
//        self::clearDB();
    }

    public function testRoomTypeFlow()
    {
        $notDefaultHotel = $this->getNotDefaultHotel();
        $crawler = $this->client->request('GET', self::ROOM_TYPE_FLOW_URL);

        $this->isSuccessfulResponse(self::ROOM_TYPE_FLOW_URL, $crawler, self::ROOM_TYPE_FLOW_FORM_NAME);
        $this->assertFieldByIdExists($crawler, self::ROOM_TYPE_FLOW_FORM_NAME . '_hotel', 'select');

        $this->checkRoomTypeStep($crawler);

        /** @var RoomType $managedRoomType */
        $managedRoomType = $this->getNotDefaultHotel()->getRoomTypes()->first();
        $urlFromRoomTypeStep = self::ROOM_TYPE_FLOW_URL . '?' . http_build_query([
                RoomTypeFlow::PREV_STEP_NAME => RoomTypeFlow::ROOM_TYPE_STEP,
                RoomTypeFlow::HOTEL_ID_PARAM => $notDefaultHotel->getId(),
            ]);
        $crawler = $this->submitFlowForm($urlFromRoomTypeStep, $crawler, ['roomType' => $managedRoomType->getId()], self::ROOM_TYPE_FLOW_FORM_NAME, 'back');

        $this->isSuccessfulResponse($urlFromRoomTypeStep, $crawler, self::ROOM_TYPE_FLOW_FORM_NAME);
        $this->assertFieldByIdExists($crawler, self::ROOM_TYPE_FLOW_FORM_NAME . '_hotel', 'select');

        $this->checkRoomTypeStep($crawler);

        $urlWithFlowId = self::ROOM_TYPE_FLOW_URL . '/' . $managedRoomType->getId();

        $this->submitFlowForm(
            $urlFromRoomTypeStep,
            $crawler,
            ['roomType' => $managedRoomType->getId()],
            self::ROOM_TYPE_FLOW_FORM_NAME
        );
        $crawler = $this->client->followRedirect();

        $this->isSuccessfulResponse($urlWithFlowId, $crawler, self::ROOM_TYPE_FLOW_FORM_NAME);
        $this->assertFieldByIdExists($crawler, self::ROOM_TYPE_FLOW_FORM_NAME . '_description', 'textarea');
        $this->assertFieldByIdExists($crawler, self::ROOM_TYPE_FLOW_FORM_NAME . '_roomSpace', 'input');
        $this->assertFieldByIdExists($crawler, self::ROOM_TYPE_FLOW_FORM_NAME . '_facilities', 'select');

        /** @var FlowConfig $flowConfig */
        $flowConfig = $this->getFlowConfig($managedRoomType->getId(), RoomTypeFlow::FLOW_TYPE);

        $description = 'some description';
        $roomSpace = 123;
        $crawler = $this->submitFlowForm($urlWithFlowId,
            $crawler,
            [
                'description' => $description,
                'roomSpace' => $roomSpace
            ],
            self::ROOM_TYPE_FLOW_FORM_NAME
        );
        $this->isSuccessfulResponse($urlWithFlowId, $crawler, 'image');

        $this->getDm()->refresh($managedRoomType);
        $this->assertEquals($description, $managedRoomType->getDescription());
        $this->assertEquals($roomSpace, $managedRoomType->getRoomSpace());
        $this->assertEquals(1, $crawler->filter('input#image_imageFile')->count());
        $this->assertEquals(1, $crawler->filter('input#image_isDefault')->count());

        $crawler = $this->submitFlowForm($urlWithFlowId, $crawler, [], 'image', 'next');

        //        $file = new UploadedFile($this->getContainer()->get('kernel')->getRootDir() . '/../web/apple-touch-icon.png', 'somename');
//        $this->client->request('POST', $urlWithFlowId, ['image' => ['_token' => $this->getFormToken($crawler, 'image')]], [$file]);
        $this->isSuccessfulResponse($urlWithFlowId, $crawler, self::ROOM_TYPE_FLOW_FORM_NAME);
        $this->assertEquals(1, $crawler->filter('#' . self::ROOM_TYPE_FLOW_FORM_NAME . '_places')->count());
        $this->assertEquals(1, $crawler->filter('#' . self::ROOM_TYPE_FLOW_FORM_NAME . '_additionalPlaces')->count());

        $crawler = $this->submitFlowForm(
            $urlWithFlowId,
            $crawler,
            [
                'places' => 2,
                'additionalPlaces' => 1
            ],
            self::ROOM_TYPE_FLOW_FORM_NAME
        );
        $this->isSuccessfulResponse($urlWithFlowId, $crawler, self::ROOM_TYPE_FLOW_FORM_NAME);
        $this->getDm()->refresh($managedRoomType);
        $this->assertEquals(2, $managedRoomType->getPlaces());
        $this->assertEquals(1, $managedRoomType->getAdditionalPlaces());
        $this->assertEquals(1, $crawler->filter('#' . self::ROOM_TYPE_FLOW_FORM_NAME . '_rooms')->count());

        $numberOfRooms = 10;
        $crawler = $this->submitFlowForm($urlWithFlowId, $crawler, [
            'rooms' => $numberOfRooms
        ], self::ROOM_TYPE_FLOW_FORM_NAME);
        $this->isSuccessfulResponse($urlWithFlowId, $crawler, self::ROOM_TYPE_FLOW_FORM_NAME);
        $this->assertFieldByIdExists($crawler, self::ROOM_TYPE_FLOW_FORM_NAME . '_begin');
        $this->assertFieldByIdExists($crawler, self::ROOM_TYPE_FLOW_FORM_NAME . '_end');

        $this->getDm()->refresh($flowConfig);
        $this->assertTrue(isset($flowConfig->getFlowData()['rooms']));

        $begin = (new \DateTime('midnight'));
        $end = new \DateTime('midnight +10 days');
        $crawler = $this->submitFlowForm($urlWithFlowId, $crawler, [
            'begin' => $begin->format('d.m.Y'),
            'end' => $end->format('d.m.Y'),
        ], self::ROOM_TYPE_FLOW_FORM_NAME);

        $this->isSuccessfulResponse($urlWithFlowId, $crawler, self::ROOM_TYPE_FLOW_FORM_NAME);
        $this->assertFieldByIdExists($crawler, self::ROOM_TYPE_FLOW_FORM_NAME . '_tariff', 'select');

        $this->getDm()->refresh($flowConfig);
        $flowData = $flowConfig->getFlowData();
        $this->assertTrue(isset($flowData['begin']));
        $this->assertEquals($begin->format('d.m.Y H:i:s'), $flowData['begin']);
        $this->assertTrue(isset($flowData['end']));
        $this->assertEquals($end->format('d.m.Y H:i:s'), $flowData['end']);

        $crawler = $this->submitFlowForm($urlWithFlowId, $crawler, [
            'tariff' => $notDefaultHotel->getBaseTariff()->getId()
        ], self::ROOM_TYPE_FLOW_FORM_NAME);

        $this->isSuccessfulResponse($urlWithFlowId, $crawler, self::ROOM_TYPE_FLOW_FORM_NAME);
        $this->assertFieldByIdExists($crawler, self::ROOM_TYPE_FLOW_FORM_NAME . '_price');

        $this->getDm()->refresh($flowConfig);
        $this->assertTrue(isset($flowConfig->getFlowData()['pricesTariffId']));
        $this->assertEquals($notDefaultHotel->getBaseTariff()->getId(), $flowConfig->getFlowData()['pricesTariffId']);

        $price = 1234;
        $this->submitFlowForm($urlWithFlowId, $crawler, [
            'price' => $price
        ], self::ROOM_TYPE_FLOW_FORM_NAME, 'finish');
        $crawler = $this->client->followRedirect();
        $this->isSuccessfulResponse(self::ROOM_TYPE_FLOW_URL, $crawler, self::ROOM_TYPE_FLOW_FORM_NAME);

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

    public function testHotelFlow()
    {
        $notDefaultHotel = $this->getNotDefaultHotel();
        $crawler = $this->client->request('GET', self::HOTEL_FLOW_URL);
        $this->isSuccessfulResponse(self::HOTEL_FLOW_URL, $crawler, self::HOTEL_FLOW_FORM_NAME);

        $numberOfHotels = 2;
        $hotelRadioInputs = $crawler->filter('input#' . self::HOTEL_FLOW_FORM_NAME . '_hotel');
        $this->assertEquals($numberOfHotels, $hotelRadioInputs->count());

        $radioButtonValues = array_map(function (\DOMElement $hotelInput) {
            return $hotelInput->getAttribute('value');
        }, iterator_to_array($hotelRadioInputs->getIterator()));
        $hotels = $this->getDm()->getRepository('MBHHotelBundle:Hotel')->findAll();
        $this->assertEquals($this->getContainer()->get('mbh.helper')->toIds($hotels), $radioButtonValues);

        $urlWithFlowId = self::HOTEL_FLOW_URL . '/' . $notDefaultHotel->getId();
        $this->submitFlowForm(self::HOTEL_FLOW_URL, $crawler, ['hotel' => $notDefaultHotel->getId()], self::HOTEL_FLOW_FORM_NAME);
        $crawler = $this->client->followRedirect();
        $this->isSuccessfulResponse($urlWithFlowId, $crawler, self::HOTEL_FLOW_FORM_NAME);

        $this->submitFlowForm($urlWithFlowId, $crawler, [], self::HOTEL_FLOW_FORM_NAME, 'back');
        $crawler = $this->client->followRedirect();
        $this->isSuccessfulResponse(self::HOTEL_FLOW_URL, $crawler, self::HOTEL_FLOW_FORM_NAME);

        $this->submitFlowForm(self::HOTEL_FLOW_URL, $crawler, ['hotel' => $notDefaultHotel->getId()], self::HOTEL_FLOW_FORM_NAME);
        $crawler = $this->client->followRedirect();
        $this->isSuccessfulResponse($urlWithFlowId, $crawler, self::HOTEL_FLOW_FORM_NAME);
        $this->assertFieldByIdExists($crawler, self::HOTEL_FLOW_FORM_NAME . '_description', 'textarea');

        $description = 'some description';
        $crawler = $this->submitFlowForm($urlWithFlowId, $crawler, ['description' => $description], self::HOTEL_FLOW_FORM_NAME);
        $this->isSuccessfulResponse($urlWithFlowId, $crawler, self::HOTEL_FLOW_FORM_NAME);
        $this->getDm()->refresh($notDefaultHotel);
        $this->assertEquals($description, $notDefaultHotel->getDescription());
        $this->assertFieldByIdExists($crawler, self::HOTEL_FLOW_FORM_NAME . '_logoImage_imageFile_file');

        $crawler = $this->submitFlowForm($urlWithFlowId, $crawler, [], self::HOTEL_FLOW_FORM_NAME);
        $hotelAddressFormName = 'hotel_address';
        $this->isSuccessfulResponse($urlWithFlowId, $crawler, $hotelAddressFormName);
        $this->assertFieldByIdExists($crawler, $hotelAddressFormName . '_cityId');
        $this->assertFieldByIdExists($crawler, $hotelAddressFormName . '_street');
        $this->assertFieldByIdExists($crawler, $hotelAddressFormName . '_house');

        $cityId = 10000;
        $house = 123;
        $crawler = $this->submitFlowForm($urlWithFlowId, $crawler, [
            'cityId' => $cityId,
            'house' => $house
        ], $hotelAddressFormName);
        $hotelLocationFormName = 'mbhhotel_bundle_hotel_location_type';
        $this->isSuccessfulResponse($urlWithFlowId, $crawler, $hotelLocationFormName);

        $this->getDm()->refresh($notDefaultHotel);
        $this->assertEquals($cityId, $notDefaultHotel->getCityId());
        $this->assertEquals($house, $house);
        $this->assertFieldByIdExists($crawler, $hotelLocationFormName . '_latitude');
        $this->assertFieldByIdExists($crawler, $hotelLocationFormName . '_longitude');

        $longitude = '12.123123';
        $latitude = 22.111111;
        $crawler = $this->submitFlowForm($urlWithFlowId, $crawler, [
            'longitude' => $longitude,
            'latitude' => $latitude
        ], $hotelLocationFormName);

        $this->isSuccessfulResponse($urlWithFlowId, $crawler, self::HOTEL_FLOW_FORM_NAME);
        $hotelContactInfoForm = 'mbhhotel_bundle_hotel_flow_contactInformation';
        $this->assertFieldByIdExists($crawler, $hotelContactInfoForm . '_fullName');

        $this->getDm()->refresh($notDefaultHotel);
        $this->assertEquals($longitude, $notDefaultHotel->getLongitude());
        $this->assertEquals($latitude, $notDefaultHotel->getLatitude());

        $fullName = 'Valera';
        $phone = 89670441344;
        $crawler = $this->submitFlowForm($urlWithFlowId, $crawler, [
            'contactInformation' => [
                'fullName' => $fullName,
                'phoneNumber' => $phone
            ]
        ], self::HOTEL_FLOW_FORM_NAME);
        $this->isSuccessfulResponse($urlWithFlowId, $crawler, self::HOTEL_FLOW_FORM_NAME);
        $this->assertFieldByIdExists($crawler, self::HOTEL_FLOW_FORM_NAME . '_defaultImage_imageFile_file');

        $this->getDm()->refresh($notDefaultHotel);
        $this->assertEquals($fullName, $notDefaultHotel->getContactInformation()->getFullName());
        $this->assertEquals($phone, $notDefaultHotel->getContactInformation()->getPhoneNumber(true));

        $hotelImageType = 'mbhhotel_bundle_hotel_image_type';
        $crawler = $this->submitFlowForm($urlWithFlowId, $crawler, [], self::HOTEL_FLOW_FORM_NAME);
        $this->isSuccessfulResponse($urlWithFlowId, $crawler, $hotelImageType);
        $this->assertFieldByIdExists($crawler, $hotelImageType . '_imageFile');

        $this->submitFlowForm($urlWithFlowId, $crawler, [], $hotelImageType, 'finish');
        $crawler = $this->client->followRedirect();
        $this->assertEquals('http://localhost' . self::HOTEL_FLOW_URL, $crawler->getUri());

        /** @var FlowConfig $flowConfig */
        $flowConfig = $this->getFlowConfig($notDefaultHotel->getId(), HotelFlow::FLOW_TYPE);
        $this->assertTrue($flowConfig->isFinished());
    }


    /**
     * @param Crawler $crawler
     * @param string $formName
     * @return string
     */
    private function getFormToken(Crawler $crawler, $formName)
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
    private function isSuccessfulResponse($url, Crawler $crawler, $formName): void
    {
        $this->isSuccessful($this->client->getResponse());
        $this->assertEquals('http://localhost' . $url, $crawler->getUri());
        $this->assertEquals(1, $crawler->filter('form[name="' . $formName .'"]')->count());
        $this->assertEquals(0, $crawler->filter('#' . $formName . '>div.alert-danger')->count());
    }

    /**
     * @param $crawler
     * @return Crawler
     */
    private function checkRoomTypeStep($crawler): Crawler
    {
        $notDefaultHotel = $this->getNotDefaultHotel();
        $crawler = $this->submitFlowForm(self::ROOM_TYPE_FLOW_URL, $crawler, ['hotel' => $notDefaultHotel->getId()], self::ROOM_TYPE_FLOW_FORM_NAME);

        $this->isSuccessfulResponse(self::ROOM_TYPE_FLOW_URL, $crawler, self::ROOM_TYPE_FLOW_FORM_NAME);

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
    private function submitFlowForm(string $url, Crawler $crawler, array $formData, $formName, $button = 'next'): Crawler
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

    /**
     * @param string $flowId
     * @param string $flowType
     * @return FlowConfig|null|object
     */
    private function getFlowConfig(string $flowId, string $flowType)
    {
        $flowConfig = $this
            ->getDm()
            ->getRepository(FlowConfig::class)
            ->findOneBy([
                'flowType' => $flowType,
                'flowId' => $flowId
            ]);

        return $flowConfig;
    }
}