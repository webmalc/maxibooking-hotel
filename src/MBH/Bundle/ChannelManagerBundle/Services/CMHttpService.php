<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use MBH\Bundle\BillingBundle\Lib\Model\Result;

class CMHttpService
{
    /**
     * @param string $url
     * @return Result
     */
    public function getByAirbnbUrl(string $url)
    {
        $result = new Result();
        $client = new Client();

        $response = $client->get($url, [
            RequestOptions::HTTP_ERRORS => false
        ]);

        $success = $response->getStatusCode() === 200;
        $result->setIsSuccessful($success)
            ->setData((string)$response->getBody());

        return $result;
    }
}