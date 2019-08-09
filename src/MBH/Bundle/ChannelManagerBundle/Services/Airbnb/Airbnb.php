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
    public const NAME = 'airbnb';
    public const DOMAIN_NAME = self::NAME;
    public const SYNC_URL_BEGIN = 'https://www.' . self::DOMAIN_NAME . '.';
    const CONFIG = 'AirbnbConfig';
    const PERIOD_LENGTH = '1 year';
    const CLOSED_PERIOD_SUMMARY = 'Not available';

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
            $packagesByRoomIds = $this->getAirbnbPackages();
            $airbnbPackageIds = [];
            foreach ($config->getRooms() as $room) {
                $result = $httpService->getByAirbnbUrl($room->getSyncUrl());
                if ($result->isSuccessful()) {

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
                        $this->modifyOrCreatePackage($packagesByRoomIds, $orderInfo);
                    }
                } else {
                    $this->notifyErrorRequest(self::NAME, 'channelManager.commonCM.notification.request_error.pull_orders');
                    $logErrorMessage = $this->container
                            ->get('translator')
                            ->trans('channelManager.commonCM.notification.request_error.pull_orders', [
                                '%channelManagerName%' => 'Airbnb'
                            ], 'MBHChannelManagerBundle')
                        . '. URL:' . $room->getSyncUrl()
                        . '. Response: '
                        . $result->getData();
                    $this->log($logErrorMessage, 'error');
                    $isSuccess = false;
                }
            }
            $this->removeMissingOrders($packagesByRoomIds, $airbnbPackageIds);
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

        $begin = new \DateTime('midnight');
        $end = new \DateTime('midnight +' . self::PERIOD_LENGTH);
        $this->logger->info('Request availability data from Airbnb for room type with id:' . $roomType->getId());

        //TODO: Уточнить
        $calendar = new Calendar('maxibooking');

        if ($airbnbConfig->isReadyToSync()) {
            /** @var Tariff $tariff */
            $tariff = $airbnbConfig->getTariffs()->first()->getTariff();

            $warningsCompiler = $this->container->get('mbh.warnings_compiler');
            $emptyPriceCachePeriods = $warningsCompiler
                ->getEmptyCachePeriodsForRoomTypeAndTariff($roomType, $begin, $end, $tariff, PriceCache::class, 'price');
            $emptyRoomCachePeriods = $warningsCompiler
                ->getEmptyCachePeriodsForRoomTypeAndTariff($roomType, $begin, $end, $tariff, RoomCache::class, 'leftRooms');
            $closedPeriods = $warningsCompiler->getClosedPeriods($begin, $end, $roomType, $tariff);

            $emptyCachePeriods = array_map(function (EmptyCachePeriod $emptyCachePeriod) {
                return ['begin' => $emptyCachePeriod->getBegin(), 'end' => $emptyCachePeriod->getEnd()];
            }, array_merge($emptyPriceCachePeriods, $emptyRoomCachePeriods, $closedPeriods));

            $combinedPeriods = $this->container
                ->get('mbh.periods_compiler')
                ->combineIntersectedPeriods($emptyCachePeriods);

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
        }
        $this->dm->flush();
    }

    private function addEvent(Calendar $calendar, \DateTime $begin, \DateTime $end)
    {
        $vEvent = new Event();
        $vEvent->setDtStart($begin);
        //if "notime" param is true, vendor increase end date by one day(class Event, line 263)
        $vEvent->setDtEnd($end);
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
        } else if ($this->checkOutOfDateOrder($orderInfo->getOrderData())) {
            $order = $this->container
                ->get('mbh.channelmanager.order_handler')
                ->createOrder($orderInfo);
            $this->notify($order, 'commonCM', 'new', ['%channelManagerName%' => $orderInfo->getChannelManagerName()]);
        }

        return $packagesInRoom;
    }

    private function checkOutOfDateOrder(array $orderData): bool
    {
        if (isset($orderData['DTEND_array'][2])) {
            $departureDate = (new \DateTime())->setTimestamp($orderData['DTEND_array'][2]);

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
                'channelManagerType' => self::NAME
            ]);

        foreach ($packages as $package) {
            $packagesByRoomIds[$package->getChannelManagerId()] = $package;
        }

        return $packagesByRoomIds ?? [];
    }
}
