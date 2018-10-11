<?php

namespace MBH\Bundle\HotelBundle\Form\MBSiteFlow;

use MBH\Bundle\BillingBundle\Lib\Model\WebSite;
use MBH\Bundle\ClientBundle\Service\ClientManager;
use MBH\Bundle\HotelBundle\Model\FlowRuntimeException;
use MBH\Bundle\HotelBundle\Service\FormFlow;
use MBH\Bundle\OnlineBundle\Document\SiteConfig;
use MBH\Bundle\OnlineBundle\Services\SiteManager;
use Symfony\Component\Form\FormInterface;

class MBSiteFlow extends FormFlow
{
    const FLOW_TYPE = 'site';

    private $siteManager;
    private $clientManager;

    public function __construct(SiteManager $siteManager, ClientManager $clientManager)
    {
        $this->siteManager = $siteManager;
        $this->clientManager = $clientManager;
    }

    public static function getFlowType()
    {
        return self::FLOW_TYPE;
    }

    protected function getStepsConfig(): array
    {
        return [
            [
                'label' => 'mb_site_flow.step_labels.site_name',
                'form_type' => MBSiteFlowType::class,
            ],
            [
                'label' => 'mb_site_flow.step_labels.payment_types',
                'form_type' => MBSiteFlowType::class,
            ],
            [
                'label' => 'mb_site_flow.step_labels.color_theme',
                'form_type' => MBSiteFlowType::class,
            ],
            [
                'label' => 'mb_site_flow.step_labels.key_words',
                'form_type' => MBSiteFlowType::class,
            ],
            [
                'label' => 'mb_site_flow.step_labels.pers_data_policy',
                'form_type' => MBSiteFlowType::class,
            ],
        ];
    }

    protected function getFormData()
    {
        return $this->getSiteConfig();
    }

    /**
     * @param FormInterface $form
     * @throws \Exception
     */
    protected function handleForm(FormInterface $form)
    {
        $siteConfig = $this->getSiteConfig();
        if ($this->getCurrentStepNumber() === 1) {
            $newSiteAddress = $this->siteManager->compileSiteAddress($this->getFormData()->getSiteDomain());
            $client = $this->clientManager->getClient();
            $clientSite = $this->clientManager->getClientSite() ?? (new WebSite())->setClient($client);

            if ($clientSite->getUrl() !== $newSiteAddress) {
                $clientSite
                    ->setUrl($newSiteAddress)
                    ->setClient($client->getLogin());
                $result = $this->clientManager->addOrUpdateSite($clientSite);
                if (!$result->isSuccessful()) {
                    $error = !empty($result->getErrors()) && $result->getErrors()['url']
                        ? $result->getErrors()['url']
                        : $this->translator->trans('mb_site_flow.unexpected_error');
                    throw new FlowRuntimeException($error);
                }
            }
        } elseif ($this->getCurrentStepNumber() === 2) {
            $formConfig = $this->getSiteFormConfig();
            $this->siteManager->updateSiteFormConfig($siteConfig, $formConfig, $this->request->get($form->getName())['paymentTypes']);
        }

        $this->dm->flush();
    }

    /**
     * @return \MBH\Bundle\OnlineBundle\Document\FormConfig
     */
    private function getSiteFormConfig()
    {
        return $this->siteManager->fetchFormConfig();
    }

    /**
     * @return SiteConfig|null|object
     */
    private function getSiteConfig()
    {
        return $this->siteManager->getSiteConfig() ?? new SiteConfig();
    }
}