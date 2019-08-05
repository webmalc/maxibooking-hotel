<?php

namespace MBH\Bundle\PackageBundle\Services\Search;

use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *  Search with tariffs service
 */
class SearchMultipleDates implements SearchInterface
{
    const MAX_RESULTS = 100;

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
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $this->container->get('doctrine_mongodb')->getManager();
        $this->manager = $container->get('mbh.hotel.room_type_manager');
        $this->memcached = $this->container->get('mbh.cache');
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
        } elseif ($this->manager->getIsUseCategories()) {
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

        $dates = function (\DateTime $date, int $range): array {
            $from = clone $date;
            $to = clone $date;
            $from->modify('- ' . ceil($range / 2) . ' days');
            $to->modify('+ ' . (ceil($range / 2) + 1) . ' days');

            $result = iterator_to_array(new \DatePeriod($from, new \DateInterval('P1D'), $to));
            uasort($result, function ($a, $b) use ($date) {
                $diffA = $date->diff($a)->days;
                $diffB = $date->diff($b)->days;

                if ($diffA == $diffB) {
                    return 0;
                }
                return ($diffA < $diffB) ? -1 : 1;
            });
            return $result;
        };

        $begins = $dates($begin, $range);
        $ends = $dates($end, $range);

        foreach ($begins as $arrival) {
            foreach ($ends as $departure) {
                if ($arrival < $departure) {
                    $result[$arrival->format('Y-m-d') . '_' . $departure->format('Y-m-d')] = [$arrival, $departure];
                }
            }
        }

        return $result;
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
