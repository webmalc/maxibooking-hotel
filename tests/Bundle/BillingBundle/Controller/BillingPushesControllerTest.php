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
        $clientConfig = $this->getContainer()
            ->get('mbh.client_config_manager')
            ->changeCacheValidity(true);

        $content = json_encode(['token' => $this->getContainer()->getParameter('billing_front_token')]);
        $this->client->request('POST', $url, [], [], ['HTTP_CONTENT_TYPE' => 'application/json'], $content);
        $this->assertStatusCode(200, $this->client);
        $dm->refresh($clientConfig);
        $this->assertFalse($clientConfig->isCacheValid());
    }
}