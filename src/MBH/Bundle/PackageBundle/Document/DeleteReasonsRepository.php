<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Class DeleteReasonsRepository
 */
class DeleteReasonsRepository extends DocumentRepository
{
   public function getNotDeleted()
   {
       return $this->createQueryBuilder()->field('deletedAt')->equals(null)->getQuery()->execute();
   }

   public function getSelectedReason($deleteReasonId)
   {
       return $this->createQueryBuilder()->field('id')->equals($deleteReasonId)->getQuery()->getSingleResult();
   }
}