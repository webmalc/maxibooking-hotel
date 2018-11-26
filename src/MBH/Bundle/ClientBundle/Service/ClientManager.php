<?php

namespace MBH\Bundle\ClientBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\BillingBundle\Lib\Model\Client;
use MBH\Bundle\BillingBundle\Lib\Model\Country;
use MBH\Bundle\BillingBundle\Lib\Model\PaymentOrder;
use MBH\Bundle\BillingBundle\Lib\Model\Result;
use MBH\Bundle\BillingBundle\Lib\Model\WebSite;
use MBH\Bundle\BillingBundle\Service\BillingApi;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelInterface;

class ClientManager
{
    const CLIENT_DATA_STORAGE_TIME_IN_MINUTES = 120;
    const DEFAULT_ROUTE_FOR_INACTIVE_CLIENT = 'user_payment';
    const ACCESSED_ROUTES_FOR_CLIENT = ['user_contacts', 'user_services', 'add_client_service', 'user_payer', 'user_payment', 'payments_list_json', 'show_payment_order', 'order_payment_systems', 'user_tariff', 'update_tariff_modal'];
    const SESSION_CLIENT_FIELD = 'client';
    const SESSION_CLIENT_SITE = 'client-site';
    const IS_AUTHORIZED_BY_TOKEN = 'is_authorized_by_token';
    const NOT_CONFIRMED_BECAUSE_OF_ERROR = 'not_confirmed_because_of_error';
    const INSTALLATION_PAGE_RU = 'https://login.maxi-booking.ru/';
    const INSTALLATION_PAGE_COM = 'https://login.maxi-booking.com/';

    private $dm;
    private $session;
    private $billingApi;
    private $logger;
    private $client;
    private $kernel;
    private $clientConfigManager;
    private $helper;

    public function __construct(DocumentManager $dm, Session $session, BillingApi $billingApi, Logger $logger, $client, KernelInterface $kernel, ClientConfigManager $clientConfigManager, Helper $helper)
    {
        $this->dm = $dm;
        $this->session = $session;
        $this->billingApi = $billingApi;
        $this->logger = $logger;
        $this->client = $client;
        $this->kernel = $kernel;
        $this->clientConfigManager = $clientConfigManager;
        $this->helper = $helper;
    }

    /**
     * @param $numberOfCreatedRooms
     * @return bool
     */
    public function isLimitOfRoomsExceeded($numberOfCreatedRooms = 0)
    {
        $numberOfExistedRooms = $this->dm
            ->getRepository('MBHHotelBundle:Room')
            ->getNumberOfEnabledRooms();

        return ($numberOfCreatedRooms + $numberOfExistedRooms) > $this->getAvailableNumberOfRooms();
    }

    public function isLimitOfRoomCachesExceeded(array $modifiedRoomCaches)
    {
        if ($this->isDefaultClient()) {
            return false;
        }

        $roomCacheRepository = $this->dm->getRepository('MBHPriceBundle:RoomCache');

        $date = reset($modifiedRoomCaches)->getDate();
        $roomCachesByDate = $roomCacheRepository
            ->fetch($date, $date, null, [], null)
            ->toArray();
        $roomCachesByDate = array_merge($modifiedRoomCaches, $roomCachesByDate);

        $numberOfExistedRooms = 0;
        $roomCachesIds = [];
        /** @var RoomCache $roomCache */
        foreach ($roomCachesByDate as $roomCache) {
            if (!in_array($roomCache->getId(), $roomCachesIds)) {
                $roomCachesIds[] = $roomCache->getId();
                $numberOfExistedRooms += $roomCache->getTotalRooms();
            }
        }

        return $numberOfExistedRooms > $this->getAvailableNumberOfRooms();
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param array $rawNewRoomCachesData
     * @param array $rawUpdatedRoomCaches
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function getDaysWithExceededLimitNumberOfRoomsInSell(
        \DateTime $begin,
        \DateTime $end,
        array $rawNewRoomCachesData = [],
        array $rawUpdatedRoomCaches = []
    )
    {
        $totalNumbersOfRoomsByDates = [];
        foreach ($rawNewRoomCachesData as $rawRoomCache) {
            if (!isset($rawRoomCache['tariff'])) {
                /** @var \MongoDate $date */
                $date = $rawRoomCache['date'];
                $dateString = $date->toDateTime()->format('d.m.Y');
                if (isset($totalNumbersOfRoomsByDates[$dateString])) {
                    $totalNumbersOfRoomsByDates[$dateString] += $rawRoomCache['totalRooms'];
                } else {
                    $totalNumbersOfRoomsByDates[$dateString] = $rawRoomCache['totalRooms'];
                }
            }
        }

        $sortedByIdsUpdatedData = [];
        foreach ($rawUpdatedRoomCaches as $updatedData) {
            $sortedByIdsUpdatedData[$updatedData['criteria']['_id']->serialize()] = $updatedData['values']['totalRooms'];
        }

        $rawExistedRoomCaches = $this->dm
            ->getRepository('MBHPriceBundle:RoomCache')
            ->getRawExistedRoomCaches($begin, $end, ['date', 'totalRooms']);

        foreach ($rawExistedRoomCaches as $rawRoomCache) {
            /** @var \MongoDate $mongoDate */
            $mongoDate = $rawRoomCache['date'];
            $dateString = date('d.m.Y', $mongoDate->sec);
            $rawRoomCacheId = $rawRoomCache['_id']->serialize();
            $numberOfRooms = isset($sortedByIdsUpdatedData[$rawRoomCacheId])
                ? $sortedByIdsUpdatedData[$rawRoomCacheId]
                : $rawRoomCache['totalRooms'];

            if (isset($totalNumbersOfRoomsByDates[$dateString])) {
                $totalNumbersOfRoomsByDates[$dateString] += $numberOfRooms;
            } else {
                $totalNumbersOfRoomsByDates[$dateString] = $numberOfRooms;
            }
        }

        $daysWithExcessNumber = [];
        foreach ($totalNumbersOfRoomsByDates as $dateString => $numberOfRooms) {
            if ($numberOfRooms > $this->getAvailableNumberOfRooms()) {
                $daysWithExcessNumber[] = $dateString;
            }
        }

        return $daysWithExcessNumber;
    }

    /**
     * @return mixed
     */
    public function getAvailableNumberOfRooms()
    {
        return $this->getClient()->getRoomsLimit();
    }

    /**
     * @return bool
     */
    public function isClientActive()
    {
        return $this->getClient()->getStatus() === Client::CLIENT_ACTIVE_STATUS
            || $this->session->get(self::IS_AUTHORIZED_BY_TOKEN) !== false;
    }

    /**
     * @param Client $client
     * @return Result
     */
    public function confirmClient(Client $client)
    {
        $result = $this->billingApi->confirmClient($client);
        if ($result->isSuccessful()) {
            $client = $this->billingApi->getClient($client->getLogin());
            $this->updateSessionClientData($client, new \DateTime());
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isDefaultClient()
    {
        return false && $this->client === \AppKernel::DEFAULT_CLIENT || $this->kernel->getEnvironment() === 'test';
    }

    /**
     * @param $routeName
     * @return bool
     */
    public function isRouteAccessibleForInactiveClient($routeName)
    {
        return in_array($routeName, self::ACCESSED_ROUTES_FOR_CLIENT);
    }

    /**
     * @return Client
     * @throws \Exception
     */
    public function getClient()
    {
        $dataReceiptTime = $this->session->get(Client::CLIENT_DATA_RECEIPT_DATETIME);
        $currentDateTime = new \DateTime();
        $config = $this->clientConfigManager->fetchConfig();

        if (is_null($dataReceiptTime)|| !$config->isCacheValid()
            || $currentDateTime->diff($dataReceiptTime)->i >= self::CLIENT_DATA_STORAGE_TIME_IN_MINUTES
        ) {
            /** @var Client $client */
            $client = $this->isDefaultClient() ? $this->getDefaultClientData() : $this->billingApi->getClient();
            $this->clientConfigManager->changeCacheValidity(true);
            if (!isset($client) || !$client instanceof Client) {
                throw new NotFoundHttpException('Can not get client with login "' . $this->client . '"');
            }
            $this->updateSessionClientData($client, $currentDateTime);
        } else {
            $client = $this->session->get(self::SESSION_CLIENT_FIELD);
        }

        return $client;
    }

    /**
     * @param Client $client
     * @return \MBH\Bundle\BillingBundle\Lib\Model\Result
     * @throws \Exception
     */
    public function updateClient(Client $client)
    {
        $clientResponse = $this->billingApi->updateBillingEntity($client, BillingApi::CLIENTS_ENDPOINT_SETTINGS, $client->getLogin());
        if ($clientResponse->isSuccessful()) {
            $this->updateSessionClientData($client, new \DateTime());
        }

        return $clientResponse;
    }

    /**
     * @param Client $client
     * @param \DateTime $currentDateTime
     */
    public function updateSessionClientData(Client $client, \DateTime $currentDateTime)
    {
        $this->session->set(Client::CLIENT_DATA_RECEIPT_DATETIME, $currentDateTime);
        $this->session->set(self::SESSION_CLIENT_FIELD, $client);
    }

    /**
     * @return WebSite|null
     */
    public function getClientSite()
    {
        $clientSite = $this->session->get(self::SESSION_CLIENT_SITE);
        if (empty($clientSite)) {
            $clientSite = $this->billingApi->getClientSite();
            $this->session->set(self::SESSION_CLIENT_SITE, $clientSite);
        }

        return $clientSite;
    }

    /**
     * @param WebSite $site
     * @return Result
     */
    public function addOrUpdateSite(WebSite $site)
    {
        $result = $site->getId() ? $this->billingApi->updateClientSite($site) : $this->billingApi->addClientSite($site);
        if ($result->isSuccessful()) {
            $this->session->set(self::SESSION_CLIENT_SITE, $site);
        }

        return $result;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isRussianClient()
    {
        return $this->getClient()->getCountry() === Country::RUSSIA_TLD;
    }

    /**
     * @return int|null
     */
    public function getNumberOfDaysBeforeDisable()
    {
        $clientOrdersResult = $this->billingApi->getClientOrdersSortedByExpiredData($this->client);
        if ($clientOrdersResult->isSuccessful()) {
            $clientOrders = $clientOrdersResult->getData();
            if (!empty($clientOrders)) {
                /** @var PaymentOrder $lastOrder */
                $lastOrder = $clientOrders[0];
                $currentDateTime = new \DateTime();

                $daysBeforeDisable = $this->helper
                    ->getDifferenceInDaysWithSign($currentDateTime, $lastOrder->getExpiredDateAsDateTime());
                if ($lastOrder->getExpiredDateAsDateTime() > $currentDateTime
                    && $lastOrder->getStatus() !== PaymentOrder::STATUS_PAID && $daysBeforeDisable >= 0) {
                    return $daysBeforeDisable;
                }
            }
        }

        return null;
    }

    private function getDefaultClientData()
    {
        return (new Client())
            ->setEmail('d.zaluev@maxi-booking.ru')
            ->setName('maxiboooking')
            ->setCountry('ru')
            ->setLogin('maxibooking')
            ->setPhone('+89670447992')
            ->setCity(16970)
            ->setRegion(2832)
            ->setStatus(Client::CLIENT_ACTIVE_STATUS)
            ->setRestrictions(['rooms_limit' => 200])
            ->setTrial_activated(true)
            ->setInstallation('installed');
    }
}