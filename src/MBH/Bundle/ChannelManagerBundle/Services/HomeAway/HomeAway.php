<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\HomeAway;


use ICal\ICal;
use MBH\Bundle\ChannelManagerBundle\Document\HomeAwayConfig;
use MBH\Bundle\ChannelManagerBundle\Document\HomeAwayRoom;
use MBH\Bundle\ChannelManagerBundle\Lib\ICalType\AbstractICalTypeChannelManagerService;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Package;


class HomeAway extends AbstractICalTypeChannelManagerService
{
    public const CHANNEL_MANAGER_TYPE = 'homeAway';
    public const NAME = 'homeaway';
    public const CONFIG = 'HomeAwayConfig';
    public const PERIOD_LENGTH = '1 year';
    protected const CLOSED_PERIOD_SUMMARY = 'TODOTODOTODOTODOTODO';

    protected function getPeriodLength(): string
    {
        return self::PERIOD_LENGTH;
    }

    protected function getName(): string
    {
        return self::NAME;
    }

    protected function getClosedPeriodSummary(): string
    {
        return self::CLOSED_PERIOD_SUMMARY;
    }

    /**
     * @param RoomType $roomType
     * @return string
     * @throws \Exception
     */
    public function generateRoomCalendar(RoomType $roomType): string
    {
        return $this->generateCalendar($roomType, $roomType->getHotel()->getHomeAwayConfig());
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
        /** @var HomeAwayConfig $config */
        foreach ($this->getConfig() as $config) {
            $packagesByRoomIds = $this->getPackagesByRoomIds($config);

            foreach ($config->getRooms() as $room) {
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
                    if (stripos($event['SUMMARY'], self::CLOSED_PERIOD_SUMMARY) !== false) {
                        continue;
                    }
                    $orderInfo = $this->container->get('mbh.homeaway_order_info')
                        ->setInitData($event, $room, $config->getTariffs()->first()->getTariff());

                    $airbnbPackageIds[] = $orderInfo->getChannelManagerOrderId();
                    $packagesInRoom = $this->modifyOrCreatePackage($packagesInRoom, $orderInfo);
                }

                $this->removeMissingOrders($packagesInRoom, $airbnbPackageIds);
            }
        }

        return $isSuccess;
    }

    /**
     * @param array $packagesInRoom
     * @param HomeAwayOrderInfo $orderInfo
     * @return mixed
     */
    private function modifyOrCreatePackage(array $packagesInRoom, HomeAwayOrderInfo $orderInfo)
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

    /**
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    private function getPackagesByRoomIds(ChannelManagerConfigInterface $config): array
    {
        $roomTypes = array_map(function (HomeAwayRoom $room) {
            return $room->getRoomType();
        }, $config->getRooms()->toArray());

        $roomTypeIds = $this->helper::toIds($roomTypes);

        $packages = $this->dm
            ->getRepository(Package::class)
            ->findBy([
                'roomType.id' => ['$in' => $roomTypeIds],
                'channelManagerType' => self::NAME
            ]);

        $packagesByRoomIds = [];
        foreach ($packages as $package) {
            $packagesByRoomIds[$package->getRoomType()->getId()][$package->getChannelManagerId()] = $package;
        }

        return $packagesByRoomIds;
    }
}
