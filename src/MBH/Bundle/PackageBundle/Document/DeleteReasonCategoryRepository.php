<?php


namespace MBH\Bundle\PackageBundle\Document;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;

class DeleteReasonCategoryRepository extends DocumentRepository
{
    const DEFAULT_CATEGORY = "По-умолчанию";

    public function getEmptyCategory(ArrayCollection $noCategoryDeleteReasons)
    {
        /* @var $dm  DocumentManager */
        $qb = $this->createQueryBuilder();
        $category = $qb
            ->field('fullTitle')->equals(self::DEFAULT_CATEGORY)
            ->getQuery()
            ->getSingleResult();

        if (!$category) {
            $category = new DeleteReasonCategory();
            $category
                ->setFullTitle(self::DEFAULT_CATEGORY)
                ->setDeleteReasons($noCategoryDeleteReasons);

            $this->getDocumentManager()->persist($category);
            $this->dm->flush();
        }

        return $category;

    }
}