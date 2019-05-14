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

class MBSiteFormConfigData extends AbstractFixture implements OrderedFixtureInterface
{
    const LOCALE_RU = 'ru';

    public function doLoad(ObjectManager $manager)
    {
        $formConfig = new FormConfig();
        $formConfig
            ->setForMbSite(true);


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
