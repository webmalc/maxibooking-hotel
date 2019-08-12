<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Class ClientConfigRepository
 */
class ClientConfigRepository extends DocumentRepository
{
    /**
     * @return ClientConfig
     */
    public function fetchConfig(): ClientConfig
    {
        $config = $this->getConfig();

        if (!$config) {
            $config = new ClientConfig();
            $dm = $this->getDocumentManager();
            $dm->persist($config);
            $dm->flush();
        }

        return $config;
    }

    public function getConfig(): ?ClientConfig
    {
        /** @var DocumentManager $qb */
        $qb = $this->createQueryBuilder();

        return $qb->getQuery()->getSingleResult();
    }
}
