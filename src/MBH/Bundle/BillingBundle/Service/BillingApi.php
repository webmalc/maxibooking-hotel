<?php


namespace MBH\Bundle\BillingBundle\Service;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Uri;
use Monolog\Logger;

class BillingApi
{
    const BILLING_HOST = 'http://172.17.0.1:8000/ru';
    const RESULT_API_URL = '/result';
    const CLIENT_REQUEST_URL = '/clients';
    const CLIENT_PROPERTY_URL = '/property';
    const AUTH_TOKEN = '4cad23aaaa290b256a06b1c1b8fd678dc881cfab';

    /** @var GuzzleClient */
    private $guzzle;
    private $logger;
    private $billingLogin;

    public function __construct(Logger $logger, $billingLogin)
    {
        $this->guzzle = new GuzzleClient();
        $this->logger = $logger;
        $this->billingLogin = $billingLogin;
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

    private function sendGet(string $uri)
    {
        return $this->guzzle->get($uri, [
            'headers' => [
                'Authorization' => 'Token ' . self::AUTH_TOKEN
            ]
        ]);
    }

    private function sendPost(string $uri)
    {
        $this->guzzle->post($uri);
    }

    public function getClient()
    {
        $response = $this->sendGet(self::BILLING_HOST.self::CLIENT_REQUEST_URL . '/' . $this->billingLogin );

        return json_decode($response->getBody(), true);
    }
}