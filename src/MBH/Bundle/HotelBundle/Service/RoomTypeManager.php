<?php

namespace MBH\Bundle\HotelBundle\Service;

use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\HotelBundle\Model\RoomTypeRepositoryInterface;
use MBH\Bundle\HotelBundle\Model\RoomTypeInterface;

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
     * @return RoomTypeCategory|RoomType
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
}