<?php

namespace MBH\Bundle\PackageBundle\Services\Search;

use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\ClientBundle\Document\ClientConfig;

/**
 *  Search with tariffs service
 */
class SearchMultipleDates implements SearchInterface
{

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
     * @var ClientConfig;
     */
    protected $config;

    /**
     * @var int
     */
    protected $dates;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $this->container->get('doctrine_mongodb')->getManager();
        $this->config = $this->dm->getRepository('MBHClientBundle:ClientConfig')->findOneBy([]);
        $this->dates = $this->config && $this->config->getSearchDates() ? $this->config->getSearchDates() : 0;
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
     * @throws \Doctrine\ODM\MongoDB\LockException
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
                    $this->container->get('mbh.helper')->toIds($hotel->getRoomTypes()), $roomTypes
                );
            }
        }

        $tariff = $query->tariff;
        if (!empty($query->tariff) && !$query->tariff instanceof Tariff) {
            $tariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->find($query->tariff);
        }
        $results = [];

        foreach ($roomTypes as $roomTypeId) {

            $roomType = $this->dm->getRepository('MBHHotelBundle:RoomType')->find($roomTypeId);

            $dates = $this->getDates($query->begin, $query->end, $roomType, $tariff);

            $q = clone $query;
            foreach ($dates as $pair) {
                $q->begin = $pair[0];
                $q->end = $pair[1];
                $q->roomTypes = [$roomTypeId];
                $q->forceRoomTypes = true;
                $row = $this->search->search($q);
                $results = array_merge($results, $row);

                if ($q->begin == $query->begin && $q->end == $query->end && !empty($row[0]) && $tariff && $row[0]->getTariff() == $tariff) {
                    break;
                }

                if (count($results) > 20) {
                    return $results;
                }
            }
        }

        return $results;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType|null $roomType
     * @param Tariff|null $tariff
     * @param null $count
     * @return mixed
     */
    public function getDates(\DateTime $begin, \DateTime $end, RoomType $roomType = null, Tariff $tariff = null, $count = null)
    {
        $count = is_numeric($count) ? $count : $this->dates;

        /**
         * @param $date
         * @param $count
         * @param RoomType $roomType
         * @param Tariff $tariff
         * @param bool|false $departure
         * @return mixed
         */
        $dates = function ($date, $count, RoomType $roomType = null, Tariff $tariff = null, $departure = false) {
            $result[0] = $date;

            $plus = $minus = 1;
            for ($i = 1; count($result) <= $count; $i++) {
                $new = clone $date;

                if ($i % 2) {
                    $new->modify('-' . $minus . ' day');
                    $minus += 1;
                } else {
                    $new->modify('+' . $plus . ' day');
                    $plus += 1;
                }

                if ($new < new \DateTime('midnight')) {
                    continue;
                }

                if ($tariff && $roomType) {
                    $restriction = $this->dm->getRepository('MBHPriceBundle:Restriction')
                        ->findOneByDate($new, $roomType, $tariff);

                    if ($restriction) {
                        if ($restriction->getCLosed()) {
                            continue;
                        }
                        if ($departure and $restriction->getClosedOnDeparture()) {
                            continue;
                        }
                        if (!$departure and $restriction->getClosedOnArrival()) {
                            continue;
                        }
                    }
                }

                $result[$new->format('U')] = $new;
            }

            ksort($result);
            return $result;
        };

        $departures = $dates($end, $count, $roomType, $tariff, true);
        $arrivals = $dates($begin, $count, $roomType, $tariff);

        foreach ($arrivals as $arrival) {
            foreach ($departures as $departure) {
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
}
