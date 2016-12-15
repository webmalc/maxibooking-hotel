<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Class PackageSourceRepository
 */
class PackageSourceRepository extends DocumentRepository
{

    /**
     * Get last PackageSource or null
     *
     * @return array|null|object
     */
    public function getLastPackageSource()
    {
        $qb = $this->createQueryBuilder()->sort('createdAt', 'desc')->limit(1)->getQuery()->getSingleResult();

        return $qb ?? null;
    }

}