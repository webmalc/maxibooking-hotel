<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;

use MBH\Bundle\BaseBundle\Lib\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface as BaseInterface;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\HotelBundle\Document\RoomType;


abstract class AbstractChannelManagerService implements ChannelManagerServiceInterface
{

    /**
     * Test mode on/off
     */
    CONST TEST = true;

    /**
     * Default period for room/prices upload
     */
    CONST DEFAULT_PERIOD = 365;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Doctrine\Bundle\MongoDBBundle\ManagerRegistry
     */
    protected $dm;

    /**
     * @var \Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine
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

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->templating = $this->container->get('templating');
        $this->request = $container->get('request');
        $this->helper = $container->get('mbh.helper');
        $this->logger = $container->get('mbh.channelmanager.logger');
        $this->currency = $container->get('mbh.currency');
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
     * {@inheritDoc}
     */
    public function update(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = true;

        $check = $this->updateRooms($begin, $end, $roomType);
        $result ? $result = $check : $result;

        $this->updateRestrictions($begin, $end, $roomType);
        $result ? $result = $check : $result;

        $this->updatePrices($begin, $end, $roomType);
        $result ? $result = $check : $result;

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function clearAllConfigs()
    {
        foreach ($this->getConfig() as $config) {
            $this->clearConfig($config);
        }
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

        if (!isset($tariffsInfo[$id])) {
            return null;
        }
        $info = $tariffsInfo[$id];

        $oldTariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->findOneBy([
            'title' => $info['title']
        ]);
        if ($oldTariff) {
            return $oldTariff;
        }

        $tariff = new Tariff();
        $tariff->setTitle($info['title'])
            ->setFullTitle($info['title'])
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
     * @return array
     */
    public function getConfig()
    {
        $result = [];

        foreach ($this->dm->getRepository('MBHHotelBundle:Hotel')->findAll() as $hotel) {
            $method = 'get'.static::CONFIG;
            $config = $hotel->$method();

            if ($config && $config instanceof BaseInterface && $config->getIsEnabled()) {
                $result[] = $config;
            }
        }

        return $result;
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
     * @param $post $error
     * @return mixed
     */
    public function send($url, $data = [], $headers = null, $error = false, $post = true)
    {
        $ch = curl_init($url);

        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
        }

        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if ($post && !empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        if (static::TEST) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
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

    public function notify(Order $order, $service, $type = 'new')
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

            $packages = [];
            $hotel = null;

            foreach ($order->getPackages() as $package) {
                if ($package->getDeletedAt()) {
                    continue;
                }
                $packages[] = $package->getNumberWithPrefix();
                if (!$hotel) {
                    $hotel = $package->getRoomType()->getHotel();
                }
            }

            $message
                ->setText($tr->trans($text, ['%order%' => $order->getId(), '%packages%' => implode(', ',  $packages)], 'MBHChannelManagerBundle'))
                ->setFrom('channelmanager')
                ->setSubject($tr->trans($subject, [], 'MBHChannelManagerBundle'))
                ->setType($type == 'delete' ? 'danger' : 'info')
                ->setCategory('notification')
                ->setAutohide(false)
                ->setHotel($hotel)
                ->setOrder($order)
                ->setTemplate('MBHBaseBundle:Mailer:order.html.twig')
                ->setEnd(new \DateTime('+10 minute'))
            ;

            $notifier->setMessage($message)->notify();

        } catch (\Exception $e) {
            return false;
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

        return $amount;
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

        return $amount;
    }
}