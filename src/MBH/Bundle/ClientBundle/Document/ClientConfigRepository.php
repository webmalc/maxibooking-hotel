<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Class ClientConfigRepository
 */
class ClientConfigRepository extends DocumentRepository
{
    /**
     * @return ClientConfig
     */
    public function fetchConfig()
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $qb = $this->createQueryBuilder('s');
        $config = $qb->getQuery()->getSingleResult();

        if (!$config) {
            $config = new ClientConfig();
            $dm = $this->getDocumentManager();
            $dm->persist($config);
            $dm->flush();
        }

        return $config;
    }

    /**
     * Check is disableable filter on
     *
     * @return bool
     */
    public function isDisableableOn()
    {
        return $this->fetchConfig()->isIsDisableableOn();
    }

    /**
     * @param $disableMode
     */
    public function changeDisableableMode($disableMode)
    {
        $this->fetchConfig()->setIsDisableableOn($disableMode);
        $this->dm->flush();
    }

}
