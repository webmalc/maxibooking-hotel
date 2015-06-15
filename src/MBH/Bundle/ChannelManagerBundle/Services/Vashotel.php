<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractChannelManagerService as Base;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;

/**
 *  ChannelManager service
 */
class Vashotel extends Base
{

    /**
     * Config class
     */
    const CONFIG = 'VashotelConfig';

    /**
     * Base API URL
     */
    const BASE_URL = 'https://www.dev.vashotel.ru/hotel_xml/';

    /**
     * Get roomTypes & tariffs template file
     */
    const GET_TEMPLATE = 'MBHChannelManagerBundle:Vashotel:get.xml.twig';

    /**
     * Update rooms template
     */
    const UPDATE_ROOMS_TEMPLATE = 'MBHChannelManagerBundle:Vashotel:updateRooms.xml.twig';

    /**
     * Close all rooms
     */
    const CLOSE_TEMPLATE = 'MBHChannelManagerBundle:Vashotel:close.xml.twig';

    /**
     * @var array
     */
    private $params;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->params = $container->getParameter('mbh.channelmanager.services')['vashotel'];
    }

    /**
     * {@inheritdoc}
     */
    public function createPackages()
    {
        return $this->pullOrders();
    }


    /**
     * {@inheritdoc}
     */
    public function update(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $this->updateRooms($begin, $end, $roomType);
        $this->updatePrices($begin, $end, $roomType);
        $this->updateRestrictions($begin, $end, $roomType);

        return true;
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

        if ($script) {
            $string = $script.';'.$string;
        }
        if ($key) {
            $string .= $key;
        }

        return $string;
    }

    /**
     * @param array $fields
     * @param boolean $dev
     * @return string
     */
    private function getStringFromXmlArray(array $fields, $dev = false)
    {
        $string = '';
        foreach ($fields as $field) {
            if (is_array($field['value'])) {
                $string .= $this->getStringFromXmlArray($field['value'], $dev);
            } else {
                $string .= ($dev) ? $field['name'].'-'.$field['value'].';' : $field['value'].';';
            }
        }

        return $string;
    }

    /**
     * @param array $fields
     * @return array
     */
    private function sortXmlArray(array $fields)
    {
        usort(
            $fields,
            function ($a, $b) {
                return ($a['name'] < $b['name']) ? -1 : 1;
            }
        );
        foreach ($fields as $key => $field) {
            if (is_array($field['value'])) {
                $fields[$key]['value'] = $this->sortXmlArray($field['value']);
                $result[] = $this->sortXmlArray($field['value']);
            }
        }

        return $fields;
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

            $count = 'o';

            foreach ($fields as $field) {
                if (preg_match('/'.$child->getName().'_sort_number_[o]*$/iu', $field['name'])) {
                    $count .= 'o';
                }
            }

            if ($child->count()) {
                $fields[] = [
                    'name' => $child->getName().'_sort_number_'.$count,
                    'value' => $this->getXmlFieldsAsArray($child)
                ];
            } else {
                $fields[] = [
                    'name' => $child->getName().'_sort_number_'.$count,
                    'value' => (string)$child
                ];
            }
        }

        return $fields;
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
            $this->params['password']
        );
        $data['sig'] = md5($sig);

        $response = $this->sendXml(static::BASE_URL.$script, $this->templating->render(static::GET_TEMPLATE, $data));

        if (!$this->checkResponse($response, ['script' => $script, 'key' => $this->params['password']])) {
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
    public function pullTariffs(ChannelManagerConfigInterface $config)
    {
        $script = 'get_rates.php';
        $salt = $this->helper->getRandomString(20);
        $data = ['config' => $config, 'salt' => $salt, 'sig' => null];

        $sig = $this->getSignature(
            $this->templating->render(static::GET_TEMPLATE, $data),
            $script,
            $this->params['password']
        );
        $data['sig'] = md5($sig);

        $response = $this->sendXml(static::BASE_URL.$script, $this->templating->render(static::GET_TEMPLATE, $data));

        if (!$this->checkResponse($response, ['script' => $script, 'key' => $this->params['password']])) {
            return [];
        }
        $result = [
            0 => 'Standard rate'
        ];

        foreach ($response->xpath('rate') as $rate) {
            $result[(int)$rate->id] = (string)$rate->name;
        }

        return $result;
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
    public function closeAll()
    {
        $result = false;

        foreach ($this->getConfig() as $config) {
            $result = $this->closeForConfig($config);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function closeForConfig(ChannelManagerConfigInterface $config)
    {
        //foreach ($this->pullTariffs($config) as $tariffId => $tariff) {
        $script = 'set_availability.php';
        $salt = $this->helper->getRandomString(20);
        $data = [
            'config' => $config,
            'salt' => $salt,
            'sig' => null,
            'rooms' => $this->pullRooms($config),
            'rate' => 0
        ];

        $sig = $this->getSignature(
            $this->templating->render(static::CLOSE_TEMPLATE, $data),
            $script,
            $this->params['password']
        );
        $data['sig'] = md5($sig);

        $response = $this->sendXml(static::BASE_URL.$script, $this->templating->render(static::CLOSE_TEMPLATE, $data));

        return $this->checkResponse($response, ['script' => $script, 'key' => $this->params['password']]);
        //}
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
    public function updateRooms(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = false;
        $script = 'set_availability.php';

        // iterate hotels
        foreach ($this->getConfig() as $config) {

            $salt = $this->helper->getRandomString(20);
            $data = ['config' => $config, 'salt' => $salt, 'sig' => null];
            $roomTypes = $this->getRoomTypes($config);

            //roomCache
            $roomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')->fetch(
                $begin, $end, $config->getHotel(), $roomType ? [$roomType->getId()] : [], null
            );
            if (!$roomCaches->count()) {
                continue;
            }

            //group caches
            foreach ($roomCaches as $roomCache) {
                $roomType = $roomCache->getRoomType();
                isset($roomTypes[$roomType->getId()]) ? $roomTypeSyncId = $roomTypes[$roomType->getId()]['syncId'] : $roomTypeSyncId = null;
                $formattedDate = $roomCache->getDate()->format('Y-m-d');

                if ($roomTypeSyncId) {
                    $data['rooms'][$roomTypeSyncId][$formattedDate] = [
                        'sellquantity' => $roomCache->getLeftRooms(),
                        'closed' => $roomCache->getIsClosed()
                    ];
                }
            }

            //dump($this->templating->render(static::UPDATE_ROOMS_TEMPLATE, $data)); exit();

            $sig = $this->getSignature(
                $this->templating->render(static::UPDATE_ROOMS_TEMPLATE, $data),
                $script,
                $this->params['password']
            );
            $data['sig'] = md5($sig);

            $response = $this->send(static::BASE_URL.$script, $this->templating->render(static::UPDATE_ROOMS_TEMPLATE, $data));

            dump($response); exit();

            //return $this->checkResponse($response, ['script' => $script, 'key' => $this->params['password']]);

        }
    }

    /**
     * {@inheritDoc}
     */
    public function updateRestrictions(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
    }
}
