<?php


namespace Tests\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\SearchBundle\Lib\Exceptions\RoomCacheLimitException;

class RoomCacheSearchProvider extends WebTestCase
{


    public function testCheck()
    {
        $provider = $this->getContainer()->get('mbh_search.room_cache_search_provider');
        $dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
        /** @var Hotel $hotel */
        $hotel = $dm->getRepository(Hotel::class)->findOneBy([]);
        $roomType = $hotel->getRoomTypes()->first();
        $tariff = $hotel->getTariffs()->first();
        $begin = new \DateTime('tomorrow midnight');
        $end = new \DateTime('tomorrow midnight + 3 days');
        $actual = $provider->fetchAndCheck($begin, $end, $roomType, $tariff);
        $cachesCount = (int)$end->diff($begin)->format('%a');
        $this->assertCount($cachesCount, $actual);
    }

    public function testFetchAndCheckFail()
    {
        $provider = $this->getContainer()->get('mbh_search.room_cache_search_provider');
        $dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
        /** @var Hotel $hotel */
        $hotel = $dm->getRepository(Hotel::class)->findOneBy([]);
        $roomType = $hotel->getRoomTypes()->first();
        $tariff = $hotel->getTariffs()->first();
        $begin = new \DateTime('tomorrow midnight +1 year');
        $end = new \DateTime('tomorrow midnight + 3 days +1 year');
        $this->expectException(RoomCacheLimitException::class);
        $this->assertNull($provider->fetchAndCheck($begin, $end, $roomType, $tariff));
    }

}