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
     * @param \DateTime|null $date
     * @return self
     */
    public function remove(\DateTime $date = null): self
    {
        $builder = $this->createQueryBuilder()
            ->updateMany()
            ->field('deletedAt')->set(new \DateTime());

        if ($date) {
            $builder->field('createdAt')->lte($date);
        }
        $builder->getQuery()
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
