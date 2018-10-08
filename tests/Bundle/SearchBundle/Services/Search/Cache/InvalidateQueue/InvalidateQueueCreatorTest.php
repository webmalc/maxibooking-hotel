<?php


namespace Tests\Bundle\SearchBundle\Services\Search\Cache\InvalidateQueue;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateMessage;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateQuery;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InvalidateQueueCreatorTest extends WebTestCase
{
    /**
     * @param $document
     * @param $expected
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\InvalidateException
     * @dataProvider dataProvider
     */
    public function testAddToQueue($document, $expected): void
    {
        $producer = $this->createMock(ProducerInterface::class);
        $producer->expects($this->atLeastOnce())->method('publish')->willReturnCallback(
            function ($msgBody) use ($expected) {
                /** @var InvalidateMessage $actual */
                $actual = unserialize($msgBody, [InvalidateMessage::class => true]);
                $this->assertInstanceOf(InvalidateMessage::class, $actual);
                $this->assertEquals($expected['begin'], $actual->getBegin());
                $this->assertEquals($expected['end'], $actual->getEnd());
                $this->assertArraySimilar($expected['roomTypeIds'], $actual->getRoomTypeIds());
                $this->assertArraySimilar($expected['tariffIds'], $actual->getTariffIds());
            }
        );
        /** @var ContainerInterface $container */
        $this->getContainer()->set('old_sound_rabbit_mq.cache_invalidate_producer', $producer);
        $service = $this->getContainer()->get('mbh_search.invalidate_queue_creator');
        $service->addToQueue($document);
        $service->addBatchToQueue([$document]);
    }

    public function dataProvider()
    {
        /** @var PriceCache $priceCache */
        $priceCache = $this->getDocument(PriceCache::class);
        /** @var RoomCache $roomCache */
        $roomCache = $this->getDocument(RoomCache::class);
        /** @var RoomType $roomType */
        $roomType = $this->getDocument(RoomType::class);
        /** @var Tariff $tariff */
        $tariff = $this->getDocument(Tariff::class);

        return [
            [
                'document' => $priceCache,
                'expected' => [
                    'begin' => $priceCache->getDate(),
                    'end' => $priceCache->getDate(),
                    'roomTypeIds' => (array)$priceCache->getRoomType()->getId(),
                    'tariffIds' => (array)$priceCache->getTariff()->getId(),

                ],
            ],
            [
                'document' => $roomCache,
                'expected' => [
                    'begin' => $roomCache->getDate(),
                    'end' => $roomCache->getDate(),
                    'roomTypeIds' => (array)$roomCache->getRoomType()->getId(),
                    'tariffIds' => [],
                ],
            ],
            [
                'document' => $roomType,
                'expected' => [
                    'begin' => null,
                    'end' => null,
                    'roomTypeIds' => (array)$roomType->getId(),
                    'tariffIds' => [],
                ],
            ],
            [
                'document' => $tariff,
                'expected' => [
                    'begin' => $tariff->getBegin(),
                    'end' => $tariff->getEnd(),
                    'roomTypeIds' => [],
                    'tariffIds' => (array)$tariff->getId(),
                ],
            ],
            [
                'document' => [
                    'begin' => new \DateTime('midnight'),
                    'end' =>  new \DateTime('midnight +3 days'),
                    'type' => InvalidateQuery::ROOM_CACHE_GENERATOR,
                    'roomTypeIds' => ['fakeRoomType1', 'fakeRoomType2'],
                    'tariffIds' => ['fakeTariffId1', 'fakeTariffId2']
                ],
                'expected' => [
                    'begin' => new \DateTime('midnight'),
                    'end' => new \DateTime('midnight +3 days'),
                    'roomTypeIds' => ['fakeRoomType1', 'fakeRoomType2'],
                    'tariffIds' => [],
                ],
            ],
            [
                'document' => [
                    'begin' => new \DateTime('midnight'),
                    'end' =>  new \DateTime('midnight +3 days'),
                    'type' => InvalidateQuery::PRICE_GENERATOR,
                    'roomTypeIds' => ['fakeRoomType1', 'fakeRoomType2'],
                    'tariffIds' => ['fakeTariffId1', 'fakeTariffId2']
                ],
                'expected' => [
                    'begin' => new \DateTime('midnight'),
                    'end' => new \DateTime('midnight +3 days'),
                    'roomTypeIds' => ['fakeRoomType1', 'fakeRoomType2'],
                    'tariffIds' => ['fakeTariffId1', 'fakeTariffId2'],
                ],
            ],
            [
                'document' => [
                    'begin' => new \DateTime('midnight'),
                    'end' =>  new \DateTime('midnight +3 days'),
                    'type' => InvalidateQuery::RESTRICTION_GENERATOR,
                    'roomTypeIds' => ['fakeRoomType1', 'fakeRoomType2'],
                    'tariffIds' => ['fakeTariffId1', 'fakeTariffId2']
                ],
                'expected' => [
                    'begin' => new \DateTime('midnight'),
                    'end' => new \DateTime('midnight +3 days'),
                    'roomTypeIds' => ['fakeRoomType1', 'fakeRoomType2'],
                    'tariffIds' => ['fakeTariffId1', 'fakeTariffId2'],
                ],
            ],
        ];
    }

    private function getDocument(string $documentName)
    {
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');

        return $dm->getRepository($documentName)->findOneBy([]);
    }

}