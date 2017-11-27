<?php

namespace MBH\Bundle\ClientBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BillingBundle\Lib\Model\Client;
use MBH\Bundle\BillingBundle\Lib\Model\ClientService;
use MBH\Bundle\BillingBundle\Lib\Model\Result;
use MBH\Bundle\BillingBundle\Service\BillingApi;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use Symfony\Component\HttpFoundation\Session\Session;

class ClientManager
{
    const CLIENT_DATA_STORAGE_TIME_IN_MINUTES = 1;
    const DEFAULT_ROUTE_FOR_INACTIVE_CLIENT = 'user_contacts';
    const ACCESSED_ROUTES_FOR_CLIENT = ['user_contacts', 'user_services', 'add_client_service', 'user_payer', 'user_payment', 'payments_list_json', 'show_payment_order', 'order_payment_systems'];
    const SESSION_CLIENT_FIELD = 'client';
    const IS_AUTHORIZED_BY_TOKEN = 'is_authorized_by_token';

    private $dm;
    private $session;
    private $billingApi;

    public function __construct(DocumentManager $dm, Session $session, BillingApi $billingApi)
    {
        $this->dm = $dm;
        $this->session = $session;
        $this->billingApi = $billingApi;
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
        $roomCacheRepository = $this->dm->getRepository('MBHPriceBundle:RoomCache');

        $date = reset($modifiedRoomCaches)->getDate();
        $roomCachesByDate = $roomCacheRepository
            ->fetch($date, $date, null, [], null)
            ->toArray();
        $roomCachesByDate = array_unique(array_merge($modifiedRoomCaches, $roomCachesByDate), SORT_REGULAR);

        $numberOfExistedRooms = 0;
        /** @var RoomCache $roomCache */
        foreach ($roomCachesByDate as $roomCache) {
            $numberOfExistedRooms += $roomCache->getTotalRooms();
        }

        return $numberOfExistedRooms > $this->getAvailableNumberOfRooms();
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param array $rawNewRoomCachesData
     * @param array $rawUpdatedRoomCaches
     * @return array
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
            /** @var \MongoDate $date */
            $date = $rawRoomCache['date'];
            $dateString = $date->toDateTime()->format('d.m.Y');
            if (isset($totalNumbersOfRoomsByDates[$dateString])) {
                $totalNumbersOfRoomsByDates[$dateString] += $rawRoomCache['totalRooms'];
            } else {
                $totalNumbersOfRoomsByDates[$dateString] = $rawRoomCache['totalRooms'];
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
        return $this->getClient()->getRooms_limit();
    }

    /**
     * @return bool
     */
    public function isClientActive()
    {
        return $this->getClient()->getStatus() == Client::CLIENT_ACTIVE_STATUS
            || $this->session->get(self::IS_AUTHORIZED_BY_TOKEN) !== false;
    }

    public function confirmClient(Client $client)
    {
        $this->billingApi->confirmClient($client);
        $client = $this->billingApi->getClient();
        $this->updateSessionClientData($client, $currentDateTime);
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
     */
    public function getClient()
    {
        $dataReceiptTime = $this->session->get(Client::CLIENT_DATA_RECEIPT_DATETIME);
        $currentDateTime = new \DateTime();

        if (is_null($dataReceiptTime)
            || $currentDateTime->diff($dataReceiptTime)->i >= self::CLIENT_DATA_STORAGE_TIME_IN_MINUTES
        ) {
            /** @var Client $client */
            $client = $this->billingApi->getClient();
            $this->updateSessionClientData($client, $currentDateTime);
        } else {
            $client = $this->session->get(self::SESSION_CLIENT_FIELD);
        }

        return $client;
    }

    /**
     * @param Client $client
     * @return \MBH\Bundle\BillingBundle\Lib\Model\Result
     */
    public function updateClient(Client $client)
    {
        $clientResponse = $this->billingApi->updateClient($client);
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
     * @return Result
     */
    public function getAvailableServices()
    {
        $client = $this->getClient();
        $clientServicesRequestResult = $this->billingApi->getClientServices($client);
        if (!$clientServicesRequestResult->isSuccessful()) {
            return $clientServicesRequestResult;
        }

        $clientServicesIds = array_map(function (ClientService $clientService) {
            return $clientService->getService();
        }, $clientServicesRequestResult->getData());

        $servicesRequestResult = $this->billingApi->getServices();
        if (!$servicesRequestResult->isSuccessful()) {
            return $servicesRequestResult;
        }

        $services = [];
        /** @var Service $service */
        foreach ($servicesRequestResult->getData() as $service) {
            //TODO: Временно для тестов
//            if (!in_array($service->getId(), $clientServicesIds)) {
                $services[] = $service;
//            }
        }

        return Result::createSuccessResult($services);
    }
}