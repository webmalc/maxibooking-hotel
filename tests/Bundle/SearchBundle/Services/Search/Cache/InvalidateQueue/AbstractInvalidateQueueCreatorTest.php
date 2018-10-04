<?php


namespace Tests\Bundle\SearchBundle\Services\Search\Cache\InvalidateQueue;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateMessage;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

class AbstractInvalidateQueueCreatorTest extends WebTestCase
{
    /**
     * @param $serviceName
     * @param $data
     * @param $expected
     * @dataProvider dataProvider
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\InvalidateException
     */
    public function testAddToQueue($serviceName, $data, $expected): void
    {
        $producer = $this->createMock(ProducerInterface::class);
        $producer->expects($this->exactly(2))->method('publish')->willReturnCallback(
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

        $factory = $this->getContainer()->get('mbh_search.invalidate_adapter_factory');
        $nameSpace = 'MBH\\Bundle\\SearchBundle\\Services\\Cache\\InvalidateQueue\\';
        $serviceName = $nameSpace.$serviceName;
        /** @var \MBH\Bundle\SearchBundle\Services\Cache\InvalidateQueue\AbstractInvalidateQueueCreator $service */
        $service = new $serviceName($producer, $factory);
        $service->addToQueue($data);
        $service->addBatchToQueue([$data]);
    }

    public function dataProvider()
    {
        $begin = new \DateTime('midnight');
        $end = new \DateTime('midnight +3 days');

        return [
            [
                'serviceName' =>  'PriceCacheQueue',
                'data' => $this->getPriceCache($begin),
                'expected' => [
                    'begin' => $begin,
                    'end' => $begin,
                    'roomTypeIds' => ['fakeRoomTypeId'],
                    'tariffIds' => ['fakeTariffId']
                ]
            ]
        ];
    }

    private function getPriceCache(\DateTime $date): PriceCache
    {
        $priceCache =  new PriceCache();
        $priceCache
            ->setDate($date)
            ->setTariff((new Tariff())->setId('fakeTariffId'))
            ->setRoomType((new RoomType())->setId('fakeRoomTypeId'))
        ;

        return $priceCache;
    }

}