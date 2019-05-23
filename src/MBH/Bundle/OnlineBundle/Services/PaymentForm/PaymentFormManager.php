<?php
/**
 * Date: 23.05.19
 */

namespace MBH\Bundle\OnlineBundle\Services\PaymentForm;


use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Service\ClientConfigManager;
use MBH\Bundle\OnlineBundle\Document\PaymentFormConfig;

class PaymentFormManager
{
    /**
     * @var DocumentRepository
     */
    private $repo;

    /**
     * @var MBSitePaymentFormConfigData
     */
    private $mbSitePaymentFormConfigData;

    /**
     * @var ClientConfig
     */
    private $clientConfig;

    /**
     * PaymentFormManager constructor.
     * @param DocumentManager $dm
     * @param MBSitePaymentFormConfigData $formConfig
     */
    public function __construct(
        DocumentManager $dm,
        MBSitePaymentFormConfigData $formConfig,
        ClientConfigManager $clientConfigManager
    )
    {
        $this->repo = $dm->getRepository(PaymentFormConfig::class);
        $this->mbSitePaymentFormConfigData = $formConfig;
        $this->clientConfig = $clientConfigManager->fetchConfig();
    }


    public function findOneById(string $id = null): ?PaymentFormConfig
    {
        $qb = $this->repo->createQueryBuilder();
        if ($id !== null) {
            $qb->field('id')->equals($id);
        }

        $formConfig = $qb->getQuery()->getSingleResult();

        $formConfig = $this->checkAndInjectConfig($formConfig);

        return $formConfig;
    }

    public function getForMBSite(bool $styleIsNeed = true): ?PaymentFormConfig
    {
        $formConfig = $this->repo->findOneBy(['forMbSite' => true]);
        $formConfig = $this->checkAndInjectConfig($formConfig, $styleIsNeed);

        return $formConfig;
    }

    /**
     * @return PaymentFormConfig[]
     */
    public function findAll(): array
    {
        $formConfigHolder = [];

        /** @var PaymentFormConfig|null $formConfig */
        foreach ($this->repo->findAll() as $formConfig) {
            $formConfig = $this->checkAndInjectConfig($formConfig);
            if ($formConfig !== null) {
                $formConfigHolder[] = $formConfig;
            }
        }

        return $formConfigHolder;
    }

    private function checkAndInjectConfig(?PaymentFormConfig $formConfig, bool $styleIsNeed = true): ?PaymentFormConfig
    {
        if ($formConfig === null || !$formConfig->isForMbSite()) {
            return $formConfig;
        }

        if (!$this->clientConfig->isMBSiteEnabled()) {
            return null;
        }

        $this->mbSitePaymentFormConfigData->changeConfig($formConfig, $styleIsNeed);

        return $formConfig;
    }
}
