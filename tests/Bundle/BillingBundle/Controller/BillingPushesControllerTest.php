<?php

namespace Tests\Bundle\BillingBundle\Controller;

use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\ClientBundle\Document\ClientConfigRepository;

class BillingPushesControllerTest extends WebTestCase
{
    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    public function testInvalidateCache()
    {
        $url = '/deploy/invalidate_billing_cache';
        $this->client->request('POST', $url);
        $this->assertStatusCode(401, $this->client);

        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        /** @var ClientConfigRepository $clientConfigRepo */
        $clientConfigRepo = $clientConfig = $dm->getRepository('MBHClientBundle:ClientConfig');

        $this->client->request('GET', '/package/');
        $this->assertStatusCode(200, $this->client);
        $clientConfig = $clientConfigRepo->fetchConfig();
        $this->assertTrue($clientConfig->isCacheValid());

        $this->client->request('POST', $url, ['token' => $this->getContainer()->getParameter('billing_front_token')]);
        $this->assertStatusCode(200, $this->client);
        $dm->refresh($clientConfig);
        $this->assertFalse($clientConfig->isCacheValid());
    }
}