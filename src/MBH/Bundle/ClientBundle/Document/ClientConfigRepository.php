<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Class ClientConfigRepository
 */
class ClientConfigRepository extends DocumentRepository
{
    private $clientConfig;

    /**
     * @return ClientConfig
     */
    public function fetchConfig()
    {
        if (is_null($this->clientConfig)) {
            /** @var DocumentManager $qb */
            $qb = $this->createQueryBuilder('s');
            $config = $qb->getQuery()->getSingleResult();

            if (!$config) {
                $config = new ClientConfig();
                $dm = $this->getDocumentManager();
                $dm->persist($config);
                $dm->flush();
            }
            $this->clientConfig = $config;
        }

        return $this->clientConfig;
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
