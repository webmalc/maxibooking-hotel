<?php

namespace MBH\Bundle\OnlineBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Class RoomRepository
 */
class FormConfigRepository extends DocumentRepository
{
    /**
     * @param null $id
     * @return array|FormConfig|null|object
     */
    public function findOneById($id = null)
    {
        $qb = $this->createQueryBuilder();
        if ($id) {
            $qb->field('id')->equals($id);
        }

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @return FormConfig|null|object
     */
    public function getForMBSite(): ?FormConfig
    {
        return $this->findOneBy(['forMbSite' => true]);
    }
}
