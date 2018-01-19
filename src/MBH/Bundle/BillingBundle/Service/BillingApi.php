<?php

namespace MBH\Bundle\BillingBundle\Service;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\BillingBundle\Lib\Model\Client;
use MBH\Bundle\BillingBundle\Lib\Model\ClientService;
use MBH\Bundle\BillingBundle\Lib\Model\Company;
use MBH\Bundle\BillingBundle\Lib\Model\Country;
use MBH\Bundle\BillingBundle\Lib\Model\PaymentOrder;
use MBH\Bundle\BillingBundle\Lib\Model\PaymentSystem;
use MBH\Bundle\BillingBundle\Lib\Model\Region;
use MBH\Bundle\BillingBundle\Lib\Model\Result;
use MBH\Bundle\BillingBundle\Lib\Model\Service;
use MBH\Bundle\BillingBundle\Lib\Model\AuthorityOrgan;
use MBH\Bundle\BillingBundle\Lib\Model\City;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Serializer\Serializer;

class BillingApi
{
    const BILLING_QUERY_PARAM_NAME = 'search';
    const BILLING_HOST = 'https://billing.maxi-booking.com';
    const RESULT_API_URL = '/result';
    const CLIENT_PROPERTY_URL = '/property';
    const CLIENT_INSTALL_RESULT_URL_END = '/install_result';
    const FMS_ORGANS_ENDPOINT_SETTINGS = ['endpoint' => 'fms-fms', 'model' => AuthorityOrgan::class, 'returnArray' => false];
    const COUNTRIES_ENDPOINT_SETTINGS = ['endpoint' => 'countries', 'model' => Country::class, 'returnArray' => false];
    const REGIONS_ENDPOINT_SETTINGS = ['endpoint' => 'regions', 'model' => Region::class, 'returnArray' => false];
    const CITIES_ENDPOINT_SETTINGS = ['endpoint' => 'cities', 'model' => City::class, 'returnArray' => false];
    const CLIENTS_ENDPOINT_SETTINGS = ['endpoint' => 'clients', 'model' => Client::class, 'returnArray' => false];
    const CLIENT_SERVICES_ENDPOINT_SETTINGS = ['endpoint' => 'client-services', 'model' => ClientService::class, 'returnArray' => false];
    const SERVICES_ENDPOINT_SETTINGS = ['endpoint' => 'services', 'model' => Service::class, 'returnArray' => false];
    const ORDERS_ENDPOINT_SETTINGS = ['endpoint' => 'orders', 'model' => PaymentOrder::class, 'returnArray' => false];
    const PAYMENT_SYSTEMS_ENDPOINT_SETTINGS = ['endpoint' => 'payment-systems', 'model' => PaymentSystem::class, 'returnArray' => true];
    const PAYER_COMPANY_ENDPOINT_SETTINGS = ['endpoint' => 'companies', 'model' => Company::class, 'returnArray' => false];

    const AUTH_TOKEN = 'e3cbe9278e7c5821c5e75d2a0d0caf9e851bf1fd';
    const BILLING_DATETIME_FORMAT = 'Y-m-d\TH:i:s\Z';
    const BILLING_DATETIME_FORMAT_WITH_MICROSECONDS = 'Y-m-d\TH:i:s.u\Z';

    /** @var GuzzleClient */
    private $guzzle;
    private $logger;
    private $billingLogin;
    private $locale;
    private $serializer;

    private $loadedEntities = [];
    private $clientServices;
    private $isClientServicesInit = false;
    private $clientCompanies;
    private $isClientCompaniesInit = false;

    public function __construct(Logger $logger, $billingLogin, Serializer $serializer, $locale)
    {
        $this->guzzle = new GuzzleClient();
        $this->logger = $logger;
        $this->billingLogin = $billingLogin;
        $this->locale = $locale;
        $this->serializer = $serializer;
    }

    public function sendFalse(): void
    {
        $this->guzzle->post(self::BILLING_HOST . self::RESULT_API_URL, []);
    }

    /**
     * @param Result $result
     * @param $clientName
     * @return ResponseInterface
     */
    public function sendClientInstallationResult(Result $result, $clientName)
    {
        $url = $this->getBillingUrl(self::CLIENTS_ENDPOINT_SETTINGS['endpoint'], $clientName) . self::CLIENT_INSTALL_RESULT_URL_END;

        return $this->sendPost($url, $result->getApiResponse(true), true);
    }


    /**
     * @param string $dateString
     * @return bool|\DateTime
     */
    public static function getDateByBillingFormat(string $dateString)
    {
        $date = \DateTime::createFromFormat(self::BILLING_DATETIME_FORMAT, $dateString);
        if (!$date instanceof \DateTime) {
            $date = \DateTime::createFromFormat(self::BILLING_DATETIME_FORMAT_WITH_MICROSECONDS, $dateString);
        }

        return $date;
    }

    /**
     * @param $login
     * @return object|Client
     */
    public function getClient($login = null)
    {
        $login = $login ?? $this->billingLogin;

        return $this->getBillingEntityById(self::CLIENTS_ENDPOINT_SETTINGS, $login);
    }

    /**
     * @param $serviceId
     * @return object
     */
    public function getServiceById($serviceId)
    {
        return $this->getBillingEntityById(self::SERVICES_ENDPOINT_SETTINGS, $serviceId);
    }

    /**
     * @param Client $client
     * @return Result
     */
    public function updateClient(Client $client)
    {
        $url = $this->getBillingUrl(self::CLIENTS_ENDPOINT_SETTINGS['endpoint'], $client->getLogin());
        $clientData = $this->serializer->normalize($client);
        $clientData['url'] = null;

        return $this->updateEntity($url, $clientData, Client::class);
    }

    /**
     * @param Company $company
     * @return Result
     */
    public function updateClientPayerCompany(Company $company)
    {
        $url = $this->getBillingUrl(self::PAYER_COMPANY_ENDPOINT_SETTINGS['endpoint'], $company->getId());
        $companyData = $this->serializer->normalize($company);

        return $this->updateEntity($url, $companyData, Company::class);
    }

    /**
     * @param Company $company
     * @return Result
     */
    public function createClientPayerCompany(Company $company)
    {
        $url = $this->getBillingUrl(self::PAYER_COMPANY_ENDPOINT_SETTINGS['endpoint']);
        $companyData = $this->serializer->normalize($company);
        $requestResult = new Result();

        try {
            $response = $this->sendPost($url, $companyData, true);
        } catch (RequestException $exception) {
            $requestResult->setIsSuccessful(false);

            $response = $exception->getResponse();
            $this->handleErrorResponse($response, $requestResult);

            return $requestResult;
        }

        $requestResult = $this->tryDeserializeObject($response, $requestResult, Company::class);

        return $requestResult;
    }

    /**
     * @param Client $client
     * @return Result
     */
    public function getClientServices(Client $client)
    {
        if (!$this->isClientServicesInit) {
            $queryData = ['client' => $client->getId()];

            $this->clientServices = $this->getEntities(self::CLIENT_SERVICES_ENDPOINT_SETTINGS, $queryData);
            $this->isClientServicesInit = true;
        }

        return $this->clientServices;
    }

    /**
     * @param Client $client
     * @return array
     */
    public function getClientCompanies(Client $client)
    {
        if (!$this->isClientCompaniesInit) {
            $queryData = ['client' => $client->getId()];

            $this->clientCompanies = $this->getEntities(self::PAYER_COMPANY_ENDPOINT_SETTINGS, $queryData)->getData();

            $this->isClientCompaniesInit = true;
        }

        return $this->clientCompanies;
    }

    /**
     * @param array $serviceData
     * @param Client $client
     * @return Result
     */
    public function createClientService(array $serviceData, Client $client)
    {
        $newClientServiceData = [
            'quantity' => $serviceData['quantity'],
            'service' => $serviceData['service'],
            'client' => $client->getLogin(),
            'begin' => (new \DateTime())->format(self::BILLING_DATETIME_FORMAT),
            'end' => (new \DateTime('+' . $serviceData['period'] . $serviceData['units']))->format(self::BILLING_DATETIME_FORMAT)
        ];

        $response = $this->sendPost($this->getBillingUrl(self::CLIENT_SERVICES_ENDPOINT_SETTINGS['endpoint']), $newClientServiceData);
        $requestResult = new Result();

        if ($response->getStatusCode() == 200) {
            return $this->tryDeserializeObject($response, $requestResult, ClientService::class);
        }

        return $this->handleErrorResponse($response, $requestResult);
    }

    /**
     * @return Result
     */
    public function getServices()
    {
        return $this->getEntities(self::SERVICES_ENDPOINT_SETTINGS);
    }

    /**
     * @param Client $client
     * @param \DateTime $begin
     * @param \DateTime $end
     * @return Result
     */
    public function getClientOrdersResultByCreationDate(Client $client, \DateTime $begin, \DateTime $end)
    {
        $queryData = [
            'client__login' => $client->getLogin(),
            'created__gte' => $begin->format(BillingApi::BILLING_DATETIME_FORMAT),
            'created__lte' => $end->format(BillingApi::BILLING_DATETIME_FORMAT)
        ];

        return $this->getEntities(self::ORDERS_ENDPOINT_SETTINGS, $queryData);
    }

    /**
     * @param $orderId
     * @return PaymentOrder|object
     */
    public function getClientOrderById($orderId)
    {
        return $this->getBillingEntityById(self::ORDERS_ENDPOINT_SETTINGS, $orderId);
    }

    /**
     * @return Result
     */
    public function getPaymentSystems()
    {
        return $this->getEntities(self::PAYMENT_SYSTEMS_ENDPOINT_SETTINGS);
    }

    /**
     * @param PaymentOrder $order
     * @return Result
     */
    public function getPaymentSystemsForOrder(PaymentOrder $order)
    {
        return $this->getEntities(self::PAYMENT_SYSTEMS_ENDPOINT_SETTINGS, ['order' => $order->getId()]);
    }

    /**
     * @param array $newTariffData
     * @return Result
     */
    public function changeTariff(array $newTariffData)
    {
        $requestResult = new Result();
        $newTariffData['rooms'] = (int)$newTariffData['rooms'];
        $url = self::BILLING_HOST . '/' . $this->locale . '/clients/' . $this->billingLogin . '/tariff_update/';

        try {
            $response = $this->sendPost($url, $newTariffData, true);
        } catch (RequestException $exception) {
            $requestResult->setIsSuccessful(false);
            $response = $exception->getResponse();
            $this->handleErrorResponse($response, $requestResult);

            return $requestResult;
        }

        $decodedResponse = json_decode((string)$response->getBody(), true);

        if ($decodedResponse['status'] !== true) {
            $requestResult->setIsSuccessful(false);
        }

        return $requestResult;
    }

    /**
     * @return array
     */
    public function getTariffsData()
    {
        $response = $this->sendGet(self::BILLING_HOST . '/' . $this->locale . '/clients/' . $this->billingLogin . '/tariff_detail/');
        $decodedResponse = json_decode((string)$response->getBody(), true);

        return $decodedResponse;
    }

    /**
     * @param ResponseInterface $response
     * @param Result $requestResult
     * @param $modelType
     * @return Result
     */
    private function tryDeserializeObject(ResponseInterface $response, Result $requestResult, $modelType)
    {
        try {
            $client = $this->serializer->deserialize($response->getBody(), $modelType, 'json');
            $requestResult->setData($client);
        } catch (\Exception $exception) {
            $this->logger->error('Error by deserialization of client: "' . $exception->getMessage() . '"');
            $requestResult->setIsSuccessful(false);
        }

        return $requestResult;
    }

    /**
     * @param ResponseInterface $response
     * @param Result $requestResult
     * @return Result
     */
    private function handleErrorResponse(ResponseInterface $response, Result $requestResult)
    {
        if ($response->getStatusCode() == 400) {
            $requestResult->setErrors(json_decode((string)$response->getBody(), true));
        } else {
            $this->logger->error('Error by update of client "' . (string)$response->getBody() . '"');
        }

        return $requestResult;
    }

    /**
     * @param $authorityId
     * @param $locale
     * @return AuthorityOrgan|object
     */
    public function getAuthorityOrganById($authorityId, $locale = null)
    {
        return $this->getBillingEntityById(self::FMS_ORGANS_ENDPOINT_SETTINGS, $authorityId, $locale);
    }

    /**
     * @param $countryTld
     * @param $locale
     * @return Country|object
     */
    public function getCountryByTld($countryTld, $locale = null) {
        return $this->getBillingEntityById(self::COUNTRIES_ENDPOINT_SETTINGS, $countryTld, $locale);
    }

    /**
     * @param $regionId
     * @param null $locale
     * @return Region|object
     */
    public function getRegionById($regionId, $locale = null) {
         return $this->getBillingEntityById(self::REGIONS_ENDPOINT_SETTINGS, $regionId, $locale);
    }

    /**
     * @param $regionQuery
     * @param null $locale
     * @return Region[]
     */
    public function getRegionByQuery($regionQuery, $locale = null) {
        return $this->getBillingEntitiesByQuery(self::REGIONS_ENDPOINT_SETTINGS, [self::BILLING_QUERY_PARAM_NAME => $regionQuery], Region::class, $locale);
    }

    /**
     * @param $cityId
     * @param null $locale
     * @return City|object
     */
    public function getCityById($cityId, $locale = null) {
        return $this->getBillingEntityById(self::CITIES_ENDPOINT_SETTINGS, $cityId, $locale);
    }

    /**
     * @param Client $client
     * @throws Exception
     */
    public function confirmClient(Client $client)
    {
        $response = $this->sendPost(self::BILLING_HOST . '/' . $this->locale . '/' . $client->getLogin() . '/confirm', [], false);
        $decodedResponse = json_decode((string)$response->getBody(), true);
        if ($decodedResponse['status'] === false) {
            throw new Exception($decodedResponse['message']);
        }
    }

    /**
     * @param string $url
     * @param array $requestData
     * @param $model
     * @return Result
     */
    private function updateEntity(string $url, array $requestData, $model)
    {
        $requestResult = new Result();

        try {
            $response = $this->sendPatch($url, $requestData);
        } catch (RequestException $exception) {
            $requestResult->setIsSuccessful(false);

            $response = $exception->getResponse();
            $this->handleErrorResponse($response, $requestResult);

            return $requestResult;
        }

        $requestResult = $this->tryDeserializeObject($response, $requestResult, $model);

        return $requestResult;
    }

    /**
     * @param $endpointSettings
     * @param array $queryData
     * @return Result
     */
    private function getEntities($endpointSettings, array $queryData = [])
    {
        $endpoint = $endpointSettings['endpoint'];
        $url = $this->getBillingUrl($endpoint, null, null, $queryData);

        $response = $this->guzzle->get($url, [
            RequestOptions::HEADERS => $this->getAuthorizationHeaderAsArray(),
            RequestOptions::HTTP_ERRORS => false
        ]);

        if ($response->getStatusCode() !== 200) {
            $this->logger->error('Error when retrieving data from the server by url' . $url . ': ' . $response->getBody());
            return Result::createErrorResult();
        }

        $decodedResponse = json_decode($response->getBody(), true);
        $entitiesData = $endpointSettings['returnArray'] ? $decodedResponse : $decodedResponse['results'];

        $entities = [];
        foreach ($entitiesData as $serviceData) {
            $entities[] = $this->serializer->denormalize($serviceData, $endpointSettings['model']);
        }

        return Result::createSuccessResult($entities);
    }

    /**
     * @param $endpointSettings
     * @param $id
     * @param null $locale
     * @return object
     * @internal param $endpoint
     */
    private function getBillingEntityById($endpointSettings, $id, $locale = null)
    {
        if (is_null($id)) {
            throw new \InvalidArgumentException('ID is not specified');
        }

        $endpoint = $endpointSettings['endpoint'];
        if (!isset($this->loadedEntities[$endpoint][$id])) {
            $response = $this->sendGet($this->getBillingUrl($endpoint, $id, $locale));
            $entity = $this->serializer->deserialize($response->getBody(), $endpointSettings['model'], 'json');
            $this->loadedEntities[$endpoint][$id] = $entity;
        }

        return $this->loadedEntities[$endpoint][$id];
    }

    /**
     * @param $url
     * @param $modelType
     * @return object
     */
    public function getBillingEntityByUrl($url, $modelType)
    {
        $response = $this->sendGet($url);

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
            . (count($queryParams) > 0 ? ('?' . http_build_query($queryParams)) : '');
    }

    /**
     * @param string $uri
     * @param array $data
     * @param bool $throwException
     * @return ResponseInterface
     */
    private function sendPost(string $uri, array $data, $throwException = false)
    {
        return $this->guzzle->post($uri . '/', [
            RequestOptions::HEADERS => $this->getAuthorizationHeaderAsArray(),
            RequestOptions::JSON => $data,
            RequestOptions::HTTP_ERRORS => $throwException
        ]);
    }

    /**
     * @param string $uri
     * @return ResponseInterface
     */
    private function sendGet(string $uri)
    {
        return $this->guzzle->get($uri, [
            RequestOptions::HEADERS => $this->getAuthorizationHeaderAsArray(),
        ]);
    }

    /**
     * @param string $uri
     * @param array $data
     * @return ResponseInterface
     */
    private function sendPatch(string $uri, array $data)
    {
        return $this->guzzle->patch($uri . '/', [
            RequestOptions::HEADERS => [
                'Authorization' => 'Token ' . self::AUTH_TOKEN,
                'Content-type' => 'application/json',
            ],
            RequestOptions::JSON => $data,
        ]);
    }

    /**
     * @return array
     */
    private function getAuthorizationHeaderAsArray()
    {
        return ['Authorization' => 'Token ' . self::AUTH_TOKEN];
    }
}