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

            //Test data from BOOKING.COM
            /*$sendResult = simplexml_load_string('<?xml version="1.0"?> <reservations>   <reservation>     <commissionamount>16.20</commissionamount>     <currencycode>EUR</currencycode>     <customer>       <address>asdasd</address>       <cc_cvc>123</cc_cvc>       <cc_expiration_date>10/2015</cc_expiration_date>       <cc_name>werwer erwwererw</cc_name>       <cc_number>5346330641608164</cc_number>       <cc_type>MasterCard</cc_type>       <city>asdasd</city>       <company/>       <countrycode>ru</countrycode>       <dc_issue_number/>       <dc_start_date/>       <email>677651674-qsbf.ntc7.66c6.6mcb@guest.booking.com</email>       <first_name>werwer</first_name>       <last_name>erwwererw</last_name>       <remarks/>       <telephone>213123321</telephone>       <zip>asdasd</zip>     </customer>     <date>2015-06-07</date>     <hotel_id>1189796</hotel_id>     <hotel_name>Potential Provider MaxiBooking</hotel_name>     <id>677651674</id>     <reservation_extra_info booker_is_genius="no" booking_managed_payment="no" no_address_reservation="no"/>     <room>       <addons>         <addon>           <name>Интернет</name>           <nights>1</nights>           <persons>1</persons>           <price_mode>3</price_mode>           <price_per_unit>15</price_per_unit>           <totalprice>15</totalprice>           <type>21</type>         </addon>         <addon>           <name>Парковка</name>           <nights>1</nights>           <persons>1</persons>           <price_mode>3</price_mode>           <price_per_unit>15</price_per_unit>           <totalprice>15</totalprice>           <type>22</type>         </addon>       </addons>       <arrival_date>2015-06-07</arrival_date>       <commissionamount>16.2</commissionamount>       <currencycode>EUR</currencycode>       <departure_date>2015-06-08</departure_date>       <extra_info>This double room features a minibar, air conditioning and seating area.</extra_info>       <facilities>Мини-бар, Телефон, Кондиционер, Фен, Утюг, Радио, Рабочий стол, Гладильные принадлежности, Гостиный уголок, Отопление, Ванна или душ, Ковровое покрытие, Телевизор с плоским экраном, Будильник, Шкаф/гардероб, Гипоаллергенный, Одеяла с электроподогревом, Кофемашина, Вид на город, Полотенца, Для доступа к верхним этажам работает лифт, Отдельно стоящее, Сушилка для одежды</facilities>       <guest_name>werwer erwwererw</guest_name>       <id>118979601</id>       <info>Питание не входит в цену данного номера.  Размещение детей и предоставление дополнительных кроватей: Разрешается проживание детей любого возраста. При размещении одного ребёнка младше 4 лет на имеющихся кроватях взимается EUR 20 с человека за ночь. При размещении одного ребёнка старшего возраста или взрослого на дополнительной кровати взимается EUR 50 с человека за ночь. Максимальное количество дополнительных кроватей/детских кроваток в номере -  1.  Предоплата: Предоплата не  взимается.  Порядок отмены бронирования: В случае отмены бронирования в срок до 1 суток до даты заезда  штраф не взимается. </info>       <max_children>0</max_children>       <meal_plan>Питание не входит в цену данного номера. </meal_plan>       <name>Стандартный двухместный номер с 1 кроватью или 2 отдельными кроватями - Single Use</name>       <numberofguests>1</numberofguests>       <price date="2015-06-07" genius_rate="no" rate_id="4326886">105</price>       <price_details>         <guest>           <extracomponent amount="17.500000" currency="EUR" included="yes" per_night="no" per_person="no" percentage="20%" text="НДС"/>           <total>135.00</total>         </guest>         <hotel>           <extracomponent amount="17.500000" currency="EUR" included="yes" per_night="no" per_person="no" percentage="20%" text="НДС"/>           <total>135</total>         </hotel>       </price_details>       <remarks/>       <roomreservation_id>679520841</roomreservation_id>       <smoking>1</smoking>       <totalprice>135</totalprice>     </room>     <status>new</status>     <time>15:15:45</time>     <totalprice>135</totalprice>   </reservation> </reservations> <!-- RUID: [UmFuZG9tSVYkc2RlIyh9YQ957bIQASwa5ZteJp7/pO1T7RsFANQ+WJJHU6OpV90TNHBM2vk4Jyt9ED/yo/MVjg==] -->');*/

            $request = $this->templating->render('MBHChannelManagerBundle:Booking:reservations.xml.twig', ['config' => $config, 'lastChange' => false]);
            $sendResult = $this->sendXml(static::BASE_SECURE_URL . 'reservations', $request, null, true);
            $this->log($sendResult->asXML());

            foreach ($sendResult->reservation as $reservation) {

                if ((string)$reservation->status == 'modified') {
                    if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                        $this->dm->getFilterCollection()->disable('softdeleteable');
                    }
                }
                //old order
                $order = $this->dm->getRepository('MBHPackageBundle:Order')->findOneBy([
                    'channelManagerId' => (string)$reservation->id, 'channelManagerType' => 'booking'
                ]);
                if ((string)$reservation->status == 'modified') {
                    if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                        $this->dm->getFilterCollection()->enable('softdeleteable');
                    }
                }

                //new
                if ((string)$reservation->status == 'new' && !$order) {
                    $result = $this->createPackage($reservation, $config, $order);
                    $this->notify($result, 'booking', 'new');
                }
                //edit
                if ((string)$reservation->status == 'modified' && $order) {
                    $result = $this->createPackage($reservation, $config, $order);
                    $this->notify($result, 'booking', 'edit');
                }
                //delete
                if((string)$reservation->status == 'cancelled' && $order) {
                    $order->setChannelManagerStatus('cancelled');
                    $this->dm->persist($order);
                    $this->dm->flush();
                    $this->notify($order, 'booking', 'delete');
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
        $customer = $reservation->customer;

        $payerNote = 'country=' . (string) $customer->countrycode;
        $payerNote .= '; city=' . (string) $customer->city;
        $payerNote .= '; zip=' . (string) $customer->zip;
        $payerNote .= '; company=' . (string) $customer->company;
        $payer = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
            (string)$customer->last_name, (string)$customer->first_name, null, null,
            empty((string) $customer->email) ? null : (string) $customer->email,
            empty((string) $customer->telephone) ? null : (string) $customer->telephone,
            empty((string) $customer->address) ? null : (string) $customer->address,
            empty($payerNote) ? null : $payerNote
        );
        //order
        if (!$order) {
            $order = new Order();
            $order->setChannelManagerStatus('new');
        } else {
            foreach($order->getPackages() as $package) {
                $this->dm->remove($package);
                $this->dm->flush();
            }
            $order->setChannelManagerStatus('modified');
            $order->setDeletedAt(null);
        }
        $order->setChannelManagerType('booking')
            ->setChannelManagerId((string)$reservation->id)
            ->setChannelManagerHumanId(empty((string) $customer->loyalty_id) ? null : (string) $customer->loyalty_id)
            ->setMainTourist($payer)
            ->setConfirmed(false)
            ->setStatus('channel_manager')
            ->setPrice((float)$reservation->totalprice)
            ->setTotalOverwrite((float)$reservation->totalprice)
            ->setNote('remarks=' . (string) $customer->remarks)
        ;

        if (!empty((string)$customer->cc_number)) {
            $order->setCard('cc_cvc: ' . $customer->cc_cvc . '; cc_expiration_date: ' . $customer->cc_expiration_date . '; cc_name: ' . $customer->cc_name . '; cc_number: ' . $customer->cc_number . '; cc_type: ' . $customer->cc_type);
        }

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
                $guest = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate('н/д', (string) $room->guest_name);
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

            $packageNote = 'remarks: ' . $room->remarks . '; extra_info: ' . $room->extra_info . '; facilities: ' . $room->facilities . '; max_children: ' . $room->max_children;
            $packageNote . '; commissionamount=' . $room->commissionamount . '; currencycode = ' . $room->currencycode;

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
                ->setIsSmoking((int) $room->smoking ? true : false)
                ->setPricesByDate($pricesByDate)
                ->setPrice((float) $total)
                ->setNote($packageNote)
                ->setOrder($order)
                ->addTourist($guest)
            ;

            //services
            $servicesTotal = 0;

            if ($room->addons->addon) {
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
                        'closed' => $roomCache->getIsClosed()
                    ];
                }
            }
            $request = $this->templating->render('MBHChannelManagerBundle:Booking:updateRooms.xml.twig', ['config' => $config, 'data' => $data]);

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
                        'maximumstay' => $restriction->getMaxStay(),
                        'closedonarrival' => $restriction->getClosedOnArrival() ? 1 : 0,
                        'closedondeparture' => $restriction->getClosedOnDeparture() ? 1 : 0,
                        'closed' => $restriction->getClosed() ? 1 : 0,
                    ];
                }
            }
            $request = $this->templating->render('MBHChannelManagerBundle:Booking:updateRestrictions.xml.twig', ['config' => $config, 'data' => $data]);

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
        return $this->pullOrders();
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
