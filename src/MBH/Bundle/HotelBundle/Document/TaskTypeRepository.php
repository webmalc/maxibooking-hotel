<?php

namespace MBH\Bundle\HotelBundle\Document;


use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Class TaskTypeRepository
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
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

    public function getOptCategoryGroupList()
    {
        $categoryRepository = $this->dm->getRepository('MBHHotelBundle:TaskTypeCategory');
        $categories = $categoryRepository->findAll();
        $list = [];
        foreach($categories as $category) {
            /** @var TaskType[] $types */
            $types = $this->createQueryBuilder()
                ->field('category.id')->equals($category->getId())
                ->select(['title'])
                ->getQuery()->execute();

            $typeList = [];
            foreach($types as $type) {
                $typeList[$type->getId()] = $type->getTitle();
            }

            $list[$category->getTitle()] = $typeList;
        }

        return $list;
    }
}