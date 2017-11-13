<?php

namespace MBH\Bundle\BillingBundle\Service;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use MBH\Bundle\BillingBundle\Lib\Model\Client;
use MBH\Bundle\BillingBundle\Lib\Model\ClientService;
use MBH\Bundle\BillingBundle\Lib\Model\Result;
use MBH\Bundle\BillingBundle\Lib\Model\Service;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Serializer\Serializer;

class BillingApi
{
    const BILLING_HOST = 'http://billing.maxibooking.ru';
    const RESULT_API_URL = '/result';
    const CLIENTS_ENDPOINT = 'clients';
    const CLIENT_PROPERTIES_ENDPOINT = 'property';
    const CLIENT_SERVICES_ENDPOINT = 'client-services';
    const SERVICES_ENDPOINT = 'services';
    const AUTH_TOKEN = 'e3cbe9278e7c5821c5e75d2a0d0caf9e851bf1fd';
    const BILLING_DATETIME_FORMAT = 'Y-m-d\TH:i:s.u\Z';

    /** @var GuzzleClient */
    private $guzzle;
    private $logger;
    private $billingLogin;
    private $locale;
    private $serializer;

    private $loadedEntities = [];
    private $clientServices;
    private $isClientServicesInit = false;

    public function __construct(Logger $logger, $billingLogin, $locale, Serializer $serializer)
    {
        $this->guzzle = new GuzzleClient();
        $this->logger = $logger;
        $this->billingLogin = $billingLogin;
        $this->locale = $locale;
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

    /**
     * @return object
     */
    public function getClient()
    {
        return $this->getBillingEntityById(self::CLIENTS_ENDPOINT, $this->billingLogin, Client::class);
    }

    /**
     * @param $serviceId
     * @return object
     */
    public function getServiceById($serviceId)
    {
        return $this->getBillingEntityById(self::SERVICES_ENDPOINT, $serviceId, Service::class);
    }

    /**
     * @param Client $client
     * @return Result
     */
    public function updateClient(Client $client)
    {
        $url = $this->getBillingUrl(self::CLIENTS_ENDPOINT, $client->getLogin());
        $clientData = $this->serializer->normalize($client);
        $clientData['url'] = null;
        $requestResult = new Result();

        try {
            $response = $this->sendPatch($url, $clientData);
        } catch (RequestException $exception) {
            $requestResult->setIsSuccessful(false);

            $response = $exception->getResponse();
            $this->handleErrorResponse($response, $requestResult);

            return $requestResult;
        }

        $requestResult = $this->tryDeserializeObject($response, $requestResult, Client::class);

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

            $this->clientServices = $this->getEntities(self::CLIENT_SERVICES_ENDPOINT, $queryData, ClientService::class);
            $this->isClientServicesInit = true;
        }

        return $this->clientServices;
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

        $response = $this->sendPost($this->getBillingUrl(self::CLIENT_SERVICES_ENDPOINT), $newClientServiceData);
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
        return $this->getEntities(self::SERVICES_ENDPOINT, [], Service::class);
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
     * @param string $uri
     * @return ResponseInterface
     */
    private function sendGet(string $uri)
    {
        return $this->guzzle->get($uri, [
            RequestOptions::HEADERS => [
                'Authorization' => 'Token ' . self::AUTH_TOKEN
            ]
        ]);
    }

    /**
     * @param string $uri
     * @param array $data
     * @param bool $throwException
     * @return ResponseInterface
     */
    public function sendPost(string $uri, array $data, $throwException = false)
    {
        return $this->guzzle->post($uri . '/', [
            RequestOptions::HEADERS => [
                'Authorization' => 'Token ' . self::AUTH_TOKEN,
            ],
            RequestOptions::JSON => $data,
            RequestOptions::HTTP_ERRORS => $throwException
        ]);
    }

    /**
     * @param string $endpoint
     * @param array $queryData
     * @param string $modelType
     * @return Result
     */
    private function getEntities(string $endpoint, array $queryData, string $modelType)
    {
        $url = $this->getBillingUrl($endpoint, null, null, $queryData);

        $response = $this->guzzle->get($url, [
            RequestOptions::HEADERS => [
                'Authorization' => 'Token ' . self::AUTH_TOKEN,
            ],
            RequestOptions::HTTP_ERRORS => false
        ]);

        if ($response->getStatusCode() !== 200) {
            $this->logger->error('Error when retrieving data from the server by url' . $url . ': ' . $response->getBody());
            return Result::createErrorResult();
        }

        $entitiesData = json_decode($response->getBody(), true)['results'];

        $entities = [];
        foreach ($entitiesData as $serviceData) {
            $entities[] = $this->serializer->denormalize($serviceData, $modelType);
        }

        return Result::createSuccessResult($entities);
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
        if (!isset($this->loadedEntities[$endpoint][$id])) {
            $response = $this->sendGet($this->getBillingUrl($endpoint, $id, $locale));
            $entity = $this->serializer->deserialize($response->getBody(), $modelType, 'json');
            $this->loadedEntities[$endpoint][$id] = $entity;
        }

        return $this->loadedEntities[$endpoint][$id];
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
}