<?php

namespace MBH\Bundle\ClientBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\ClientBundle\Document\ClientConfig;

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
            $this->clientConfig = $this->dm->getRepository(ClientConfig::class)->fetchConfig();
            $this->isClientConfigInit = true;
        }

        return $this->clientConfig;
    }

    public function getClientConfig(): ?ClientConfig
    {
        return $this->dm->getRepository(ClientConfig::class)->getConfig();
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
     * @return \MBH\Bundle\ClientBundle\Document\ClientConfig
     */
    public function changeCacheValidity($isCacheValid)
    {
        $config = $this->fetchConfig();
        $config->setIsCacheValid($isCacheValid);
        $this->dm->flush();

        return $config;
    }

    /**
     * @return bool
     */
    public function hasSingleLanguage()
    {
        $config = $this->fetchConfig();
        $languages = $config->getLanguages();

        return empty($languages) || count($languages) === 1;
    }
}
