<?php

namespace Tests\Bundle\UserBundle\Controller;


use MBH\Bundle\BaseBundle\Lib\Test\CrudWebTestCase;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\UserBundle\DataFixtures\MongoDB\UserData;
use Symfony\Component\HttpFoundation\Response;

class ClientConfigControllerTest extends WebTestCase
{

    protected $admin = [
        'PHP_AUTH_USER' => UserData::USER_ADMIN,
        'PHP_AUTH_PW' => UserData::USER_ADMIN
    ];

    protected $lmanager = [
        'PHP_AUTH_USER' => UserData::USER_L_MANAGER,
        'PHP_AUTH_PW' => UserData::USER_L_MANAGER
    ];

    protected $manager = [
        'PHP_AUTH_USER' => UserData::USER_MANAGER,
        'PHP_AUTH_PW' => UserData::USER_MANAGER
    ];

    protected $urlIndex = '/';

    protected $clientConfigUrl = '/management/client/config/';

    protected $formName = 'mbh_bundle_clientbundle_client_config_type';

    protected $newTimeZone = 'Africa/Mogadishu';

    protected $defaultTimeZone = 'Europe/Moscow';

    protected $defaultShowLabelTips = true;

    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    public function testRedirectCode()
    {
        $this->client = self::makeClient(true);
        $this->getListCrawler($this->urlIndex);

        $this->assertStatusCode(
            Response::HTTP_FOUND,
            $this->client
        );
    }

    /**
     * @depends testRedirectCode
     */
    public function testAdminTimeZone()
    {
        $clientConfig = $this->changeTimeZoneGetClientConfig($this->admin);

        $this->assertEquals($this->defaultTimeZone, $clientConfig->getTimeZone());
        $this->assertEquals($this->defaultShowLabelTips, !$clientConfig->isShowLabelTips());
    }

    /**
     * @depends testAdminTimeZone
     */
    public function testLmanagerTimeZone()
    {
        $this->client = self::createClient([], $this->lmanager);

        $this->getListCrawler($this->clientConfigUrl);

        $this->assertStatusCode(
            Response::HTTP_FORBIDDEN,
            $this->client
        );
    }

    /**
     * @depends testLmanagerTimeZone
     */
    public function testManagerTimeZone()
    {
        $this->client = self::createClient([], $this->manager);

        $this->getListCrawler($this->clientConfigUrl);

        $this->assertStatusCode(
            Response::HTTP_FOUND,
            $this->client
        );
    }

    /**
     * @depends testManagerTimeZone
     */
    public function testMbUserTimeZone()
    {
        $clientConfig = $this->changeTimeZoneGetClientConfig([
            'PHP_AUTH_USER' => UserData::MB_USER_USERNAME,
            'PHP_AUTH_PW' => $this->getContainer()->getParameter('mb_user_pwd')
        ]);

        $this->assertEquals($this->newTimeZone, $clientConfig->getTimeZone());
        $this->assertEquals($this->defaultShowLabelTips, !$clientConfig->isShowLabelTips());
    }

    private function changeTimeZoneGetClientConfig(array $user): ClientConfig
    {
        $this->client = self::createClient([], $user);

        $crawler = $this->getListCrawler($this->clientConfigUrl);
        $form = $crawler->filter('form[name="' . $this->formName . '"]')->form();
        $form->setValues(CrudWebTestCase::prepareFormValues($this->formName, [
            'timeZone' => $this->newTimeZone,
            'showLabelTips' => $this->defaultShowLabelTips
        ]));
        $this->client->submit($form);
        $this->defaultShowLabelTips = !$this->defaultShowLabelTips;

        return $this->getContainer()->get('mbh.client_config_manager')->fetchConfig();
    }
}
