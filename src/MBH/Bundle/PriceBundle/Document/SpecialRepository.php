<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Query\Builder;
use MBH\Bundle\PriceBundle\Lib\SpecialFilter;
use  Doctrine\MongoDB\CursorInterface;

class SpecialRepository extends DocumentRepository
{

    /**
     * @param SpecialFilter $filter
     * @return Builder
     */
    public function getFilteredQueryBuilder(SpecialFilter $filter): Builder
    {
        $qb = $this->createQueryBuilder();

        if ($filter->getBegin()) {
            $qb->field('end')->gte($filter->getBegin());
        }

        if ($filter->getEnd()) {
            $qb->field('begin')->lte($filter->getEnd());
        }

        if ($filter->getTariff()) {
            $qb->addAnd($qb->expr()->addOr(
                $qb->expr()->field('tariffs')->exists(false),
                $qb->expr()->field('tariffs')->size(0),
                $qb->expr()->field('tariffs')->includesReferenceTo($filter->getTariff())
            ));
        }

        if (!$filter->getIsEnabled()) {
            $qb->field('isEnabled')->equals(true);
        }

        if ($filter->getRoomType()) {
            $qb->addAnd($qb->expr()->addOr(
                $qb->expr()->field('roomTypes')->exists(false),
                $qb->expr()->field('roomTypes')->size(0),
                $qb->expr()->field('roomTypes')->includesReferenceTo($filter->getRoomType())
            ));
        }

        if ($filter->getHotel()) {
            $qb->field('hotel')->references($filter->getHotel());
        }

        return $qb;
    }

    /**
     * @param SpecialFilter $filter
     * @return CursorInterface
     */
    public function getFiltered(SpecialFilter $filter): CursorInterface
    {
        $qb = $this->getFilteredQueryBuilder($filter);

        return $qb->getQuery()->execute();
    }
}
