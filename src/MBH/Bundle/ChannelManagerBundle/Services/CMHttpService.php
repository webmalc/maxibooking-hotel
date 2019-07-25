<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use MBH\Bundle\BillingBundle\Lib\Model\Result;

class CMHttpService
{
    /** @var Client */
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @param string $url
     * @return Result
     */
    public function getResult(string $url): Result
    {
        $result = new Result();

        $response = $this->client->get($url, [
            RequestOptions::HTTP_ERRORS => false
        ]);

        $success = $response->getStatusCode() === 200;
        $result->setIsSuccessful($success)
            ->setData((string)$response->getBody());

        return $result;
    }
}
