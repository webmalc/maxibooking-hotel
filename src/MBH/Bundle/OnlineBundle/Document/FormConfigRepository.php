<?php

namespace MBH\Bundle\OnlineBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Class RoomRepository
 */
class FormConfigRepository extends DocumentRepository
{
    public function findOneById($id = null)
    {
        $qb = $this->createQueryBuilder();
        if ($id) {
            $qb->field('id')->equals($id);
        }

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @return FormConfig
     */
    public function getForMBSite()
    {
        return $this->findOneBy(['forMbSite' => true]);
    }
}
