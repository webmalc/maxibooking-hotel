<?php

namespace Tests\Bundle\ApiBundle\Controller;

use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\FlowConfig;
use MBH\Bundle\HotelBundle\Form\HotelFlow\HotelFlow;
use MBH\Bundle\HotelBundle\Form\RoomTypeFlow\RoomTypeFlow;

class CommonDataApiControllerTest extends WebTestCase
{
    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }
//
//    public function testFlowProgressDataAction()
//    {
//        $this->loadFlowConfigs();
//        $this->client->request('GET', '/api/v1/common_data/flow_progress');
//        $this->isSuccessful($this->client->getResponse(), true, 'application/json');
//
//        $flowProgressData = json_decode($this->client->getResponse()->getContent(), true);
//        $this->assertTrue($flowProgressData['success']);
//        $this->assertEquals(22, $flowProgressData['data']['roomType']);
//        $this->assertEquals(100, $flowProgressData['data']['hotel']);
//        $this->assertEquals(0, $flowProgressData['data']['site']);
//    }
//
//    private function loadFlowConfigs(): void
//    {
//        //TODO: Исправить
//        $dm = $this->getDm();
//        $roomTypeFlowConfig = (new FlowConfig())
//            ->setCurrentStep(3)
////            ->setFlowId(RoomTypeFlow::getFlowId())
//        $dm->persist($roomTypeFlowConfig);
//
//        $hotelFlowConfig = (new FlowConfig())
//            ->setCurrentStep(8)
//            //TODO: Исправить
////            ->setFlowId(HotelFlow::getFlowId());
//        $dm->persist($hotelFlowConfig);
//
//        $dm->flush();
//    }
}