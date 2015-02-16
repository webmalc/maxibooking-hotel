<?php

namespace MBH\Bundle\PackageBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;

/**
 *  Search service
 */
class Search
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Doctrine\Bundle\MongoDBBundle\ManagerRegistry
     */
    protected $dm;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
    }

    /**
     * @param \MBH\Bundle\PackageBundle\Lib\SearchQuery $query
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult[]
     */
    public function search(SearchQuery $query)
    {
        $groupedCaches = $caches = $results = $groupedCachesMin = [];

        if (empty($query->end) || empty($query->begin)) {
            return $results;
        }

        $qb = $this->dm->getRepository('MBHPackageBundle:RoomCache')
            ->createQueryBuilder('q')
            ->field('date')->lt($query->end)
            ->field('date')->gte($query->begin)
            ->field('places')->gte($query->adults + $query->children)
            ->field('rooms')->gt(0)
            ->sort('date', 'asc');
        if (!empty($query->tariff)) {

            if ($query->tariff instanceof \MBH\Bundle\PriceBundle\Document\Tariff) {
                $qb->field('tariff.id')->equals($query->tariff->getId());
            } else {
                $qb->field('tariff.id')->equals($query->tariff);
            }
        } else {
            $qb->field('isDefault')->equals(true);
        }
        if($query->isOnline) {
            $qb->field('isOnline')->equals(true);
        }

        if (!empty($query->roomTypes)) {
            $qb->field('roomType.id')->in($query->roomTypes);
        }

        $caches = $qb->getQuery()->execute();

        //Group cache
        foreach ($caches as $cache) {

            $groupedCaches[$cache->getRoomType()->getId()][] = $cache;

            if (!isset($groupedCachesMin[$cache->getRoomType()->getId()])) {
                $groupedCachesMin[$cache->getRoomType()->getId()] = $cache->getRooms();
            }
            if ($groupedCachesMin[$cache->getRoomType()->getId()] > $cache->getRooms()) {
                $groupedCachesMin[$cache->getRoomType()->getId()] = $cache->getRooms();
            }

        }

        //Delete short cache chains
        foreach ($groupedCaches as $key => $groupedCache) {

            if ($query->end->diff($query->begin)->format("%a") != count($groupedCache)) {
                unset($groupedCaches[$key]);
            }
        }

        //Generate result
        foreach ($groupedCaches as $key => $groupedCache) {

            if ($query->end->diff($query->begin)->format("%a") != count($groupedCache)) {
                continue;
            }

            $firstCache = array_values($groupedCache)[0];
            $lastDate = clone array_slice($groupedCache, -1)[0]->getDate();

            $result = new SearchResult();

            $tourists = $firstCache->getRoomType()->getAdultsChildrenCombination($query->adults, $query->children);

            $result->setBegin($firstCache->getDate())
                ->setEnd($lastDate->modify('+1 day'))
                ->setRoomType($firstCache->getRoomType())
                ->setTariff($firstCache->getTariff())
                ->setRoomsCount($groupedCachesMin[$key])
                ->setAdults($tourists['adults'])
                ->setChildren($tourists['children'])
            ;

            // delete  too short & long results
            if ($result->getTariff()->getMinPackageDuration(true) && $result->getNights() < $result->getTariff()->getMinPackageDuration(true)) {
                continue;
            }
            if ($result->getTariff()->getMaxPackageDuration(true) && $result->getNights() > $result->getTariff()->getMaxPackageDuration(true)) {
                continue;
            }

            // delete results with disabled tariffs
            if (!$result->getTariff()->getIsEnabled()) {
                continue;
            }

            // check hotel permission
            if (!$query->isOnline && !$this->container->get('mbh.hotel.selector')->checkPermissions($result->getRoomType()->getHotel())) {
                continue;
            }

            //Set foods & prices
            foreach ($groupedCache as $cache) {

                foreach ($cache->getPrices() as $price) {
                    $result->addFood($price->getFood(), $price->getPrice())
                           ->addPrice($price->getFood(), $price->getPrice(), $price->getAdults(), $price->getChildren());
                }
            }

            if(empty($result->getPrices())) {
                continue;
            }

            $results[] = $result;
        }

        return $results;
    }

    public function searchTariffs(SearchQuery $query)
    {
        $qb = $this->dm->getRepository('MBHPriceBundle:Tariff')
            ->createQueryBuilder('q')
            ->field('isEnabled')->equals(true)
            ->sort('begin', 'asc')
        ;

        if (!empty($query->roomTypes)) {
            $roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')
                ->createQueryBuilder('r')
                ->field('id')->in($query->roomTypes)
                ->getQuery()
                ->execute();

            $hotelsIds = [];
            foreach ($roomTypes as $roomType) {
                $hotelsIds[] = $roomType->getHotel()->getId();
            }
            $qb->field('hotel.id')->in($hotelsIds);
        }

        if($query->isOnline) {
            $qb->field('isOnline')->equals(true);
        }

        $qb->addOr(
            $qb->expr()
                ->field('end')->gte($query->begin)
                ->field('begin')->lte($query->begin)
        );
        $qb->addOr(
            $qb->expr()
                ->field('end')->gte($query->end)
                ->field('begin')->lte($query->end)
        );
        $qb->addOr(
            $qb->expr()
                ->field('end')->lte($query->end)
                ->field('begin')->gte($query->begin)
        );

        $tariffs =  $qb->getQuery()->execute();

        $results = [];

        foreach($tariffs as $tariff) {
            if (!$query->isOnline && !$this->container->get('mbh.hotel.selector')->checkPermissions($tariff->getHotel())) {
                continue;
            }
            $results[] = $tariff;
        }

        return $results;
    }

}
