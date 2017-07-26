<?php


namespace MBH\Bundle\BillingBundle\Service;


use MBH\Bundle\BillingBundle\Lib\Model\string;
use GuzzleHttp\Client as GuzzleClient;

class BillingApi
{
    const BILLING_HOST = 'http://localhost';

    const RESULT_API_URL = '/result';


    /** @var GuzzleClient */
    private $guzzle;

    public function __construct()
    {
        $this->guzzle = new GuzzleClient();
    }


    public function sendFalse(string $clientName): void
    {
        $this->guzzle->post(self::BILLING_HOST.self::RESULT_API_URL, []);
    }

    public function sendSuccess(string $client): void
    {

    }
}