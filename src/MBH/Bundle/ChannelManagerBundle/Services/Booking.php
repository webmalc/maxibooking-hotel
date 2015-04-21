<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractChannelManagerService as Base;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;

/**
 *  ChannelManager service
 */
class Booking extends Base
{

    /**
     * Config class
     */
    const CONFIG = 'BookingConfig';

    /**
     * Base API URL
     */
    const BASE_URL = 'https://supply-xml.booking.com/hotels/xml/';

    /**
     * Base secure API URL
     */
    const BASE_SECURE_URL = 'https://secure-supply-xml.booking.com/hotels/xml/';
    
    public $servicesConfig = [
        1 => 'Breakfast',
        2 => 'Continental breakfast',
        3 => 'American breakfast',
        4 => 'Buffet breakfast',
        5 => 'Full english breakfast',
        6 => 'Lunch',
        7 => 'Dinner',
        8 => 'Half board',
        9 => 'Full board',
        11 => 'Breakfast for Children',
        12 => 'Continental breakfast for Children',
        13 => 'American breakfast for Children',
        14 => 'Buffet breakfast for Children',
        15 => 'Full english breakfast for Children',
        16 => 'Lunch for Children',
        17 => 'Dinner for Children',
        18 => 'Half board for Children',
        19 => 'Full board for Children',
        20 => 'WiFi',
        21 => 'Internet',
        22 => 'Parking space',
        23 => 'Extrabed',
        24 => 'Babycot'
    ];

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    /**
     * {@inheritDoc}
     */
    public function pullOrders()
    {
        $result = true;

        foreach ($this->getConfig() as $config) {
            $request = $this->templating->render('MBHChannelManagerBundle:Booking:reservations.xml.twig', ['config' => $config]);
            $sendResult = $this->send(static::BASE_SECURE_URL . 'reservations', $request, null, true);

            foreach ($sendResult->reservation as $reservation) {

                //old order
                $order = $this->dm->getRepository('MBHPackageBundle:Order')->findOneBy([
                    'channelManagerId' => (string)$reservation->id, 'channelManagerType' => 'booking'
                ]);

                //new
                if ((string)$reservation->status == 'new' && !$order) {
                    $result = $this->createPackage($reservation, $config, $order);
                }
                //edit
                if ((string)$reservation->status == 'modified' && $order) {
                    $result = $this->createPackage($reservation, $config, $order);
                }
                //delete
                if((string)$reservation->status == 'cancelled' && $order) {
                    $this->dm->remove($order);
                    $this->dm->flush();
                    $result = true;
                };
            };
        }
        return $result;
    }

    /**
     * @param \SimpleXMLElement $reservation
     * @param ChannelManagerConfigInterface $config
     * @param Order $order
     * @return Order
     */
    private function createPackage(\SimpleXMLElement $reservation, ChannelManagerConfigInterface $config, Order $order = null)
    {
        $helper = $this->container->get('mbh.helper');
        $roomTypes = $this->getRoomTypes($config, true);
        $tariffs = $this->getTariffs($config, true);
        $services = $this->getServices($config);

        //tourist
        //TODO: company, country, zip
        $customer = $reservation->customer;
        $payer = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
            (string)$customer->last_name, (string)$customer->first_name, null, null,
            empty((string) $customer->email) ? null : (string) $customer->email,
            empty((string) $customer->telephone) ? null : (string) $customer->telephone,
            empty((string) $customer->address) ? null : (string) $customer->address,
            empty((string) $customer->remarks) ? null : (string) $customer->remarks
        );
        //order
        if (!$order) {
            $order = new Order();
        } else {
            foreach($order->getPackages() as $package) {
                $this->dm->remove($package);
                $this->dm->flush();
            }
        }
        $order->setChannelManagerType('booking')
            ->setChannelManagerId((string)$reservation->id)
            ->setChannelManagerHumanId(empty((string) $customer->loyalty_id) ? null : (string) $customer->loyalty_id)
            ->setMainTourist($payer)
            ->setCard('cc_cvc: ' . $customer->cc_cvc . '; cc_expiration_date: ' . $customer->cc_expiration_date . '; cc_name: ' . $customer->cc_name . '; cc_number: ' . $customer->cc_number . '; cc_type: ' . $customer->cc_type)
            ->setConfirmed(false)
            ->setStatus('channel_manager')
            ->setPrice((float)$reservation->totalprice)
            ->setTotalOverwrite((float)$reservation->totalprice)
            ->setIsPaid(false)
        ;

        $this->dm->persist($order);
        $this->dm->flush();

        //packages
        foreach ($reservation->room as $room) {
            //roomType
            if (!isset($roomTypes[(string) $room->id])) {
                continue;
            }
            $roomType = $roomTypes[(string) $room->id]['doc'];

            //guests
            if ($payer->getFirstName() . ' ' . $payer->getLastName() == (string) $room->guest_name) {
                $guest = $payer;
            } else {
                $guestArray = explode(' ', (string) $room->guest_name ) ;
                $guest = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
                    $guestArray[1], $guestArray[0]
                );
            }

            //prices
            $total = 0;
            $tariff = null;
            $pricesByDate = [];
            foreach ($room->price as $price) {
                if (!$tariff && isset($tariffs[(string) $price['rate_id']])) {
                    $tariff = $tariffs[(string) $price['rate_id']]['doc'];
                }
                $total += (float) $price;
                $date = $helper->getDateFromString((string) (string) $price['date'], 'Y-m-d');
                $pricesByDate[$date->format('d_m_Y')] = (float) $price;
            }
            if (!$tariff) {
                continue;
            }

            $package = new Package();
            $package
                ->setChannelManagerId((string)$room->roomreservation_id )
                ->setChannelManagerType('booking')
                ->setBegin($helper->getDateFromString((string) $room->arrival_date, 'Y-m-d'))
                ->setEnd($helper->getDateFromString((string) $room->departure_date, 'Y-m-d'))
                ->setRoomType($roomType)
                ->setTariff($tariff)
                ->setAdults((int) $room->numberofguests)
                ->setChildren(0)
                ->setIsSmoking((boolean) $room->smoking)
                ->setPricesByDate($pricesByDate)
                ->setPrice((float) $total)
                ->setNote('remarks: ' . $room->remarks . '; extra_info: ' . $room->extra_info . '; facilities: ' . $room->facilities)
                ->setOrder($order)
                ->addTourist($guest)
            ;

            //services
            $servicesTotal = 0;
            foreach ($room->addons->addon as $addon) {
                $servicesTotal += (float) $addon->totalprice;
                if (!$services[(int) $addon->type]) {
                    continue;
                }

                $packageService = new PackageService();
                $packageService
                    ->setService($services[(int) $addon->type]['doc'])
                    ->setIsCustomPrice(true)
                    ->setNights(empty((string) $addon->nights) ? null : (int) $addon->nights)
                    ->setPersons(empty((string) $addon->persons) ? null : (int) $addon->persons)
                    ->setPrice(empty((string) $addon->price_per_unit) ? null : (float) $addon->price_per_unit)
                    ->setTotalOverwrite((float) $addon->totalprice)
                    ->setPackage($package);
                ;
                $this->dm->persist($packageService);
                $package->addService($packageService);
            }
            $package->setServicesPrice($servicesTotal);
            $package->setTotalOverwrite((float)$room->totalprice);

            $order->addPackage($package);
            $this->dm->persist($package);
            $this->dm->persist($order);
            $this->dm->flush();
        }
        $order->setTotalOverwrite((float)$reservation->totalprice);
        $this->dm->persist($order);
        $this->dm->flush();

        return $order;
    }

    /**
     * {@inheritDoc}
     */
    public function closeAll()
    {
        $result = false;
        
        foreach ($this->getConfig() as $config) {
            $request = $this->templating->render('MBHChannelManagerBundle:Booking:close.xml.twig', [
                'config' => $config, 'rooms' => $this->getRoomTypes($config), 'rates' => $this->getTariffs($config)]
            );
            $sendResult = $this->send(static::BASE_URL . 'availability', $request, null, true);
            $result = $this->checkResponse($sendResult);
        }
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function updateRooms(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = false;

        // iterate hotels
        foreach ($this->getConfig() as $config) {
            $roomTypes = $this->getRoomTypes($config);

            //roomCache
            $roomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')->fetch(
                $begin, $end, $config->getHotel(), $roomType ? [$roomType->getId()] : [], null
            );
            if(!$roomCaches->count()) {
                continue;
            }
            //group caches
            foreach ($roomCaches as $roomCache) {
                $roomType = $roomCache->getRoomType();
                isset($roomTypes[$roomType->getId()]) ? $roomTypeSyncId = $roomTypes[$roomType->getId()]['syncId'] : $roomTypeSyncId = null;
                $formattedDate = $roomCache->getDate()->format('Y-m-d');

                if ($roomTypeSyncId) {
                    $data[$roomTypeSyncId][$formattedDate] = [
                        'roomstosell' => $roomCache->getLeftRooms(),
                    ];
                }
            }
            $request = $this->templating->render('MBHChannelManagerBundle:Booking:updateRooms.xml.twig', ['config' => $config, 'data' => $data]);

            /*header("Content-type: text/xml; charset=utf-8");
            echo($request); exit();*/

            $sendResult = $this->send(static::BASE_URL . 'availability', $request, null, true);

            $result = $this->checkResponse($sendResult);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function updatePrices(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = false;

        // iterate hotels
        foreach ($this->getConfig() as $config) {
            $roomTypes = $this->getRoomTypes($config);
            $tariffs = $this->getTariffs($config);

            //priceCache with tariffs
            $priceCaches = $this->dm->getRepository('MBHPriceBundle:PriceCache')->fetch(
                $begin, $end, $config->getHotel(), $roomType ? [$roomType->getId()] : []
            );
            if(!$priceCaches->count()) {
                continue;
            }
            //group caches
            foreach ($priceCaches as $priceCache) {
                $roomType = $priceCache->getRoomType();
                $tariff = $priceCache->getTariff();
                isset($roomTypes[$roomType->getId()]) ? $roomTypeSyncId = $roomTypes[$roomType->getId()]['syncId'] : $roomTypeSyncId = null;
                isset($tariffs[$tariff->getId()]) ? $tariffSyncId = $tariffs[$tariff->getId()]['syncId'] : $tariffSyncId = null;
                $formattedDate = $priceCache->getDate()->format('Y-m-d');

                if ($roomTypeSyncId && $tariffSyncId) {
                    $data[$roomTypeSyncId][$formattedDate][$tariffSyncId] = [
                        'price' => $priceCache->getPrice(),
                        'price1' => $priceCache->getSinglePrice() ? $priceCache->getSinglePrice() : null
                    ];
                }
            }
            $request = $this->templating->render('MBHChannelManagerBundle:Booking:updatePrices.xml.twig', ['config' => $config, 'data' => $data]);

            /*header("Content-type: text/xml; charset=utf-8");
            echo($request); exit();*/

            $sendResult = $this->send(static::BASE_URL . 'availability', $request, null, true);

            $result = $this->checkResponse($sendResult);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function updateRestrictions(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = false;

        // iterate hotels
        foreach ($this->getConfig() as $config) {
            $roomTypes = $this->getRoomTypes($config);
            $tariffs = $this->getTariffs($config);

            //restrictions
            $restrictions = $this->dm->getRepository('MBHPriceBundle:Restriction')->fetch(
                $begin, $end, $config->getHotel(), $roomType ? [$roomType->getId()] : []
            );
            if(!$restrictions->count()) {
                continue;
            }
            //group caches
            foreach ($restrictions as $restriction) {
                $roomType = $restriction->getRoomType();
                $tariff = $restriction->getTariff();
                isset($roomTypes[$roomType->getId()]) ? $roomTypeSyncId = $roomTypes[$roomType->getId()]['syncId'] : $roomTypeSyncId = null;
                isset($tariffs[$tariff->getId()]) ? $tariffSyncId = $tariffs[$tariff->getId()]['syncId'] : $tariffSyncId = null;
                $formattedDate = $restriction->getDate()->format('Y-m-d');

                if ($roomTypeSyncId && $tariffSyncId) {
                    $data[$roomTypeSyncId][$formattedDate][$tariffSyncId] = [
                        'minimumstay_arrival' => $restriction->getMinStayArrival(),
                        'maximumstay_arrival' => $restriction->getMaxStayArrival(),
                        'minimumstay' => $restriction->getMinStay(),
                        'maximumstay' => $restriction->getMinStay(),
                        'closedonarrival' => $restriction->getClosedOnArrival() ? 1 : 0,
                        'closedondeparture' => $restriction->getClosedOnDeparture() ? 1 : 0,
                    ];
                }
            }
            $request = $this->templating->render('MBHChannelManagerBundle:Booking:updateRestrictions.xml.twig', ['config' => $config, 'data' => $data]);

            /*header("Content-type: text/xml; charset=utf-8");
            echo($request); exit();*/

            $sendResult = $this->send(static::BASE_URL . 'availability', $request, null, true);

            $result = $this->checkResponse($sendResult);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function update (\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $this->updateRooms($begin, $end, $roomType);
        $this->updatePrices($begin, $end, $roomType);
        $this->updateRestrictions($begin, $end, $roomType);

        return true;
    }
    
    /**
     * {@inheritDoc}
     */
    public function checkResponse($response, array $params = null)
    {
        if (!$response) {
            return false;
        }
        $xml = simplexml_load_string($response);

        return count($xml->xpath('/ok')) ? true : false;;
    }

    /**
     * {@inheritDoc}
     */
    public function createPackages()
    {

    }

    /**
     * {@inheritDoc}
     */
    public function sync()
    {
        $configs = $this->getConfig();

        if (empty($configs)) {
            throw new \Exception('Config not found');
        }
        foreach ($configs as $config) {

            $request = $this->templating->render('MBHChannelManagerBundle:Booking:get.xml.twig', ['config' => $config]);
            $hotel = $config->getHotel();

            // rooms
            $response = $this->sendXml(static::BASE_URL . 'rooms', $request);
            $config->removeAllRooms();
            foreach ($response->xpath('room') as $room) {
                foreach($hotel->getRoomTypes() as $roomType) {
                    if ($roomType->getFullTitle() == (string)$room ) {
                        $configRoom = new Room();
                        $configRoom->setRoomType($roomType)->setRoomId((string)$room['id']);
                        $config->addRoom($configRoom);
                        $this->dm->persist($config);
                    }
                }
            }
            $this->dm->flush();

            //tariffs
            $response = $this->sendXml(static::BASE_URL . 'rates', $request);
            $config->removeAllTariffs();
            
            foreach ($response->xpath('rate') as $rate) {
                
                foreach($hotel->getTariffs() as $tariff) {
                    
                    if ($tariff->getFullTitle() == (string)$rate ) {
                        $configTariff = new Tariff();
                        $configTariff->setTariff($tariff)->setTariffId((string)$rate['id']);
                        $config->addTariff($configTariff);
                        $this->dm->persist($config);
                    }
                }
            }
            
            //services
            $config->removeAllServices();
            foreach ($this->servicesConfig as $serviceKey => $serviceName) {
                $serviceDoc = $this->dm->getRepository('MBHPriceBundle:Service')->findOneBy([
                    'code' => $serviceName
                ]);
                
                if(empty($serviceDoc) || $serviceDoc->getCategory()->getHotel()->getId() != $config->getHotel()->getId()) {
                    continue;
                }
                
                $service = new \MBH\Bundle\ChannelManagerBundle\Document\Service();
                $service->setServiceId($serviceKey)->setService($serviceDoc);
                $config->addService($service);
                $this->dm->persist($config);
            }
            
            $this->dm->flush();

        }
        return $config;
    }

}
