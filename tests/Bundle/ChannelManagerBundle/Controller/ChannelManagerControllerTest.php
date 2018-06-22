<?php

namespace Tests\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;

class ChannelManagerControllerTest extends WebTestCase
{
//    public static function setUpBeforeClass()
//    {
//        self::baseFixtures();
//    }
//
//    public static function tearDownAfterClass()
//    {
//        self::clearDB();
//    }

//    public function setUp()
//    {
//        parent::setUp();
//    }

    /**
     * @dataProvider channelManagersProvider
     * @param string $serviceName
     */
    public function testWizardInfoAction(string $serviceName, $hasForm)
    {
        $client = static::makeClient(true);
        $crawler = $client->request('GET', '/management/channelmanager/wizard_info/' . $serviceName);
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);

        $instructionBlock = $crawler->filter('#channel-manager-instruction');
        $this->assertEquals(1, $instructionBlock->count());

        $expectedMessageTransId = 'controller.channelManagerController.wizard_info_text.' . $serviceName;
        $expectedMessage = $this
            ->getContainer()
            ->get('translator')
            ->trans('controller.channelManagerController.wizard_info_text.' . $serviceName);
        $this->assertNotContains($expectedMessageTransId, $instructionBlock->text());
        $this->assertContains($expectedMessage, $instructionBlock->text());

        $introForm = $crawler->filter('form[name="mbhchannel_manager_bundle_' . $serviceName . '_intro_type' . '"]');
        $this->assertEquals(intval($hasForm), $introForm->count());
    }

    /**
     * @return array
     */
    public function channelManagersProvider()
    {
        $channelManagers = array_keys($this->getContainer()->getParameter('mbh.channelmanager.services'));

        return array_map(function (string $cmName) {
            return [$cmName, $this->getContainer()->get('mbh.cm_wizard_manager')->hasIntroForm($cmName)];
        }, $channelManagers);
    }
}