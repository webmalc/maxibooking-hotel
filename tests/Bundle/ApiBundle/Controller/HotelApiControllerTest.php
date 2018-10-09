<?php

namespace Tests\Bundle\ApiBundle\Controller;

use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\OnlineBundle\Document\FormConfig;
use MBH\Bundle\PriceBundle\DataFixtures\MongoDB\PriceCacheData;
use MBH\Bundle\PriceBundle\Document\Tariff;

class HotelApiControllerTest extends WebTestCase
{
    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    public function testGetRoomTypes()
    {
        $hotel = $this->getDefaultHotel();
        $criteria = ['hotelIds' => [$hotel->getId()], 'limit' => 2];
        $decodedResponse = $this->sendRequestAndGetDecodedResponse('roomTypes', $criteria);

        $this->assertTrue($decodedResponse['success']);
        $this->assertEquals(2, count($decodedResponse['data']));

        $firstRoomType = $this
            ->getDm()
            ->getRepository(RoomType::class)
            ->findOneBy(['fullTitle' => 'Стандартный одноместный', 'hotel.id' => $hotel->getId()]);
        $expected = [
            'id' => $firstRoomType->getId(),
            'isEnabled' => true,
            'hotel' => $hotel->getId(),
            'title' => 'Стандартный одноместный',
            'description' => null,
            'places' => 1,
            'additionalPlaces' => 1,
            'isSmoking' => false,
            'isHostel' => false,
            'facilities' => [],
            'roomSpace' => null,
            'onlineImages' => []
        ];
        $this->assertEquals($expected, $decodedResponse['data'][0]);
    }

    public function testGetTariffs()
    {
        $hotel = $this->getDefaultHotel();
        $criteria = ['hotelIds' => [$hotel->getId()]];
        $decodedResponse = $this->sendRequestAndGetDecodedResponse('tariffs', $criteria);

        $this->assertTrue($decodedResponse['success']);
        $this->assertEquals(2, count($decodedResponse['data']));

        $mainTariff = $this
            ->getDm()
            ->getRepository(Tariff::class)
            ->findOneBy(['fullTitle' => 'Основной тариф', 'hotel.id' => $hotel->getId()]);
        $expected = [
            'id' => $mainTariff->getId(),
            'title' => 'Основной тариф',
            'description' => null,
            'hotel' => $hotel->getId(),
            'isEnabled' => true,
            'isDefault' => true,
            'isOnline' => true,
        ];
        $this->assertEquals($expected, $decodedResponse['data'][0]);
    }

    public function testGetHotels()
    {
        $hotel = $this->getDefaultHotel();
        $criteria = ['ids' => [$hotel->getId()]];
        $decodedResponse = $this->sendRequestAndGetDecodedResponse('hotels', $criteria);

        $this->assertTrue($decodedResponse['success']);
        $this->assertEquals(1, count($decodedResponse['data']));

        $expected = [
            'id' => $hotel->getId(),
            'title' => 'Отель Волга',
            'isEnabled' => true,
            'isDefault' => true,
            'isHostel' => false,
            'description' => null,
            'facilities' => [],
            'images' => [],
            'logo' => null,
            'latitude' => null,
            'longitude' => null,
            'street' => null,
            'house' => null,
            'corpus' => null,
            'flat' => null,
            'zipCode' => null,
            'contactInformation' => null,
            'mapImage' => null
        ];
        $this->assertEquals($expected, $decodedResponse['data'][0]);
    }

    public function testGetServices()
    {
        $criteria = ['tariffId' => $this->getDefaultHotel()->getBaseTariff()->getId()];
        $decodedResponse = $this->sendRequestAndGetDecodedResponse('services', $criteria);
        $this->assertTrue($decodedResponse['success']);
    }

    public function testGetBookingOptionsWithoutMandatoryFields()
    {
        $decodedResponse = $this->sendRequestAndGetDecodedResponse('booking_options');
        $this->assertFalse($decodedResponse['success']);
        $this->assertEquals([
            'begin' => 'Отсутствует обязательное поле "begin"',
            'end' => 'Отсутствует обязательное поле "end"',
            'adults' => 'Отсутствует обязательное поле "adults"',
        ], $decodedResponse['errors']);
    }

    public function testGetBookingOptionsWithIncorrectFields()
    {
        $requestParams = [
            'begin' => (new \DateTime())->format('d.m.Y'),
            'end' => (new \DateTime('+ 3 days'))->format('d.m.Y'),
            'adults' => 2,
            'childrenAges' => 15,
            'roomTypeIds' => [$this->getDefaultHotel()->getRoomTypes()->first()->getId()],
            'hotelIds' => $this->getDefaultHotel()->getId()
        ];
        $decodedResponse = $this->sendRequestAndGetDecodedResponse('booking_options', $requestParams);
        $this->assertFalse($decodedResponse['success']);
        $this->assertEquals([
            'childrenAges' => 'Поле "childrenAges" должно быть массивом',
            'hotelIds' => 'Поле "hotelIds" должно быть массивом',
        ], $decodedResponse['errors']);
    }

    public function testGetBookingOptions()
    {
        $hotel = $this->getDefaultHotel();
        $requestParams = [
            'begin' => (new \DateTime())->format('d.m.Y'),
            'end' => (new \DateTime('+ 3 days'))->format('d.m.Y'),
            'adults' => 2,
            'hotelIds' => [$hotel->getId()]
        ];

        $decodedResponse = $this->sendRequestAndGetDecodedResponse('booking_options', $requestParams);
        $this->assertTrue($decodedResponse['success']);
    }

    public function testGetMinPricesForRoomTypesAction()
    {
        $hotel = $this->getDefaultHotel();
        $lowestPrice = 100;
        $minPriceDate = new \DateTime('midnight + 30 days');
        $roomTypeWithLowPrice = $this
            ->getDm()
            ->getRepository('MBHHotelBundle:RoomType')
            ->findOneBy(['fullTitle' => 'Стандартный одноместный']);
        $priceCacheWithLowestPrice = $this->getDm()
            ->getRepository('MBHPriceBundle:PriceCache')
            ->findOneBy(['roomType.id' => $roomTypeWithLowPrice->getId(), 'date' => $minPriceDate]);
        $priceCacheWithLowestPrice->setPrice($lowestPrice);
        $this->getDm()->flush();

        $formConfig = $this->createFormConfig($hotel);

        $decodedResponse = $this->sendRequestAndGetDecodedResponse('minPrices', [
            'hotelId' => $hotel->getId(),
            'onlineFormId' => $formConfig->getId()
        ]);
        $this->assertTrue($decodedResponse['success']);

        $expected = [];
        $priceCacheData = PriceCacheData::DATA['special-tariff'];
        foreach ($hotel->getRoomTypes() as $roomType) {
            if ($roomType->getFullTitle() === 'Стандартный одноместный') {
                $minPrice = $lowestPrice;
            } elseif ($roomType->getFullTitle() === 'Стандартный двухместный') {
                $minPrice = $priceCacheData['roomtype-double']['ru'][0];
            } else {
                $minPrice = $priceCacheData['hotel-triple']['ru'][0];
            }

            $expected[$roomType->getId()] = ['hasPrices' => true, 'price' => $minPrice];
        }

        $this->assertEquals($expected, $decodedResponse['data']);
    }

    public function testGetMinPricesWithoutMandatoryParams()
    {
        $decodedResponse =
            $this->sendRequestAndGetDecodedResponse('minPrices', ['hotelId' => $this->getDefaultHotel()->getId()]);
        $this->assertFalse($decodedResponse['success']);
        $this->assertEquals([
            'onlineFormId' => 'Отсутствует обязательное поле "onlineFormId"'
        ], $decodedResponse['errors']);
    }

    public function testGetFacilitiesData()
    {
        $formConfig = $this->createFormConfig($this->getDefaultHotel());
        $decodedResponse =
            $this->sendRequestAndGetDecodedResponse('facilities_data', [
                'onlineFormId' => $formConfig->getId(),
                'hotelId' => $this->getDefaultHotel()->getId()
            ]);
        $this->assertTrue($decodedResponse['success']);
    }

    private function createFormConfig(Hotel $hotel)
    {
        $formConfig = (new FormConfig())
            ->setRoomTypeChoices($hotel->getRoomTypes()->toArray())
            ->setHotels([$hotel])
            ->setRoomTypes(true);

        $dm = $this->getDm();
        $dm->persist($formConfig);
        $dm->flush();
        $dm->refresh($formConfig);

        return $formConfig;
    }

    private function sendRequestAndGetDecodedResponse(string $endpoint, array $query = [])
    {
        $this->client->request('GET', '/api/v1/' . $endpoint . '?' . http_build_query($query));
        $this->isSuccessful($this->client->getResponse(), true, 'application/json');

        return json_decode($this->client->getResponse()->getContent(), true);
    }
}