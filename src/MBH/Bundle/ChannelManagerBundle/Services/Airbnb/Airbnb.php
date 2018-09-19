<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Airbnb;

use ICal\ICal;
use MBH\Bundle\ChannelManagerBundle\Document\AirbnbConfig;
use MBH\Bundle\ChannelManagerBundle\Document\AirbnbRoom;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractChannelManagerService;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\Request;

class Airbnb extends AbstractChannelManagerService
{
    const NAME = 'airbnb';
    const SYNC_URL_BEGIN = 'https://www.airbnb.ru/calendar/ical/';
    const CONFIG = 'AirbnbConfig';

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @return boolean
     * @throw \Exception
     */
    public function updatePrices(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
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
    public function updateRooms(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
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
    public function updateRestrictions(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
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
     * @return mixed
     */
    public function pullOrders()
    {
        $httpService = $this->container->get('mbh.cm_http_service');
        /** @var AirbnbConfig $config */
        foreach ($this->getConfig() as $config) {
            $roomTypes = array_map(function(AirbnbRoom $room) {
                return $room->getRoomType();
            }, $config->getRooms()->toArray());
            $roomTypeIds = $this->helper->toIds($roomTypes);

            $getAirbnbPackages = function () use ($roomTypeIds) {
                return $this->dm
                    ->getRepository('MBHPackageBundle:Package')
                    ->findBy([
                        'roomType.id' => ['$in' => $roomTypeIds],
                        'channelManagerType' => self::NAME
                    ]);
            };
            /** @var Package[] $packages */
            $packages = $this->helper->getWithoutFilter($getAirbnbPackages);

            $packagesByRoomIds = [];
            foreach ($packages as $package) {
                $packagesByRoomIds[$package->getRoomType()->getId()][$package->getChannelManagerId()] = $package;
            }

            foreach ($config->getRooms() as $room) {
                $result = $httpService->getByUrl($room->getSyncUrl());
                if ($result->isSuccessful()) {
                    $airbnbPackageIds = [];
                    $packagesInRoom = $packagesByRoomIds[$room->getRoomType()->getId()] ?? [];

                    $iCalResponse = new ICal($result->getData());
                    $events = $iCalResponse->cal['VEVENT'];

                    foreach ($events as $event) {
                        $orderInfo = $this->container
                            ->get('mbh.airbnb_order_info')
                            ->setInitData($event, $room, $config->getTariffs()->first());
                        $airbnbPackageIds[] = $orderInfo->getChannelManagerOrderId();

                        if (isset($packagesInRoom[$orderInfo->getChannelManagerOrderId()])) {
                            /** @var Package $existingPackage */
                            $existingPackage = $packagesInRoom[$orderInfo->getChannelManagerOrderId()];
                            $packageInfo = $orderInfo->getPackagesData()[0];

                            if ($existingPackage->getBegin() != $packageInfo->getBeginDate()
                                || $existingPackage->getEnd() != $packageInfo->getEndDate()) {
                                $this->container
                                    ->get('mbh.channelmanager.order_handler')
                                    ->createOrder($orderInfo, $existingPackage->getOrder());
                            }
                        } else {
                            $this->container
                                ->get('mbh.channelmanager.order_handler')
                                ->createOrder($orderInfo);
                        }
                    }

                    $deletedPackageIds = array_diff(array_keys($packagesInRoom), $airbnbPackageIds);
                    foreach ($deletedPackageIds as $packageId) {
                        /** @var Package $deletedPackage */
                        $deletedPackage = $packagesInRoom[$packageId];
                        if (!$deletedPackage->isDeleted()) {
                            $this->dm->remove($deletedPackage);
                        }
                    }
                } else {
                    $this->log($result->getData());
                }
            }
        }

        return $this;
    }

    /**
     * Pull rooms from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullRooms(ChannelManagerConfigInterface $config)
    {
        return [];
    }

    /**
     * Pull tariffs from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullTariffs(ChannelManagerConfigInterface $config)
    {
        return [];
    }

    /**
     * Check response from booking service
     * @param mixed $response
     * @param array $params
     * @return boolean
     */
    public function checkResponse($response, array $params = null)
    {
        // TODO: Implement checkResponse() method.
    }

    /**
     * Close sales on service
     * @param ChannelManagerConfigInterface $config
     * @return boolean
     */
    public function closeForConfig(ChannelManagerConfigInterface $config)
    {
        return true;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function pushResponse(Request $request)
    {
        return new Response();
    }
}