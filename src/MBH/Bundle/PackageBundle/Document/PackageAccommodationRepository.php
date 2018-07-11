<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 16.12.16
 * Time: 12:09
 */

namespace MBH\Bundle\PackageBundle\Document;


use Doctrine\MongoDB\Cursor;
use Doctrine\ODM\MongoDB\DocumentRepository;

class PackageAccommodationRepository extends DocumentRepository
{
    public function getAccommodationByPeriod(\DateTime $begin = null, \DateTime $end = null)
    {
        if (!$begin) {
            $begin = new \DateTime("now");
        }

        if (!$end) {
            $end = new \DateTime("now");
        }

        $qb = $this->createQueryBuilder()
            ->field('begin')->gte($begin)
            ->field('end')->lte($end);

        return $qb->getQuery()->execute();
    }

    public function getWithAccommodationQB(
        \DateTime $begin = null,
       \DateTime $end = null,
       $rooms = null,
       $excludePackages = null
    ) {
        /** Find PackageAccommodations  */
        $accQb = $this->createQueryBuilder();

        $accQb
            ->field('end')->gte($begin)
            ->field('begin')->lte($end);

        if ($rooms) {
            $rooms = is_array($rooms) ? $rooms : [$rooms];
            $accQb->field('accommodation.id')->in($rooms);
        }

        if ($excludePackages) {
            $excludedAccommodationIds = [];
            if (!is_array($excludePackages)) {
                $excludePackages = [$excludePackages];
            }
            foreach ($excludePackages as $excludePackage) {
                foreach ($excludePackage->getAccommodations() as $accommodation) {
                    $excludedAccommodationIds[] = $accommodation->getId();
                }
            }
            $accQb->field('id')->notIn($excludedAccommodationIds);
        }

        return $accQb;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param null $rooms
     * @param Package[] $excludePackages
     * @param boolean $departure
     * @return mixed
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function fetchWithAccommodation(
        \DateTime $begin = null,
        \DateTime $end = null,
        $rooms = null,
        $excludePackages = null,
        $departure = true
    )
    {
        $accQb = $this->getWithAccommodationQB($begin, $end, $rooms, $excludePackages);

        return $accQb->getQuery()->execute();
    }

    public function getAccommodationByDate(\DateTime $dateTime)
    {
        return $this->getAccommodationByPeriod($dateTime, $dateTime);
    }

    /**
     * @param array $roomsIds
     * @param bool $returnIds
     * @return Cursor
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function getByRoomsIds(array $roomsIds, $returnIds = false)
    {
        $qb = $this
            ->createQueryBuilder()
            ->field('accommodation.id')->in($roomsIds);
        if ($returnIds) {
            $qb->distinct('id');
        }

        return $qb
            ->getQuery()
            ->execute();
    }
}