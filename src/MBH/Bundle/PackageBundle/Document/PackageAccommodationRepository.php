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

    public function getAccommodationByDate(\DateTime $dateTime)
    {
        return $this->getAccommodationByPeriod($dateTime, $dateTime);
    }
}