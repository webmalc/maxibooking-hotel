<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Class DeleteReasonRepository
 */
class DeleteReasonRepository extends DocumentRepository
{
    public function findDefault()
    {
        return $this->findOneBy(['isDefault' => true]);
    }
}