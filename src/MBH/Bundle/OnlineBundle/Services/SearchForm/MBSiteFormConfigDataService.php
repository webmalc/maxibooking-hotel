<?php
/**
 * Date: 13.05.19
 */

namespace MBH\Bundle\OnlineBundle\Services\SearchForm;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfig;
use MBH\Bundle\OnlineBundle\Document\SiteConfig;
use MBH\Bundle\OnlineBundle\Services\ResultsForm\MBSiteResultFormStyle;

class MBSiteFormConfigDataService
{
    /**
     * @var MBSiteSearchFormStyle
     */
    private $searchStyleHolder;

    /**
     * @var MBSiteResultFormStyle
     */
    private $resultStyleHolder;

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
        MBSiteResultFormStyle $resultFormStyle,
        DocumentManager $documentManager,
        string $locale
    )
    {
        $this->searchStyleHolder = $styleFormHolder;
        $this->resultStyleHolder = $resultFormStyle;
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

        if ($siteConfig !== null) {
            $this->addSearchStyle($formConfig, $siteConfig);
            $this->addResultStyle($formConfig, $siteConfig);
            $this->addStepOneButtonStyle($formConfig, $siteConfig);
        }
    }

    private function addStepOneButtonStyle(FormConfig $formConfig, SiteConfig $siteConfig): void
    {
        $css = sprintf(
            ":root {--main: %s; --mainlight: %s;}\n%s",
            $siteConfig->getThemeColors()['main'],
            $siteConfig->getThemeColors()['mainlight'],
            $this->resultStyleHolder->getStepOneButtonStyle()
        );

        $formConfig
            ->setStepOneButtonHeight(80)
            ->setStepOneButtonCss($css);
    }

    private function addResultStyle(FormConfig $formConfig, SiteConfig $siteConfig): void
    {
        $css = sprintf(
            ":root {--main: %s; --mainlight: %s;}\n%s",
            $siteConfig->getThemeColors()['main'],
            $siteConfig->getThemeColors()['mainlight'],
            $this->resultStyleHolder->getMainStyle()
        );

        $formConfig
            ->setResultFormCss($css);
    }

    private function addSearchStyle(FormConfig $formConfig, SiteConfig $siteConfig): void
    {
        $colorMain = $siteConfig->getThemeColors()['main'];
        $colorMainLight = $siteConfig->getThemeColors()['mainlight'];

        $format = ":root {--main: %s; --mainlight: %s;}\n%s";

        $css = sprintf(
            $format,
            $colorMain,
            $colorMainLight,
            $this->searchStyleHolder->getStyleSearchForm()
        );

        $cssAdditionalForm = sprintf(
            $format,
            $colorMain,
            $colorMainLight,
            $this->searchStyleHolder->getStyleAdditionalForm()
        );

        $formConfig
            ->setAdditionalFormFrameWidth('251px')
            ->setAdditionalFormFrameHeight('auto')
            ->setCalendarFrameHeight('200px')
            ->setCalendarFrameWidth('235px')
            ->setCss($css)
            ->setCalendarCss($this->searchStyleHolder->getStyleCalendar())
            ->setAdditionalFormCss($cssAdditionalForm);
    }

}
