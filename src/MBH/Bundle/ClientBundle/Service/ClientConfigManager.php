<?php

namespace MBH\Bundle\ClientBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;

class ClientConfigManager
{
    private $dm;

    private $clientConfig;
    private $isClientConfigInit = false;

    public function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    /**
     * @return \MBH\Bundle\ClientBundle\Document\ClientConfig
     */
    public function fetchConfig()
    {
        if (!$this->isClientConfigInit) {
            $this->clientConfig = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();
            $this->isClientConfigInit = true;
        }

        return $this->clientConfig;
    }

    /**
     * @param $disableMode
     */
    public function changeDisableableMode($disableMode)
    {
        $this->fetchConfig()->setIsDisableableOn($disableMode);
        $this->dm->flush();
    }

    /**
     * @param $isCacheValid
     */
    public function changeCacheValidity($isCacheValid)
    {
        $config = $this->fetchConfig();
        $config->setIsCacheValid($isCacheValid);
        $this->dm->flush();
    }
}