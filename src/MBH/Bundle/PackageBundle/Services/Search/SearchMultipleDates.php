<?php

namespace MBH\Bundle\PackageBundle\Services\Search;

use MBH\Bundle\BaseBundle\Lib\AdditionalDatesException;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *  Search with tariffs service
 */
class SearchMultipleDates implements SearchInterface
{
    const MAX_RESULTS = 100;

    const MAX_ADDITIONAL_PERIOD = 10;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var SearchInterface
     */
    protected $search;

    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $dm;

    /**
     * @var \MBH\Bundle\BaseBundle\Service\Cache
     */
    private $memcached;

    /**
     * @var RoomTypeManager
     */
    private $manager;


    /**
     * @var array
     */
    private $inOut;
    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $this->container->get('doctrine_mongodb')->getManager();
        $this->manager = $container->get('mbh.hotel.room_type_manager');
        $this->memcached = $this->container->get('mbh.cache');
        $this->inOut = $this->dm->getRepository('MBHPriceBundle:Restriction')->fetchInOut($this->memcached);
    }

    /**
     * @param SearchInterface $search
     * @return $this
     */
    public function setSearch(SearchInterface $search)
    {
        $this->search = $search;

        return $this;
    }

    /**
     * @param SearchQuery $query
     * @return array
     * @throws Exception
     */
    public function search(SearchQuery $query)
    {
        if (!$this->search) {
            throw new Exception('SearchInterface $search is null.');
        }
        $roomTypes = $query->roomTypes;
        if (empty($roomTypes)) {
            foreach ($this->dm->getRepository('MBHHotelBundle:Hotel')->findAll() as $hotel) {
                $roomTypes = array_merge(
                    $this->container->get('mbh.helper')->toIds($hotel->getRoomTypes()),
                    $roomTypes
                );
            }
        } elseif ($this->manager->useCategories) {
            $roomTypes = [];
            foreach ($query->roomTypes as $catId) {
                $cat = $this->dm->getRepository('MBHHotelBundle:RoomTypeCategory')->find($catId);
                if ($cat) {
                    $roomTypes = array_merge($this->container->get('mbh.helper')->toIds($cat->getTypes()), $roomTypes);
                }
            }
            $query->roomTypes = count($roomTypes) ? $roomTypes : [0];
        }

        $tariff = $query->tariff;
        if (!empty($query->tariff) && !$query->tariff instanceof Tariff) {
            $tariff = $this->dm->getRepository('MBHPriceBundle:Tariff')
                ->fetchById($query->tariff, $this->memcached);
        }
        $results = [];

        $dates = $this->getDates(clone $query->begin, clone $query->end, $query->range, null, $tariff);

        $q = clone $query;
        foreach ($dates as $pair) {
            $q->begin = $pair[0];
            $q->end = $pair[1];
            $q->roomTypes = $roomTypes;
            $q->forceRoomTypes = true;
            $row = $this->search->search($q);
            $results = array_merge($results, $row);

            if ($q->begin == $query->begin && $q->end == $query->end && !empty($row[0]) && $tariff && $row[0]->getTariff() == $tariff) {
                break;
            }

            if (count($results) > self::MAX_RESULTS) {
                return $results;
            }
        }
        return $results;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param int $range
     * @param RoomType|null $roomType
     * @param Tariff|null $tariff
     * @return array
     */
    public function getDates(\DateTime $begin, \DateTime $end, int $range, RoomType $roomType = null, Tariff $tariff = null)
    {
        $result = [];
        $begins = $this->searchDaysByRestriction($begin, $range);
        $ends = $this->searchDaysByRestriction($end, $range);

        foreach ($begins as $arrival) {
            foreach ($ends as $departure) {
                if ($arrival < $departure) {
                    $result[$arrival->format('Y-m-d') . '_' . $departure->format('Y-m-d')] = [$arrival, $departure];
                }
            }
        }

        return $result;
    }

    private function searchDaysByRestriction(\DateTime $date, int $range, string $direction = null): array
    {
        $dates = [];
        //Для азовского все ограничения одинаковы. Берем первый попавшийся массив
        $restrictions = $this->inOut[array_keys($this->inOut)[0]];

        if (!$direction) {
            $dates = array_merge($dates, $this->searchDaysByRestriction($date, $range, 'up'));
            $dates = array_merge($dates, $this->searchDaysByRestriction($date, $range, 'down'));
            //Date of query if not in restriction
            if (!in_array($date->format('d.m.Y'), $restrictions)) {
                $dates = array_merge($dates, [$date]);
            }

            uasort($dates, function ($a, $b) use ($date) {
                $diffA = $date->diff($a)->days;
                $diffB = $date->diff($b)->days;

                if ($diffA == $diffB) {
                    return 0;
                }
                return ($diffA < $diffB) ? -1 : 1;
            });

            return $dates;
        }

        $directions = [ 'up' => '+', 'down' => '-'];
        if (!in_array($direction, array_keys($directions))) {
            throw new AdditionalDatesException('There is incorrect search days by restriction direction');
        }

        //First array element
        $newDate = clone($date);
        $loop = 0;
        while (0 != $range) {
            $loop++;
            $newDate->modify($directions[$direction].' 1 day');
            if (!in_array($newDate->format('d.m.Y'), array_keys($restrictions))) {
                $dates[] = clone($newDate);
                $range--;
            }
            if (self::MAX_ADDITIONAL_PERIOD < $loop) {
                break;
            }
        }

        return $dates;
    }

    /**
     * @param SearchQuery $query
     * @return array
     */
    public function searchTariffs(SearchQuery $query)
    {
        return $this->search->searchTariffs($query);
    }

    /**
     * @param SearchQuery $query
     * @return array
     */
    public function searchSpecials(SearchQuery $query)
    {
        return $this->search->searchSpecials($query);
    }
}
