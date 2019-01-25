<?php

namespace MBH\Bundle\HotelBundle\Service;

use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Model\RoomTypeInterface;
use MBH\Bundle\HotelBundle\Model\RoomTypeRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RoomTypeManager

 */
class RoomTypeManager
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $dm;

    /**
     * @var ClientConfig;
     */
    private $config;

    /**
     * @var bool
     */
    public $useCategories = false;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $this->container->get('doctrine_mongodb')->getManager();
        $this->config = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();
        $this->useCategories = $this->config && $this->config ->getUseRoomTypeCategory();
    }

    /**
     * @return RoomTypeRepositoryInterface
     */
    public function getRepository()
    {
        $repoName = $this->useCategories ? 'MBHHotelBundle:RoomTypeCategory' : 'MBHHotelBundle:RoomType';

        return $this->dm->getRepository($repoName);
    }

    /**
     * @param Hotel $hotel
     * @param array $rooms
     * @return mixed
     */
    public function getRooms(Hotel $hotel = null, $rooms = null)
    {
        $repo = $this->getRepository();

        return $repo->fetch($hotel, $rooms);
    }


    /**
     * @param $id
     * @return RoomTypeInterface
     */
    public function findRoom($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * Get room types sorted by keys ['hotel', 'rooms']
     *
     * @return array
     */
    public function getSortedByHotels()
    {
        $roomTypes = $this->getRooms();
        $result = [];

        /** @var RoomTypeInterface $roomType */
        foreach ($roomTypes as $roomType) {
            isset($result[$roomType->getHotel()->getId()])
                ? $result[$roomType->getHotel()->getId()]['rooms'][] = $roomType
                : $result[$roomType->getHotel()->getId()] = ['hotel' => $roomType->getHotel(), 'rooms' => [$roomType]];
        }

        return $result;
    }
}