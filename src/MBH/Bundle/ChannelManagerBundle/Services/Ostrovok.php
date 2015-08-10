<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractChannelManagerService as Base;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\HttpFoundation\Request;
use MBH\Bundle\ChannelManagerBundle\Document\Service;


/**
 *  ChannelManager service
 */
class Ostrovok extends Base
{

    /**
     * Config class
     */
    const CONFIG = 'OstrovokConfig';

    /**
     * Debug mode on/off
     */
    const TEST = true;

    /**
     * Test url
     */
    const TEST_URL= 'https://extratest.ostrovok.ru';

    /**
     * Test url
     */
    const URL= 'https://ostrovok.ru';

    const SERVICES = [
        1 => 'Buffet breakfast',
        2 => 'Continental breakfast',
        4 => 'American breakfast',
        5 => 'Half board',
        6 => 'Full board',
        7 => 'Breakfast',
        8 => 'Breakfast and Lunch',
        9 => 'Dinner',
        10 => 'Full pansion'

    ];

    /**
     * @var array
     */
    private $params;

    /**
     * @var string
     */
    private $url = self::URL;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->params = $container->getParameter('mbh.channelmanager.services')['ostrovok'];
        !self::TEST ?: $this->url = self::TEST_URL;
    }

    /**
     * {@inheritDoc}
     */
    public function pullOrders()
    {
    }


    /**
     * {@inheritDoc}
     */
    public function closeForConfig(ChannelManagerConfigInterface $config)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function updateRooms(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
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
    public function checkResponse($response, array $params = null)
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
     * @param ChannelManagerConfigInterface $config
     * @param string $url
     * @param array $query
     * @return string
     */
    public function getUrl(ChannelManagerConfigInterface $config, $url, array $query = [])
    {
        $query['hotel'] = $config->getHotelId();
        $query['token'] = $this->params['username'];
        $query['sign'] = $this->signature($query);

        $url = $this->url . $url . '?' . http_build_query($query);

        return $url;
    }

    /**
     * @param array $query
     * @return string
     */
    private function signature(array $query)
    {
        $values = [];
        $query['private'] = $this->params['password'];
        ksort($query);
        foreach ($query as $k => $v) {
            $values[] = $k . '=' . $v;
        }

        return md5(implode(';', $values));
    }

    /**
     * @param array $response
     * @throws Exception
     */
    private function checkErrors($response)
    {
        if (!empty($response['error'])) {
            throw new Exception(
                is_array($response['error']) ? http_build_query($response['error']) : $response['error']
            );
        };
    }


    /**
     * {@inheritDoc}
     */
    public function pullRooms(ChannelManagerConfigInterface $config)
    {
        $response = $this->sendJson(
            $this->getUrl($config, '/echannel/api/v0.1/room_categories/')
        );

        $this->checkErrors($response);

        $rooms = [];
        foreach($response['room_categories']  as $room) {
            $rooms[$room['id']] = $room['name'];
        }

        return $rooms;
    }

    /**
     * {@inheritDoc}
     */
    public function pullTariffs(ChannelManagerConfigInterface $config)
    {
        $response = $this->sendJson(
            $this->getUrl($config, '/echannel/api/v0.1/rates_plans/')
        );

        $this->checkErrors($response);

        $rooms = $this->pullRooms($config);
        $rates = [];
        foreach($response['rates_plans']  as $rate) {
            $rates[$rate['id']] = [
                'title' => $rate['name'],
                'readonly' => false,
                'is_child_rate' => empty($response['parent']) ? false : true,
            ];
            if (!empty($rooms[$rate['room_category']])) {
                $rates[$rate['id']]['title'] .= '<br><small>' . $rooms[$rate['room_category']] . '</small>';
            }
        }

        return $rates;
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
    public function pushResponse(Request $request)
    {

    }
}
