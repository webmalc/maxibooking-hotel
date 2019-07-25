<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib\ICalType;


use ICal\ICal;
use MBH\Bundle\BillingBundle\Lib\Model\Result;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractChannelManagerService;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractOrderInfo;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractICalTypeChannelManagerService extends AbstractChannelManagerService
{
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

    /**
     * Create packages from service request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throw \Exception
     */
    public function createPackages()
    {
        // TODO: Implement createPackages() method.
    }

    /**
     * Pull orders from service server
     * @return bool
     * @throws \Throwable
     */
    public function pullOrders(): bool
    {
        $isSuccess = true;

        $httpService = $this->container->get('mbh.cm_http_service');
        /** @var ChannelManagerConfigInterface $config */
        foreach ($this->getConfig() as $config) {
            $packagesByRoomIds = $this->getPackagesByRoomIds($config);

            foreach ($config->getRooms() as $room) {
                /** @var Result $result */
                $result = $httpService->getResult($room->getSyncUrl());
                if (!$result->isSuccessful()) {
                    $this->notifyAndLogError($room, $result);
                    $isSuccess = false;
                    continue;
                }

                $airbnbPackageIds = [];
                $packagesInRoom = $packagesByRoomIds[$room->getRoomType()->getId()] ?? [];

                $iCalResponse = new ICal($result->getData());
                $events = $iCalResponse->cal['VEVENT'];

                foreach ($events as $event) {
                    if (stripos($event['SUMMARY'], $this->getClosedPeriodSummary()) !== false) {
                        continue;
                    }
                    $orderInfo = $this->getOrderInfoService()
                        ->setInitData($event, $room, $config->getTariffs()->first()->getTariff());
                    $airbnbPackageIds[] = $orderInfo->getChannelManagerOrderId();
                    $packagesInRoom = $this->modifyOrCreatePackage($packagesInRoom, $orderInfo);
                }

                $this->removeMissingOrders($packagesInRoom, $airbnbPackageIds);
            }
        }

        return $isSuccess;
    }

    abstract protected function getOrderInfoService(): ICalTypeOrderInfoInterface;

    abstract protected function getClosedPeriodSummary(): string;

    /**
     * @param AbstractICalTypeChannelManagerRoom $room
     * @param Result $result
     * @throws \Throwable
     */
    protected function notifyAndLogError(AbstractICalTypeChannelManagerRoom $room, Result $result): void
    {
        $this->notifyErrorRequest(
            $this->getName(),
            'channelManager.commonCM.notification.request_error.pull_orders'
        );
        $message = $this->getName() .' pull orders error. URL: '. $room->getSyncUrl() .' Response: ' . $result->getData();
        $this->log($message, 'error');
    }

    /** @return string */
    abstract protected function getPeriodLength(): string;

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
            return $iCalGenerator->generateRoomCalendar(
                $begin,
                $end,
                $roomType,
                $config->getTariffs()->first()->getTariff()
            );
        }

        return $iCalGenerator->renderEmptyCalendar($begin, $end);
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
     * @param $packagesInRoom
     * @param $airbnbPackageIds
     */
    protected function removeMissingOrders($packagesInRoom, $airbnbPackageIds): void
    {
        $deletedPackageIds = array_diff(array_keys($packagesInRoom), $airbnbPackageIds);
        foreach ($deletedPackageIds as $packageId) {
            /** @var Package $deletedPackage */
            $deletedPackage = $packagesInRoom[$packageId];
            $deletedOrder = $deletedPackage->getOrder();
            $this->dm->remove($deletedOrder);
        }
        $this->dm->flush();
    }

    /**
     * @param array $packagesInRoom
     * @param ICalTypeOrderInfoInterface $orderInfo
     * @return mixed
     */
    private function modifyOrCreatePackage(array $packagesInRoom, ICalTypeOrderInfoInterface $orderInfo)
    {
        if (isset($packagesInRoom[$orderInfo->getChannelManagerOrderId()])) {
            /** @var Package $existingPackage */
            $existingPackage = $packagesInRoom[$orderInfo->getChannelManagerOrderId()];
            $packageInfo = $orderInfo->getPackagesData()[0];

            if ($existingPackage->getBegin() != $packageInfo->getBeginDate()
                || $existingPackage->getEnd() != $packageInfo->getEndDate()) {
                $order = $this->container
                    ->get('mbh.channelmanager.order_handler')
                    ->createOrder($orderInfo, $existingPackage->getOrder());
                $this->notify($order, 'commonCM', 'edit', ['%channelManagerName%' => $orderInfo->getChannelManagerName()]);
            }
        } else if ($this->checkOutOfDateOrder($orderInfo->getOrderData())) {
            $order = $this->container
                ->get('mbh.channelmanager.order_handler')
                ->createOrder($orderInfo);
            $this->notify($order, 'commonCM', 'new', ['%channelManagerName%' => $orderInfo->getChannelManagerName()]);
        }

        return $packagesInRoom;
    }

    protected function checkOutOfDateOrder(array $orderData): bool
    {
        if (isset($orderData['DTEND_array'][2])) {
            $departureDate = (new \DateTime())->setTimestamp($orderData['DTEND_array'][2]);

            return $departureDate >= new \DateTime();
        }

        return false;
    }

    abstract protected function getName(): string;

    /**
     * @param $config
     * @return array
     */
    private function getPackagesByRoomIds(ChannelManagerConfigInterface $config): array
    {
        $roomTypes = array_map(static function (AbstractICalTypeChannelManagerRoom $room) {
            return $room->getRoomType();
        }, $config->getRooms()->toArray());
        $roomTypeIds = $this->helper::toIds($roomTypes);

        $packages = $this->dm
            ->getRepository(Package::class)
            ->findBy([
                'roomType.id' => ['$in' => $roomTypeIds],
                'channelManagerType' => $this->getName()
            ]);

        $packagesByRoomIds = [];
        foreach ($packages as $package) {
            $packagesByRoomIds[$package->getRoomType()->getId()][$package->getChannelManagerId()] = $package;
        }

        return $packagesByRoomIds;
    }
}
