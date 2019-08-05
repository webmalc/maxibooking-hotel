<?php

namespace Tests\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\BaseBundle\Lib\Test\ChannelManagerServiceTestCase;
use MBH\Bundle\BaseBundle\Service\Cache;
use MBH\Bundle\ChannelManagerBundle\Document\HundredOneHotelsConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Services\HundredOneHotels;
use Symfony\Component\DependencyInjection\ContainerInterface;

class HundredOneHotelsTest extends ChannelManagerServiceTestCase
{
    protected const UPDATE_PRICES = 'updatePrices';
    protected const UPDATE_RESTRICTIONS = 'updateRestrictions';

    protected const HOH_HOTEL_ID1 = 101;
    protected const HOH_HOTEL_ID2 = 202;

    /**@var ContainerInterface */
    private $container;

    /**@var \DateTime */
    private $startDate;

    /**@var \DateTime */
    private $endDate;

    /**@var HundredOneHotels */
    private $hoh;

    /**@var \DateTime */
    private $beginDateHelper;

    private $datum = true;

    /**
     * @var Cache
     */
    private $cache;

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
        $this->endDate = new \DateTime('midnight +30 days');
        $this->hoh = new HundredOneHotels($this->container);
        $this->cache = $this->container->get('mbh.cache');
    }

    protected function getServiceHotelIdByIsDefault(bool $isDefault): int
    {
        return $isDefault ? self::HOH_HOTEL_ID1 : self::HOH_HOTEL_ID2;
    }

    protected function getServiceConfig(): ChannelManagerConfigInterface
    {
        return new HundredOneHotelsConfig();
    }

    protected function setMock($method): void
    {
        $mock = \Mockery::mock(HundredOneHotels::class, [$this->container])->makePartial();

        $mock->shouldReceive('pullTariffs')->andReturnUsing(function() {
            $serviceTariffs['ID1']['rooms'] = $this->getServiceRoomIds($this->datum);
            $serviceTariffs['ID1']['occupantCount'] = 1;
            $this->datum = !$this->datum;

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

        $this->container->set('mbh.channelmanager.hundred_one_hotels', $mock);
    }

    protected function mockUpdatePricesSend($mock): void
    {
        $mock->shouldReceive('send')->andReturnUsing(function(...$data) {
            $this->assertEquals(
                json_decode($this->getUpdatePricesRequestData(!$this->datum), true),
                json_decode($data[1]['request'], true)
            );

            $this->cache->clear('price_caches_fetch', null, null, true);
        });
    }

    protected function mockUpdateRestrictionsSend($mock): void
    {
        $mock->shouldReceive('send')->andReturnUsing(function(...$data) {
            $this->assertEquals(
                json_decode($this->getUpdateRestrictionsRequestData(!$this->datum), true),
                json_decode($data[1]['request'], true)
            );
        });
    }

    public function testUpdatePrices(): void
    {
        $this->cache->clear('price_caches_fetch', null, null, true);
        $this->unsetPriceCache((clone $this->startDate)->modify('+3 days'), true);
        $this->unsetPriceCache((clone $this->startDate)->modify('+4 days'));
        $this->setRestriction((clone $this->startDate)->modify('+5 days'));
        $this->setMock(self::UPDATE_PRICES);

        $cm = $this->container->get('mbh.channelmanager.hundred_one_hotels');
        $cm->updatePrices($this->startDate, $this->endDate);
    }

    public function testUpdateRestrictions(): void
    {
        $this->unsetPriceCache((clone $this->startDate)->modify('+3 days'), true);
        $this->unsetPriceCache((clone $this->startDate)->modify('+4 days'));
        $this->setRestriction((clone $this->startDate)->modify('+5 days'));
        $this->setMock(self::UPDATE_RESTRICTIONS);

        $cm = $this->container->get('mbh.channelmanager.hundred_one_hotels');
        $cm->updateRestrictions($this->startDate, $this->endDate);
    }

    public function testGetConfig(): void
    {
        $configs = $this->hoh->getConfig();

        $this->assertCount(2, $configs);
        $this->assertInstanceOf(ChannelManagerConfigInterface::class, $configs[0]);
        $this->assertInstanceOf(ChannelManagerConfigInterface::class, $configs[1]);
    }

    public function testUpdateRooms(): void
    {
        /** @var HundredOneHotels $hoh */
        $hoh = \Mockery::mock(HundredOneHotels::class, [$this->container])->makePartial();
        $hoh->shouldReceive('send')->andReturn(true);
        $hoh->shouldReceive('checkResponse')->andReturn(true);

        $this->assertTrue($hoh->updateRooms($this->startDate, $this->endDate));
    }

    public function testHOHRequestFormatter(): void
    {
        /** @var HundredOneHotels $hoh */
        $hoh = \Mockery::mock(HundredOneHotels::class, [$this->container])->makePartial();
        $num = $this->intInc();
        $hoh->shouldReceive('send')->andReturnUsing(function (...$data) use ($num) {
            $this->assertEquals($this->getRequestData($num() ? false : true), $data[1]['request']);
        });

        $hoh->updateRooms($this->startDate, $this->endDate);
    }

    private function intInc()
    {
        $num = -1;

        return function () use (&$num) {
            $num++;

            return $num;
        };
    }

    /**
     * @param bool $init
     * @return string
     */
    private function dateInc(bool $init = false)
    {
        if ($init) {
            $this->beginDateHelper = clone $this->startDate;

            return $this->beginDateHelper->format('Y-m-d');
        }

        return $this->beginDateHelper->modify('+1 day')->format('Y-m-d');
    }

    /**
     * @param bool $isDefaultHotel
     * @return string
     */
    private function getRequestData($isDefaultHotel)
    {
        if ($isDefaultHotel) {
            $defRoomsId = $this->getServiceRoomIds(true);
            return '{"api_key":null,"hotel_id":'.self::HOH_HOTEL_ID1.',"service":"set_calendar","data":[{"day":"'.$this->dateInc(true).'",'.
                '"quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":3,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'"'.
                ':10,"'.$defRoomsId[1].'":2,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":1,'.
                '"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":6,"'.$defRoomsId[2].'":10}},'.
                '{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":5,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'",'.
                '"quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":5,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,'.
                '"'.$defRoomsId[1].'":8,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":8,"'.$defRoomsId[2].'":10}},'.
                '{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":5,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota"'.
                ':{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":5,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":4,'.
                '"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":7,"'.$defRoomsId[2].'":10}},{"day":'.
                '"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":7,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":'.
                '{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":7,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":7,'.
                '"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":7,"'.$defRoomsId[2].'":10}},{"day":'.
                '"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":8,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'"'.
                ':10,"'.$defRoomsId[1].'":8,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}}'.
                ',{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":'.
                '{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,'.
                '"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},{"day":'.
                '"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'"'.
                ':10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}}'.
                ',{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":'.
                '{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,'.
                '"'.$defRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}},{"day":'.
                '"'.$this->dateInc().'","quota":{"'.$defRoomsId[0].'":10,"'.$defRoomsId[1].'":10,"'.$defRoomsId[2].'":10}}]}';
        } else {
            $notDefRoomsId = $this->getServiceRoomIds(false);
            return '{"api_key":null,"hotel_id":'.self::HOH_HOTEL_ID2.',"service":"set_calendar","data":[{"day":"'.$this->dateInc(true).'","quota":'.
                '{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,'.
                '"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,'.
                '"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},'.
                '{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'",'.
                '"quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":'.
                '{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,'.
                '"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,'.
                '"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},'.
                '{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'",'.
                '"quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":'.
                '{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,'.
                '"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,'.
                '"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},'.
                '{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'",'.
                '"quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":'.
                '{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,'.
                '"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,'.
                '"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},'.
                '{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'",'.
                '"quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":'.
                '{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,'.
                '"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,'.
                '"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},'.
                '{"day":"'.$this->dateInc().'","quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'",'.
                '"quota":{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}},{"day":"'.$this->dateInc().'","quota":'.
                '{"'.$notDefRoomsId[0].'":10,"'.$notDefRoomsId[1].'":10,"'.$notDefRoomsId[2].'":10}}]}';
        }
    }

    protected function getUpdateRestrictionsRequestData($isDefaultHotel): string
    {
        $date1 = clone $this->startDate;
        $date2 = clone $this->startDate;

        return $isDefaultHotel
            ?
            '{
   "api_key": null,
   "hotel_id": 101,
   "service": "set_calendar",
   "data": [
      {
         "day": "'. $date1->format('Y-m-d') .'",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 1,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 1,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 1,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 2
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date1->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "closed_to_arrival": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "def_room1": {
               "ID1": 0
            },
            "def_room2": {
               "ID1": 0
            },
            "def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "def_room1": {
               "ID1": 1
            },
            "def_room2": {
               "ID1": 3
            },
            "def_room3": {
               "ID1": 2
            }
         }
      }
   ]
}'
            :
            '{
   "api_key": null,
   "hotel_id": 202,
   "service": "set_calendar",
   "data": [
      {
         "day": "'. $date2->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      },
      {
         "day": "'. $date2->modify('+1 days')->format('Y-m-d') .'",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "closed_to_arrival": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "closed_to_departure": {
            "not_def_room1": {
               "ID1": 0
            },
            "not_def_room2": {
               "ID1": 0
            },
            "not_def_room3": {
               "ID1": 0
            }
         },
         "min_stay": {
            "not_def_room1": {
               "ID1": 1
            },
            "not_def_room2": {
               "ID1": 3
            },
            "not_def_room3": {
               "ID1": 2
            }
         }
      }
   ]
}';
    }

    protected function getUpdatePricesRequestData($isDefaultHotel): string
    {
        $date1 = clone $this->startDate;
        $date2 = clone $this->startDate;

        return $isDefaultHotel
            ?
            '{
   "api_key": null,
   "hotel_id": ' . self::HOH_HOTEL_ID1 . ',
   "service": "set_calendar",
   "data": [
      {
         "day": "' . $date1->format('Y-m-d') . '",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room1": {
               "ID1": 1200
            },
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room1": {
               "ID1": 1200
            },
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room1": {
               "ID1": 1200
            },
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 1,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 1,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 1,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room1": {
               "ID1": 1200
            },
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room1": {
               "ID1": 1200
            },
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room1": {
               "ID1": 1200
            },
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room1": {
               "ID1": 1200
            },
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room1": {
               "ID1": 1200
            },
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room1": {
               "ID1": 1200
            },
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room1": {
               "ID1": 1200
            },
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room1": {
               "ID1": 1200
            },
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room1": {
               "ID1": 1200
            },
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room1": {
               "ID1": 1200
            },
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room1": {
               "ID1": 1200
            },
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room1": {
               "ID1": 1200
            },
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room1": {
               "ID1": 1200
            },
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room1": {
               "ID1": 1200
            },
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room1": {
               "ID1": 1200
            },
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room1": {
               "ID1": 1200
            },
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room1": {
               "ID1": 1200
            },
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room1": {
               "ID1": 1200
            },
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room1": {
               "ID1": 1200
            },
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room1": {
               "ID1": 1200
            },
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room1": {
               "ID1": 1200
            },
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room1": {
               "ID1": 1200
            },
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room1": {
               "ID1": 1200
            },
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room1": {
               "ID1": 1200
            },
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room1": {
               "ID1": 1200
            },
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date1->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "def_room1": 0,
            "def_room2": 0,
            "def_room3": 0
         },
         "prices": {
            "def_room1": {
               "ID1": 1200
            },
            "def_room2": {
               "ID1": 1500
            },
            "def_room3": {
               "ID1": 2200
            }
         }
      }
   ]
}'
            :
            '{
   "api_key": null,
   "hotel_id": ' . self::HOH_HOTEL_ID2 . ',
   "service": "set_calendar",
   "data": [
      {
         "day": "' . $date2->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      },
      {
         "day": "' . $date2->modify('+1 day')->format('Y-m-d') . '",
         "closed": {
            "not_def_room1": 0,
            "not_def_room2": 0,
            "not_def_room3": 0
         },
         "prices": {
            "not_def_room1": {
               "ID1": 1200
            },
            "not_def_room2": {
               "ID1": 1500
            },
            "not_def_room3": {
               "ID1": 2200
            }
         }
      }
   ]
}';
    }
}
