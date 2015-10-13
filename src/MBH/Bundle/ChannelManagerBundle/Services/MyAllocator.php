<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\ChannelManagerBundle\Document\MyallocatorConfig;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\HttpFoundation\Request;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractChannelManagerService as Base;
use MyAllocator\phpsdk\src\Api\VendorSet;
use MyAllocator\phpsdk\src\Api\AssociateUserToPMS;
use MyAllocator\phpsdk\src\Object\Auth;
use MyAllocator\phpsdk\src\Api\MaApi;
use MyAllocator\phpsdk\src\Api\PropertyList;
use MyAllocator\phpsdk\src\Api\RoomList;
use MyAllocator\phpsdk\src\Api\ARIUpdate;


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

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->params = $container->getParameter('mbh.channelmanager.services')['myallocator'];
        $this->today = new \DateTime('midnight');
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

        if (!empty($response['response']['body']['Success'])) {
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
                dump($e);
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
                foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end) as $day) {
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

    }

    /**
     * {@inheritDoc}
     */
    public function updateRestrictions(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {

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

    }


    /**
     * @param ChannelManagerConfigInterface $config
     */
    public function syncServices(ChannelManagerConfigInterface $config){}

    /**
     * {@inheritDoc}
     */
    public function pushResponse(Request $request)
    {

    }
}
