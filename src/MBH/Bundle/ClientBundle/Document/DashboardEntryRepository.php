<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

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
}
