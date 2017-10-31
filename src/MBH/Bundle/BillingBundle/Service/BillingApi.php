<?php

namespace MBH\Bundle\BillingBundle\Service;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use MBH\Bundle\PackageBundle\Models\AuthorityOrgan;
use MBH\Bundle\PackageBundle\Models\Country;
use Monolog\Logger;
use Symfony\Component\Serializer\Serializer;

class BillingApi
{
    //TODO: Сменить на локаль
    const BILLING_HOST = 'http://billing.maxibooking.ru/ru';
    const RESULT_API_URL = '/result';
    const CLIENT_REQUEST_URL = '/clients';
    const CLIENT_PROPERTY_URL = '/property';
    const FMS_ORGAN_URL_END = '/fms-fms';
    const COUNTRY_URL_END = '/countries';
    const AUTH_TOKEN = 'e3cbe9278e7c5821c5e75d2a0d0caf9e851bf1fd';

    /** @var GuzzleClient */
    private $guzzle;
    private $logger;
    private $billingLogin;
    private $serializer;

    public function __construct(Logger $logger, $billingLogin, Serializer $serializer)
    {
        $this->guzzle = new GuzzleClient();
        $this->logger = $logger;
        $this->billingLogin = $billingLogin;
        $this->serializer = $serializer;
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
        return $this->guzzle->post($uri, [
            'headers' => [
                'Authorization' => 'Token ' . self::AUTH_TOKEN
            ]
        ]);
    }

    /**
     * @param $authorityId
     * @return AuthorityOrgan
     */
    public function getAuthorityOrganById($authorityId)
    {
        $response = $this->sendGet(self::BILLING_HOST . self::FMS_ORGAN_URL_END . '/' . $authorityId);

        return $this->serializer->deserialize($response->getBody(), 'MBH\Bundle\PackageBundle\Models\AuthorityOrgan', 'json');
    }

    /**
     * @param $countryTld
     * @return Country
     */
    public function getCountryByTld($countryTld)
    {
        $response = $this->sendGet(self::BILLING_HOST . self::COUNTRY_URL_END . '/' . $countryTld);

        return $this->serializer->deserialize($response->getBody(), 'MBH\Bundle\PackageBundle\Models\Country', 'json');
    }

    /**
     * @param $countryName
     * @return Country
     */
    public function getCountryByName($countryName)
    {
        $response = $this->sendGet(self::BILLING_HOST . self::COUNTRY_URL_END . '/' . $countryName);

        return $this->serializer->deserialize($response->getBody(), 'MBH\Bundle\PackageBundle\Models\Country', 'json');
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getClient()
    {
        return $this->sendGet(self::BILLING_HOST . self::CLIENT_REQUEST_URL . '/' . $this->billingLogin );
    }
}