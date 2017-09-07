<?php


namespace MBH\Bundle\PackageBundle\Document;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;

class DeleteReasonCategoryRepository extends DocumentRepository
{
    public function getEmptyCategory(ArrayCollection $noCategoryDeleteReasons)
    {
        /* @var $dm  DocumentManager */
        $qb = $this->createQueryBuilder();
        $category = $qb
            ->field('fullTitle')->equals('empty')
            ->getQuery()
            ->getSingleResult();

        if (!$category) {
            $category = new DeleteReasonCategory();
            $category
                ->setFullTitle('empty')
                ->setDeleteReasons($noCategoryDeleteReasons);

            $this->getDocumentManager()->persist($category);
            $this->dm->flush();
        }

        return $category;

    }
}