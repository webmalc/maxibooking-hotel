<?php


namespace MBH\Bundle\RestaurantBundle\Document;


use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Query\Builder;
use MBH\Bundle\HotelBundle\Document\Hotel;

class DishOrderItemRepository extends DocumentRepository
{
    public function findByQueryCriteria(DishOrderCriteria $criteria, $offset = 0, $limit = 10, $sort = ['id', -1], Hotel $hotel = null)
    {
        $queryBuilder = $this->queryCriteriaToBuilder($criteria);
        if ($hotel) {
            $queryBuilder->field('hotel.id')->equals($hotel->getId());
        }
        $queryBuilder
            ->skip($offset)
            ->limit($limit)
            ->sort($sort[0], $sort[1])
        ;

        $orderItems = $queryBuilder->getQuery()->execute();
        return $orderItems;
    }


    /**
     * @param $criteria
     * @return Builder
     */
    private function queryCriteriaToBuilder(DishOrderCriteria $criteria)
    {
        $queryBuilder = $this->createQueryBuilder();

        if ($criteria->search) {

            if (is_numeric($criteria->search)) {
                $queryBuilder->field('id')->equals($criteria->search);
            }
        }

        if ($criteria->begin) {
            $queryBuilder->field('createdAt')->gte($criteria->begin);
        }
        if ($criteria->end) {
            $queryBuilder->field('createdAt')->lte($criteria->endWholeDay());
        }
        if ($criteria->isFreezed === true || $criteria->isFreezed === false) {
            $queryBuilder->field('isFreezed')->equals($criteria->isFreezed);
        }


        return $queryBuilder;
    }
}