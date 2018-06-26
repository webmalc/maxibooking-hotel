<?php

namespace Tests\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;

class ChannelManagerControllerTest extends WebTestCase
{
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
    }

    /**
     * @dataProvider channelManagersProvider
     * @param string $serviceName
     * @param $hasForm
     */
    public function testWizardInfoAction(string $serviceName, bool $hasForm)
    {
        $crawler = $this->client->request('GET', '/management/channelmanager/' . $serviceName . '/wizard_info');
        $this->assertEquals($this->client->getResponse()->getStatusCode(), 200);

        $instructionBlock = $crawler->filter('#channel-manager-instruction');
        $this->assertEquals(1, $instructionBlock->count());

        $expectedMessageTransId = 'controller.channelManagerController.wizard_info_text.' . $serviceName;
        $expectedMessage = $this
            ->getContainer()
            ->get('translator')
            ->trans('controller.channelManagerController.wizard_info_text.' . $serviceName);
        $this->assertEquals(0, $crawler->filter('#connection-request-sent-message')->count());
        $this->assertNotContains($expectedMessageTransId, $instructionBlock->text());
        $this->assertContains($expectedMessage, $instructionBlock->text());

        $introFormCrawler = $crawler->filter('form[name="mbhchannel_manager_bundle_intro_type"]');
        $this->assertEquals(intval($hasForm), $introFormCrawler->count());

        if ($hasForm) {
            $introForm = $introFormCrawler->form(['mbhchannel_manager_bundle_intro_type[hotelId]' => 123]);
            $crawler = $this->client->submit($introForm);
            $this->assertEquals(0, $crawler->filter('form[name="mbhchannel_manager_bundle_intro_type"]')->count());
            $requestSentMessageBlock = $crawler->filter('#connection-request-sent-message');
            $this->assertContains('Заявка отправлена, ожидайте сообщения тех. поддержки', $requestSentMessageBlock->text());
        }
    }

    /**
     * @return array
     */
    public function channelManagersProvider()
    {
        $channelManagers = array_keys($this->getContainer()->getParameter('mbh.channelmanager.services'));

        return array_map(function (string $cmName) {
            return [$cmName, $this->getContainer()->get('mbh.cm_wizard_manager')->isConfiguredByTechSupport($cmName)];
        }, $channelManagers);
    }
}