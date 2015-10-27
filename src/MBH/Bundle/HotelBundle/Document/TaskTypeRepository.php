<?php

namespace MBH\Bundle\HotelBundle\Document;


use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Class TaskTypeRepository
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class TaskTypeRepository extends DocumentRepository
{
    /**
     * @param TaskTypeCategory $category
     * @return int
     */
    public function getCountByCategory(TaskTypeCategory $category)
    {
        $query = $this->createQueryBuilder()
            ->field('category.id')->equals($category->getId())
            ->count()
            ->getQuery();

        return $query->execute();
    }
}