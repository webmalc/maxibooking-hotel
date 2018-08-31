<?php

namespace MBH\Bundle\BillingBundle\Service;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\BillingBundle\Lib\Model\Client;
use MBH\Bundle\BillingBundle\Lib\Model\ClientAuth;
use MBH\Bundle\BillingBundle\Lib\Model\ClientPayer;
use MBH\Bundle\BillingBundle\Lib\Model\ClientService;
use MBH\Bundle\BillingBundle\Lib\Model\Company;
use MBH\Bundle\BillingBundle\Lib\Model\Country;
use MBH\Bundle\BillingBundle\Lib\Model\PaymentOrder;
use MBH\Bundle\BillingBundle\Lib\Model\PaymentSystem;
use MBH\Bundle\BillingBundle\Lib\Model\Region;
use MBH\Bundle\BillingBundle\Lib\Model\Result;
use MBH\Bundle\BillingBundle\Lib\Model\RuCompany;
use MBH\Bundle\BillingBundle\Lib\Model\Service;
use MBH\Bundle\BillingBundle\Lib\Model\AuthorityOrgan;
use MBH\Bundle\BillingBundle\Lib\Model\City;
use MBH\Bundle\BillingBundle\Lib\Model\WorldCompany;
use MBH\Bundle\BillingBundle\Lib\Model\WebSite;
use MBH\Bundle\UserBundle\Document\User;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
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
    const SITES_ENDPOINT_SETTINGS = ['endpoint' => 'client-websites', 'model' => WebSite::class, 'returnArray' => false];
    const CLIENT_PAYER_ENDPOINT_SETTINGS = ['endpoint' => 'client-payer', 'model' => ClientPayer::class, 'returnArray' => false];
    const RU_PAYER_COMPANY = ['endpoint' => 'companies-ru', 'model' => RuCompany::class, 'returnArray' => false];
    const WORLD_PAYER_COMPANY = ['endpoint' => 'companies-world', 'model' => WorldCompany::class, 'returnArray' => false];

    const BILLING_DATETIME_FORMAT = 'Y-m-d\TH:i:s\Z';
    const BILLING_DATETIME_FORMAT_WITH_MICROSECONDS = 'Y-m-d\TH:i:s.u\Z';

    /** @var GuzzleClient */
    private $guzzle;
    private $logger;
    private $billingLogin;
    private $locale;
    private $serializer;
    /** @var  \AppKernel */
    private $kernel;
    private $billingToken;
    private $billingHost;

    private $loadedEntities = [];
    private $clientServices;
    private $isClientServicesInit = false;

    public function __construct(Logger $logger, KernelInterface $kernel, Serializer $serializer, $locale, TokenStorage $tokenStorage, string $billingToken, $billingHost)
    {
        $this->guzzle = new GuzzleClient();
        $this->logger = $logger;
        $this->kernel = $kernel;
        $this->billingLogin = $this->kernel->getClient();
        $this->serializer = $serializer;
        $this->billingToken = $billingToken;
        $this->billingHost = $billingHost;

        /** @var User $user */
        $user = $tokenStorage->getToken();
        $this->locale = is_object($user) && !empty($user->getLocale()) ? $user->getLocale() : $locale;
    }

    public function sendFalse(): void
    {
        $this->guzzle->post($this->billingHost . self::RESULT_API_URL, []);
    }

    /**
     * @param Result $result
     * @param $clientName
     * @return ResponseInterface
     * @throws Exception
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
     * @param $updatedEntity
     * @param array $endpointSettings
     * @param null $id
     * @return Result
     */
    public function updateBillingEntity($updatedEntity, array $endpointSettings, $id = null)
    {
        if ($id === null) {
            $id = $updatedEntity->getId();
        }
        $url = $this->getBillingUrl($endpointSettings['endpoint'], $id);
        $data = $this->serializer->normalize($updatedEntity);

        return $this->updateEntity($url, $data, $endpointSettings['model']);
    }

    /**
     * @param $entity
     * @param array $endpointSettings
     * @return Result
     */
    public function createBillingEntityBySettings($entity, array $endpointSettings)
    {
        $url = $this->getBillingUrl($endpointSettings['endpoint']);
        $entityData = $this->serializer->normalize($entity);
        $requestResult = new Result();

        try {
            $response = $this->sendPost($url, $entityData, true);
        } catch (RequestException $exception) {
            $requestResult->setIsSuccessful(false);

            $response = $exception->getResponse();
            $this->handleErrorResponse($response, $requestResult, $url, $entityData);

            return $requestResult;
        }

        $requestResult = $this->tryDeserializeObject($response, $requestResult, $endpointSettings['model']);

        return $requestResult;
    }

    /**
     * @param Company $company
     * @return Result
     */
    public function createClientPayerCompany(Company $company)
    {
        return $this->createBillingEntityBySettings($company, self::PAYER_COMPANY_ENDPOINT_SETTINGS);
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
        $queryData = ['client' => $client->getId()];

        return $this->getEntities(self::PAYER_COMPANY_ENDPOINT_SETTINGS, $queryData)->getData();
    }

    /**
     * @param Company $company
     * @param bool $isRu
     * @return null|object
     */
    public function getClientPayerCompany(Company $company, $isRu = true)
    {
        $endpointSettings = $isRu ? self::RU_PAYER_COMPANY : self::WORLD_PAYER_COMPANY;

        $url = $this->getBillingUrl($endpointSettings['endpoint'], $company->getId());

        try {
            $response = $this->sendGet($url);
            return $this->serializer->deserialize((string)$response->getBody(), $endpointSettings['model'], 'json');
        } catch (RequestException $exception) {
            return null;
        }
    }

    /**
     * @param Client $client
     * @return null|object
     */
    public function getClientPayer(Client $client)
    {
        $url = $this->getBillingUrl(self::CLIENT_PAYER_ENDPOINT_SETTINGS['endpoint'], $client->getLogin());

        try {
            $response = $this->sendGet($url);
            return $this->serializer
                ->deserialize((string)$response->getBody(), self::CLIENT_PAYER_ENDPOINT_SETTINGS['model'], 'json');
        } catch (RequestException $exception) {
            return null;
        }
    }

    /**
     * @return null|WebSite
     */
    public function getClientSite()
    {
        $url = $this->getBillingUrl(self::SITES_ENDPOINT_SETTINGS['endpoint'], $this->billingLogin);
        try {
            $response = $this->sendGet($url);
            return $this->serializer->deserialize($response->getBody(), self::SITES_ENDPOINT_SETTINGS['model'], 'json');
        } catch (ClientException $exception) {
            return null;
        }
    }

    /**
     * @param WebSite $clientSite
     * @return Result
     */
    public function addClientSite(WebSite $clientSite)
    {
        return $this->createBillingEntityBySettings($clientSite, self::SITES_ENDPOINT_SETTINGS);
    }

    /**
     * @param WebSite $clientSite
     * @return Result
     */
    public function updateClientSite(WebSite $clientSite)
    {
        $url = $this->getBillingUrl(self::SITES_ENDPOINT_SETTINGS['endpoint'], $this->billingLogin);

        return $this->updateEntity($url, $this->serializer->normalize($clientSite), self::SITES_ENDPOINT_SETTINGS['model']);
    }

    /**
     * @param string $siteUrl
     * @return Result
     */
    public function getSitesByUrlResult(string $siteUrl)
    {
        $queryParams = ['search' => $siteUrl];

        return $this->getEntities(self::SITES_ENDPOINT_SETTINGS, $queryParams);
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

        $url = $this->getBillingUrl(self::CLIENT_SERVICES_ENDPOINT_SETTINGS['endpoint']);
        $response = $this->sendPost($url, $newClientServiceData);
        $requestResult = new Result();

        if ($response->getStatusCode() === 200) {
            return $this->tryDeserializeObject($response, $requestResult, ClientService::class);
        }

        return $this->handleErrorResponse($response, $requestResult, $url, $newClientServiceData);
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
     * @throws \Exception
     */
    public function getClientOrdersResultByCreationDate(Client $client, \DateTime $begin, \DateTime $end)
    {
        $queryData = [
            'client__login' => $client->getLogin(),
            'created__gte' => $begin->format(BillingApi::BILLING_DATETIME_FORMAT),
            'created__lte' => (clone $end)->add(new \DateInterval('P1D'))->format(BillingApi::BILLING_DATETIME_FORMAT)
        ];

        return $this->getEntities(self::ORDERS_ENDPOINT_SETTINGS, $queryData);
    }

    /**
     * @param string $clientLogin
     * @return Result
     */
    public function getClientOrdersSortedByExpiredData(string $clientLogin)
    {
        $queryData = [
            'client__login' => $clientLogin,
            'ordering' => '-expired_date'
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
        $newTariffData['rooms'] = (int)$newTariffData['rooms'];
        $url = $this->billingHost . '/' . $this->locale . '/clients/' . $this->billingLogin . '/tariff_update/';

        return $this->sendPostAndHandleResult($url, $newTariffData);
    }

    /**
     * @return array
     */
    public function getTariffsData()
    {
        $response = $this->sendGet($this->billingHost . '/' . $this->locale . '/clients/' . $this->billingLogin . '/tariff_detail/');
        $decodedResponse = json_decode((string)$response->getBody(), true);

        return $decodedResponse;
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
    public function getCountryByTld($countryTld, $locale = null)
    {
        return $this->getBillingEntityById(self::COUNTRIES_ENDPOINT_SETTINGS, $countryTld, $locale);
    }

    /**
     * @param $regionId
     * @param null $locale
     * @return Region|object
     */
    public function getRegionById($regionId, $locale = null)
    {
        return $this->getBillingEntityById(self::REGIONS_ENDPOINT_SETTINGS, $regionId, $locale);
    }

    /**
     * @param array $endpointSettings
     * @param $entity
     * @return ResponseInterface
     */
    public function createBillingEntity(array $endpointSettings, $entity)
    {
        $url = $this->getBillingUrl($endpointSettings['endpoint']);
        $data = $this->serializer->normalize($entity);

        return $this->sendPost($url, $data, false);
    }


    /**
     * @param $cityId
     * @param null $locale
     * @return City|object
     */
    public function getCityById($cityId, $locale = null)
    {
        return $this->getBillingEntityById(self::CITIES_ENDPOINT_SETTINGS, $cityId, $locale);
    }

    /**
     * @param Client $client
     * @return Result
     * @throws Exception
     */
    public function confirmClient(Client $client)
    {
        $url = $this->billingHost . '/' . $this->locale . '/clients/' . $client->getLogin() . '/confirm';

        return $this->sendPostAndHandleResult($url, []);
    }

    /**
     * @return Result
     */
    public function getInstalledClients()
    {
        return $this->getEntities(self::CLIENTS_ENDPOINT_SETTINGS, [
            'installation' => 'installed'
        ]);
    }

    /**
     * @param $clientIp
     * @param $userAgent
     */
    public function sendClientAuthMessage($clientIp, $userAgent)
    {
        $clientAuth = (new ClientAuth())
            ->setIp($clientIp)
            ->setClient($this->billingLogin)
            ->setAuth_date((new \DateTime())->format(self::BILLING_DATETIME_FORMAT))
            ->setUser_agent($userAgent)
        ;

        $url = $this->getBillingUrl('authentications');
        $data = $this->serializer->normalize($clientAuth);
        try {
            $response = $this->sendPost($url, $data, true);
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
            $requestResult = new Result();
            $this->handleErrorResponse($response, $requestResult, $url, $data);
        }
    }

    /**
     * @param $url
     * @param $data
     * @return Result
     */
    private function sendPostAndHandleResult($url, $data)
    {
        $requestResult = new Result();

        try {
            $response = $this->sendPost($url, $data, true);
        } catch (RequestException $exception) {
            $requestResult->setIsSuccessful(false);
            $response = $exception->getResponse();
            $this->handleErrorResponse($response, $requestResult, $url, $data);

            return $requestResult;
        }

        $decodedResponse = json_decode((string)$response->getBody(), true);
        if ($decodedResponse['status'] !== true) {
            $requestResult->setIsSuccessful(false);
        }

        return $requestResult;
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
            $data = $this->serializer->deserialize($response->getBody(), $modelType, 'json');
            $requestResult->setData($data);
        } catch (\Exception $exception) {
            $this->logger->error('Error by deserialization of client: "' . $exception->getMessage() . '"');
            $requestResult->setIsSuccessful(false);
        }

        return $requestResult;
    }

    /**
     * @param ResponseInterface $response
     * @param Result $requestResult
     * @param array $requestData
     * @param string $url
     * @return Result
     */
    private function handleErrorResponse(?ResponseInterface $response, Result &$requestResult, string $url, array $requestData = [])
    {
        if (!is_null($response) && $response->getStatusCode() == 400) {
            $requestResult->setErrors(json_decode((string)$response->getBody(), true));
        } else {
            $errorMessage = !is_null($response) ? (string)$response->getBody() : ['billing is not available'];
            $this->logErrorResponse($errorMessage, $url, $requestData);
            $requestResult->setErrors(json_decode($errorMessage, true));
        }

        return $requestResult;
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
            $this->handleErrorResponse($response, $requestResult, $url, $requestData);

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
    public function getEntities($endpointSettings, array $queryData = [])
    {
        $entities = [];
        $endpoint = $endpointSettings['endpoint'];
        $url = $this->getBillingUrl($endpoint, null, null, $queryData);

        try {
            $response = $this->sendGet($url);
        } catch (RequestException $exception) {
            return $this->handleErrorResponse($exception->getResponse(), new Result(), $url, []);
        }

        $decodedResponse = json_decode($response->getBody(), true);
        if (!$endpointSettings['returnArray'] && $decodedResponse['next']) {
            $entities = array_merge($this->getEntitiesByUrl($decodedResponse['next'], $endpointSettings['model']));
        }

        $entitiesData = $endpointSettings['returnArray'] ? $decodedResponse : $decodedResponse['results'];
        foreach ($entitiesData as $serviceData) {
            $entities[] = $this->serializer->denormalize($serviceData, $endpointSettings['model']);
        }

        return Result::createSuccessResult($entities);
    }

    /**
     * @param string $url
     * @param string $modelType
     * @param bool $isRecursive
     * @return array
     */
    public function getEntitiesByUrl(string $url, string $modelType, $isRecursive = true)
    {
        $entities = [];
        try {
            $response = $this->sendGet($url);
        } catch (RequestException $exception) {
            $this->logErrorAndThrowException($exception, $url);
        }

        $decodedResponse = json_decode($response->getBody(), true);
        if ($decodedResponse['next'] && $isRecursive) {
            $entities = array_merge($this->getEntitiesByUrl($decodedResponse['next'], $modelType));
        }

        foreach ($decodedResponse['results'] as $serviceData) {
            $entities[] = $this->serializer->denormalize($serviceData, $modelType);
        }
        
        return $entities;
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
            $url = $this->getBillingUrl($endpoint, $id, $locale);
            try {
                $response = $this->sendGet($url);
            } catch (RequestException $exception) {
                $this->logErrorAndThrowException($exception, $url);
            }

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
        try {
            $response = $this->sendGet($url);
        } catch (RequestException $exception) {
            $this->logErrorAndThrowException($exception, $url);
        }

        return $this->serializer->deserialize($response->getBody(), $modelType, 'json');
    }

    /**
     * @param $endpoint
     * @param $queryParams
     * @return array
     */
    public function getBillingEntitiesByQuery($endpoint, $queryParams)
    {
        $url = $this->getBillingUrl($endpoint['endpoint'], null, $this->locale, $queryParams);

        try {
            $response = $this->sendGet($url);
        } catch (RequestException $exception) {
            $this->logErrorAndThrowException($exception, $url);
        }
        $decodedResponse = json_decode($response->getBody(), true);

        $entities = [];
        foreach ($decodedResponse['results'] as $entityData) {
            $entities[] = $this->serializer->denormalize($entityData, $endpoint['model']);
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

        return $this->billingHost
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
            RequestOptions::HEADERS => array_merge($this->getAuthorizationHeaderAsArray(), [
                'Content-type' => 'application/json',
            ]),
            RequestOptions::JSON => $data,
        ]);
    }

    /**
     * @return array
     */
    private function getAuthorizationHeaderAsArray()
    {
        return ['Authorization' => 'Token ' . $this->billingToken];
    }

    /**
     * @param $errorMessage
     * @param string $url
     * @param array $requestData
     */
    private function logErrorResponse($errorMessage, string $url, array $requestData): void
    {
        $this->logger->err('Exception was thrown by requesting ' . ' by url ' . $url
            . '. Request data: ' . json_encode($requestData)
            . '. Response: ' . $errorMessage
        );
    }

    /**
     * @param $exception
     * @param $url
     */
    private function logErrorAndThrowException($exception, $url): void
    {
        $message = $exception->getResponse() ? (string)$exception->getResponse()->getBody : $exception->getMessage();
        $this->logErrorResponse($message, $url, []);
        throw new \RuntimeException('Can not get data by url ' . $url);
    }
}