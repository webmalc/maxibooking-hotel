<?php
/**
 * Date: 23.05.19
 */

namespace MBH\Bundle\OnlineBundle\Services\PaymentForm;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\OnlineBundle\Document\PaymentFormConfig;
use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfig;
use MBH\Bundle\OnlineBundle\Document\SiteConfig;
use MBH\Bundle\OnlineBundle\Lib\MBSite\StyleDataInterface;

class MBSitePaymentFormConfigData
{
    private const DEFAULT_BOOTSTRAP_THEME = 'cerulean';

    private const FORM_NAME = 'payment-form';
    private const FILE_NAME = 'style.css';

    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var StyleDataInterface
     */
    private $styleData;

    public function __construct(DocumentManager $dm, StyleDataInterface $styleData)
    {
        $this->dm = $dm;
        $this->styleData = $styleData;
    }

    public function changeConfig(PaymentFormConfig $formConfig, bool $styleIsNeed = true): void
    {
        $formConfig
            ->setUseAccordion(true)
            ->setIsFullWidth(true)
            ->setTheme(FormConfig::THEMES[self::DEFAULT_BOOTSTRAP_THEME])
            ->setFrameHeight(600);

        if ($styleIsNeed) {

            /** @var SiteConfig $siteConfig */
            $siteConfig = $this->dm->getRepository(SiteConfig::class)->findOneBy([]);

            $css = sprintf(
                '.panel-primary .panel-heading {background-color: %1$s;}.btn-info:active {background: %1$s !important;border-color: %1$s !important;} %2$s',
                $siteConfig->getThemeColors()['main'],
                $this->getStyle()
            );

            $formConfig->setCss($css);
        }
    }

    private function getStyle(): ?string
    {
        return $this->styleData->getContent(self::FILE_NAME, self::FORM_NAME);
    }
}
