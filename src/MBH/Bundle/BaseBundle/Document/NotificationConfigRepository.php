<?php


namespace MBH\Bundle\BaseBundle\Document;


use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;

class NotificationConfigRepository extends DocumentRepository
{
    public function fetchConfig()
    {
        /* @var $dm  DocumentManager */
        $qb = $this->createQueryBuilder();
        $config = $qb->getQuery()->getSingleResult();
        if (!$config) {
            $config = new NotificationConfig();
            $dm = $this->getDocumentManager();
            $dm->persist($config);
            $dm->flush();
        }

        return $config;
    }
}