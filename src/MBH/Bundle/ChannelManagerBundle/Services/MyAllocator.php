<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\CashBundle\Validator\Constraints\CashDocument;
use MBH\Bundle\ChannelManagerBundle\Document\MyallocatorConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractChannelManagerService as Base;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\PriceBundle\Services\PriceCacheRepositoryFilter;
use MyAllocator\phpsdk\src\Api\ARIUpdate;
use MyAllocator\phpsdk\src\Api\AssociateUserToPMS;
use MyAllocator\phpsdk\src\Api\BookingList;
use MyAllocator\phpsdk\src\Api\MaApi;
use MyAllocator\phpsdk\src\Api\PropertyList;
use MyAllocator\phpsdk\src\Api\RoomList;
use MyAllocator\phpsdk\src\Api\VendorSet;
use MyAllocator\phpsdk\src\Object\Auth;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;


/**
 *  MyAllocator service
 */
class MyAllocator extends Base
{

    /**
     * Config class
     */
    const CONFIG = 'MyallocatorConfig';

    /**
     * @var array
     */
    private $params;

    /**
     * @var \DateTime
     */
    private $today = null;

    /**
     * @var \DateTime
     */
    private $tomorrow = null;

    /**
     * @var PriceCacheRepositoryFilter
     */
    protected $priceCacheFilter;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->params = $container->getParameter('mbh.channelmanager.services')['myallocator'];
        $this->today = new \DateTime('midnight');
        $this->tomorrow = new \DateTime('midnight +1 day');
        $this->priceCacheFilter = $container->get('mbh.price_cache_repository_filter');
    }

    /**
     * @param MyallocatorConfig $config
     * @return Auth
     */
    public function getAuth(MyallocatorConfig $config = null)
    {
        $auth = new Auth();
        $auth->vendorId = $this->params['api_username'];
        $auth->vendorPassword = $this->params['api_password'];

        if ($config && $config->getToken()) {
            $auth->userToken = $config->getToken();
        }
        if ($config && $config->getHotelId()) {
            $auth->propertyId = $config->getHotelId();
        }

        return $auth;
    }

    /**
     * @param MyallocatorConfig $config
     * @return array
     */
    public function propertyList(MyallocatorConfig $config)
    {
        $api = new PropertyList();
        $api->setAuth($this->getAuth($config));
        $response = $this->call($api);

        if (!empty($response['response']['body']['Success'])) {
            return $response['response']['body']['Properties'];
        }

        return [];
    }

    /**
     * @param MyallocatorConfig $config
     * @param bool|false $grouped
     * @return array
     */
    public function roomList(MyallocatorConfig $config, $grouped = false)
    {
        $api = new RoomList();
        $api->setAuth($this->getAuth($config));
        $response = $this->call($api);

        if (!empty($response['response']['body']['Success'])) {
            $rooms = $response['response']['body']['RoomTypes'];

            if (!$grouped) {
                return $rooms;
            }
            $result = [];

            foreach ($rooms as $room) {
                if ($room['Disabled'] === true || $room['Disabled'] === 'true') {
                    continue;
                }
                $result[$room['RoomId']] = $room['Label'];
            }

            return $result;
        }

        return [];
    }

    /**
     * @param string $username
     * @param string $password
     * @return null|string
     */
    public function associateUser($username, $password)
    {
        $api = new AssociateUserToPMS();
        $auth = $this->getAuth();
        $auth->userId = $username;
        $auth->userPassword = $password;
        $api->setAuth($auth);
        $response = $this->call($api);

        if (!empty($response['response']['body']['Auth/UserToken'])) {
            return $response['response']['body']['Auth/UserToken'];
        }

        return null;
    }

    /**
     * @return bool
     */
    public function vendorSet()
    {
        $api = new VendorSet();
        $api->setAuth($this->getAuth());
        $api->setParams([
            'Callback/URL' => $this->params['url'],
            'Callback/Password' => $this->params['vendor_password']
        ]);

        $response = $this->call($api);

        if (!empty($response['response']['body']['Success'])) {
            return true;
        }

        return false;
    }

    /**
     * @param MaApi $api
     * @return bool|mixed
     */
    public function call(MaApi $api)
    {
        try {
            $response = $api->callApi();
        } catch (\Exception $e) {
            if ($this->isDevEnvironment()) {
                dump($e->getMessage());
            }
            return false;
        }

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function closeForConfig(ChannelManagerConfigInterface $config)
    {
        $api = new ARIUpdate();
        $api->setAuth($this->getAuth($config));
        $end = new \DateTime('midnight +2 years');
        $allocations = [];
        $rooms = $this->roomList($config);

        foreach ($rooms as $room) {
            if ($room['Disabled'] === true || $room['Disabled'] === 'true') {
                continue;
            }
            $allocations[] = [
                'RoomId' => $room['RoomId'],
                'StartDate' => $this->today->format('Y-m-d'),
                'EndDate' => $end->format('Y-m-d'),
                'Units' => 0
            ];
        }

        $api->setParams([
            'Channels' => ['all'], 'Allocations' => $allocations
        ]);
        $response = $this->call($api);

        if (!empty($response['response']['body']['Success'])) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function pullTariffs(ChannelManagerConfigInterface $config)
    {
        return ['base' => ['title' => 'Тариф']];
    }

    /**
     * {@inheritDoc}
     */
    public function pullRooms(ChannelManagerConfigInterface $config)
    {
        return $this->roomList($config, true);
    }

    /**
     * {@inheritDoc}
     */
    public function checkResponse($response, array $params = null)
    {

    }

    /**
     * {@inheritDoc}
     */
    public function updateRooms(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = true;
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin, $end);

        // iterate hotels
        foreach ($this->getConfig() as $config) {

            $api = new ARIUpdate();
            $api->setAuth($this->getAuth($config));
            $allocations = [];
            $roomTypes = $this->getRoomTypes($config);
            $roomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')->fetch(
                $begin,
                $end,
                $config->getHotel(),
                $roomType ? [$roomType->getId()] : [],
                null,
                true
            );

            foreach ($roomTypes as $roomTypeId => $roomType) {
                foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), (clone $end)->modify('+1 day')) as $day) {
                    if (isset($roomCaches[$roomTypeId][0][$day->format('d.m.Y')])) {
                        $info = $roomCaches[$roomTypeId][0][$day->format('d.m.Y')];
                        $allocations[] = [
                            'RoomId' => $roomType['syncId'],
                            'StartDate' => $day->format('Y-m-d'),
                            'EndDate' => $day->format('Y-m-d'),
                            'Units' => $info->getLeftRooms() > 0 ? $info->getLeftRooms() : 0
                        ];
                    } else {
                        $allocations[] = [
                            'RoomId' => $roomType['syncId'],
                            'StartDate' => $day->format('Y-m-d'),
                            'EndDate' => $day->format('Y-m-d'),
                            'Units' => 0
                        ];
                    }
                }
            }

            $api->setParams([
                'Channels' => ['all'], 'Allocations' => $allocations
            ]);

            $response = $this->call($api);

            if ($result && empty($response['response']['body']['Success'])) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function updatePrices(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = true;
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin, $end);

        // iterate hotels
        foreach ($this->getConfig() as $config) {
            $api = new ARIUpdate();
            $api->setAuth($this->getAuth($config));
            $allocations = [];
            $roomTypes = $this->getRoomTypes($config);
            $tariffs = $this->getTariffs($config);
            $priceCachesCallback = function () use ($begin, $end, $config, $roomType) {
                $filtered = $this->priceCacheFilter->filterFetch(
                    $this->dm->getRepository('MBHPriceBundle:PriceCache')->fetch(
                        $begin,
                        $end,
                        $config->getHotel(),
                        $this->getRoomTypeArray($roomType),
                        [],
                        true,
                        $this->roomManager->useCategories
                    )
                );
                return $filtered;
            };
            $priceCaches = $this->helper->getFilteredResult($this->dm, $priceCachesCallback);

            foreach ($roomTypes as $roomTypeId => $roomType) {

                $roomTypeId = $this->getRoomTypeArray($roomType['doc'])[0];

                foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), (clone $end)->modify('+1 day')) as $day) {
                    foreach ($tariffs as $tariffId => $tariff) {
                        if (isset($priceCaches[$roomTypeId][$tariffId][$day->format('d.m.Y')])) {
                            $info = $priceCaches[$roomTypeId][$tariffId][$day->format('d.m.Y')];

                            $allocations[] = [
                                'RoomId' => $roomType['syncId'],
                                'StartDate' => $day->format('Y-m-d'),
                                'EndDate' => $day->format('Y-m-d'),
                                'Price' => $this->currencyConvertFromRub($config, $info->getPrice()),
                                'PriceSingle' => $info->getSinglePrice() ? $this->currencyConvertFromRub(
                                    $config,
                                    $info->getSinglePrice()
                                ) : false,
                            ];

                        } else {
                            $allocations[] = [
                                'RoomId' => $roomType['syncId'],
                                'StartDate' => $day->format('Y-m-d'),
                                'EndDate' => $day->format('Y-m-d'),
                                'Closed' => true
                            ];
                        }
                    }
                }
            }

            $api->setParams([
                'Channels' => ['all'], 'Allocations' => $allocations
            ]);

            $response = $this->call($api);

            if ($result && empty($response['response']['body']['Success'])) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function updateRestrictions(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = true;
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin, $end);

        // iterate hotels
        foreach ($this->getConfig() as $config) {
            $api = new ARIUpdate();
            $api->setAuth($this->getAuth($config));
            $allocations = [];
            $roomTypes = $this->getRoomTypes($config);
            $tariffs = $this->getTariffs($config);
            $restrictions = $this->dm->getRepository('MBHPriceBundle:Restriction')->fetch(
                $begin,
                $end,
                $config->getHotel(),
                $roomType ? [$roomType->getId()] : [],
                [],
                true
            );
            foreach ($roomTypes as $roomTypeId => $roomType) {
                foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), (clone $end)->modify('+1 day')) as $day) {
                    foreach ($tariffs as $tariffId => $tariff) {

                        if (isset($restrictions[$roomTypeId][$tariffId][$day->format('d.m.Y')])) {
                            $info = $restrictions[$roomTypeId][$tariffId][$day->format('d.m.Y')];
                            $allocations[] = [
                                'RoomId' => $roomType['syncId'],
                                'StartDate' => $day->format('Y-m-d'),
                                'EndDate' => $day->format('Y-m-d'),
                                'MinStay' => (int)$info->getMinStay() < 1 ? 1 : (int)$info->getMinStay(),
                                'MaxStay' => (int)$info->getMaxStay(),
                                'Closed' => $info->getClosed(),
                                'ClosedForArrival' => $info->getClosedOnArrival(),
                                'ClosedForDeparture' => $info->getClosedOnDeparture(),
                            ];
                        } else {
                            $allocations[] = [
                                'RoomId' => $roomType['syncId'],
                                'StartDate' => $day->format('Y-m-d'),
                                'EndDate' => $day->format('Y-m-d'),
                                'MinStay' => 1,
                                'MaxStay' => 0,
                                'Closed' => false,
                                'ClosedForArrival' => false,
                                'ClosedForArrival' => false,
                            ];
                        }
                    }
                }
            }

            $api->setParams([
                'Channels' => ['all'], 'Allocations' => $allocations
            ]);

            $response = $this->call($api);

            if ($result && empty($response['response']['body']['Success'])) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function createPackages()
    {
        return $this->pullOrders();
    }

    /**
     * {@inheritDoc}
     */
    public function pullOrders()
    {
        $result = true;

        foreach ($this->getConfig() as $config) {
            $api = new BookingList();
            $api->setAuth($this->getAuth($config));
            $api->setParams([
                'ModificationStartDate' => $this->today->format('Y-m-d'),
                'ModificationEndDate' => $this->tomorrow->format('Y-m-d')
            ]);
            $response = $response = $this->call($api);

            if (
                $result &&
                empty($response['response']['body']['Success']) ||
                !isset($response['response']['body']['Bookings'])
            ) {
                $result = false;
            }

            //TODO: Remove logs
            //$this->log('Reservations count: ' . count($response['response']['body']['Bookings']));
            $this->log('Reservations: ' . json_encode($response));

            foreach ($response['response']['body']['Bookings'] as $orderInfo) {

                $creationDateTime = $orderInfo['MyallocatorCreationDate'] . ' ' . $orderInfo['MyallocatorCreationTime'];
                $editDateTime = $orderInfo['MyallocatorModificationDate'] . ' ' . $orderInfo['MyallocatorModificationTime'];
                $status = 'new';

                if ($creationDateTime != $editDateTime) {
                    $status = 'edit';
                }
                if (!empty($orderInfo['IsCancellation'])) {
                    $status = 'delete';
                }
                if ($status == 'edit') {
                    if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                        $this->dm->getFilterCollection()->disable('softdeleteable');
                    }
                }

                //old order
                $order = $this->dm->getRepository('MBHPackageBundle:Order')->findOneBy(
                    [
                        'channelManagerId' => $orderInfo['MyallocatorId'],
                        'channelManagerType' => 'myallocator'
                    ]
                );
                if ($status == 'edit') {
                    if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                        $this->dm->getFilterCollection()->enable('softdeleteable');
                    }
                }

                //new
                if ($status == 'new' && !$order) {
                    $result = $this->createPackage($orderInfo, $config, $order);
                    $this->notify($result, 'myallocator', 'new');
                }

                //edit
                if ($status == 'edit' && $order && $order->getChannelManagerEditDateTime() != $editDateTime) {
                    $result = $this->createPackage($orderInfo, $config, $order);
                    $this->notify($result, 'myallocator', 'edit');
                }
                //delete
                if ($status == 'delete' && $order) {

                    empty($orderInfo['CancellationReason']) ? $reason = '': $reason = $orderInfo['CancellationReason'];
                    $reason = "\n" . 'cancellation reason' . $reason . "\n";

                    $order->setChannelManagerStatus('cancelled')
                        ->setNote($order->getNote() . $reason)
                    ;
                    $this->dm->persist($order);
                    $this->dm->flush();
                    $this->notify($order, 'myallocator', 'delete');
                    $this->dm->remove($order);
                    $this->dm->flush();
                    $result = true;

                };

                if ($status == 'edit' && !$order) {
                    $this->notifyError(
                        'myallocator',
                        '#' . $orderInfo['MyallocatorId']
                    );
                }
            }
        }

        return $result;
    }

    public function createPackage(array $booking, ChannelManagerConfigInterface $config, Order $order = null)
    {
        $helper = $this->container->get('mbh.helper');
        $roomTypes = $this->getRoomTypes($config, true);
        $tariffs = $this->getTariffs($config, true);
        $guests = [];
        $guestNote = '';
        //tourist
        if (!empty($booking['Customers'])) {
            foreach ($booking['Customers'] as $customer) {

                $phone = null;

                if (!empty($customer['CustomerPhone'])) {
                    $phone = $customer['CustomerPhone'];
                }
                if (!empty($customer['CustomerPhoneMobile'])) {
                    $phone = $customer['CustomerPhoneMobile'];
                }
                if (!empty($customer['CustomerCompany'])) {
                    $guestNote .= 'company: ' . $customer['CustomerCompany'] . ";\n";
                }
                if (!empty($customer['CustomerCompanyDepartment'])) {
                    $guestNote .= 'company department: ' . $customer['CustomerCompanyDepartment'] . ";\n";
                }
                if (!empty($customer['CustomerState'])) {
                    $guestNote .= 'state: ' . $customer['CustomerState'] . ";\n";
                }
                if (!empty($customer['CustomerPostCode'])) {
                    $guestNote .= 'post code: ' . $customer['CustomerPostCode'] . ";\n";
                }
                if (!empty($customer['CustomerCountry'])) {
                    $guestNote .= 'country: ' . $customer['CustomerCountry'] . ";\n";
                }
                if (!empty($customer['CustomerNationality'])) {
                    $guestNote .= 'nationality: ' . $customer['CustomerNationality'] . ";\n";
                }
                if (!empty($customer['CustomerArrivalTime'])) {
                    $guestNote .= 'arrival time: ' . $customer['CustomerArrivalTime'] . ";\n";
                }
                if (!empty($customer['CustomerNote'])) {
                    $guestNote .= 'note: ' . $customer['CustomerNote'] . ";\n";
                }

                $guest = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
                    $customer['CustomerLName'],
                    empty($customer['CustomerFName']) ? 'н/д' : $customer['CustomerFName'],
                    null,
                    null,
                    empty($customer['CustomerEmail']) ? null : $customer['CustomerEmail'],
                    $phone,
                    empty($customer['CustomerAddress']) ? null : $customer['CustomerAddress'],
                    $guestNote

                );

                $guests[] = $guest;
            }
        }

        //order
        if (!$order) {
            $order = new Order();
            $order->setChannelManagerStatus('new')
                ->setChannelManagerHumanId($booking['OrderId'])
                ->setChannelManagerHumanText($booking['Channel'])
                ->setChannelManagerId($booking['MyallocatorId'])
                ->setChannelManagerType('myallocator');
        } else {
            foreach ($order->getPackages() as $package) {
                $this->dm->remove($package);
                $this->dm->flush();
            }
            foreach ($order->getFee() as $cashDoc) {
                $this->dm->remove($cashDoc);
                $this->dm->flush();
            }
            $order->setChannelManagerStatus('modified');
            $order->setChannelManagerEditDateTime(
                $booking['MyallocatorModificationDate'] . ' ' . $booking['MyallocatorModificationTime']
            );
            $order->setDeletedAt(null);
        }
        $orderNote = '';

        if (!empty($booking['OrderSource'])) {
            $orderNote .= 'source: ' . $booking['OrderSource'] . ";\n";
        }
        if (!empty($booking['OrderDate'])) {
            $orderNote .= 'date: ' . $booking['OrderDate'] . ";\n";
        }
        if (!empty($booking['OrderTime'])) {
            $orderNote .= 'time: ' . $booking['OrderTime'] . ";\n";
        }
        if (!empty($booking['Deposit'])) {
            $orderNote .= 'deposit: ' . $booking['Deposit'] . ";\n";
        }
        if (!empty($booking['DepositCurrency'])) {
            $orderNote .= 'deposit currency: ' . $booking['DepositCurrency'] . ";\n";
        }
        if (!empty($booking['TotalPrice'])) {
            $orderNote .= 'total price: ' . $booking['TotalPrice'] . ";\n";
        }
        if (!empty($booking['TotalCurrency'])) {
            $orderNote .= 'total currency: ' . $booking['TotalCurrency'] . ";\n";
        }

        $order
            ->setMainTourist(empty($guests[0]) ? null : $guests[0])
            ->setConfirmed(false)
            ->setStatus('channel_manager')
            ->setNote($orderNote . $guestNote);


        $this->dm->persist($order);
        $this->dm->flush();

        //fee
        if (!empty($booking['Commission'])) {

            $fee = new CashDocument();
            $fee->setIsConfirmed(false)
                ->setIsPaid(false)
                ->setMethod('electronic')
                ->setOperation('fee')
                ->setOrder($order)
                ->setTouristPayer($order->getMainTourist())
                ->setTotal($this->currencyConvertToRub($config, (float)$booking['Commission']));
            $this->dm->persist($fee);
            $this->dm->flush();
        }

        //packages
        $orderTotal = 0;
        foreach ($booking['Rooms'] as $room) {
            $corrupted = false;
            $errorMessage = '';
            $orderTotal += (float)$room['Price'];

            //roomType
            if (isset($roomTypes[$room['RoomTypeIds'][0]])) {
                $roomType = $roomTypes[$room['RoomTypeIds'][0]]['doc'];
            } else {
                $roomType = $this->dm->getRepository('MBHHotelBundle:RoomType')->findOneBy(
                    [
                        'hotel.id' => $config->getHotel()->getId(),
                        'isEnabled' => true,
                        'deletedAt' => null
                    ]
                );
                $corrupted = true;
                $errorMessage = 'ERROR: invalid roomType #' . $room['RoomTypeIds'][0] . '. ';

                if (!$roomType) {
                    continue;
                }
            }

            //prices
            foreach ($room['DayRates'] as $day) {

                $date = $helper->getDateFromString($day['Date'], 'Y-m-d');
                $packagePrices[] = new PackagePrice(
                    $date, $this->currencyConvertToRub($config, (float)$day['Rate']), $tariffs['base']['doc']
                );
            }

            $packageNote = '';

            if (!empty($room['RateId'])) {
                $packageNote .= 'rate id: ' . $room['RateId'] . ";\n";
            }
            if (!empty($room['RateDesc'])) {
                $packageNote .= 'rate description: ' . $room['RateDesc'] . ";\n";
            }
            if (!empty($room['RoomDesc'])) {
                $packageNote .= 'room description: ' . $room['RoomDesc'] . ";\n";
            }
            if (!empty($room['ChannelRoomType'])) {
                $packageNote .= 'channel room type: ' . $room['ChannelRoomType'] . ";\n";
            }
            if (!empty($room['OccupantNote'])) {
                $packageNote .= 'note: ' . $room['OccupantNote'] . ";\n";
            }
            if (!empty($errorMessage)) {
                $packageNote .= 'ERROR: ' . $errorMessage . ";\n";
            }

            $packageTotal = $this->currencyConvertToRub($config, (float)$room['Price']);

            $package = new Package();
            $startDate = $helper->getDateFromString($room['StartDate'], 'Y-m-d');
            $endDate = $helper->getDateFromString($room['EndDate'], 'Y-m-d');
            $endDate->modify('+1 day');
            $package
                ->setChannelManagerId($booking['MyallocatorId'])
                ->setChannelManagerType('myallocator')
                ->setBegin($startDate)
                ->setEnd($endDate)
                ->setRoomType($roomType)
                ->setTariff($tariffs['base']['doc'])
                ->setAdults(isset($room['Occupancy']) ? (int)$room['Occupancy']: 1)
                ->setChildren(0)
                ->setIsSmoking(!empty($room['OccupantSmoker']) ? true : false)
                ->setPrices($packagePrices)
                ->setPrice($packageTotal)
                ->setOriginalPrice($packageTotal)
                ->setTotalOverwrite($packageTotal)
                ->setNote($packageNote)
                ->setOrder($order)
                ->setCorrupted($corrupted)
            ;

            $packageGuest = false;
            if (isset($room['OccupantFName'])) {
                $pGuestFirstName = empty($room['OccupantFName']) ? 'н/д' : $room['OccupantFName'];
                $pGuestLastName = $room['OccupantLName'];
                foreach ($guests as $guest) {
                    if ($guest->getFirstName() == $pGuestFirstName && $guest->getLastName() == $pGuestLastName) {
                        $packageGuest = $guest;
                    }
                }
                if (!$packageGuest) {
                    $packageGuest = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
                        $pGuestLastName, $pGuestFirstName
                    );
                }
                $package->addTourist($packageGuest);
            }

            $order->addPackage($package);
            $this->dm->persist($package);
            $this->dm->persist($order);
            $this->dm->flush();
        }

        $orderPrice = $this->currencyConvertToRub($config, $orderTotal);

        $order
            ->setPrice($orderPrice)
            ->setOriginalPrice(
                empty($booking['TotalPrice']) ? $orderPrice : $this->currencyConvertToRub($config, (float)$booking['TotalPrice'])
            )
            ->setTotalOverwrite($orderPrice);
        $this->dm->persist($order);
        $this->dm->flush();

        return $order;
    }

    /**
     * @param ChannelManagerConfigInterface $config
     */
    public function syncServices(ChannelManagerConfigInterface $config)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function pushResponse(Request $request)
    {
        $this->log($request->getContent());

        return new Response('OK');
    }
}
