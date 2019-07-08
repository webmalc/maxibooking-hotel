<?php

namespace Tests\Bundle\ChannelManagerBundle\Services;


use MBH\Bundle\BaseBundle\Lib\Test\ChannelManagerServiceTestCase;
use MBH\Bundle\ChannelManagerBundle\Document\OstrovokConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\Ostrovok\OstrovokApiService;
use MBH\Bundle\ChannelManagerBundle\Services\Ostrovok;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;

class OstrovokExtendedTest extends ChannelManagerServiceTestCase
{
    protected const OST_HOTEL_ID1 = 101;
    protected const OST_HOTEL_ID2 = 202;

    /**@var ContainerInterface */
    private $container;

    /**@var \DateTime */
    private $startDate;

    /**@var \DateTime */
    private $endDate;

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
    }

    protected function getServiceHotelIdByIsDefault(bool $isDefault): int
    {
        return $isDefault ? self::OST_HOTEL_ID1 : self::OST_HOTEL_ID2;
    }

    protected function getServiceConfig(): ChannelManagerConfigInterface
    {
        return new OstrovokConfig();
    }

    protected function initConfig($isDefault): void
    {
        $hotelId = $isDefault
            ? $this->getServiceHotelIdByIsDefault(true)
            : $this->getServiceHotelIdByIsDefault(false);

        /** @var ChannelManagerConfigInterface $config */
        $config = $this->getServiceConfig();
        $config->setHotelId($hotelId);
        $config->setHotel($this->getHotelByIsDefault($isDefault));

        $rt = $this->getHotelByIsDefault($isDefault)->getRoomTypes()->get(2);
        $this->getHotelByIsDefault($isDefault)->removeRoomType($rt);

        $serviceRoomIds = $this->getServiceRoomIds($isDefault);
        foreach ($this->getHotelByIsDefault($isDefault)->getRoomTypes() as $number => $roomType) {
            $config->addRoom((new Room())->setRoomId($serviceRoomIds[$number])->setRoomType($roomType));
        }

        if ($isDefault) {
            $tariff = (new Tariff())
                ->setTariff($this->getHotelByIsDefault($isDefault)->getBaseTariff())
                ->setTariffId(629788);
            $config->addTariff($tariff);
            $tariff = (new Tariff())
                ->setTariff($this->getHotelByIsDefault($isDefault)->getBaseTariff())
                ->setTariffId(647512);
            $config->addTariff($tariff);
        } else {
            $tariff = (new Tariff())
                ->setTariff($this->getHotelByIsDefault($isDefault)->getBaseTariff())
                ->setTariffId(790780);
            $config->addTariff($tariff);
            $tariff = (new Tariff())
                ->setTariff($this->getHotelByIsDefault($isDefault)->getBaseTariff())
                ->setTariffId(790798);
            $config->addTariff($tariff);
        }
        $config->setIsEnabled(true);
        $config->setIsTariffsConfigured(true);
        $config->setIsRoomsConfigured(true);
        $config->setIsConfirmedWithDataWarnings(true);

        $hotelSetConfigMethod = 'set'.(new \ReflectionClass($config))->getShortName();

        $this->getHotelByIsDefault($isDefault)->$hotelSetConfigMethod($config);

        $this->dm->persist($config);
        $this->dm->flush();
    }

    protected function getServiceRoomIds($isDefault = true): array
    {
        return $isDefault ? [257008, 261630] : [296710, 296715];
    }

    protected function mockApiBrowser($flag): void
    {
        $mock = \Mockery::mock(OstrovokApiService::class)->makePartial();

        $mock->shouldReceive('getOccupancies')->andReturnUsing(function (...$data) {
            $response = $data[0]['hotel'] === self::OST_HOTEL_ID1
                ? $this->getOccupanciesResponseData(true)
                : $this->getOccupanciesResponseData(false);
            $response = json_decode($response, true);
            $byKey = $data[1] ?? false;
            $data = $response['occupancies'];
            if ($byKey) {
                foreach ($data as $occupancy) {
                    $result[$occupancy['id']] = $occupancy;
                }
            } else {
                $result = $data;
            }

            return $result ?? [];
        });

        $mock->shouldReceive('getRatePlans')->andReturnUsing(function (...$data) {
            $response = $data[0]['hotel'] === self::OST_HOTEL_ID1
                ? $this->getRatePlans(true)
                : $this->getRatePlans(false);
            $response = json_decode($response, true);
            $isShowDeleted = $data[1] ?? false;
            $onlyParent = $data[2] ?? true;
            $rate_plans = [];
            foreach ($response['rate_plans'] as $rate) {
                if($rate['status'] === 'X' && !$isShowDeleted) {
                    continue;
                }
                if ($onlyParent && $rate['parent'] !== null) {
                    continue;
                }
                $rate_plans[] = $rate;
            }

            return $rate_plans;
        });

        $mock->shouldReceive('updateRNA')->andReturnUsing(function (...$data) use ($flag) {
            switch ($flag) {
                case 0:
                    $this->assertEquals((array)json_decode($this->getRequestData(), true), $data[0]);
                    break;
                case 1:
                    $this->assertEquals((array)json_decode($this->getRatePlansPutData(), true), $data[0]);
                    break;
            }

            return [];
        });

        $callableRatePlanData = $this->getUpdateRatePlansPutRequest();

        $mock->shouldReceive('updateRatePlan')->andReturnUsing(function (...$data) use ($callableRatePlanData) {
            $this->assertEquals((array)json_decode($callableRatePlanData(), true), $data);
        });

        $this->container->set('ostrovok_api_service', $mock);
    }

    public function testUpdatePrices(): void
    {
        $this->mockApiBrowser(0);
        $ost = new Ostrovok($this->container);
        $ost->updatePrices($this->startDate, $this->endDate);
    }

    public function testUpdateRestrictions(): void
    {
        $this->mockApiBrowser(1);
        $ost = new Ostrovok($this->container);
        $ost->updateRestrictions($this->startDate, $this->endDate);
    }

    protected function getUpdateRatePlansPutRequest(): callable
    {
        $num = -1;

        $arr = [
            '[629788,' . self::OST_HOTEL_ID1 . ',257008,{"advance":null,"last_minute":null,"free_nights":null,"discount":null,
            "discount_unit":null,"meal_plan":null,"meal_plan_cost":null,"meal_plan_available":false,
            "meal_plan_included":null,"cancellation_available":true,"cancellation_lead_time":480,
            "cancellation_penalty_nights":null,"deposit_available":true,"deposit_returnable":true,
            "deposit_rate":50,"deposit_unit":1,"no_show_rate":50,"no_show_unit":1,"plan_date_start_at":null,
            "plan_date_end_at":null,"booking_date_start_at":null,"booking_date_end_at":null,"min_stay_arrival":null,
            "max_stay_arrival":null,"min_stay_through":3,"max_stay_through":null,"status":"P",
            "name":"\u0421\u0442\u0430\u043d\u0434\u0430\u0440\u0442\u043d\u044b\u0439 \u0442\u0430\u0440\u0438\u0444",
            "description":"","external_id":null,"id":629788,"room_category":257008,"hotel":271728830}]'
            ,
            '[647512,' . self::OST_HOTEL_ID1 . ',261630,{"advance":null,"last_minute":null,"free_nights":null,"discount":null,
            "discount_unit":null,"meal_plan":null,"meal_plan_cost":null,"meal_plan_available":false,
            "meal_plan_included":null,"cancellation_available":true,"cancellation_lead_time":480,
            "cancellation_penalty_nights":null,"deposit_available":false,"deposit_returnable":true,
            "deposit_rate":50,"deposit_unit":1,"no_show_rate":50,"no_show_unit":1,"plan_date_start_at":null,
            "plan_date_end_at":null,"booking_date_start_at":null,"booking_date_end_at":null,"min_stay_arrival":null,
            "max_stay_arrival":null,"min_stay_through":3,"max_stay_through":null,"status":"P",
            "name":"\u0421\u0442\u0430\u043d\u0434\u0430\u0440\u0442\u043d\u044b\u0439 \u0442\u0430\u0440\u0438\u0444",
            "description":"","external_id":null,"id":647512,"room_category":261630,"hotel":271728830}]'
            ,
            '[790780,' . self::OST_HOTEL_ID2 . ',296710,{"advance":null,"last_minute":null,"free_nights":null,"discount":null,
            "discount_unit":null,"meal_plan":null,"meal_plan_cost":null,"meal_plan_available":false,
            "meal_plan_included":null,"cancellation_available":true,"cancellation_lead_time":24,
            "cancellation_penalty_nights":1,"deposit_available":true,"deposit_returnable":true,
            "deposit_rate":15,"deposit_unit":1,"no_show_rate":1,"no_show_unit":3,"plan_date_start_at":null,
            "plan_date_end_at":null,"booking_date_start_at":null,"booking_date_end_at":null,"min_stay_arrival":null,
            "max_stay_arrival":null,"min_stay_through":null,"max_stay_through":null,"status":"P",
            "name":"\u0421\u0442\u0430\u043d\u0434\u0430\u0440\u0442\u043d\u044b\u0439 \u0442\u0430\u0440\u0438\u0444",
            "description":"","external_id":null,"id":790780,"room_category":296710,"hotel":380171626}]'
            ,
            '[790798,' . self::OST_HOTEL_ID2 . ',296715,{"advance":null,"last_minute":null,"free_nights":null,"discount":null,
            "discount_unit":null,"meal_plan":null,"meal_plan_cost":null,"meal_plan_available":false,
            "meal_plan_included":null,"cancellation_available":true,"cancellation_lead_time":24,
            "cancellation_penalty_nights":1,"deposit_available":true,"deposit_returnable":true,"deposit_rate":15,
            "deposit_unit":1,"no_show_rate":1,"no_show_unit":3,"plan_date_start_at":null,"plan_date_end_at":null,
            "booking_date_start_at":null,"booking_date_end_at":null,"min_stay_arrival":null,"max_stay_arrival":null,
            "min_stay_through":null,"max_stay_through":null,"status":"P",
            "name":"\u0421\u0442\u0430\u043d\u0434\u0430\u0440\u0442\u043d\u044b\u0439 \u0442\u0430\u0440\u0438\u0444",
            "description":"","external_id":null,"id":790798,"room_category":296715,"hotel":380171626}]'
        ];

        return static function () use (&$num, $arr) {
            $num++;

            return $arr[$num];
        };
    }

    protected function getRequestData(): string
    {
        $dateStart = $this->startDate->format('Y-m-d');
        $dateEnd = $this->endDate->format('Y-m-d');

        return '{
                  "occupancies": [
                    {
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "price": 1200,
                      "occupancy": 668236,
                      "room_category": 257008,
                      "rate_plan": 629788,
                      "plan_date_start_at": "' . $dateStart . '",
                      "plan_date_end_at": "' . $dateEnd . '",
                      "format": "json"
                    },
                    {
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "price": 2100,
                      "occupancy": 668237,
                      "room_category": 257008,
                      "rate_plan": 629788,
                      "plan_date_start_at": "' . $dateStart . '",
                      "plan_date_end_at": "' . $dateEnd . '",
                      "format": "json"
                    },
                    {
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "price": 0,
                      "occupancy": 668238,
                      "room_category": 257008,
                      "rate_plan": 629788,
                      "plan_date_start_at": "' . $dateStart . '",
                      "plan_date_end_at": "' . $dateEnd . '",
                      "format": "json"
                    },
                    {
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "price": 0,
                      "occupancy": 668239,
                      "room_category": 257008,
                      "rate_plan": 629788,
                      "plan_date_start_at": "' . $dateStart . '",
                      "plan_date_end_at": "' . $dateEnd . '",
                      "format": "json"
                    },
                    {
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "price": 0,
                      "occupancy": 668240,
                      "room_category": 257008,
                      "rate_plan": 629788,
                      "plan_date_start_at": "' . $dateStart . '",
                      "plan_date_end_at": "' . $dateEnd . '",
                      "format": "json"
                    },
                    {
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "price": 0,
                      "occupancy": 668241,
                      "room_category": 257008,
                      "rate_plan": 629788,
                      "plan_date_start_at": "' . $dateStart . '",
                      "plan_date_end_at": "' . $dateEnd . '",
                      "format": "json"
                    },
                    {
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "price": 1500,
                      "occupancy": 681796,
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "plan_date_start_at": "' . $dateStart . '",
                      "plan_date_end_at": "' . $dateEnd . '",
                      "format": "json"
                    },
                    {
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "price": 1500,
                      "occupancy": 681797,
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "plan_date_start_at": "' . $dateStart . '",
                      "plan_date_end_at": "' . $dateEnd . '",
                      "format": "json"
                    },
                    {
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "price": 2500,
                      "occupancy": 681798,
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "plan_date_start_at": "' . $dateStart . '",
                      "plan_date_end_at": "' . $dateEnd . '",
                      "format": "json"
                    },
                    {
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "price": 0,
                      "occupancy": 681799,
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "plan_date_start_at": "' . $dateStart . '",
                      "plan_date_end_at": "' . $dateEnd . '",
                      "format": "json"
                    },
                    {
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "price": 1200,
                      "occupancy": 784372,
                      "room_category": 296710,
                      "rate_plan": 790780,
                      "plan_date_start_at": "' . $dateStart . '",
                      "plan_date_end_at": "' . $dateEnd . '",
                      "format": "json"
                    },
                    {
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "price": 2100,
                      "occupancy": 784373,
                      "room_category": 296710,
                      "rate_plan": 790780,
                      "plan_date_start_at": "' . $dateStart . '",
                      "plan_date_end_at": "' . $dateEnd . '",
                      "format": "json"
                    },
                    {
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "price": 0,
                      "occupancy": 784374,
                      "room_category": 296710,
                      "rate_plan": 790780,
                      "plan_date_start_at": "' . $dateStart . '",
                      "plan_date_end_at": "' . $dateEnd . '",
                      "format": "json"
                    },
                    {
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "price": 0,
                      "occupancy": 784375,
                      "room_category": 296710,
                      "rate_plan": 790780,
                      "plan_date_start_at": "' . $dateStart . '",
                      "plan_date_end_at": "' . $dateEnd . '",
                      "format": "json"
                    },
                    {
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "price": 1500,
                      "occupancy": 784384,
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "plan_date_start_at": "' . $dateStart . '",
                      "plan_date_end_at": "' . $dateEnd . '",
                      "format": "json"
                    },
                    {
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "price": 1500,
                      "occupancy": 784385,
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "plan_date_start_at": "' . $dateStart . '",
                      "plan_date_end_at": "' . $dateEnd . '",
                      "format": "json"
                    },
                    {
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "price": 2500,
                      "occupancy": 784386,
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "plan_date_start_at": "' . $dateStart . '",
                      "plan_date_end_at": "' . $dateEnd . '",
                      "format": "json"
                    },
                    {
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "price": 0,
                      "occupancy": 784387,
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "plan_date_start_at": "' . $dateStart . '",
                      "plan_date_end_at": "' . $dateEnd . '",
                      "format": "json"
                    },
                    {
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "price": 0,
                      "occupancy": 784388,
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "plan_date_start_at": "' . $dateStart . '",
                      "plan_date_end_at": "' . $dateEnd . '",
                      "format": "json"
                    },
                    {
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "price": 0,
                      "occupancy": 784389,
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "plan_date_start_at": "' . $dateStart . '",
                      "plan_date_end_at": "' . $dateEnd . '",
                      "format": "json"
                    }
                  ]
                }';
    }

    protected function dateIncClosure(): callable
    {
        $date = (clone $this->startDate);

        return static function() use (&$date) {
            $date->modify('+1 day');

            return $date->format('Y-m-d');
        };
    }

    protected function getRatePlansPutData(): string
    {
        $dateStart = $this->startDate->format('Y-m-d');
        $dateEnd = $this->endDate->format('Y-m-d');

        $date1 = $this->dateIncClosure();
        $date2 = $this->dateIncClosure();
        $date3 = $this->dateIncClosure();
        $date4 = $this->dateIncClosure();

        return '{
                  "rate_plans": [
                    { 
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "' . $dateStart . '",
                      "plan_date_end_at": "' . $dateEnd . '",
                      "room_category": 257008,
                      "rate_plan": 629788,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": null,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "' . $dateStart . '",
                      "plan_date_end_at": "' . $dateStart . '",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date1().'",
                      "plan_date_end_at": "'.$date2().'",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date1().'",
                      "plan_date_end_at": "'.$date2().'",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date1().'",
                      "plan_date_end_at": "'.$date2().'",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date1().'",
                      "plan_date_end_at": "'.$date2().'",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date1().'",
                      "plan_date_end_at": "'.$date2().'",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date1().'",
                      "plan_date_end_at": "'.$date2().'",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date1().'",
                      "plan_date_end_at": "'.$date2().'",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date1().'",
                      "plan_date_end_at": "'.$date2().'",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date1().'",
                      "plan_date_end_at": "'.$date2().'",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date1().'",
                      "plan_date_end_at": "'.$date2().'",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date1().'",
                      "plan_date_end_at": "'.$date2().'",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date1().'",
                      "plan_date_end_at": "'.$date2().'",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date1().'",
                      "plan_date_end_at": "'.$date2().'",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date1().'",
                      "plan_date_end_at": "'.$date2().'",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date1().'",
                      "plan_date_end_at": "'.$date2().'",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date1().'",
                      "plan_date_end_at": "'.$date2().'",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date1().'",
                      "plan_date_end_at": "'.$date2().'",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date1().'",
                      "plan_date_end_at": "'.$date2().'",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date1().'",
                      "plan_date_end_at": "'.$date2().'",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date1().'",
                      "plan_date_end_at": "'.$date2().'",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date1().'",
                      "plan_date_end_at": "'.$date2().'",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date1().'",
                      "plan_date_end_at": "'.$date2().'",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date1().'",
                      "plan_date_end_at": "'.$date2().'",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date1().'",
                      "plan_date_end_at": "'.$date2().'",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date1().'",
                      "plan_date_end_at": "'.$date2().'",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date1().'",
                      "plan_date_end_at": "'.$date2().'",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date1().'",
                      "plan_date_end_at": "'.$date2().'",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date1().'",
                      "plan_date_end_at": "'.$date2().'",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date1().'",
                      "plan_date_end_at": "'.$date2().'",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "' . $dateEnd . '",
                      "plan_date_end_at": "' . $dateEnd . '",
                      "room_category": 261630,
                      "rate_plan": 647512,
                      "hotel": ' . self::OST_HOTEL_ID1 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "' . $dateStart . '",
                      "plan_date_end_at": "' . $dateEnd . '",
                      "room_category": 296710,
                      "rate_plan": 790780,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": null,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "' . $dateStart . '",
                      "plan_date_end_at": "' . $dateStart . '",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date3().'",
                      "plan_date_end_at": "'.$date4().'",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date3().'",
                      "plan_date_end_at": "'.$date4().'",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date3().'",
                      "plan_date_end_at": "'.$date4().'",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date3().'",
                      "plan_date_end_at": "'.$date4().'",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date3().'",
                      "plan_date_end_at": "'.$date4().'",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date3().'",
                      "plan_date_end_at": "'.$date4().'",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date3().'",
                      "plan_date_end_at": "'.$date4().'",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date3().'",
                      "plan_date_end_at": "'.$date4().'",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date3().'",
                      "plan_date_end_at": "'.$date4().'",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date3().'",
                      "plan_date_end_at": "'.$date4().'",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date3().'",
                      "plan_date_end_at": "'.$date4().'",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date3().'",
                      "plan_date_end_at": "'.$date4().'",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date3().'",
                      "plan_date_end_at": "'.$date4().'",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date3().'",
                      "plan_date_end_at": "'.$date4().'",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date3().'",
                      "plan_date_end_at": "'.$date4().'",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date3().'",
                      "plan_date_end_at": "'.$date4().'",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date3().'",
                      "plan_date_end_at": "'.$date4().'",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date3().'",
                      "plan_date_end_at": "'.$date4().'",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date3().'",
                      "plan_date_end_at": "'.$date4().'",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date3().'",
                      "plan_date_end_at": "'.$date4().'",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date3().'",
                      "plan_date_end_at": "'.$date4().'",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date3().'",
                      "plan_date_end_at": "'.$date4().'",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date3().'",
                      "plan_date_end_at": "'.$date4().'",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date3().'",
                      "plan_date_end_at": "'.$date4().'",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date3().'",
                      "plan_date_end_at": "'.$date4().'",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date3().'",
                      "plan_date_end_at": "'.$date4().'",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date3().'",
                      "plan_date_end_at": "'.$date4().'",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date3().'",
                      "plan_date_end_at": "'.$date4().'",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "'.$date3().'",
                      "plan_date_end_at": "'.$date4().'",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    },
                    {
                      "disable_flexible": false,
                      "last_minute": null,
                      "advance": null,
                      "plan_date_start_at": "' . $dateEnd . '",
                      "plan_date_end_at": "' . $dateEnd . '",
                      "room_category": 296715,
                      "rate_plan": 790798,
                      "hotel": ' . self::OST_HOTEL_ID2 . ',
                      "min_stay_arrival": null,
                      "max_stay_arrival": null,
                      "min_stay_through": 3,
                      "max_stay_through": null,
                      "closed_on_arrival": false,
                      "closed_on_departure": false,
                      "format": "json"
                    }
                  ]
                }
                ';
    }

    protected function getRatePlans($isDefault): string
    {
        $data = $isDefault
            ?
            '{
              "limits": 0,
              "page": 1,
              "num_pages": 1,
              "rate_plans": [
                {
                  "id": 629788,
                  "name": "\u0421\u0442\u0430\u043d\u0434\u0430\u0440\u0442\u043d\u044b\u0439 \u0442\u0430\u0440\u0438\u0444",
                  "room_category": 257008,
                  "advance": null,
                  "last_minute": null,
                  "free_nights": null,
                  "discount": null,
                  "discount_unit": null,
                  "meal_plan": null,
                  "meal_plan_cost": null,
                  "meal_plan_available": false,
                  "meal_plan_included": null,
                  "cancellation_available": true,
                  "cancellation_lead_time": 480,
                  "cancellation_penalty_nights": null,
                  "cancellation_penalty_rate": 50,
                  "deposit_available": true,
                  "deposit_returnable": true,
                  "deposit_rate": 50,
                  "deposit_unit": 1,
                  "no_show_rate": 50,
                  "no_show_unit": 1,
                  "plan_date_start_at": null,
                  "plan_date_end_at": null,
                  "booking_date_start_at": null,
                  "booking_date_end_at": null,
                  "min_stay_arrival": null,
                  "max_stay_arrival": null,
                  "min_stay_through": 3,
                  "max_stay_through": null,
                  "status": "P",
                  "description": "",
                  "possible_occupancies": [
                    668236,
                    668237,
                    668238,
                    668239,
                    668240,
                    668241
                  ],
                  "external_id": null,
                  "hotel": 271728830,
                  "parent": null
                },
                {
                  "id": 629789,
                  "name": "\u041f\u0435\u0440\u0438\u043e\u0434 \u043f\u0440\u043e\u0436\u0438\u0432\u0430\u043d\u0438\u044f",
                  "room_category": 257008,
                  "advance": null,
                  "last_minute": null,
                  "free_nights": null,
                  "discount": 10.0,
                  "discount_unit": 1,
                  "meal_plan": null,
                  "meal_plan_cost": null,
                  "meal_plan_available": false,
                  "meal_plan_included": false,
                  "cancellation_available": true,
                  "cancellation_lead_time": 24,
                  "cancellation_penalty_nights": 1,
                  "cancellation_penalty_rate": null,
                  "deposit_available": false,
                  "deposit_returnable": true,
                  "deposit_rate": null,
                  "deposit_unit": null,
                  "no_show_rate": 1,
                  "no_show_unit": 3,
                  "plan_date_start_at": null,
                  "plan_date_end_at": null,
                  "booking_date_start_at": null,
                  "booking_date_end_at": null,
                  "min_stay_arrival": null,
                  "max_stay_arrival": null,
                  "min_stay_through": 10,
                  "max_stay_through": null,
                  "status": "X",
                  "description": null,
                  "possible_occupancies": [
                    668236,
                    668237,
                    668238,
                    668239,
                    668240,
                    668241
                  ],
                  "external_id": null,
                  "hotel": 271728830,
                  "parent": 629788
                },
                {
                  "id": 647512,
                  "name": "\u0421\u0442\u0430\u043d\u0434\u0430\u0440\u0442\u043d\u044b\u0439 \u0442\u0430\u0440\u0438\u0444",
                  "room_category": 261630,
                  "advance": null,
                  "last_minute": null,
                  "free_nights": null,
                  "discount": null,
                  "discount_unit": null,
                  "meal_plan": null,
                  "meal_plan_cost": null,
                  "meal_plan_available": false,
                  "meal_plan_included": null,
                  "cancellation_available": true,
                  "cancellation_lead_time": 480,
                  "cancellation_penalty_nights": null,
                  "cancellation_penalty_rate": 50,
                  "deposit_available": false,
                  "deposit_returnable": true,
                  "deposit_rate": 50,
                  "deposit_unit": 1,
                  "no_show_rate": 50,
                  "no_show_unit": 1,
                  "plan_date_start_at": null,
                  "plan_date_end_at": null,
                  "booking_date_start_at": null,
                  "booking_date_end_at": null,
                  "min_stay_arrival": null,
                  "max_stay_arrival": null,
                  "min_stay_through": 3,
                  "max_stay_through": null,
                  "status": "P",
                  "description": "",
                  "possible_occupancies": [
                    681796,
                    681797,
                    681798,
                    681799
                  ],
                  "external_id": null,
                  "hotel": 271728830,
                  "parent": null
                },
                {
                  "id": 647513,
                  "name": "\u041d\u0435\u0432\u043e\u0437\u0432\u0440\u0430\u0442\u043d\u044b\u0439 \u0442\u0430\u0440\u0438\u0444",
                  "room_category": 261630,
                  "advance": null,
                  "last_minute": null,
                  "free_nights": null,
                  "discount": 10.0,
                  "discount_unit": 1,
                  "meal_plan": null,
                  "meal_plan_cost": null,
                  "meal_plan_available": false,
                  "meal_plan_included": false,
                  "cancellation_available": false,
                  "cancellation_lead_time": null,
                  "cancellation_penalty_nights": null,
                  "cancellation_penalty_rate": null,
                  "deposit_available": true,
                  "deposit_returnable": false,
                  "deposit_rate": 100,
                  "deposit_unit": 1,
                  "no_show_rate": 100,
                  "no_show_unit": 1,
                  "plan_date_start_at": null,
                  "plan_date_end_at": null,
                  "booking_date_start_at": null,
                  "booking_date_end_at": null,
                  "min_stay_arrival": null,
                  "max_stay_arrival": null,
                  "min_stay_through": null,
                  "max_stay_through": null,
                  "status": "X",
                  "description": null,
                  "possible_occupancies": [
                    681796,
                    681797,
                    681798,
                    681799
                  ],
                  "external_id": null,
                  "hotel": 271728830,
                  "parent": 647512
                },
                {
                  "id": 647514,
                  "name": "\u0420\u0430\u043d\u043d\u0435\u0435 \u0431\u0440\u043e\u043d\u0438\u0440\u043e\u0432\u0430\u043d\u0438\u0435",
                  "room_category": 261630,
                  "advance": 120,
                  "last_minute": null,
                  "free_nights": null,
                  "discount": 10.0,
                  "discount_unit": 1,
                  "meal_plan": null,
                  "meal_plan_cost": null,
                  "meal_plan_available": false,
                  "meal_plan_included": false,
                  "cancellation_available": true,
                  "cancellation_lead_time": 24,
                  "cancellation_penalty_nights": 1,
                  "cancellation_penalty_rate": null,
                  "deposit_available": false,
                  "deposit_returnable": true,
                  "deposit_rate": null,
                  "deposit_unit": null,
                  "no_show_rate": 1,
                  "no_show_unit": 3,
                  "plan_date_start_at": null,
                  "plan_date_end_at": null,
                  "booking_date_start_at": null,
                  "booking_date_end_at": null,
                  "min_stay_arrival": null,
                  "max_stay_arrival": null,
                  "min_stay_through": null,
                  "max_stay_through": null,
                  "status": "X",
                  "description": null,
                  "possible_occupancies": [
                    681796,
                    681797,
                    681798,
                    681799
                  ],
                  "external_id": null,
                  "hotel": 271728830,
                  "parent": 647512
                },
                {
                  "id": 647515,
                  "name": "\u041f\u0435\u0440\u0438\u043e\u0434 \u043f\u0440\u043e\u0436\u0438\u0432\u0430\u043d\u0438\u044f",
                  "room_category": 261630,
                  "advance": null,
                  "last_minute": null,
                  "free_nights": null,
                  "discount": 10.0,
                  "discount_unit": 1,
                  "meal_plan": null,
                  "meal_plan_cost": null,
                  "meal_plan_available": false,
                  "meal_plan_included": false,
                  "cancellation_available": true,
                  "cancellation_lead_time": 24,
                  "cancellation_penalty_nights": 1,
                  "cancellation_penalty_rate": null,
                  "deposit_available": false,
                  "deposit_returnable": true,
                  "deposit_rate": null,
                  "deposit_unit": null,
                  "no_show_rate": 1,
                  "no_show_unit": 3,
                  "plan_date_start_at": null,
                  "plan_date_end_at": null,
                  "booking_date_start_at": null,
                  "booking_date_end_at": null,
                  "min_stay_arrival": null,
                  "max_stay_arrival": null,
                  "min_stay_through": 10,
                  "max_stay_through": null,
                  "status": "X",
                  "description": null,
                  "possible_occupancies": [
                    681796,
                    681797,
                    681798,
                    681799
                  ],
                  "external_id": null,
                  "hotel": 271728830,
                  "parent": 647512
                },
                {
                  "id": 647516,
                  "name": "\u0414\u043b\u044f \u0430\u043a\u0442\u0438\u0432\u043d\u044b\u0445 \u043f\u043e\u043b\u044c\u0437\u043e\u0432\u0430\u0442\u0435\u043b\u0435\u0439",
                  "room_category": 261630,
                  "advance": null,
                  "last_minute": null,
                  "free_nights": null,
                  "discount": 5.0,
                  "discount_unit": 1,
                  "meal_plan": null,
                  "meal_plan_cost": null,
                  "meal_plan_available": false,
                  "meal_plan_included": false,
                  "cancellation_available": false,
                  "cancellation_lead_time": 24,
                  "cancellation_penalty_nights": 1,
                  "cancellation_penalty_rate": null,
                  "deposit_available": false,
                  "deposit_returnable": true,
                  "deposit_rate": null,
                  "deposit_unit": null,
                  "no_show_rate": 1,
                  "no_show_unit": 3,
                  "plan_date_start_at": null,
                  "plan_date_end_at": null,
                  "booking_date_start_at": null,
                  "booking_date_end_at": null,
                  "min_stay_arrival": null,
                  "max_stay_arrival": null,
                  "min_stay_through": null,
                  "max_stay_through": null,
                  "status": "X",
                  "description": null,
                  "possible_occupancies": [
                    681796,
                    681797,
                    681798,
                    681799
                  ],
                  "external_id": null,
                  "hotel": 271728830,
                  "parent": 647512
                },
                {
                  "id": 647517,
                  "name": "\u0413\u043e\u0440\u044f\u0447\u0435\u0435 \u043f\u0440\u0435\u0434\u043b\u043e\u0436\u0435\u043d\u0438\u0435",
                  "room_category": 261630,
                  "advance": null,
                  "last_minute": 120,
                  "free_nights": null,
                  "discount": 10.0,
                  "discount_unit": 1,
                  "meal_plan": null,
                  "meal_plan_cost": null,
                  "meal_plan_available": false,
                  "meal_plan_included": false,
                  "cancellation_available": false,
                  "cancellation_lead_time": null,
                  "cancellation_penalty_nights": null,
                  "cancellation_penalty_rate": null,
                  "deposit_available": false,
                  "deposit_returnable": true,
                  "deposit_rate": 50,
                  "deposit_unit": 1,
                  "no_show_rate": 1,
                  "no_show_unit": 3,
                  "plan_date_start_at": null,
                  "plan_date_end_at": "2018-06-15",
                  "booking_date_start_at": null,
                  "booking_date_end_at": null,
                  "min_stay_arrival": null,
                  "max_stay_arrival": null,
                  "min_stay_through": null,
                  "max_stay_through": null,
                  "status": "X",
                  "description": null,
                  "possible_occupancies": [
                    681796,
                    681797,
                    681798,
                    681799
                  ],
                  "external_id": null,
                  "hotel": 271728830,
                  "parent": 647512
                },
                {
                  "id": 647518,
                  "name": "\u041d\u0435\u0432\u043e\u0437\u0432\u0440\u0430\u0442\u043d\u044b\u0439 \u0442\u0430\u0440\u0438\u0444",
                  "room_category": 257008,
                  "advance": null,
                  "last_minute": null,
                  "free_nights": null,
                  "discount": 10.0,
                  "discount_unit": 1,
                  "meal_plan": null,
                  "meal_plan_cost": null,
                  "meal_plan_available": false,
                  "meal_plan_included": false,
                  "cancellation_available": false,
                  "cancellation_lead_time": null,
                  "cancellation_penalty_nights": null,
                  "cancellation_penalty_rate": null,
                  "deposit_available": false,
                  "deposit_returnable": true,
                  "deposit_rate": null,
                  "deposit_unit": null,
                  "no_show_rate": 1,
                  "no_show_unit": 3,
                  "plan_date_start_at": null,
                  "plan_date_end_at": null,
                  "booking_date_start_at": null,
                  "booking_date_end_at": null,
                  "min_stay_arrival": null,
                  "max_stay_arrival": null,
                  "min_stay_through": null,
                  "max_stay_through": null,
                  "status": "X",
                  "description": null,
                  "possible_occupancies": [
                    668236,
                    668237,
                    668238,
                    668239,
                    668240,
                    668241
                  ],
                  "external_id": null,
                  "hotel": 271728830,
                  "parent": 629788
                },
                {
                  "id": 647519,
                  "name": "\u041f\u0435\u0440\u0438\u043e\u0434 \u043f\u0440\u043e\u0436\u0438\u0432\u0430\u043d\u0438\u044f",
                  "room_category": 257008,
                  "advance": null,
                  "last_minute": null,
                  "free_nights": null,
                  "discount": 10.0,
                  "discount_unit": 1,
                  "meal_plan": null,
                  "meal_plan_cost": null,
                  "meal_plan_available": false,
                  "meal_plan_included": false,
                  "cancellation_available": false,
                  "cancellation_lead_time": null,
                  "cancellation_penalty_nights": null,
                  "cancellation_penalty_rate": null,
                  "deposit_available": false,
                  "deposit_returnable": true,
                  "deposit_rate": null,
                  "deposit_unit": null,
                  "no_show_rate": 1,
                  "no_show_unit": 3,
                  "plan_date_start_at": null,
                  "plan_date_end_at": null,
                  "booking_date_start_at": null,
                  "booking_date_end_at": null,
                  "min_stay_arrival": null,
                  "max_stay_arrival": null,
                  "min_stay_through": 10,
                  "max_stay_through": null,
                  "status": "X",
                  "description": null,
                  "possible_occupancies": [
                    668236,
                    668237,
                    668238,
                    668239,
                    668240,
                    668241
                  ],
                  "external_id": null,
                  "hotel": 271728830,
                  "parent": 629788
                },
                {
                  "id": 647520,
                  "name": "\u0413\u043e\u0440\u044f\u0447\u0435\u0435 \u043f\u0440\u0435\u0434\u043b\u043e\u0436\u0435\u043d\u0438\u0435",
                  "room_category": 257008,
                  "advance": null,
                  "last_minute": 120,
                  "free_nights": null,
                  "discount": 10.0,
                  "discount_unit": 1,
                  "meal_plan": null,
                  "meal_plan_cost": null,
                  "meal_plan_available": false,
                  "meal_plan_included": false,
                  "cancellation_available": false,
                  "cancellation_lead_time": null,
                  "cancellation_penalty_nights": null,
                  "cancellation_penalty_rate": null,
                  "deposit_available": false,
                  "deposit_returnable": true,
                  "deposit_rate": null,
                  "deposit_unit": null,
                  "no_show_rate": 1,
                  "no_show_unit": 3,
                  "plan_date_start_at": null,
                  "plan_date_end_at": "2018-06-15",
                  "booking_date_start_at": null,
                  "booking_date_end_at": null,
                  "min_stay_arrival": null,
                  "max_stay_arrival": null,
                  "min_stay_through": null,
                  "max_stay_through": null,
                  "status": "X",
                  "description": null,
                  "possible_occupancies": [
                    668236,
                    668237,
                    668238,
                    668239,
                    668240,
                    668241
                  ],
                  "external_id": null,
                  "hotel": 271728830,
                  "parent": 629788
                }
              ]
            }'
            :
            '{
              "limits": 0,
              "page": 1,
              "num_pages": 1,
              "rate_plans": [
                {
                  "id": 790780,
                  "name": "\u0421\u0442\u0430\u043d\u0434\u0430\u0440\u0442\u043d\u044b\u0439 \u0442\u0430\u0440\u0438\u0444",
                  "room_category": 296710,
                  "advance": null,
                  "last_minute": null,
                  "free_nights": null,
                  "discount": null,
                  "discount_unit": null,
                  "meal_plan": null,
                  "meal_plan_cost": null,
                  "meal_plan_available": false,
                  "meal_plan_included": null,
                  "cancellation_available": true,
                  "cancellation_lead_time": 24,
                  "cancellation_penalty_nights": 1,
                  "cancellation_penalty_rate": null,
                  "deposit_available": true,
                  "deposit_returnable": true,
                  "deposit_rate": 15,
                  "deposit_unit": 1,
                  "no_show_rate": 1,
                  "no_show_unit": 3,
                  "plan_date_start_at": null,
                  "plan_date_end_at": null,
                  "booking_date_start_at": null,
                  "booking_date_end_at": null,
                  "min_stay_arrival": 3,
                  "max_stay_arrival": null,
                  "min_stay_through": null,
                  "max_stay_through": null,
                  "status": "P",
                  "description": "",
                  "possible_occupancies": [
                    784372,
                    784373,
                    784374,
                    784375
                  ],
                  "external_id": null,
                  "hotel": 380171626,
                  "parent": null
                },
                {
                  "id": 790781,
                  "name": "\u041f\u0435\u0440\u0438\u043e\u0434 \u043f\u0440\u043e\u0436\u0438\u0432\u0430\u043d\u0438\u044f",
                  "room_category": 296710,
                  "advance": null,
                  "last_minute": null,
                  "free_nights": null,
                  "discount": 10.0,
                  "discount_unit": 1,
                  "meal_plan": null,
                  "meal_plan_cost": null,
                  "meal_plan_available": false,
                  "meal_plan_included": false,
                  "cancellation_available": true,
                  "cancellation_lead_time": 24,
                  "cancellation_penalty_nights": 1,
                  "cancellation_penalty_rate": null,
                  "deposit_available": false,
                  "deposit_returnable": true,
                  "deposit_rate": null,
                  "deposit_unit": null,
                  "no_show_rate": 1,
                  "no_show_unit": 3,
                  "plan_date_start_at": null,
                  "plan_date_end_at": null,
                  "booking_date_start_at": null,
                  "booking_date_end_at": null,
                  "min_stay_arrival": null,
                  "max_stay_arrival": null,
                  "min_stay_through": 10,
                  "max_stay_through": null,
                  "status": "P",
                  "description": null,
                  "possible_occupancies": [
                    784372,
                    784373,
                    784374,
                    784375
                  ],
                  "external_id": null,
                  "hotel": 380171626,
                  "parent": 790780
                },
                {
                  "id": 790798,
                  "name": "\u0421\u0442\u0430\u043d\u0434\u0430\u0440\u0442\u043d\u044b\u0439 \u0442\u0430\u0440\u0438\u0444",
                  "room_category": 296715,
                  "advance": null,
                  "last_minute": null,
                  "free_nights": null,
                  "discount": null,
                  "discount_unit": null,
                  "meal_plan": null,
                  "meal_plan_cost": null,
                  "meal_plan_available": false,
                  "meal_plan_included": null,
                  "cancellation_available": true,
                  "cancellation_lead_time": 24,
                  "cancellation_penalty_nights": 1,
                  "cancellation_penalty_rate": null,
                  "deposit_available": true,
                  "deposit_returnable": true,
                  "deposit_rate": 15,
                  "deposit_unit": 1,
                  "no_show_rate": 1,
                  "no_show_unit": 3,
                  "plan_date_start_at": null,
                  "plan_date_end_at": null,
                  "booking_date_start_at": null,
                  "booking_date_end_at": null,
                  "min_stay_arrival": 3,
                  "max_stay_arrival": null,
                  "min_stay_through": null,
                  "max_stay_through": null,
                  "status": "P",
                  "description": "",
                  "possible_occupancies": [
                    784384,
                    784385,
                    784386,
                    784387,
                    784388,
                    784389
                  ],
                  "external_id": null,
                  "hotel": 380171626,
                  "parent": null
                },
                {
                  "id": 790799,
                  "name": "\u041f\u0435\u0440\u0438\u043e\u0434 \u043f\u0440\u043e\u0436\u0438\u0432\u0430\u043d\u0438\u044f",
                  "room_category": 296715,
                  "advance": null,
                  "last_minute": null,
                  "free_nights": null,
                  "discount": 10.0,
                  "discount_unit": 1,
                  "meal_plan": null,
                  "meal_plan_cost": null,
                  "meal_plan_available": false,
                  "meal_plan_included": false,
                  "cancellation_available": true,
                  "cancellation_lead_time": 24,
                  "cancellation_penalty_nights": 1,
                  "cancellation_penalty_rate": null,
                  "deposit_available": false,
                  "deposit_returnable": true,
                  "deposit_rate": null,
                  "deposit_unit": null,
                  "no_show_rate": 1,
                  "no_show_unit": 3,
                  "plan_date_start_at": null,
                  "plan_date_end_at": null,
                  "booking_date_start_at": null,
                  "booking_date_end_at": null,
                  "min_stay_arrival": null,
                  "max_stay_arrival": null,
                  "min_stay_through": 10,
                  "max_stay_through": null,
                  "status": "P",
                  "description": null,
                  "possible_occupancies": [
                    784384,
                    784385,
                    784386,
                    784387,
                    784388,
                    784389
                  ],
                  "external_id": null,
                  "hotel": 380171626,
                  "parent": 790798
                }
              ]
            }';

        return $data;
    }

    protected function getOccupanciesResponseData($isDefault): string
    {
        $data = $isDefault
            ?
            '{"limits": 0, "page": 1, "num_pages": 1, "occupancies":[
                {"id": 668236, "room_category": 257008, "capacity": 1, "rack_rate": 10000.0}, 
                {"id": 668237, "room_category": 257008, "capacity": 2, "rack_rate": 10000.0}, 
                {"id": 668238, "room_category": 257008, "capacity": 3, "rack_rate": 10000.0}, 
                {"id": 668239, "room_category": 257008, "capacity": 4, "rack_rate": 10000.0}, 
                {"id": 668240, "room_category": 257008, "capacity": 5, "rack_rate": 10000.0}, 
                {"id": 668241, "room_category": 257008, "capacity": 6, "rack_rate": 10000.0}, 
                {"id": 681796, "room_category": 261630, "capacity": 1, "rack_rate": 7500.0}, 
                {"id": 681797, "room_category": 261630, "capacity": 2, "rack_rate": 7500.0}, 
                {"id": 681798, "room_category": 261630, "capacity": 3, "rack_rate": 7500.0}, 
                {"id": 681799, "room_category": 261630, "capacity": 4, "rack_rate": 7500.0}
            ]}'
            :
            '{"limits": 0, "page": 1, "num_pages": 1, "occupancies":[
                 {"id": 784372, "room_category": 296710, "capacity": 1, "rack_rate": 10000.0}, 
                 {"id": 784373, "room_category": 296710, "capacity": 2, "rack_rate": 10000.0}, 
                 {"id": 784374, "room_category": 296710, "capacity": 3, "rack_rate": 10000.0}, 
                 {"id": 784375, "room_category": 296710, "capacity": 4, "rack_rate": 10000.0}, 
                 {"id": 784384, "room_category": 296715, "capacity": 1, "rack_rate": 15000.0}, 
                 {"id": 784385, "room_category": 296715, "capacity": 2, "rack_rate": 15000.0}, 
                 {"id": 784386, "room_category": 296715, "capacity": 3, "rack_rate": 15000.0}, 
                 {"id": 784387, "room_category": 296715, "capacity": 4, "rack_rate": 15000.0}, 
                 {"id": 784388, "room_category": 296715, "capacity": 5, "rack_rate": 15000.0}, 
                 {"id": 784389, "room_category": 296715, "capacity": 6, "rack_rate": 15000.0}
             ]}';

        return $data;
    }
}
