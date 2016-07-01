<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 01.07.16
 * Time: 14:02
 */

namespace MBH\Bundle\RestaurantBundle\Document;


use Doctrine\ODM\MongoDB\DocumentRepository;

class IngredientRepository extends DocumentRepository
{
    public function findIsEnabled()
    {
        return $this->createQueryBuilder()
            ->field('isEnabled')->equals('true')
            ->getQuery()
            ->execute();
    }
}