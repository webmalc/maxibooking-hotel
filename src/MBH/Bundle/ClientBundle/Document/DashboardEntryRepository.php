<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Cursor;

/**
 * Class DashboardEntryRepository
 */
class DashboardEntryRepository extends DocumentRepository
{

    /**
     * @param \DateTime $date
     * @return self
     */
    public function remove(\DateTime $date): self
    {
        $this->createQueryBuilder()
            ->updateMany()
            ->field('deletedAt')->set(new \DateTime())
            ->field('createdAt')->lte($date)
            ->getQuery()
            ->execute();

        return $this;
    }

    /**
     * @return Cursor
     */
    public function findNew(): Cursor
    {
        return $this->createQueryBuilder()
            ->field('confirmedAt')->equals(null)
            ->getQuery()
            ->execute();
    }
}
