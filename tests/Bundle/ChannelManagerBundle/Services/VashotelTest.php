<?php

namespace Tests\Bundle\ChannelManagerBundle\Services;


use MBH\Bundle\BaseBundle\Lib\Test\ChannelManagerServiceTestCase;
use MBH\Bundle\ChannelManagerBundle\Document\VashotelConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Services\Vashotel;
use MBH\Bundle\PriceBundle\Services\PriceCacheRepositoryFilter;
use Symfony\Component\DependencyInjection\ContainerInterface;

class VashotelTest extends ChannelManagerServiceTestCase
{
    protected const VAS_HOTEL_ID1 = 101;
    protected const VAS_HOTEL_ID2 = 202;

    protected const UPDATE_PRICES = 'updatePrices';
    protected const UPDATE_RESTRICTIONS = 'updateRestrictions';

    /**@var ContainerInterface */
    private $container;

    /**@var \DateTime */
    private $startDate;

    /**@var \DateTime */
    private $endDate;

    private $datum = false;

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
        parent::setUp();
        self::bootKernel();
        $this->container = self::getContainerStat();
        $this->dm = $this->container->get('doctrine.odm.mongodb.document_manager');
        $this->initConfig(true);
        $this->initConfig(false);
        $this->startDate = new \DateTime('midnight');
        $this->endDate = new \DateTime('midnight +8 days');
    }

    protected function getServiceHotelIdByIsDefault(bool $isDefault): int
    {
        return $isDefault ? self::VAS_HOTEL_ID1 : self::VAS_HOTEL_ID2;
    }

    protected function getServiceConfig(): ChannelManagerConfigInterface
    {
        return new VashotelConfig();
    }

    protected function setMock($method): void
    {
        $mock = \Mockery::mock(Vashotel::class, [$this->container, new PriceCacheRepositoryFilter($this->dm)])
            ->makePartial();

        $mock->shouldReceive('pullTariffs')->andReturnUsing(static function(): array {
            $serviceTariffs['ID1']['changePrice'] = 1;
            $serviceTariffs['ID1']['isActive'] = 1;
            $serviceTariffs['ID1']['changeQuan'] = 1;

            return $serviceTariffs;
        });

        switch ($method) {
            case self::UPDATE_PRICES:
                $this->mockUpdatePricesSend($mock);
                break;
            case self::UPDATE_RESTRICTIONS:
                $this->mockUpdateRestrictionsSend($mock);
                break;
        }

        $mock->shouldReceive('log')->andReturnTrue();
        $mock->shouldReceive('checkResponse')->andReturnTrue();

        $this->container->set('mbh.channelmanager.vashotel', $mock);
    }

    protected function mockUpdatePricesSend($mock): void
    {
        $mock->shouldReceive('sendXml')->andReturnUsing(function(...$data) {
            $this->assertEquals(
                $this->generateCompareString($data[1], true),
                $this->getUpdatePricesRequestData($this->datum)
            );

            return simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><request></request>');
        });
    }

    protected function mockUpdateRestrictionsSend($mock): void
    {
        $mock->shouldReceive('sendXml')->andReturnUsing(function(...$data) {
            $this->assertEquals(
                $this->generateCompareString($data[1], false),
                $this->getUpdateRestrictionsRequestData($this->datum)
            );

            return simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><request></request>');
        });
    }

    protected function generateCompareString(string $requestData, bool $isPrices): string
    {
        $xml = new \SimpleXMLElement($requestData);
        $date = (clone $this->startDate)->modify('-1 days');
        $arr = [];

        for ($i = 0; $i < 7; $i++) {
            $formattedDate = $date->modify('+1 days')->format('Y-m-d');
            $roomType = !$this->datum ? 'def_room1' : 'not_def_room1';
            $data = $xml->xpath('/request/room[@id="' . $roomType . '"]/date[@value="' . $formattedDate . '"]')[0];
            if ($isPrices) {
                $this->formatPricesArray($arr, $formattedDate, $data);
            } else {
                $this->formatRestrictionsArray($arr, $formattedDate, $data);
            }
        }
        $this->datum = !$this->datum;

        return json_encode($arr);
    }

    protected function formatRestrictionsArray(array &$arr, string $formattedDate, $data): void
    {
        $arr[$formattedDate] = [
            'sellquantity' => ((array)$data->sellquantity)[0]
        ];
    }

    protected function formatPricesArray(array &$arr, string $formattedDate, $data): void
    {
        if (isset($data->prices->price_1, $data->prices->price_2)) {
            $arr[$formattedDate] = [
                'closed' => ((array)$data->closed)[0],
                'price1' => ((array)$data->prices->price_1)[0],
                'price2' => ((array)$data->prices->price_2)[0]
            ];
        } else {
            $arr[$formattedDate] = [
                'closed' => ((array)$data->closed)[0]
            ];
        }
    }

    protected function getUpdateRestrictionsRequestData($isDefault): string
    {
        $date1 = clone $this->startDate;
        $date2 = clone $this->startDate;

        return $isDefault
            ?
            '{"' . $date1->format('Y-m-d') . '":{"sellquantity":"10"},"' .
            $date1->modify('+1 days')->format('Y-m-d') . '":{"sellquantity":"10"},"' .
            $date1->modify('+1 days')->format('Y-m-d') . '":{"sellquantity":"10"},'.
            '"' . $date1->modify('+1 days')->format('Y-m-d') . '":{"sellquantity":"0"},"'
            . $date1->modify('+1 days')->format('Y-m-d') . '":{"sellquantity":"0"},"' .
            $date1->modify('+1 days')->format('Y-m-d') . '":{"sellquantity":"0"},'.
            '"' . $date1->modify('+1 days')->format('Y-m-d') . '":{"sellquantity":"10"}}'
            :
            '{"' . $date2->format('Y-m-d') . '":{"sellquantity":"10"},"' .
            $date2->modify('+1 days')->format('Y-m-d') . '":{"sellquantity":"10"},"' .
            $date2->modify('+1 days')->format('Y-m-d') . '":{"sellquantity":"10"},'.
            '"' . $date2->modify('+1 days')->format('Y-m-d') . '":{"sellquantity":"10"},"' .
            $date2->modify('+1 days')->format('Y-m-d') . '":{"sellquantity":"10"},"' .
            $date2->modify('+1 days')->format('Y-m-d') . '":{"sellquantity":"10"},'.
            '"' . $date2->modify('+1 days')->format('Y-m-d') . '":{"sellquantity":"10"}}';
    }

    protected function getUpdatePricesRequestData($isDefault): string
    {
        return $isDefault
            ?
            '{"2019-07-16":{"closed":"0","price1":"1200","price2":"2100"},"2019-07-17":{"closed":"0","price1":"1200",'.
            '"price2":"2100"},"2019-07-18":{"closed":"0","price1":"1200","price2":"2100"},"2019-07-19":{"closed":"1"},'.
            '"2019-07-20":{"closed":"1"},"2019-07-21":{"closed":"1","price1":"1200","price2":"2100"},"2019-07-22"'.
            ':{"closed":"0","price1":"1200","price2":"2100"}}'
            :
            '{"2019-07-16":{"closed":"0","price1":"1200","price2":"2100"},"2019-07-17":{"closed":"0","price1":"1200",'.
            '"price2":"2100"},"2019-07-18":{"closed":"0","price1":"1200","price2":"2100"},"2019-07-19":{"closed":"0",'.
            '"price1":"1200","price2":"2100"},"2019-07-20":{"closed":"0","price1":"1200","price2":"2100"},'.
            '"2019-07-21":{"closed":"0","price1":"1200","price2":"2100"},"2019-07-22":{"closed":"0",'.
            '"price1":"1200","price2":"2100"}}';
    }

    public function testUpdatePrices(): void
    {
        $this->unsetPriceCache((clone $this->startDate)->modify('+3 days'), true);
        $this->unsetPriceCache((clone $this->startDate)->modify('+4 days'));
        $this->setRestriction((clone $this->startDate)->modify('+5 days'));
        $this->setMock(self::UPDATE_PRICES);

        $cm = $this->container->get('mbh.channelmanager.vashotel');
        $cm->updatePrices($this->startDate, $this->endDate);
    }

    public function testUpdateRestrictions(): void
    {
        $this->unsetPriceCache((clone $this->startDate)->modify('+3 days'), true);
        $this->unsetPriceCache((clone $this->startDate)->modify('+4 days'));
        $this->setRestriction((clone $this->startDate)->modify('+5 days'));
        $this->setMock(self::UPDATE_RESTRICTIONS);

        $cm = $this->container->get('mbh.channelmanager.vashotel');
        $cm->updateRestrictions($this->startDate, $this->endDate);
    }
}
