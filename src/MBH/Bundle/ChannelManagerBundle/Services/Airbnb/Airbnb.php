<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Airbnb;

use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;
use ICal\ICal;
use MBH\Bundle\BaseBundle\Lib\EmptyCachePeriod;
use MBH\Bundle\ChannelManagerBundle\Document\AirbnbConfig;
use MBH\Bundle\ChannelManagerBundle\Document\AirbnbRoom;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractChannelManagerService;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Criteria\PackageQueryCriteria;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\Request;

class Airbnb extends AbstractChannelManagerService
{
    const NAME = 'airbnb';
    const SYNC_URL_BEGIN = 'https://www.airbnb.ru/calendar/ical/';
    const CONFIG = 'AirbnbConfig';
    const PERIOD_LENGTH = '1 year';

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
     * @return bool
     * @throws \Throwable
     */
    public function pullOrders()
    {
        $isSuccess = true;

        $httpService = $this->container->get('mbh.cm_http_service');
        /** @var AirbnbConfig $config */
        foreach ($this->getConfig() as $config) {
            $packagesByRoomIds = $this->getPackagesByRoomIds($config);

            foreach ($config->getRooms() as $room) {
                $result = $httpService->getByAirbnbUrl($room->getSyncUrl());
                if ($result->isSuccessful()) {
                    $airbnbPackageIds = [];
                    $packagesInRoom = $packagesByRoomIds[$room->getRoomType()->getId()] ?? [];

                    $iCalResponse = new ICal($result->getData());
                    $events = $iCalResponse->cal['VEVENT'];

                    foreach ($events as $event) {
                        $orderInfo = $this->container
                            ->get('mbh.airbnb_order_info')
                            ->setInitData($event, $room, $config->getTariffs()->first()->getTariff());
                        $airbnbPackageIds[] = $orderInfo->getChannelManagerOrderId();
                        $packagesInRoom = $this->modifyOrCreatePackage($packagesInRoom, $orderInfo);
                    }

                    $this->removeMissingOrders($packagesInRoom, $airbnbPackageIds);
                } else {
                    $this->notifyErrorRequest(self::NAME, 'channelManager.commonCM.notification.request_error.pull_orders');
                    $logErrorMessage = $this->container
                            ->get('translator')
                            ->trans('channelManager.commonCM.notification.request_error.pull_orders', [], 'MBHChannelManagerBundle')
                        . '. URL:' . $room->getSyncUrl()
                        . '. Response: '
                        . $result->getData();
                    $this->log($logErrorMessage);
                    $isSuccess = false;
                }
            }
        }

        return $isSuccess;
    }

    /**
     * @param RoomType $roomType
     * @return string
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \Exception
     */
    public function generateRoomCalendar(RoomType $roomType)
    {
        $hotel = $roomType->getHotel();
        $airbnbConfig = $hotel->getAirbnbConfig();
        /** @var Tariff $tariff */
        $tariff = $airbnbConfig->getTariffs()->first()->getTariff();

        $begin = new \DateTime('midnight');
        $end = new \DateTime('midnight +' . self::PERIOD_LENGTH);

        //TODO: Уточнить
        $calendar = new Calendar('maxibooking');

        if ($airbnbConfig->getIsEnabled()) {
            $warningsCompiler = $this->container->get('mbh.warnings_compiler');
            $emptyPriceCachePeriods = $warningsCompiler
                ->getEmptyCachePeriodsForRoomTypeAndTariff($roomType, $begin, $end, $tariff, PriceCache::class, 'price');
            $emptyRoomCachePeriods = $warningsCompiler
                ->getEmptyCachePeriodsForRoomTypeAndTariff($roomType, $begin, $end, $tariff, RoomCache::class, 'totalRooms');
            $closedPeriods = $warningsCompiler->getClosedPeriods($begin, $end, $roomType, $tariff);

            $emptyCachePeriods = array_map(function (EmptyCachePeriod $emptyCachePeriod) {
                return ['begin' => $emptyCachePeriod->getBegin(), 'end' => $emptyCachePeriod->getEnd()];
            }, array_merge($emptyPriceCachePeriods, $emptyRoomCachePeriods, $closedPeriods));

            $busyPeriods = array_merge($this->getPackagePeriods($roomType, $begin, $end), $emptyCachePeriods);

            $combinedPeriods = $this->container
                ->get('mbh.periods_compiler')
                ->combineIntersectedPeriods($busyPeriods);

            foreach ($combinedPeriods as $period) {
                $this->addEvent($calendar, $period['begin'], $period['end']);
            }
        } else {
            $this->addEvent($calendar, $begin, $end);
        }

        return $calendar->render();
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

    /**
     * @param $packagesInRoom
     * @param $airbnbPackageIds
     */
    private function removeMissingOrders($packagesInRoom, $airbnbPackageIds): void
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

    private function addEvent(Calendar $calendar, \DateTime $begin, \DateTime $end)
    {
        $vEvent = new Event();
        $vEvent->setDtStart($begin);
        //if "notime" param is true, vendor increase end date by one day(class Event, line 263)
        $vEvent->setDtEnd(($end)->modify('-1 day'));
        $vEvent->setNoTime(true);

        $calendar->addComponent($vEvent);

        return $calendar;
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
        } else {
            $order = $this->container
                ->get('mbh.channelmanager.order_handler')
                ->createOrder($orderInfo);
            $this->notify($order, 'commonCM', 'new', ['%channelManagerName%' => $orderInfo->getChannelManagerName()]);
        }

        return $packagesInRoom;
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

    /**
     * @param RoomType $roomType
     * @param \DateTime  $begin
     * @param \DateTime  $end
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    private function getPackagePeriods(RoomType $roomType, \DateTime $begin, \DateTime $end)
    {
        $packageCriteria = new PackageQueryCriteria();
        $packageCriteria->filter = 'live_between';
        $packageCriteria->begin = $begin;
        $packageCriteria->end = $end;
        $packageCriteria->addRoomTypeCriteria($roomType);

        $rawPackages = $this->dm
            ->getRepository('MBHPackageBundle:Package')
            ->queryCriteriaToBuilder($packageCriteria)
            ->hydrate(false)
            ->select(['begin', 'end'])
            ->getQuery()
            ->execute()
            ->toArray();

        $packagePeriods = [];
        foreach ($rawPackages as $rawPackage) {
            $packageBegin = $rawPackage['begin']
                ->toDateTime()
                ->setTimezone(new \DateTimeZone(date_default_timezone_get()));
            $packageEnd = ($rawPackage['end']->toDateTime())
                ->modify('-1 day')
                ->setTimezone(new \DateTimeZone(date_default_timezone_get()));

            $packagePeriods[] = [
                'begin' => $packageBegin >= $begin ? $packageBegin : $begin,
                'end' => $packageEnd <= $end ? $packageEnd : $end
            ];
        }

        return $packagePeriods;
    }
}