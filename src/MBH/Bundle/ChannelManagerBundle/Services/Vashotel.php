<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ChannelManagerBundle\Document\Service;
use MBH\Bundle\PackageBundle\Document\CreditCard;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerServiceInterface;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PriceBundle\Document\ServiceCategory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractChannelManagerService as Base;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 *  Vashotel service
 */
class Vashotel extends Base implements ChannelManagerServiceInterface
{
    const UNAVAIBLE_PRICES = [
        'additionalChildrenPrice' => null,
    ];

    const UNAVAIBLE_RESTRICTIONS = [
        'minStay' => null,
        'maxStay' => null,
        'minStayArrival' => null,
        'maxStayArrival' => null,
        'maxGuest' => null,
        'minGuest' => null,
        'minBeforeArrival' => null,
        'maxBeforeArrival' => null,
        'closedOnArrival' => null,
        'closedOnDeparture' => null,
    ];

    /**
     * Config class
     */
    const CONFIG = 'VashotelConfig';

    /**
     * Config class
     */
    private function cancelConditions()
    {
        return [
            1 => $this->container->get('translator')->trans('package.services.first_day_live_in_booking_rooms'),
            2 => $this->container->get('translator')->trans('package.services.percent_of_price_liveing'),
            3 => $this->container->get('translator')->trans('package.services.fixed_price_for_all_booking_room')
        ];
    }

    const SERVICES = [
        'Завтрак "Шведский стол"' => 'Buffet breakfast',
        'Завтрак "Континентальный"' => 'Continental breakfast',
        'Обед' => 'Lunch',
        'Ужин' => 'Dinner',
        'Полупансион (завтрак + ужин)' => 'Half board',
        'Полный пансион (завтрак + обед + ужин)' => 'Full board',
        'Ранний заезд' => 'Early check-in',
        'Поздний выезд' => 'Late check-out',
    ];

    /**
     * {@inheritdoc}
     */
    const DEFAULT_PERIOD = 360;

    /**
     * Base API URL
     */
    const BASE_URL = 'https://www.vashotel.ru/hotel_xml/';

    /**
     * Get roomTypes & tariffs template file
     */
    const GET_TEMPLATE = 'MBHChannelManagerBundle:Vashotel:get.xml.twig';

    /**
     * Update rooms template
     */
    const UPDATE_ROOMS_TEMPLATE = 'MBHChannelManagerBundle:Vashotel:updateRooms.xml.twig';

    /**
     * Update prices template
     */
    const UPDATE_PRICES_TEMPLATE = 'MBHChannelManagerBundle:Vashotel:updatePrices.xml.twig';

    /**
     * Close all rooms
     */
    const CLOSE_TEMPLATE = 'MBHChannelManagerBundle:Vashotel:close.xml.twig';

    /**
     * Orders notifications
     */
    const NOTIFICATIONS_TEMPLATE = 'MBHChannelManagerBundle:Vashotel:notifications.xml.twig';

    /**
     * Orders info
     */
    const ORDERS_TEMPLATE = 'MBHChannelManagerBundle:Vashotel:orders.xml.twig';

    /**
     * Push response
     */
    const PUSH_TEMPLATE = 'MBHChannelManagerBundle:Vashotel:push.xml.twig';

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    /**
     * {@inheritdoc}
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
            $script = 'get_reservations_list.php';
            $salt = $this->helper->getRandomString(20);
            $data = ['config' => $config, 'salt' => $salt, 'sig' => null];

            $sig = $this->getSignature(
                $this->templating->render(static::NOTIFICATIONS_TEMPLATE, $data),
                $script,
                $config->getPassword()
            );
            $data['sig'] = md5($sig);

            $response = $this->sendXML(
                static::BASE_URL . $script,
                $this->templating->render(static::NOTIFICATIONS_TEMPLATE, $data)
            );
            $this->log($response->asXML());

            if (!$this->checkResponse($response, ['script' => $script, 'key' => $config->getPassword()])) {
                continue;
            }

            $modifyIds = $newIds = [];

            //getReservations
            foreach ($response->reservation_list->reservation as $reservation) {
                if ((string)$reservation['notification_type'] == 'cancel') {
                    $this->cancelOrder((string)$reservation['id']);
                }
                if (in_array((string)$reservation['notification_type'], ['modify'])) {
                    $modifyIds[] = (int)$reservation['id'];
                }
                if (in_array((string)$reservation['notification_type'], ['new', 'new_preliminary'])) {
                    $newIds[] = (int)$reservation['id'];
                }
            }

            if (!empty($newIds)) {
                $this->orders($newIds, $config, 'new');
            }
            if (!empty($modifyIds)) {
                $this->orders($modifyIds, $config, 'edit');
            }
        }

        return $result;
    }

    /**
     * @param string $xml
     * @param string $script
     * @param string $key
     * @param boolean $dev
     * @return string
     */
    private function getSignature($xml, $script = null, $key = null, $dev = false)
    {
        if (!$xml instanceof \SimpleXMLElement) {
            $xml = simplexml_load_string($xml);
        }

        $fields = $this->getXmlFieldsAsArray($xml);
        $fields = $this->sortXmlArray($fields);
        $string = $this->getStringFromXmlArray($fields, $dev);
        $string = trim($string, ';');

        if ($script) {
            $string = $script.';'.$string;
        }
        if ($key) {
            $string .= ';'.$key;
        }

        return $string;
    }

    /**
     * @param string $xml
     * @return array
     */
    private function getXmlFieldsAsArray($xml)
    {
        $fields = [];
        foreach ($xml->children() as $child) {
            if (in_array($child->getName(), ['sig', 'guest'])) {
                continue;
            }

            $count = 'a';

            foreach ($fields as $key => $field) {
                if (preg_match('/'.$child->getName().'_[a]*$/iu', $key)) {
                    $count .= 'a';
                }
            }

            if ($child->count()) {
                $fields[$child->getName().'_'.$count] = $this->getXmlFieldsAsArray($child);
            } else {
                $fields[$child->getName().'_'.$count] = (string)$child;
            }
        }

        return $fields;
    }

    /**
     * @param array $fields
     * @return array
     */
    private function sortXmlArray(array $fields)
    {
        ksort($fields, SORT_STRING);

        foreach ($fields as $key => $field) {
            if (is_array($field)) {
                $fields[$key] = $this->sortXmlArray($field);
            }
        }

        return $fields;
    }

    /**
     * @param array $fields
     * @param boolean $dev
     * @return string
     */
    private function getStringFromXmlArray(array $fields, $dev = false)
    {
        $string = '';

        foreach ($fields as $key => $field) {
            if (is_array($field)) {
                $string .= $this->getStringFromXmlArray($field, $dev);
            } else {
                $string .= ($dev) ? $key . '-' . $field . ';' : $field . ';';
            }
        }

        return $string;
    }

    /**
     * @param mixed $response
     * @param array $params
     * @return bool
     */
    public function checkResponse($response, array $params = null)
    {
        $script = $params['script'];
        $key = $params['key'];
        if (!$response) {
            return false;
        }
        if (!$response instanceof \SimpleXMLElement) {
            $response = simplexml_load_string($response);
        }

        if (!$this->checkResponseSignature($response, $script, $key)) {
            return false;
        }

        return ($response->xpath('/response/status')[0] == 'ok') ? true : false;
    }

    /**
     * @param string $xml
     * @param string $script
     * @param string $key
     * @return bool
     */
    private function checkResponseSignature($xml, $script, $key)
    {
        if (!$xml) {
            return false;
        }
        if (!$xml instanceof \SimpleXMLElement) {
            $xml = simplexml_load_string($xml);
        }

        if (isset($xml->xpath('status')[0]) && (string)$xml->xpath('status')[0] == 'error') {
            return false;
        };

        $responseSig = (string)$xml->xpath('sig')[0];
        $sig = $this->getSignature($xml, $script, $key);

        if (md5($sig) !== $responseSig) {
            return false;
        }

        return true;
    }

    /**
     * Remove order
     * @param $id
     * @return bool
     */
    private function cancelOrder($id)
    {
        $order = $this->dm->getRepository('MBHPackageBundle:Order')->findOneBy(
            [
                'channelManagerId' => (string) $id,
                'channelManagerType' => 'vashotel'
            ]
        );
        if (!$order) {
            $this->notifyError('vashotel', '#' . $id);
            return false;
        }
        $order->setChannelManagerStatus('cancelled');
        $this->dm->persist($order);
        $this->dm->flush();
        $this->notify($order, 'vashotel', 'delete');
        $this->dm->remove($order);
        $this->dm->flush();

        return true;
    }

    /**
     * Create/edit orders
     * @param array $serviceIds
     * @param ChannelManagerConfigInterface $config
     * @param $orderType
     * @return bool
     */
    private function orders(array $serviceIds, ChannelManagerConfigInterface $config, $orderType)
    {
        $helper = $this->container->get('mbh.helper');
        $roomTypes = $this->getRoomTypes($config, true);
        $tariffs = $this->getTariffs($config, true);
        $services = $this->getServices($config);

        foreach ($this->getReservations($serviceIds, $config) as $id => $reservation) {
            //check status
            if (!in_array($reservation->status, ['ok', 'preliminary'])) {
                continue;
            }
            //check order
            if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $this->dm->getFilterCollection()->enable('softdeleteable');
            }
            $order = $this->dm->getRepository('MBHPackageBundle:Order')->findOneBy(
                [
                    'channelManagerId' => (string) $id,
                    'channelManagerType' => 'vashotel'
                ]
            );

            if ($orderType == 'new' && $order) {
                continue;
            }
            if ($orderType == 'edit' && !$order) {
                $this->dm->getFilterCollection()->disable('softdeleteable');
                $order = $this->dm->getRepository('MBHPackageBundle:Order')->findOneBy(
                    [
                        'channelManagerId' => (string) $id,
                        'channelManagerType' => 'vashotel'
                    ]
                );
                $this->dm->getFilterCollection()->enable('softdeleteable');

                if (!$order) {
                    $this->notifyError('vashotel', '#' . $id);
                    continue;
                }
            }

            //payer
            $customer = $reservation->customer;
            $payer = null;
            if ($customer && $customer->name) {
                $nameInfo = explode(' ', $customer->name);
                $payer = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
                    $nameInfo[0],
                    empty($nameInfo[1]) ? 'н/д' : $nameInfo[1],
                    empty($nameInfo[2]) ? null : $nameInfo[2],
                    null,
                    empty($customer->email) ? null : (string)$customer->email,
                    empty((string)$customer->phone) ? null : (string)$customer->phone
                );
            }

            //order
            if (!$order && $orderType == 'new') {
                $order = new Order();
                $order->setChannelManagerStatus('new');
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
                $order->setDeletedAt(null);
            }

            $type = (string)$reservation->type;
            $type != 'prepayment' ? $orderPrice = (float)$reservation->sum_rooms + (float)$reservation->sum_services : $orderPrice = (float)$reservation->sum_hotel;

            $comment = empty((string) $reservation->customer_comments) ? $comment = '' : (string) $reservation->customer_comments . ". \n\n";

            if (!empty((string) $reservation->time_arrival)) {
                $comment .= 'Время заезда: ' . (string) $reservation->time_arrival . ". \n";
            }
            $cancelConditions = $reservation->cancel_conditions;
            $cancelTypes = $this->cancelConditions();
            if (!empty($cancelConditions) && !empty($cancelConditions->fine_type)) {
                $comment .= 'Тип штрафа при аннуляции: ' . $cancelTypes[(int)$cancelConditions->fine_type] . ". \n";
                if (!empty($cancelConditions->fine_cost)) {
                    $comment .= 'Стоимость штрафа при аннуляции: ' . $cancelConditions->fine_cost . ". \n";
                }
                if (!empty($cancelConditions->free_cancel_date) && (string)$cancelConditions->free_cancel_date != 'none') {
                    $comment .= 'Дата аннуляции без штрафных санкции: ' . $cancelConditions->free_cancel_date . ". \n";
                }
            }

            $order->setChannelManagerType('vashotel')
                ->setChannelManagerId($id)
                ->setChannelManagerHumanId($id)
                ->setMainTourist($payer)
                ->setConfirmed(false)
                ->setStatus('channel_manager')
                ->setPrice($orderPrice)
                ->setTotalOverwrite($orderPrice)
                ->setNote($comment . 'Тип: ' . $type);

            $card = $reservation->credit_card;
            if ($type == 'credit_card_secured' && !empty($card)) {
                $card = new CreditCard();
                $card->setType($card->type)
                    ->setNumber($card->number)
                    ->setDate($card->valid)
                    ->setCardholder($card->cardholder)
                    ->setCvc($card->cvc)
                ;
                $order->setCreditCard($card);
            }

            $this->dm->persist($order);
            $this->dm->flush();

            //fee
            if (!empty((float)$reservation->sum_fee)) {
                $fee = new CashDocument();
                $fee->setIsConfirmed(false)
                    ->setIsPaid(false)
                    ->setMethod('electronic')
                    ->setOperation('fee')
                    ->setOrder($order)
                    ->setTouristPayer($payer)
                    ->setTotal($orderPrice);
                $this->dm->persist($fee);
                $this->dm->flush();
            }
            //money
            if ($type == 'prepayment' && $orderType == 'new') {
                $fee = new CashDocument();
                $fee->setIsConfirmed(false)
                    ->setIsPaid(false)
                    ->setMethod('electronic')
                    ->setOperation('in')
                    ->setOrder($order)
                    ->setTouristPayer($payer)
                    ->setTotal($order->getPrice());
                $this->dm->persist($fee);
                $this->dm->flush();
            }

            //tariff
            empty($reservation->rate->id) || (string)$reservation->rate->id == 'base' ? $rateId = 0 : $rateId = (string)$reservation->rate->id;

            $corruptedTariff = false;
            $errorTariffMessage = '';

            if (isset($tariffs[$rateId])) {
                $tariff = $tariffs[$rateId]['doc'];
            } else {
                $tariff = $this->createTariff($config, $rateId);
                if (!$tariff) {
                    continue;
                }
                $corruptedTariff = true;
                $errorTariffMessage .= 'ERROR: Not mapped rate <' . $tariff->getName() . '>. ';
            }

            //begin & end
            $begin = $helper->getDateFromString((string)$reservation->date_arrival, 'Y-m-d');
            $end = $helper->getDateFromString((string)$reservation->date_departure, 'Y-m-d');

            //packages
            foreach ($reservation->rooms->room as $room) {
                $corrupted = $corruptedTariff;
                $errorMessage = $errorTariffMessage;
                $guests = [];

                //roomType
                if (isset($roomTypes[(string)$room['id']])) {
                    $roomType = $roomTypes[(string)$room['id']]['doc'];
                } else {
                    $roomType = $this->dm->getRepository('MBHHotelBundle:RoomType')->findOneBy(
                        [
                            'hotel.id' => $config->getHotel()->getId(),
                            'isEnabled' => true,
                            'deletedAt' => null
                        ]
                    );
                    $corrupted = true;
                    $errorMessage .= 'ERROR: invalid roomType #'.(string)$room->id.'. ';

                    if (!$roomType) {
                        continue;
                    }
                }

                //guests
                foreach ($room->guests->guest as $guestInfo) {
                    $lastname = (string)$guestInfo['lastname'];
                    $firstname = (string) $guestInfo['firstname'];

                    if (empty($lastname) || empty($firstname)) {
                        continue;
                    }

                    if ($payer && $payer->getLastName() == $lastname && $payer->getFirstName() == $firstname) {
                        $guests[] = $payer;
                    } else {
                        $guests[] = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
                            $lastname,
                            $firstname,
                            null,
                            null,
                            null,
                            empty((string)$guestInfo['phone']) ? null : (string)$guestInfo['phone']
                        );
                    }
                }

                //prices
                $total = $totalServices = 0;
                $packagePrices = [];
                $packageServices = [];
                $breakfastCount  = 0;
                foreach ($room->pricePerDay->price as $price) {
                    $total += (float)$price->price;
                    $date = $helper->getDateFromString((string)$price['date'], 'Y-m-d');
                    $packagePrices[] =  $packagePrices[] = new PackagePrice(
                        $date,
                        (float)$price->price,
                        $tariff
                    );

                    if ((string) $price->breakfast_included == 'yes') {
                        $breakfastCount++;
                    }

                    //services
                    foreach ($price->service as $service) {
                        $serviceName = trim((string)$service->name);
                        $serviceQuantity = (int)$service->quantity;
                        $servicePrice = (float)$service->price;
                        $totalServices +=$serviceQuantity * $servicePrice;

                        if (isset($services[$serviceName])) {
                            $serviceDoc = $services[$serviceName]['doc'];
                        } else {
                            //find service
                            $serviceDoc = $this->dm->getRepository('MBHPriceBundle:Service')->findOneBy(
                                [
                                    '$or' => [['title' => $serviceName], ['fullTitle' => $serviceName]],
                                    'deletedAt' => null,
                                    'category.id' => ['$in' => $helper->toIds($config->getHotel()->getServicesCategories())]
                                ]
                            );

                            //new service
                            if (!$serviceDoc) {
                                $serviceCategory = $this->dm->getRepository('MBHPriceBundle:ServiceCategory')->findOneBy([
                                    '$or' => [['title' => 'Vashotel.ru'], ['fullTitle' => 'Vashotel.ru']],
                                    'system' => true,
                                    'deletedAt' => null,
                                    'hotel.id' => $config->getHotel()->getId()

                                ]);

                                if (!$serviceCategory) {
                                    $serviceCategory = new ServiceCategory();
                                    $serviceCategory->setTitle('Vashotel.ru')
                                        ->setHotel($config->getHotel())
                                        ->setSystem(true)
                                    ;
                                    $this->dm->persist($serviceCategory);
                                }

                                $serviceDoc = new \MBH\Bundle\PriceBundle\Document\Service();
                                $serviceDoc->setCategory($serviceCategory)
                                    ->setIsOnline(false)
                                    ->setTitle($serviceName)
                                    ->setSystem(true)
                                    ->setCode($serviceCategory)
                                    ->setCalcType('not_applicable')
                                    ->setPrice($servicePrice)
                                ;
                                $this->dm->persist($serviceDoc);
                                $this->dm->flush();
                            }
                        }

                        $packageService = new PackageService();
                        $packageService
                            ->setService($serviceDoc)
                            ->setIsCustomPrice(true)
                            ->setNights(1)
                            ->setPersons(1)
                            ->setBegin($date)
                            ->setAmount($serviceQuantity)
                            ->setPrice($servicePrice)
                            ->setTotalOverwrite($servicePrice * $serviceQuantity)
                        ;
                        $packageServices[] = $packageService;
                    }
                }

                $package = new Package();
                $package
                    ->setChannelManagerId($id)
                    ->setChannelManagerType('vashotel')
                    ->setBegin($begin)
                    ->setEnd($end)
                    ->setRoomType($roomType)
                    ->setTariff($tariff)
                    ->setAdults((int)$room->guests_count)
                    ->setChildren(0)
                    ->setIsSmoking(false)
                    ->setPrices($packagePrices)
                    ->setPrice((float)$total)
                    ->setTotalOverwrite((float)$total)
                    ->setNote($errorMessage)
                    ->setOrder($order)
                    ->setCorrupted($corrupted)
                ;

                foreach ($guests as $tourist) {
                    $package->addTourist($tourist);
                }

                foreach ($packageServices as $packageService) {
                    $packageService->setPackage($package);
                    $this->dm->persist($packageService);
                    $package->addService($packageService);
                }

                //breakfast
                if ($breakfastCount) {
                    $breakfastService = $this->dm->getRepository('MBHPriceBundle:Service')->findOneBy([
                       'code' => 'Breakfast', 'deletedAt' => null
                    ]);
                    if ($breakfastService) {
                        $breakfast = new PackageService();
                        $breakfast
                            ->setService($breakfastService)
                            ->setIsCustomPrice(true)
                            ->setNights($breakfastCount)
                            ->setPersons($package->getAdults())
                            ->setPrice(0)
                            ->setTotalOverwrite(0)
                            ->setPackage($package);
                        ;
                        $this->dm->persist($breakfast);
                        $package->addService($breakfast);
                    }
                }

                $package->setServicesPrice($totalServices);
                $package->setTotalOverwrite($total + $totalServices);

                $order->addPackage($package);
                $this->dm->persist($package);
                $this->dm->persist($order);
                $this->dm->flush();
            }

            $this->notify($order, 'vashotel', $orderType);
        }
        return true;
    }

    /**
     * Get reservations info from service
     * @param array $serviceIds
     * @param ChannelManagerConfigInterface $config
     * @return array
     * @throws \Exception
     */
    private function getReservations(array $serviceIds, ChannelManagerConfigInterface $config)
    {
        $script = 'get_reservations.php';
        $salt = $this->helper->getRandomString(20);
        $result = [];

        foreach (array_chunk($serviceIds, 20) as $ids) {
            $data = ['config' => $config, 'salt' => $salt, 'sig' => null, 'ids' => $ids];
            $sig = $this->getSignature(
                $this->templating->render(static::ORDERS_TEMPLATE, $data),
                $script,
                $config->getPassword()
            );

            $data['sig'] = md5($sig);

            $response = $this->sendXml(
                static::BASE_URL.$script,
                $this->templating->render(static::ORDERS_TEMPLATE, $data)
            );

            //$this->log($response->asXML());

            if (!$this->checkResponse($response, ['script' => $script, 'key' =>  $config->getPassword()])) {
                continue;
            }

            foreach ($response->reservations->reservation as $reservation) {
                $result[(int)$reservation['id']] = $reservation;
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function closeForConfig(ChannelManagerConfigInterface $config)
    {
        $begin = $this->getDefaultBegin();
        $end = $this->getDefaultEnd($begin);
        $result = true;

        foreach ($this->pullTariffs($config) as $tariffId => $tariff) {
            if (!$tariff['changeQuan'] || !$tariff['isActive']) {
                continue;
            }

            $script = 'set_availability.php';
            $salt = $this->helper->getRandomString(20);
            $data = [
                'config' => $config,
                'salt' => $salt,
                'sig' => null,
                'rooms' => $this->pullRooms($config),
                'rate' => $tariffId,
                'begin' => $begin,
                'end' => $end
            ];

            $sig = $this->getSignature(
                $this->templating->render(static::CLOSE_TEMPLATE, $data),
                $script,
                $config->getPassword()
            );

            $data['sig'] = md5($sig);

            $response = $this->sendXml(
                static::BASE_URL.$script,
                $this->templating->render(static::CLOSE_TEMPLATE, $data)
            );

            $this->log($response->asXML());

            if ($result) {
                $result = $this->checkResponse($response, ['script' => $script, 'key' =>  $config->getPassword()]);
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function pullTariffs(ChannelManagerConfigInterface $config)
    {
        $script = 'get_rates.php';
        $salt = $this->helper->getRandomString(20);
        $data = ['config' => $config, 'salt' => $salt, 'sig' => null];

        $sig = $this->getSignature(
            $this->templating->render(static::GET_TEMPLATE, $data),
            $script,
            $config->getPassword()
        );
        $data['sig'] = md5($sig);

        $response = $this->sendXml(static::BASE_URL . $script, $this->templating->render(static::GET_TEMPLATE, $data));

        //$this->log($response->asXML());

        $result = [
            0 => [
                "title" => 'Standard rate',
                "changePrice" => true,
                "changeQuan" => true,
                "isActive" => true,
            ]
        ];

        if (!$this->checkResponse($response, ['script' => $script, 'key' =>  $config->getPassword()])) {
            return $result;
        }

        foreach ($response->xpath('rate') as $rate) {
            if (!(int)$rate->id) {
                continue;
            }

            $result[(int)$rate->id] = [
                'title' => (string)$rate->name,
                'changePrice' => !!(int)$rate->changePrice,
                'changeQuan' => !!(int)$rate->changeQuan,
                'isActive' => !!(int)$rate->isActive,
            ];
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function pullRooms(ChannelManagerConfigInterface $config)
    {
        $script = 'get_rooms.php';
        $salt = $this->helper->getRandomString(20);
        $data = ['config' => $config, 'salt' => $salt, 'sig' => null];

        $sig = $this->getSignature(
            $this->templating->render(static::GET_TEMPLATE, $data),
            $script,
            $config->getPassword()
        );
        $data['sig'] = md5($sig);

        $response = $this->sendXml(static::BASE_URL . $script, $this->templating->render(static::GET_TEMPLATE, $data));

        //$this->log($response->asXML());

        if (!$this->checkResponse($response, ['script' => $script, 'key' =>  $config->getPassword()])) {
            return [];
        }
        $result = [];

        foreach ($response->xpath('room') as $room) {
            $result[(int)$room->id] = (string)$room->name;
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function updatePrices(\DateTime $begin = null, \DateTime $end = null, RoomType $filterRoomType = null)
    {
        $result = true;
        $script = 'set_availability.php';
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin);
        $calc = $this->container->get('mbh.calculation');

        foreach ($this->getConfig() as $config) {
            $salt = $this->helper->getRandomString(20);
            $data = ['config' => $config, 'salt' => $salt, 'sig' => null];
            $roomTypes = $this->getRoomTypes($config);
            $configTariffs = $this->getTariffs($config, true);
            $priceCachesCallback = function () use ($begin, $end, $config, $filterRoomType) {
                return $this->dm->getRepository('MBHPriceBundle:PriceCache')->fetch(
                    $begin,
                    $end,
                    $config->getHotel(),
                    $this->getRoomTypeArray($filterRoomType),
                    [],
                    true,
                    $this->roomManager->useCategories
                );
            };
            $priceCaches = $this->helper->getFilteredResult($this->dm, $priceCachesCallback);

            //iterate tariffs
            foreach ($this->pullTariffs($config) as $tariffId => $tariff) {
                if (!$tariff['changePrice'] || !$tariff['isActive'] || !isset($configTariffs[$tariffId])) {
                    continue;
                }

                $tariffDoc = $configTariffs[$tariffId]['doc'];

                foreach ($roomTypes as $roomTypeId => $roomType) {
                    $roomTypeId = $this->getRoomTypeArray($roomType['doc'])[0];

                    $data['rate'] = $tariffId;

                    foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), (clone $end)->modify('+1 day')) as $day) {
                        $info = false;

                        if (isset($priceCaches[$roomTypeId][$tariffDoc->getId()][$day->format('d.m.Y')])) {
                            $info = $priceCaches[$roomTypeId][$tariffDoc->getId()][$day->format('d.m.Y')];
                        }

                        if ($info) {
                            $data['rooms'][$roomType['syncId']][$day->format('Y-m-d')] = [
                                'prices' => $calc->calcPrices($roomType['doc'], $tariffDoc, $day, $day),
                            ];
                        } else {
                            $data['rooms'][$roomType['syncId']][$day->format('Y-m-d')] = [
                                'prices' => []
                            ];
                        }
                    }
                }

                $sig = $this->getSignature(
                    $this->templating->render(static::UPDATE_PRICES_TEMPLATE, $data),
                    $script,
                    $config->getPassword()
                );
                $data['sig'] = md5($sig);

                $response = $this->sendXml(
                    static::BASE_URL.$script,
                    $this->templating->render(static::UPDATE_PRICES_TEMPLATE, $data)
                );

                $this->log($response->asXML());

                if ($result) {
                    $result = $this->checkResponse(
                        $response,
                        ['script' => $script, 'key' =>  $config->getPassword()]
                    );
                }
            }
        }

        return $result;
    }

    /**
     * @param ChannelManagerConfigInterface $config
     */
    public function syncServices(ChannelManagerConfigInterface $config)
    {
        $config->removeAllServices();
        foreach (self::SERVICES as $serviceKey => $serviceName) {
            $serviceDoc = $this->dm->getRepository('MBHPriceBundle:Service')->findOneBy(
                [
                    'code' => $serviceName
                ]
            );

            if (empty($serviceDoc) || $serviceDoc->getCategory()->getHotel()->getId() != $config->getHotel()->getId()) {
                continue;
            }

            $service = new Service();
            $service->setServiceId($serviceKey)->setService($serviceDoc);
            $config->addService($service);
            $this->dm->persist($config);
        }

        $this->dm->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function updateRestrictions(\DateTime $begin = null, \DateTime $end = null, RoomType $filterRoomType = null)
    {
        return $this->updateRooms($begin = null, $end = null, $filterRoomType);
    }

    /**
     * {@inheritDoc}
     */
    public function updateRooms(\DateTime $begin = null, \DateTime $end = null, RoomType $filterRoomType = null)
    {
        $result = true;
        $script = 'set_availability.php';
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin);

        // iterate hotels
        foreach ($this->getConfig() as $config) {
            $salt = $this->helper->getRandomString(20);
            $data = ['config' => $config, 'salt' => $salt, 'sig' => null];
            $roomTypes = $this->getRoomTypes($config);
            $configTariffs = $this->getTariffs($config, true);
            $roomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')->fetch(
                $begin,
                $end,
                $config->getHotel(),
                $filterRoomType ? [$filterRoomType->getId()] : [],
                null,
                true
            );

            $restrictions = $this->dm->getRepository('MBHPriceBundle:Restriction')->fetch(
                $begin,
                $end,
                $config->getHotel(),
                $filterRoomType ? [$filterRoomType->getId()] : [],
                [],
                true
            );

            //iterate tariffs
            foreach ($this->pullTariffs($config) as $tariffId => $tariff) {
                if (!$tariff['changeQuan'] || !$tariff['isActive'] || !isset($configTariffs[$tariffId])) {
                    continue;
                }

                $tariffRoomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')->fetch(
                    $begin,
                    $end,
                    $config->getHotel(),
                    $filterRoomType ? [$filterRoomType->getId()] : [],
                    [$configTariffs[$tariffId]['doc']->getId()],
                    true
                );

                foreach ($roomTypes as $roomTypeId => $roomType) {
                    $data['rate'] = $tariffId;

                    foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), (clone $end)->modify('+1 day')) as $day) {
                        $info = false;
                        $restriction = null;

                        if (isset($tariffRoomCaches[$roomTypeId][$configTariffs[$tariffId]['doc']->getId(
                        )][$day->format('d.m.Y')])) {
                            $info = $tariffRoomCaches[$roomTypeId][$configTariffs[$tariffId]['doc']->getId(
                            )][$day->format('d.m.Y')];
                        } elseif (isset($roomCaches[$roomTypeId][0][$day->format('d.m.Y')])) {
                            $info = $roomCaches[$roomTypeId][0][$day->format('d.m.Y')];
                        }

                        if (isset($restrictions[$roomTypeId][$configTariffs[$tariffId]['doc']->getId(
                        )][$day->format('d.m.Y')])) {
                            $restriction = $restrictions[$roomTypeId][$configTariffs[$tariffId]['doc']->getId()][$day->format('d.m.Y')];
                        }

                        if ($info) {
                            if ($restriction && $restriction->getClosed()) {
                                if ($tariffId) {
                                    $data['rooms'][$roomType['syncId']][$day->format('Y-m-d')] = [
                                        'sellquantity' => 0,
                                    ];
                                } else {
                                    $data['rooms'][$roomType['syncId']][$day->format('Y-m-d')] = [
                                        'closed' => 1,
                                    ];
                                }
                            } else {
                                $data['rooms'][$roomType['syncId']][$day->format('Y-m-d')] = [
                                    'sellquantity' => $info->getLeftRooms() > 0 ? $info->getLeftRooms() : 0,
                                ];
                            }
                        } else {
                            $data['rooms'][$roomType['syncId']][$day->format('Y-m-d')] = [
                                'sellquantity' => 0,
                                'closed' => 1
                            ];
                        }
                    }
                }

                $sig = $this->getSignature(
                    $this->templating->render(static::UPDATE_ROOMS_TEMPLATE, $data),
                    $script,
                    $config->getPassword()
                );
                $data['sig'] = md5($sig);

                $response = $this->sendXml(
                    static::BASE_URL.$script,
                    $this->templating->render(static::UPDATE_ROOMS_TEMPLATE, $data)
                );

                $this->log($response->asXML());

                if ($result) {
                    $result = $this->checkResponse(
                        $response,
                        ['script' => $script, 'key' =>  $config->getPassword()]
                    );
                }
            }
        }
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function pushResponse(Request $request)
    {
        $this->log($request->getContent());

        $xml = simplexml_load_string($request->getContent());
        $script = 'vashotel';

        if (!$xml || !$xml->hotel_id) {
            throw new Exception('Invalid vashotel xml');
        }

        $config = $this->dm->getRepository('MBHChannelManagerBundle:VashotelConfig')->findOneBy(['hotelId' => (string)$xml->hotel_id]);

        if (!$config) {
            throw new Exception('Vashotel config not found');
        }

        $salt = $this->helper->getRandomString(20);
        $data = ['config' => $config, 'salt' => $salt, 'sig' => null];

        $sig = $this->getSignature(
            $this->templating->render(static::PUSH_TEMPLATE, $data),
            $script,
            $config->getPassword()
        );
        $data['sig'] = md5($sig);

        return new Response($this->templating->render(static::PUSH_TEMPLATE, $data), 200, [
            'Content-Type' => 'text/xml'
        ]);
    }
}
