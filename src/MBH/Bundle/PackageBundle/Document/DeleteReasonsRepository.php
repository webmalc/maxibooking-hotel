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
}