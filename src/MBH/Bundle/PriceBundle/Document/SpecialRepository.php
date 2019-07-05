<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Query\Builder;
use MBH\Bundle\BaseBundle\Lib\ClientDataTableParams;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PriceBundle\Lib\SpecialFilter;
use  Doctrine\MongoDB\CursorInterface;

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

        if ($filter->getBegin()) {
            if ($filter->isStrict() && $filter->getEnd()) {
                $qb->field('begin')->equals($filter->getBegin());
            } else {
                $qb->field('end')->gte($filter->getBegin());
            }
        }

        if ($filter->getEnd()) {
            if ($filter->isStrict() && $filter->getBegin()) {
                $qb->field('end')->equals($filter->getEnd());
            } else {
                $qb->field('begin')->lte($filter->getEnd());
            }

        }

        if ($filter->getDisplayFrom()) {
            $qb->field('displayTo')->gte($filter->getDisplayFrom());
        }

        if ($filter->getDisplayTo()) {
            $qb->field('displayFrom')->lte($filter->getDisplayTo());
        }

        if ($filter->getTariff()) {
            $qb->addAnd(
                $qb->expr()->addOr(
                    $qb->expr()->field('tariffs')->exists(false),
                    $qb->expr()->field('tariffs')->size(0),
                    $qb->expr()->field('tariffs')->includesReferenceTo($filter->getTariff())
                )
            );
        }

        /** TODO: Тут бы ввести getIsDisabled, чтоб не путаться */
        if (!$filter->getIsEnabled()) {
            $qb->field('isEnabled')->equals(true);
        }

        if ($filter->getRemain() !== null) {
            if ($filter->getExcludeSpecial()) {
                $qb->addAnd(
                    $qb->expr()->addOr(
                        $qb->expr()->field('remain')->gte($filter->getRemain()),
                        $qb->expr()->field('id')->equals($filter->getExcludeSpecial()->getId())
                    )
                );
            } else {
                $qb->field('remain')->gte($filter->getRemain());
            }
        }

        if ($filter->getRoomType()) {
            $qb->addAnd(
                $qb->expr()->addOr(
                    $qb->expr()->field('roomTypes')->exists(false),
                    $qb->expr()->field('roomTypes')->size(0),
                    $qb->expr()->field('roomTypes')->includesReferenceTo($filter->getRoomType())
                )
            );
        }

        if ($filter->getHotel()) {
            $qb->field('hotel')->references($filter->getHotel());
        }

        if ($filter->getPromotion()) {
            $qb->field('promotion')->references($filter->getPromotion());
        }

        if ($filter->getAdults() && !$filter->getRoomType()) {

            $adults = $filter->getAdults();
            $children = $filter->getChildren();
            if ($children) {
                $children = $this->filterChildren($children, $filter->getChildrenAges(), $filter->getInfantAge());
            }

            $roomTypes = $this->getDocumentManager()->getRepository('MBHHotelBundle:RoomType')->getByCapacity(
                $adults,
                $children
            );
            if (count($roomTypes)) {
                foreach ($roomTypes as $roomType) {
                    $qb->addOr($qb->expr()->field('roomTypes')->includesReferenceTo($roomType));
                }
            } else {
                return $this->createQueryBuilder()->field('roomTypes')->in([]);
            }

        }

        $filteredRoomTypes = $filter->getRoomTypes();
        if (is_array($filteredRoomTypes) && count($filteredRoomTypes)) {
            $exp = $qb->expr();
            foreach ($filter->getRoomTypes() as $roomType) {
                $exp->addOr($qb->expr()->field('roomTypes')->includesReferenceTo($roomType));
            }
            $qb->addAnd($exp);
        }

        $qb->sort(
            [
                'begin' => 'asc',
            ]
        );

        return $qb;
    }


    /**
     * @param SpecialFilter $filter
     * @param ClientDataTableParams $tableFilter
     * @return mixed
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function getTableFiltered(SpecialFilter $filter, ClientDataTableParams $tableFilter)
    {
        $qb = $this->getFilteredQueryBuilder($filter);
        $qb->skip($tableFilter->getStart())
            ->limit($tableFilter->getLength());


        return $qb->getQuery()->execute();
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

    public function getStrictBeginFiltered(SpecialFilter $filter): CursorInterface
    {
        $qb = $this->getFilteredQueryBuilder($filter);
        $qb->addAnd($qb->expr()->field('begin')->gt(new \DateTime("midnight")));
        $qb->field('prices')->exists(true);

        return $qb->getQuery()->execute();
    }


    private function filterChildren(int $children, array $childrenAges, int $infantAge): int
    {
        if (!$children) {
            return 0;
        }
        $result = 0;
        foreach (range(1, $children) as $childValue) {
            $ageIndex = $childValue - 1;
            if (isset($childrenAges[$ageIndex]) && $childrenAges[$ageIndex] <= $infantAge) {
                continue;
            }
            $result++;
        }

        return $result;
    }

    public function fetchSpecialsByRoomTypeByDate(
        \DateTime $begin = null,
        \DateTime $end = null,
        array $roomTypes = [],
        Hotel $hotel
    ) {
        $qb = $this->createQueryBuilder();

        $qb
            ->field('isEnabled')->equals(true)
            ->field('virtualRoom')->exists(true)
            ->field('hotel')->references($hotel);
        if (count($roomTypes)) {
            $qb->field('roomTypes.id')->in($roomTypes);
        }
        if ($begin) {
            $qb->field('end')->gte($begin);
        }
        if ($end) {
            $qb->field('begin')->lte($end);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * @param array $ids
     * @return mixed
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function findByIds(array $ids)
    {
        $qb = $this->createQueryBuilder();
        $qb->field('id')->in($ids);

        return $qb->getQuery()->execute()->toArray();
    }
}
