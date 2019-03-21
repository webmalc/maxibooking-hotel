<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Document\NotificationType;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Services\Airbnb\Airbnb;
use MBH\Bundle\ChannelManagerBundle\Services\Expedia\Expedia;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface as BaseInterface;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\MongoDB\CursorInterface;

abstract class AbstractChannelManagerService implements ChannelManagerServiceInterface
{

    const COMMAND_UPDATE = 'update';

    const COMMAND_UPDATE_PRICES = 'updatePrices';

    const COMMAND_UPDATE_ROOMS = 'updateRooms';

    const COMMAND_UPDATE_RESTRICTIONS = 'updateRestrictions';

    /**
     * Test mode on/off
     */
    const TEST = true;

    const UNAVAIBLE_PRICES = [];

    const UNAVAIBLE_RESTRICTIONS = [];

    const CHANNEL_MANAGER_NAMES = [
        "vashotel",
        "booking",
        "myallocator",
        "ostrovok",
        "oktogo",
        "101Hotels",
        Airbnb::NAME
    ];

    /**
     * Default period for room/prices upload
     */
    const DEFAULT_PERIOD = 365;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @var TwigEngine
     *
     */
    protected $templating;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var array
     */
    protected $services = [];

    /**
     * @var \MBH\Bundle\BaseBundle\Service\Helper
     */
    protected $helper;

    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    protected $logger;

    /**
     * @var \MBH\Bundle\BaseBundle\Service\Currency;
     */
    protected $currency;

    protected $roomManager;

    /**
     * @var array
     */
    protected $errors = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->templating = $this->container->get('templating');
        $this->request = $container->get('request_stack')->getCurrentRequest();
        $this->helper = $container->get('mbh.helper');
        $this->logger = $container->get('mbh.channelmanager.logger');
        $this->logger::setTimezone(new \DateTimeZone('UTC'));
        $this->currency = $container->get('mbh.currency');
        $this->roomManager = $container->get('mbh.hotel.room_type_manager');
    }

    /**
     * {{ @inheritDoc }}
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * {{ @inheritDoc }}
     */
    public function getOverview(\DateTime $begin, \DateTime $end, Hotel $hotel): ?ChannelManagerOverview
    {
        $method = 'get' . static::CONFIG;
        $config = $hotel->$method();
        if (!$config || !$config->getIsEnabled()) {
            return null;
        }

        $trans = $this->container->get('translator');
        $overview = new ChannelManagerOverview();
        $overview->setBegin($begin)
            ->setName(static::class)
            ->setEnd($end);

        $getError = function (array $types, string $prefix, ChannelManagerOverview &$overview, string $method) use ($config, $trans, $begin, $end) {
            if (empty($types)) {
                return null;
            }
            $getMethod = 'get' . ucfirst($method);
            foreach ($this->$getMethod($config, $begin, $end, $types) as $val) {
                $message = $val->getDate()->format('d.m.Y') . ': ' . $val->getTariff();
                $message .= ' - ' . $val->getRoomType() . ' - ';
                $message .= implode(', ', array_filter(array_map(function ($element) use ($trans, $prefix, $val) {
                    $typeMethod = 'get' . ucfirst($element);
                    if ($val->$typeMethod()) {
                        return '"' . $trans->trans($prefix . '.type.' . $element) . '"';
                    }
                }, array_keys($types))));

                $addMethod = 'add' . ucfirst($method);
                $overview->$addMethod($val, $message);
            }
        };

        $getError(static::UNAVAIBLE_PRICES, 'channelmanager.notifications.prices', $overview, 'prices');
        $getError(static::UNAVAIBLE_RESTRICTIONS, 'channelmanager.notifications.restrictions', $overview, 'restrictions');

        return $overview;
    }

    /**
     * {{ @inheritDoc }}
     */
    public function getNotifications(ChannelManagerConfigInterface $config): array
    {
        $errors = [];
        if (!$config->getIsEnabled()) {
            return [];
        }
        $trans = $this->container->get('translator');
        $getError = function (array $types, string $message, array &$errors, string $method) use ($config, $trans) {
            if (empty($types)) {
                return $errors;
            }
            if (count($types) && $this->$method($config, $types)) {
                $error = $trans->trans($message) . ': ';
                $error .= implode(', ', array_map(function ($element) use ($trans, $message) {
                    return $trans->trans($message . '.type.' . $element);
                }, array_keys($types)));
                $errors[] = $error;
            }
            return $errors;
        };
        $getError(static::UNAVAIBLE_PRICES, 'channelmanager.notifications.prices', $errors, 'countPrices');
        $getError(static::UNAVAIBLE_RESTRICTIONS, 'channelmanager.notifications.restrictions', $errors, 'countRestrictions');

        return $errors;
    }

    /**
     * {{ @inheritDoc }}
     */
    public function addError(string $error): ChannelManagerServiceInterface
    {
        $this->errors[] = $error;
        return $this;
    }

    /**
     * @param RoomType $roomType
     * @return array
     */
    public function getRoomTypeArray(RoomType $roomType = null)
    {
        if (!$roomType) {
            return [];
        }
        if (!$this->roomManager->useCategories) {
            return [$roomType->getId()];
        }
        if ($this->roomManager->useCategories) {
            if (!$roomType->getCategory()) {
                return [0];
            }
            return [$roomType->getCategory()->getId()];
        }
    }

    /**
     * @return bool
     */
    public function isDevEnvironment()
    {
        return $this->container->get('kernel')->getEnvironment() == 'prod' ? false : true;
    }

    /**
     * {@inheritDoc}
     */
    public function closeAll()
    {
        $result = true;
        foreach ($this->getConfig() as $config) {
            $check = $this->closeForConfig($config);
            $result ? $result = $check : $result;
        }

        return $result;
    }

    /**
     * @param bool $forPullingOldOrders
     * @return array
     */
    public function getConfig($forPullingOldOrders = false)
    {
        $result = [];

        foreach ($this->dm->getRepository('MBHHotelBundle:Hotel')->findAll() as $hotel) {
            $method = 'get' . static::CONFIG;
            $config = $hotel->$method();

            if ($config && $config instanceof BaseInterface && $config->isReadyToSync(!$forPullingOldOrders)) {
                $result[] = $config;
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function update(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $this->log('ChannelManager update function start');

        $result = true;

        $check = $this->updateRooms($begin, $end, $roomType);
        $result ? $result = $check : $result;

        $check = $this->updateRestrictions($begin, $end, $roomType);
        $result ? $result = $check : $result;

        $check = $this->updatePrices($begin, $end, $roomType);
        $result ? $result = $check : $result;

        $this->log('ChannelManager update function end.');

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function clearAllConfigs()
    {
        $this->log('Abstract clearAllConfigs function start.');

        foreach ($this->getConfig() as $config) {
            $this->clearConfig($config);
        }

        $this->log('Abstract clearAllConfigs function end.');
    }

    /**
     * {@inheritDoc}
     */
    public function clearConfig(ChannelManagerConfigInterface $config)
    {
        //roomTypes
        $rooms = $this->pullRooms($config);
        foreach ($config->getRooms() as $room) {
            if (!isset($rooms[$room->getRoomId()])) {
                $config->removeRoom($room);
            }
        }
        //tariffs
        $rates = $this->pullTariffs($config);
        foreach ($config->getTariffs() as $tariff) {
            if (!isset($rates[$tariff->getTariffId()])) {
                $config->removeTariff($tariff);
            }
        }
        $this->dm->persist($config);
        $this->dm->flush();

        return $config;
    }

    /**
     * {@inheritDoc}
     */
    public function createTariff(ChannelManagerConfigInterface $config, $id)
    {
        $tariffsInfo = $this->pullTariffs($config);
        $info = null;

        if (isset($tariffsInfo[$id])) {
            $info = $tariffsInfo[$id];
        }
        $info ? $title = $info['title'] : $title = 'Automatically generated rate: undefined';
        $oldTariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->findOneBy([
            'title' => $title
        ]);
        if ($oldTariff) {
            return $oldTariff;
        }
        $tariff = new Tariff();
        $tariff->setTitle($title)
            ->setFullTitle($title)
            ->setIsDefault(false)
            ->setIsOnline(false)
            ->setHotel($config->getHotel())
            ->setDescription('Automatically generated rate.')
        ;
        $this->dm->persist($tariff);
        $this->dm->flush();

        return $tariff;
    }

    /**
     * @param \DateTime $begin
     * @return \DateTime
     */
    public function getDefaultBegin(\DateTime $begin = null)
    {
        $now = new \DateTime('midnight');

        if (!$begin || $begin < $now) {
            $begin = $now;
        }

        return $begin;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @return \DateTime
     */
    public function getDefaultEnd(\DateTime $begin, \DateTime $end = null)
    {
        if (!$end || $end < new \DateTime('midnight')) {
            $end = clone $begin;
            $end->modify('+' . static::DEFAULT_PERIOD .' days');
        }

        return $end;
    }

    /**
     * @param string $message
     * @param string $method
     * @return $this
     */
    public function log($message, $method = 'info')
    {
        (method_exists($this->logger, $method)) ? $method : $method = 'info';
        $this->logger->$method(get_called_class() . ': ' . (string)$message);

        return $this;
    }

    /**
     * @param ChannelManagerConfigInterface $config
     * @param bool $byService
     * @return array
     */
    public function getRoomTypes(BaseInterface $config, $byService = false)
    {
        $result = [];

        foreach ($config->getRooms() as $room) {
            /** @var Room $room */
            $roomType = $room->getRoomType();
            if (empty($room->getRoomId()) || !$roomType->getIsEnabled() || !empty($roomType->getDeletedAt())) {
                continue;
            }

            if ($byService) {
                $result[$room->getRoomId()] = [
                    'syncId' => $room->getRoomId(),
                    'doc' => $roomType
                ];
            } else {
                $result[$roomType->getId()] = [
                    'syncId' => $room->getRoomId(),
                    'doc' => $roomType
                ];
            }
        }

        return $result;
    }

    /**
     * Get roomTypeIds from config
     *
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    private function getRoomTypeIdsFromConfig(ChannelManagerConfigInterface $config): array
    {
        return  array_unique(array_map(function ($element) {
            return $element->getRoomType()->getId();
        }, $config->getRooms()->toArray()));
    }

    /**
     * Get tariffIds from config
     *
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    private function getTariffIdsFromConfig(ChannelManagerConfigInterface $config): array
    {
        return  array_unique(array_map(function ($element) {
            return $element->getTariff()->getId();
        }, $config->getTariffs()->toArray()));
    }

    /**
     * Get restrictions by config
     *
     * @param ChannelManagerConfigInterface $config
     * @return Builder
     */
    protected function getRestrictionsByConfigQueryBuilder(ChannelManagerConfigInterface $config): Builder
    {
        $builder = $this->dm->getRepository('MBHPriceBundle:Restriction')->createQueryBuilder();

        $tariffsIds = $this->getTariffIdsFromConfig($config);
        $roomTypeIds = $this->getRoomTypeIdsFromConfig($config);

        $builder->field('tariff.id')->in($tariffsIds)
            ->field('roomType.id')->in($roomTypeIds)
            ->field('date')->gte(new \DateTime('midnight'))
        ;

        return $builder;
    }

    /**
     * Get priceCaches by config
     *
     * @param ChannelManagerConfigInterface $config
     * @return Builder
     */
    protected function getPricesByConfigQueryBuilder(ChannelManagerConfigInterface $config): Builder
    {
        $builder = $this->dm->getRepository('MBHPriceBundle:PriceCache')->createQueryBuilder();

        $tariffsIds = $this->getTariffIdsFromConfig($config);
        $roomTypeIds = $this->getRoomTypeIdsFromConfig($config);

        $builder->field('tariff.id')->in($tariffsIds)
            ->field('roomType.id')->in($roomTypeIds)
            ->field('cancelDate')->equals(null)
            ->field('date')->gte(new \DateTime('midnight'))
        ;

        return $builder;
    }

    /**
     * Count restrictions by config and type
     *
     * @param ChannelManagerConfigInterface $config
     * @param array $types
     * @return int
     */
    protected function countRestrictions(ChannelManagerConfigInterface $config, array $types = []): int
    {
        $builder = $this->getRestrictionsByConfigQueryBuilder($config);

        foreach ($types as $type => $val) {
            $builder->addOr($builder->expr()->field($type)->notEqual($val));
        }
        return $builder->getQuery()->count();
    }

    /**
     * Get restrictions by config and type
     *
     * @param ChannelManagerConfigInterface $config
     * @param array $types
     * @param \DateTime $begin
     * @param \DateTime $end
     * @return CursorInterface
     */
    protected function getRestrictions(ChannelManagerConfigInterface $config, \DateTime $begin, \DateTime $end, array $types = []): CursorInterface
    {
        $builder = $this->getRestrictionsByConfigQueryBuilder($config);
        $builder->field('date')->gte($begin)
            ->field('date')->lte($end)
            ->sort('date');
        foreach ($types as $type => $val) {
            $builder->addOr($builder->expr()->field($type)->notEqual($val));
        }
        return $builder->getQuery()->execute();
    }

    /**
     * Get prices by config and type
     *
     * @param ChannelManagerConfigInterface $config
     * @param array $types
     * @param \DateTime $begin
     * @param \DateTime $end
     * @return CursorInterface
     */
    protected function getPrices(ChannelManagerConfigInterface $config, \DateTime $begin, \DateTime $end, array $types = []): CursorInterface
    {
        $builder = $this->getPricesByConfigQueryBuilder($config);
        $builder->field('date')->gte($begin)
            ->field('date')->lte($end)
            ->sort('date');
        foreach ($types as $type => $val) {
            $builder->addOr($builder->expr()->field($type)->notEqual($val));
        }
        return $builder->getQuery()->execute();
    }

    /**
     * Count prices by config and type
     *
     * @param ChannelManagerConfigInterface $config
     * @param array $types
     * @return int
     */
    protected function countPrices(ChannelManagerConfigInterface $config, array $types = []): int
    {
        $builder = $this->getPricesByConfigQueryBuilder($config);

        foreach ($types as $type => $val) {
            $builder->addOr($builder->expr()->field($type)->notEqual($val));
        }

        return $builder->getQuery()->count();
    }

    /**
     * @param ChannelManagerConfigInterface $config
     * @param bool $byService
     * @return array
     */
    public function getTariffs(BaseInterface $config, $byService = false)
    {
        $result = [];

        foreach ($config->getTariffs() as $configTariff) {
            $tariff = $configTariff->getTariff();

            if ($configTariff->getTariffId() === null || !$tariff->getIsEnabled() || !empty($tariff->getDeletedAt())) {
                continue;
            }

            if ($byService) {
                $result[$configTariff->getTariffId()] = [
                    'syncId' => $configTariff->getTariffId(),
                    'doc' => $tariff
                ];
            } else {
                $result[$tariff->getId()] = [
                    'syncId' => $configTariff->getTariffId(),
                    'doc' => $tariff
                ];
            }
        }

        return $result;
    }

    /**
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function getServices(BaseInterface $config)
    {
        $result = [];

        foreach ($config->getServices() as $configService) {
            $service = $configService->getService();

            if (empty($configService->getServiceId()) || !empty($service->getDeletedAt())) {
                continue;
            }

            $result[$configService->getServiceId()] = [
                'syncId' => $configService->getServiceId(),
                'doc' => $service
            ];
        }

        return $result;
    }

    /**
     * @param $url
     * @param array $data
     * @param array $headers
     * @param bool $error
     * @return \SimpleXMLElement
     * @throws \Exception
     */
    public function sendXml($url, $data = [], $headers = null, $error = false)
    {
        $result = $this->send($url, $data, $headers, $error);
        $xml = simplexml_load_string($result);

        if (!$xml instanceof \SimpleXMLElement) {
            throw new Exception('Invalid xml response. Response: ' . $result);
        }

        return $xml;
    }

    /**
     * @param $url
     * @param array $data
     * @param array $headers
     * @param bool $error
     * @param string $method
     * @return mixed
     */
    public function send($url, $data = [], $headers = null, $error = false, $method = 'POST')
    {
        $ch = curl_init();

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        if ($method == 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        }
        if (static::TEST) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if (!empty($data)) {
            if ($method == 'POST' || $method == 'PUT') {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            } elseif ($method == 'GET') {
                $url = $url . '?' . http_build_query($data);
            }
        }

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        $output = curl_exec($ch);

        if (!$output && $error) {
            trigger_error(curl_error($ch));
        }

        curl_close($ch);

        return $output;
    }

    /**
     * @param $url
     * @param array $data
     * @param array $headers
     * @param bool $error
     * @param bool $post
     * @return array
     * @throws \Exception
     */
    public function sendJson($url, $data = [], $headers = null, $error = false, $post = false)
    {
        $result = $this->send($url, json_encode($data), $headers, $error, $post);
        $json = json_decode($result, true);

        if (!$json) {
            throw new Exception('Invalid json response. Response: ' . $result);
        }

        return $json;
    }

    /**
     * @param $service
     * @param null $info
     * @param array $transParams
     * @return bool|\MBH\Bundle\BaseBundle\Service\Messenger\Notifier
     */
    public function notifyError($service, $info = null, $transParams = [])
    {
        try {
            $notifier = $this->container->get('mbh.notifier');
            $tr = $this->container->get('translator');
            $message = $notifier::createMessage();

            $text = 'channelManager.' . $service . '.notification.error';
            $subject = 'channelManager.' . $service . '.notification.error.subject';

            $message
                ->setText(
                    $tr->trans($text, ['%info%' => $info], 'MBHChannelManagerBundle') . '<br>' .
                    $tr->trans('channelManager.booking.notification.bottom', [], 'MBHChannelManagerBundle')
                )
                ->setTranslateParams($transParams)
                ->setFrom('channelmanager')
                ->setSubject($tr->trans($subject, [], 'MBHChannelManagerBundle'))
                ->setType('danger')
                ->setCategory('notification')
                ->setAutohide(false)
                ->setEnd(new \DateTime('+10 minute'))
                ->setMessageType(NotificationType::CHANNEL_MANAGER_TYPE)
            ;

            return $notifier->setMessage($message)->notify();
        } catch (\Exception $e) {
            $this->logger->addAlert('Error notification Error ChannelManager'.$e->getMessage());
        }
    }

    /**
     * @param string $channelManager
     * @param string $requestDescription
     * @throws \Throwable
     */
    public function notifyErrorRequest(string $channelManager, string $requestDescription)
    {
        try {
            $notifier = $this->container->get('mbh.notifier');
            $tr = $this->container->get('translator');
            $message = $notifier::createMessage();

            $subject = 'channelManager.commonCM.notification.error.subject';
            $transParams = ['%channelManagerName%' => $channelManager];
            $text = $tr->trans($requestDescription, $transParams, 'MBHChannelManagerBundle')
                . '<br>'
                . $tr->trans('channelManager.booking.notification.bottom', $transParams, 'MBHChannelManagerBundle');

            $message
                ->setText($text)
                ->setFrom('channelmanager')
                ->setSubject($tr->trans($subject, $transParams, 'MBHChannelManagerBundle'))
                ->setType('danger')
                ->setCategory('notification')
                ->setAutohide(false)
                ->setEnd(new \DateTime('+10 minute'))
                ->setMessageType(NotificationType::CHANNEL_MANAGER_TYPE)
            ;

            $notifier->setMessage($message)->notify();
        } catch (\Exception $e) {
            $this->logger->addAlert('Error notification Error ChannelManager'.$e->getMessage());
        }
    }

    public function notify(Order $order, $service, $type = 'new', $transParams = [])
    {
        try {
            $notifier = $this->container->get('mbh.notifier');
            $tr = $this->container->get('translator');
            $message = $notifier::createMessage();

            $text = 'channelManager.'.$service.'.notification.'.$type;
            $subject = 'channelManager.'.$service.'.notification.subject.' . $type;

            if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $this->dm->getFilterCollection()->enable('softdeleteable');
            }

            $packageNumbers = [];
            $hotel = null;
            $desc = null;
            $packageId = null;

            foreach ($order->getPackages() as $package) {
                if ($package->getDeletedAt() && $type != 'delete') {
                    continue;
                }
                $packageNumbers[] = $package->getNumberWithPrefix();

                $roomType = $package->getRoomType()->getFullTitle();
                $dateBegin = $package->getBegin()->format('d.m.y');
                $dateEnd = $package->getEnd()->format('d.m.y');
                $packageId = $package->getId();

                $desc .= " - $roomType, $dateBegin-$dateEnd";

                if (!$hotel) {
                    $hotel = $package->getRoomType()->getHotel();
                }
            }

            $textHtmlLink = $this->container->get('router')->generate('package_order_edit', [
                'id' => $order->getId(),
                'packageId' => $packageId
            ]);

            $html = '<a href='. $textHtmlLink .'>'. $order->getId() .'</a>';

            $message
                ->setText($tr->trans($text, ['%order%' => $order->getId(), '%packages%' => implode(', ', $packageNumbers)], 'MBHChannelManagerBundle'))
                ->setFrom('channelmanager')
                ->setSubject($tr->trans($subject, [], 'MBHChannelManagerBundle'))
                ->setType($type == 'delete' ? 'danger' : 'info')
                ->setCategory('notification')
                ->setAutohide(false)
                ->setHotel($hotel)
                ->setTranslateParams($transParams)
                ->setOrder($order)
                ->setTemplate('MBHBaseBundle:Mailer:order.html.twig')
                ->setEnd(new \DateTime('+10 minute'))
                ->setMessageType(NotificationType::CHANNEL_MANAGER_TYPE)
                ->setTextHtmlLink(
                    $tr->trans(
                        $text,
                        ['%order%' => $html, '%packages%' => implode(', ', $packageNumbers) . $desc],
                        'MBHChannelManagerBundle'
                    )
                );

            $notifier->setMessage($message)->notify();
        } catch (\Exception $e) {
            $this->logger->addAlert('Notification channelManager ERROR'.$e->getMessage());
        }
    }

    /**
     * @param CurrencyConfigInterface $config
     * @param $amount
     * @return float
     */
    public function currencyConvertToRub(CurrencyConfigInterface $config, $amount)
    {
        $code = $config->getCurrency();

        if (!$code) {
            return $amount;
        }
        try {
            return $this->currency->convertToRub($amount, $code);
        } catch (Exception $e) {
            return $amount * $config->getCurrencyDefaultRatio();
        }
    }

    /**
     * @param CurrencyConfigInterface $config
     * @param $amount
     * @return float
     */
    public function currencyConvertFromRub(CurrencyConfigInterface $config, $amount)
    {
        $code = $config->getCurrency();

        if (!$code) {
            return $amount;
        }
        try {
            return $this->currency->convertFromRub($amount, $code);
        } catch (Exception $e) {
            return $amount / $config->getCurrencyDefaultRatio();
        }
    }

    /**
     * @param Order $order
     * @param $isModified
     * @return string
     */
    public function getUnexpectedOrderError(Order $order, $isModified)
    {
        $errorMessageId = $isModified
            ? 'services.channel_manager.error.unexpected_modified_order'
            : 'services.channel_manager.error.unexpected_removed_order';

        return $this->container->get('translator')->trans($errorMessageId, [
            '%orderId%' => $order->getChannelManagerId(),
            '%service_name%' => $order->getChannelManagerType()
        ], 'MBHChannelManagerBundle');
    }

    /**
     * @return array
     */
    public static function getChannelManagerNames()
    {
        $expediaSources = [];
        foreach (Expedia::BOOKING_SOURCES as $expediaSource) {
            $expediaSources[] = mb_strtolower($expediaSource);
        }

        return array_merge($expediaSources, self::CHANNEL_MANAGER_NAMES);
    }
}
