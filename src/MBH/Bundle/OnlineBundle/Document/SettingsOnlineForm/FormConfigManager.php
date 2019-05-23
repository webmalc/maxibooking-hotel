<?php

namespace MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Service\ClientConfigManager;
use MBH\Bundle\OnlineBundle\Services\SearchForm\MBSiteFormConfigDataService;

class FormConfigManager
{
    /**
     * @var DocumentRepository
     */
    private $repo;

    /**
     * @var MBSiteFormConfigDataService
     */
    private $configData;

    /**
     * @var ClientConfig
     */
    private $clientConfig;

    public function __construct(
        DocumentManager $documentManager,
        MBSiteFormConfigDataService $configSite,
        ClientConfigManager $clientConfigManager
    )
    {
        $this->repo = $documentManager->getRepository(FormConfig::class);
        $this->configData = $configSite;
        $this->clientConfig = $clientConfigManager->fetchConfig();
    }

    public function findOneById(string $id = null): ?FormConfig
    {
        $qb = $this->repo->createQueryBuilder();
        if ($id !== null) {
            $qb->field('id')->equals($id);
        }

        $formConfig = $qb->getQuery()->getSingleResult();

        $formConfig = $this->checkAndInjectConfig($formConfig);

        return $formConfig;
    }

    public function getForMBSite(bool $styleIsNeed = true): ?FormConfig
    {
        $formConfig = $this->repo->findOneBy(['forMbSite' => true]);
        $formConfig = $this->checkAndInjectConfig($formConfig, $styleIsNeed);

        return $formConfig;
    }

    /**
     * @return FormConfig[]
     */
    public function findAll(): array
    {
        $formConfigHolder = [];

        foreach ($this->repo->findAll() as $formConfig) {
            $formConfig = $this->checkAndInjectConfig($formConfig);
            if ($formConfig !== null) {
                $formConfigHolder[] = $formConfig;
            }
        }

        return $formConfigHolder;
    }

    private function checkAndInjectConfig(?FormConfig $formConfig, bool $styleIsNeed = true): ?FormConfig
    {
        if ($formConfig === null || !$formConfig->isForMbSite()) {
            return $formConfig;
        }

        if (!$this->clientConfig->isMBSiteEnabled()) {
            return null;
        }

        $this->configData->changeConfig($formConfig, $styleIsNeed);

        return $formConfig;
    }
}
