<?php


namespace Tests\Bundle\SearchBundle\Services\Data\Serializers;


use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\Result\ResultConditions;
use MBH\Bundle\SearchBundle\Lib\Result\ResultDayPrice;
use MBH\Bundle\SearchBundle\Lib\Result\ResultPrice;
use MBH\Bundle\SearchBundle\Lib\Result\ResultRoomType;
use MBH\Bundle\SearchBundle\Lib\Result\ResultTariff;
use MBH\Bundle\SearchBundle\Services\Data\Serializers\SearchSerializerFactory;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class ResultSerializerTest extends SearchWebTestCase
{

    public function testSerialize(): void
    {
        $serializer = $this->getContainer()->get('mbh_search.result_serializer');
        $data = $this->createSearchResult();
        $actual = $serializer->serialize($data);
        $this->assertJson($actual);
    }

    public function testDeserialize(): void
    {
        $data = '{"begin":"2018-08-07T00:00:00+03:00","end":"2018-08-10T00:00:00+03:00","resultRoomType":{"id":"5b695c694a4e0d007c01ad86","name":"Стандартный одноместный","categoryName":"CategoryOne","hotelName":"Отель Волга"},"resultTariff":{"id":"5b695c684a4e0d007c01ad62","name":"Основной тариф"},"resultConditions":{"id":"fakeConditionsId","begin":"2018-08-07T00:00:00+03:00","end":"2018-08-10T00:00:00+03:00","adults":2,"children":2,"childrenAges":[3,7],"searchHash":"","forceBooking":false},"prices":[{"searchAdults":2,"searchChildren":2,"total":33333,"dayPrices":[{"date":"2018-08-07T00:00:00+03:00","tariff":{"id":"5b695c684a4e0d007c01ad62","name":"Основной тариф"},"price":333,"adults":2,"children":2,"infants":0,"promotion":null}]}],"minRoomsCount":5,"accommodationRooms":[],"virtualRoom":null,"status":"ok","error":"","id":"results_id5b699800239c63.87485490","searchHash":""}';
        $serializer = $this->getContainer()->get('mbh_search.result_serializer');
        $actual = $serializer->deserialize($data);
        $this->assertInstanceOf(Result::class, $actual);
    }

    private function createSearchResult(): Result
    {
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $hotel = $dm->getRepository(Hotel::class)->findAll()[0];

        /** @var RoomType $roomType */
        $roomType = $hotel->getRoomTypes()->first();
        /** @var Tariff $tariff */
        $tariff = $hotel->getTariffs()->first();

        $adults = 2;
        $children = 2;
        $childrenAges = [3, 7];
        $begin = new \DateTime('midnight');
        $end = new \DateTime('midnight + 3 days');

        return $this->resultCreator($adults, $children, $childrenAges, $begin, $end, $tariff, $roomType)['result'];

    }
}