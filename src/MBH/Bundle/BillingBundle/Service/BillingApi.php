<?php


namespace MBH\Bundle\BillingBundle\Service;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Uri;
use Monolog\Logger;

class BillingApi
{
    const BILLING_HOST = 'http://localhost';

    const RESULT_API_URL = '/result';

    const CLIENT_REQUEST_URL = '/client';

    const CLIENT_PROPERTY_URL = '/property';


    /** @var GuzzleClient */
    private $guzzle;

    private $logger;

    public function __construct(Logger $logger)
    {
        $this->guzzle = new GuzzleClient();
        $this->logger = $logger;
    }


    public function sendFalse(string $clientName): void
    {
        $this->guzzle->post(self::BILLING_HOST.self::RESULT_API_URL, []);
    }

    public function sendSuccess(string $json): void
    {

    }

    public function getClientProperties()
    {

    }

    public function confirmClientEmail()
    {
        //TODO: Реализовать после мержа.
    }

    private function sendGet()
    {

    }

    private function sendPost(string $uri)
    {
        $this->guzzle->post($uri);
    }
}