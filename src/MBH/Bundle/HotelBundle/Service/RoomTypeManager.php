<?php

namespace MBH\Bundle\HotelBundle\Service;

use MBH\Bundle\BillingBundle\Lib\Model\BillingRoom;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
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
        $this->config = $this->container->get('mbh.client_config_manager')->fetchConfig();
        $this->useCategories = $this->config && $this->config->getUseRoomTypeCategory();
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
     * @param BillingRoom $billingRoom
     * @param Hotel $hotel
     * @param bool $withRooms
     * @return RoomType
     */
    public function createByBillingRoom(BillingRoom $billingRoom, Hotel $hotel, bool $withRooms)
    {
        $roomType = new RoomType();
        $roomType
            ->setHotel($hotel)
            ->setFullTitle($billingRoom->getName())
            ->setPlaces(2)
            ->setAdditionalPlaces(0);

        $this->dm->persist($roomType);

        if ($withRooms) {
            for ($i = 1; $i <= $billingRoom->getRooms(); $i++) {
                $room = (new Room())
                    ->setRoomType($roomType)
                    ->setHotel($hotel)
                    ->setFullTitle($i);

                $this->dm->persist($room);
            }
        }

        return $roomType;
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