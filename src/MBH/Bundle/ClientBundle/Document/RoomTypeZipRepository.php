<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Class ClientConfigRepository
 */
class RoomTypeZipRepository extends DocumentRepository
{
    /**
     * @return RoomTypeZip
     */
    public function fetchConfig()
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $qb = $this->createQueryBuilder('s');
        $config = $qb->getQuery()->getSingleResult();

        if (!$config) {
            $config = new RoomTypeZip;
            $dm = $this->getDocumentManager();
            $dm->persist($config);
            $dm->flush();
        }

        return $config;
    }

}
