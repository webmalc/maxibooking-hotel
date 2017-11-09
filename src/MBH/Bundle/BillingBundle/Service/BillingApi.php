<?php

namespace MBH\Bundle\BillingBundle\Service;

use GuzzleHttp\Client as GuzzleClient;
use MBH\Bundle\PackageBundle\Models\Billing\AuthorityOrgan;
use MBH\Bundle\PackageBundle\Models\Billing\City;
use MBH\Bundle\PackageBundle\Models\Billing\Country;
use MBH\Bundle\PackageBundle\Models\Billing\Region;
use Monolog\Logger;
use Symfony\Component\Serializer\Serializer;

class BillingApi
{
    const BILLING_QUERY_PARAM_NAME = 'search';
    const BILLING_HOST = 'http://billing.maxibooking.ru';
    const RESULT_API_URL = '/result';
    const CLIENT_REQUEST_URL = '/clients';
    const CLIENT_PROPERTY_URL = '/property';
    const FMS_ORGANS_ENDPOINT = 'fms-fms';
    const COUNTRIES_ENDPOINT = 'countries';
    const REGIONS_ENDPOINT = 'regions';
    const CITIES_ENDPOINT = 'cities';
    const AUTH_TOKEN = 'e3cbe9278e7c5821c5e75d2a0d0caf9e851bf1fd';

    /** @var GuzzleClient */
    private $guzzle;
    private $logger;
    private $billingLogin;
    private $serializer;
    private $locale;

    private $loadedAuthorityOrgans = [];
    private $loadedCountries = [];
    private $loadedRegions = [];
    private $loadedCities = [];

    public function __construct(Logger $logger, $billingLogin, Serializer $serializer, $locale)
    {
        $this->guzzle = new GuzzleClient();
        $this->logger = $logger;
        $this->billingLogin = $billingLogin;
        $this->serializer = $serializer;
        $this->locale = $locale;
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
     * @param $locale
     * @return AuthorityOrgan
     */
    public function getAuthorityOrganById($authorityId, $locale = null)
    {
        if (!isset($this->loadedAuthorityOrgans[$authorityId])) {
            $authorityOrgan = $this->getBillingEntityById(self::FMS_ORGANS_ENDPOINT, $authorityId, AuthorityOrgan::class, $locale);
            $this->loadedAuthorityOrgans[$authorityId] = $authorityOrgan;
        }

        return $this->loadedAuthorityOrgans[$authorityId];
    }

    /**
     * @param $countryTld
     * @param $locale
     * @return Country
     */
    public function getCountryByTld($countryTld, $locale = null) {
        if (!isset($this->loadedCountries[$countryTld])) {
            $country = $this->getBillingEntityById(self::COUNTRIES_ENDPOINT, $countryTld, Country::class, $locale);
            $this->loadedCountries[$countryTld] = $country;
        }

        return $this->loadedCountries[$countryTld];
    }

    /**
     * @param $regionId
     * @param null $locale
     * @return Region
     */
    public function getRegionById($regionId, $locale = null) {
        if (!isset($this->loadedRegions[$regionId])) {
            $region = $this->getBillingEntityById(self::REGIONS_ENDPOINT, $regionId, Region::class, $locale);
            $this->loadedRegions[$regionId] = $region;
        }

        return $this->loadedRegions[$regionId];
    }

    /**
     * @param $regionQuery
     * @param null $locale
     * @return Region[]
     */
    public function getRegionByQuery($regionQuery, $locale = null) {
        return $this->getBillingEntitiesByQuery(self::REGIONS_ENDPOINT, [self::BILLING_QUERY_PARAM_NAME => $regionQuery], Region::class, $locale);
    }

    /**
     * @param $cityId
     * @param null $locale
     * @return City
     */
    public function getCityById($cityId, $locale = null) {
        if (!isset($this->loadedCities[$cityId])) {
            $city = $this->getBillingEntityById(self::CITIES_ENDPOINT, $cityId, City::class, $locale);
            $this->loadedCities[$cityId] = $city;
        }

        return $this->loadedCities[$cityId];
    }

    /**
     * @param $endpoint
     * @param $id
     * @param $modelType
     * @param null $locale
     * @return object
     */
    private function getBillingEntityById($endpoint, $id, $modelType, $locale = null)
    {
        $response = $this->sendGet($this->getBillingUrl($endpoint, $id, $locale));

        return $this->serializer->deserialize($response->getBody(), $modelType, 'json');
    }

    /**
     * @param $endpoint
     * @param $queryParams
     * @param $modelType
     * @param $locale
     * @return array
     */
    private function getBillingEntitiesByQuery($endpoint, $queryParams, $modelType, $locale)
    {
        $response = $this->sendGet($this->getBillingUrl($endpoint, null, $locale, $queryParams));
        $decodedResponse = json_decode($response->getBody(), true);

        $entities = [];
        foreach ($decodedResponse['results'] as $entityData) {
            $entities[] = $this->serializer->denormalize($entityData, $modelType);
        }
        
        return $entities;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getClient()
    {
        return $this->sendGet($this->getBillingUrl(self::CLIENT_REQUEST_URL, $this->billingLogin));
    }

    /**
     * @param $endpoint
     * @param null $identifier
     * @param null $locale
     * @param array $queryParams
     * @return string
     */
    private function getBillingUrl($endpoint, $identifier = null, $locale = null, $queryParams = [])
    {
        $locale = $locale ?? $this->locale;

        return self::BILLING_HOST
            . '/' . $locale
            . '/' . $endpoint
            . ($identifier ? '/' . $identifier : '')
            . '?' . http_build_query($queryParams);
    }
}