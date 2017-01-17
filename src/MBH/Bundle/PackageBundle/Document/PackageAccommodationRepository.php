<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 16.12.16
 * Time: 12:09
 */

namespace MBH\Bundle\PackageBundle\Document;


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
        array $excludePackages = null,
        $departure = true
    )
    {
        /** Find PackageAccommodations  */
        $accQb = $this->createQueryBuilder();

        $accQb
            ->field('end')->gte($begin)
            ->field('begin')->lte($end);

        if ($rooms) {
            is_array($rooms) ? $rooms : $rooms = [$rooms];
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

        //$qb->sort('begin', 'asc');

        return $accQb->getQuery()->execute();
    }

    public function getAccommodationByDate(\DateTime $dateTime)
    {
        return $this->getAccommodationByPeriod($dateTime, $dateTime);
    }
}