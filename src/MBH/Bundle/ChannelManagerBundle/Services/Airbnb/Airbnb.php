<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Airbnb;

use ICal\ICal;
use MBH\Bundle\BillingBundle\Lib\Model\Result;
use MBH\Bundle\ChannelManagerBundle\Document\AirbnbConfig;
use MBH\Bundle\ChannelManagerBundle\Document\AirbnbRoom;
use MBH\Bundle\ChannelManagerBundle\Lib\ICalType\AbstractICalTypeChannelManagerService;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Package;

class Airbnb extends AbstractICalTypeChannelManagerService
{
    public const NAME = 'airbnb';
    public const DOMAIN_NAME = self::NAME;
    public const SYNC_URL_BEGIN = 'https://www.' . self::DOMAIN_NAME . '.';
    const CONFIG = 'AirbnbConfig';
    const PERIOD_LENGTH = '1 year';
    const CLOSED_PERIOD_SUMMARY = 'Not available';

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
     * Pull orders from service server
     * @return bool
     * @throws \Throwable
     */
    public function pullOrders(): bool
    {
        $isSuccess = true;

        $httpService = $this->container->get('mbh.cm_http_service');
        /** @var AirbnbConfig $config */
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
                    if (stripos($event['SUMMARY'], self::CLOSED_PERIOD_SUMMARY) !== false) {
                        continue;
                    }
                    $orderInfo = $this->container
                        ->get('mbh.airbnb_order_info')
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
     * @param RoomType $roomType
     * @return string
     * @throws \Exception
     */
    public function generateRoomCalendar(RoomType $roomType): string
    {
        return $this->generateCalendar($roomType, $roomType->getHotel()->getAirbnbConfig());
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
            $this->dm->flush();
        }
    }

    /**
     * @param array $packagesInRoom
     * @param AirbnbOrderInfo $orderInfo
     * @return mixed
     */
    private function modifyOrCreatePackage(array $packagesInRoom, AirbnbOrderInfo $orderInfo)
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

    /**
     * @param $config
     * @return array
     */
    private function getPackagesByRoomIds($config): array
    {
        $roomTypes = array_map(function (AirbnbRoom $room) {
            return $room->getRoomType();
        }, $config->getRooms()->toArray());
        $roomTypeIds = $this->helper->toIds($roomTypes);

        $packages = $this->dm
            ->getRepository('MBHPackageBundle:Package')
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
