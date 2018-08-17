<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Query\Builder;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PriceBundle\Lib\SpecialFilter;
use Doctrine\MongoDB\CursorInterface;

class SpecialRepository extends DocumentRepository
{
    /**
     * @param Special $special
     * @param Package $exclude
     */
    public function recalculate(Special $special, Package $exclude = null)
    {
        $dm = $this->getDocumentManager();

        $sold = $dm
            ->getRepository('MBHPackageBundle:Package')
            ->countBySpecial($special, $exclude);
        $special->setSold($sold);

        $dm->persist($special);
        $dm->flush();
    }

    /**
     * @param SpecialFilter $filter
     * @return Builder
     */
    public function getFilteredQueryBuilder(SpecialFilter $filter): Builder
    {
        $qb = $this->createQueryBuilder();

        if (!$filter->showDeleted()) {
            $qb->field('deletedAt')->exists(false);
        } else {
            $qb->addOr($qb->expr()->field('deletedAt')->exists(false));
            $qb->addOr($qb->expr()->field('deletedAt')->exists(true));
        }

        if ($filter->getBegin()) {
            $qb->field('end')->gte($filter->getBegin());
        }

        if ($filter->getEnd()) {
            $qb->field('begin')->lte($filter->getEnd());
        }

        if ($filter->getDisplayFrom()) {
            $qb->field('displayTo')->gte($filter->getDisplayFrom());
        }

        if ($filter->getDisplayTo()) {
            $qb->field('displayFrom')->lte($filter->getDisplayTo());
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

        if ($filter->getRemain() !== null) {
            if ($filter->getExcludeSpecial()) {
                $qb->addAnd($qb->expr()->addOr(
                    $qb->expr()->field('remain')->gte($filter->getRemain()),
                    $qb->expr()->field('id')->equals($filter->getExcludeSpecial()->getId())
                ));
            } else {
                $qb->field('remain')->gte($filter->getRemain());
            }
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
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function getFiltered(SpecialFilter $filter): CursorInterface
    {
        $qb = $this->getFilteredQueryBuilder($filter);

        return $qb->getQuery()->execute();
    }
}
