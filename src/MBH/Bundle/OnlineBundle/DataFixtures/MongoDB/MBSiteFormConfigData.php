<?php
/**
 * Date: 07.05.19
 */

namespace MBH\Bundle\OnlineBundle\DataFixtures\MongoDB;


use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FieldsName;
use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfig;
use MBH\Bundle\OnlineBundle\Services\MBSiteStyleFormHolder;
use MBH\Bundle\OnlineBundle\Services\SiteManager;

class MBSiteFormConfigData extends AbstractFixture implements OrderedFixtureInterface
{
    const LOCALE_RU = 'ru';

    public function doLoad(ObjectManager $manager)
    {
        $styleHolder = $this->container->get(MBSiteStyleFormHolder::class);

        $formConfig = new FormConfig();
        $formConfig
            ->setForMbSite(true)
            ->setIsFullWidth(true)
            ->setIsHorizontal(true)
            ->setResultsUrl(SiteManager::DEFAULT_RESULTS_PAGE);

        $formConfig
            ->setUseAdditionalForm(true)
            ->setIconLogoLink(true)
            ->setAdditionalFormFrameWidth('270px')
            ->setAdditionalFormFrameHeight('auto')
            ->setCalendarFrameHeight('230px')
            ->setCalendarFrameWidth('235px');

        $formConfig
            ->setCss($styleHolder->getStyleSearchForm())
            ->setCalendarCss($styleHolder->getStyleCalendar())
            ->setAdditionalFormCss($styleHolder->getStyleAdditionalForm());

        if ($this->container->getParameter('locale') === self::LOCALE_RU) {
            $fields = new FieldsName();
            $fields
                ->setBegin('заезд')
                ->setEnd('выезд');

            $formConfig
                ->setFieldsName($fields);
        }

        $manager->persist($formConfig);
        $manager->flush();
    }

    public function getOrder()
    {
        return 10001;
    }

}
