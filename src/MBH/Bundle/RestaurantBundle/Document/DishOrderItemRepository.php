<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 11.07.16
 * Time: 12:57
 */

namespace MBH\Bundle\RestaurantBundle\Document;


use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Query\Builder;
use MBH\Bundle\HotelBundle\Document\Hotel;

class DishOrderItemRepository extends DocumentRepository
{
    public function findByQueryCriteria(DishOrderCriteria $criteria, $offset = 0, $limit = 10, Hotel $hotel = null)
    {
        $queryBuilder = $this->queryCriteriaToBuilder($criteria);
        if ($hotel) {
            $queryBuilder->field('hotel.id')->equals($hotel->getId());
        }
        $queryBuilder
            ->skip($offset)
            ->limit($limit)
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

//        if ($criteria->search) {
//            $fullNameRegex = new \MongoRegex('/.*' . $criteria->search . '.*/ui');
//            if(is_numeric($criteria->search)) {
//                $queryBuilder->field('id')->equals($fullNameRegex);
//            } else {
//                $queryBuilder->field('fullName')->equals($fullNameRegex);
//            }
//        }

        if ($criteria->begin) {
            $queryBuilder->field('createdAt')->gte($criteria->begin);
        }
        if ($criteria->end) {
            $queryBuilder->field('createdAt')->lte($criteria->end);
        }
        if ($criteria->isFreezed === true || $criteria->isFreezed === false) {
            $queryBuilder->field('isFreezed')->equals($criteria->isFreezed);
        }
        //TODO: Решить вопрос - как быть и что делать ?
        /*if ($criteria->moneyBegin) {
            $queryBuilder->
            $queryBuilder->field('price')->gte($criteria->moneyBegin);
        }
        if ($criteria->moneyEnd) {
            $queryBuilder->field('price')->lte($criteria->moneyEnd);
        }*/

        return $queryBuilder;
    }
}