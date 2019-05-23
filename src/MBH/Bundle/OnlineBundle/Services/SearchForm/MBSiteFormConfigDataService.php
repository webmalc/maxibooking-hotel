<?php
/**
 * Date: 13.05.19
 */

namespace MBH\Bundle\OnlineBundle\Services\SearchForm;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfig;
use MBH\Bundle\OnlineBundle\Document\SiteConfig;

class MBSiteFormConfigDataService
{
    /**
     * @var MBSiteSearchFormStyle
     */
    private $styleHolder;

    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var string
     */
    private $locale;

    public function __construct(
        MBSiteSearchFormStyle $styleFormHolder,
        DocumentManager $documentManager,
        string $locale
    )
    {
        $this->styleHolder = $styleFormHolder;
        $this->dm = $documentManager;
        $this->locale = $locale;
    }

    public function changeConfig(FormConfig $formConfig, bool $styleIsNeed = true): void
    {
        $formConfig
            ->setIsFullWidth(true)
            ->setUseAdditionalForm(true)
            ->setIconLogoLink(true)
            ->setTheme(null);

        if ($styleIsNeed) {
            $this->addStyle($formConfig);
        }
    }

    private function addStyle(FormConfig $formConfig): void
    {
        /** @var SiteConfig $siteConfig */
        $siteConfig = $this->dm->getRepository(SiteConfig::class)->findOneBy([]);

        $css = sprintf(
            "#mbh-body-search-iframe #mbh-form-wrapper form#mbh-form #mbh-form-submit {background: %s;}\n%s",
            $siteConfig->getThemeColors()['main'],
            $this->styleHolder->getStyleSearchForm()
        );

        $formConfig
            ->setAdditionalFormFrameWidth('251px')
            ->setAdditionalFormFrameHeight('auto')
            ->setCalendarFrameHeight('230px')
            ->setCalendarFrameWidth('235px')
            ->setCss($css)
            ->setCalendarCss($this->styleHolder->getStyleCalendar())
            ->setAdditionalFormCss($this->styleHolder->getStyleAdditionalForm());
    }

}
