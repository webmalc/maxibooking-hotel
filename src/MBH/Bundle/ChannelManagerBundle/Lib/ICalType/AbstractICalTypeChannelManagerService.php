<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib\ICalType;

use ICal\ICal;
use MBH\Bundle\BillingBundle\Lib\Model\Result;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractChannelManagerService;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use MBH\Bundle\ChannelManagerBundle\Document\AbstractICalTypeChannelManagerRoom;

abstract class AbstractICalTypeChannelManagerService extends AbstractChannelManagerService
{
    protected const VENENT = 'VEVENT';

    abstract protected function getOrderInfoService(): AbstractICalTypeOrderInfo;
    abstract protected function getClosedPeriodSummary(): string;
    abstract protected function getPeriodLength(): string;
    abstract protected function getName(): string;
    abstract protected function getCheckClosedPeriodElement(): string;

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @return boolean
     * @throw \Exception
     */
    public function updatePrices(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null): bool
    {
        return true;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @return boolean
     * @throw \Exception
     */
    public function updateRooms(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null): bool
    {
        return true;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @return boolean
     * @throw \Exception
     */
    public function updateRestrictions(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null): bool
    {
        return true;
    }

    public function createPackages()
    {
    }

    /**
     * Pull rooms from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullRooms(ChannelManagerConfigInterface $config): array
    {
        return [];
    }

    /**
     * Pull tariffs from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullTariffs(ChannelManagerConfigInterface $config): array
    {
        return [];
    }

    /**
     * Check response from booking service
     * @param mixed $response
     * @param array $params
     * @return boolean
     */
    public function checkResponse($response, array $params = null): bool
    {
        return true;
    }

    /**
     * Close sales on service
     * @param ChannelManagerConfigInterface $config
     * @return boolean
     */
    public function closeForConfig(ChannelManagerConfigInterface $config): bool
    {
        return true;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function pushResponse(Request $request): Response
    {
        return new Response();
    }

    /**
     * @param RoomType $roomType
     * @param ChannelManagerConfigInterface $config
     * @return string
     * @throws \Exception
     */
    protected function generateCalendar(RoomType $roomType, ChannelManagerConfigInterface $config): string
    {
        $begin = new \DateTime('midnight');
        $end = new \DateTime('midnight +' . $this->getPeriodLength());
        $this->logger->info(
            'Request availability data from ' . $config->getName() .
            ' for room type with id:' . $roomType->getId()
        );

        $iCalGenerator = $this->container->get('mbh.ical_generator');

        if ($config->isReadyToSync()) {
            return $iCalGenerator->renderRoomCalendar(
                $begin,
                $end,
                $roomType,
                $config->getTariffs()->first()->getTariff()
            );
        }

        return $iCalGenerator->renderEmptyCalendar($begin, $end);
    }

    /**
     * Pull orders from service server
     * @return bool
     * @throws \Throwable
     */
    public function pullOrders(): bool
    {
        $httpService = $this->container->get('mbh.cm_http_service');
        /** @var ICalTypeChannelManagerConfigInterface $config */
        foreach ($this->getConfig() as $config) {
            $packagesByRoomIds = $this->getAirbnbPackages();
            $channelManagerPackageIds = [];
            foreach ($config->getRooms() as $room) {
                /** @var Result $result */
                $result = $httpService->getResult($room->getSyncUrl());

                if (!$result->isSuccessful()) {
                    $this->notifyAndLogError($room, $result);
                    $isSuccess = false;
                    continue;
                }

                $PackageIdsByRoom = $this->handleResponse($result, $room, $config, $packagesByRoomIds);
                $channelManagerPackageIds = array_merge($channelManagerPackageIds, $PackageIdsByRoom);
            }
            $this->removeMissingOrders($packagesByRoomIds, $channelManagerPackageIds);
        }

        return $isSuccess ?? true;
    }

    /**
     * @param Result $response
     * @param AbstractICalTypeChannelManagerRoom $room
     * @param ICalTypeChannelManagerConfigInterface $config
     * @param array $packagesByRoomIds
     * @return array
     * @throws \Exception
     */
    private function handleResponse(
        Result $response,
        AbstractICalTypeChannelManagerRoom $room,
        ICalTypeChannelManagerConfigInterface $config,
        array $packagesByRoomIds
    ): array
    {
        $channelManagerPackageIds = [];
        $iCalResponse = new ICal($response->getData());
        $events = $iCalResponse->cal[self::VENENT];

        foreach ($events as $event) {
            if ($this->isClosedPeriodSummary($event)) {
                continue;
            }

            $orderInfo = $this->getOrderInfoService()
                ->setInitData($event, $room, $config->getTariffs()->first()->getTariff());

            if (!$this->checkOutOfDateOrder($orderInfo)) {
                continue;
            }

            $channelManagerPackageIds[] = $orderInfo->getChannelManagerOrderId();

            if (isset($packagesByRoomIds[$orderInfo->getChannelManagerOrderId()])) {
                /** @var Package $existingPackage */
                $existingPackage = $packagesByRoomIds[$orderInfo->getChannelManagerOrderId()];
                $packageInfo = $orderInfo->getPackagesData()[0];

                if ($existingPackage->getBegin() != $packageInfo->getBeginDate()
                    || $existingPackage->getEnd() != $packageInfo->getEndDate()
                ) {
                    $this->modifyPackage($orderInfo, $existingPackage);
                }
            } else {
                $this->createPackage($orderInfo);
            }
        }

        return $channelManagerPackageIds;
    }

    /**
     * @param AbstractICalTypeOrderInfo $orderInfo
     */
    private function createPackage(AbstractICalTypeOrderInfo $orderInfo): void
    {
        $order = $this->container->get('mbh.channelmanager.order_handler')
            ->createOrder($orderInfo);

        $this->notify(
            $order,
            'commonCM',
            'edit',
            ['%channelManagerName%' => $orderInfo->getChannelManagerName()]
        );
    }

    /**
     * @param AbstractICalTypeOrderInfo $orderInfo
     * @param Package $existingPackage
     */
    private function modifyPackage(AbstractICalTypeOrderInfo $orderInfo, Package $existingPackage): void
    {
        $order = $this->container->get('mbh.channelmanager.order_handler')
            ->createOrder($orderInfo, $existingPackage->getOrder());

        $this->notify(
            $order,
            'commonCM',
            'edit',
            ['%channelManagerName%' => $orderInfo->getChannelManagerName()]
        );
    }

    /**
     * @param array $event
     * @return bool
     */
    private function isClosedPeriodSummary(array $event): bool
    {
        return (stripos($event[$this->getCheckClosedPeriodElement()], $this->getClosedPeriodSummary()) !== false);
    }

    /**
     * @param array $packagesInRoom
     * @param array $channelManagerPackageIds
     */
    private function removeMissingOrders(array $packagesInRoom, array $channelManagerPackageIds): void
    {
        $deletedPackageIds = array_diff(array_keys($packagesInRoom), $channelManagerPackageIds);
        foreach ($deletedPackageIds as $packageId) {
            $deletedPackage = $packagesInRoom[$packageId];
            $deletedOrder = $deletedPackage->getOrder();
            $this->dm->remove($deletedOrder);
        }
        $this->dm->flush();
    }

    /**
     * @param AbstractICalTypeOrderInfo $orderData
     * @return bool
     * @throws \Exception
     */
    private function checkOutOfDateOrder(AbstractICalTypeOrderInfo $orderData): bool
    {
        if ($orderData->getDepartureDate()) {
            $departureDate = (new \DateTime())->setTimestamp($orderData->getDepartureDate());

            return $departureDate >= new \DateTime();
        }

        return false;
    }

    /**
     * @return array
     */
    private function getAirbnbPackages(): array
    {
        $packages = $this->dm
            ->getRepository(Package::class)
            ->findBy([
                'channelManagerType' => $this->getName()
            ]);

        foreach ($packages as $package) {
            $packagesByRoomIds[$package->getChannelManagerId()] = $package;
        }

        return $packagesByRoomIds ?? [];
    }

    /**
     * @param AbstractICalTypeChannelManagerRoom $room
     * @param Result $result
     * @throws \Throwable
     */
    private function notifyAndLogError(AbstractICalTypeChannelManagerRoom $room, Result $result): void
    {
        $this->notifyErrorRequest(
            $this->getName(),
            'channelManager.commonCM.notification.request_error.pull_orders'
        );
        $message = $this->getName() .' pull orders error. URL: '. $room->getSyncUrl() .' Response: ' . $result->getData();
        $this->log($message, 'error');
    }
}
