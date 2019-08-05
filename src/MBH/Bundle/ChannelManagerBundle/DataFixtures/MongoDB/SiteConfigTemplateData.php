<?php


namespace MBH\Bundle\ChannelManagerBundle\DataFixtures\MongoDB;


use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use MBH\Bundle\OnlineBundle\Document\SiteConfig;
use MBH\Bundle\PackageBundle\Document\Tourist;

class SiteConfigTemplateData extends AbstractFixture
{
    const SITE_CONFIG = ['siteDomain' => 'test', 'scheme' => 'https', 'domain' => '.maaaxi.com', 'isEnabled' => true];

    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $this->persistSiteConfig($manager);
    }

    /**
     * @param ObjectManager $manager
     */
    private function persistSiteConfig(ObjectManager $manager)
    {
        $siteConfig = new SiteConfig();

        $siteConfig->setDomain(self::SITE_CONFIG['domain']);
        $siteConfig->setScheme(self::SITE_CONFIG['scheme']);
        $siteConfig->setSiteDomain(self::SITE_CONFIG['siteDomain']);
        $siteConfig->setIsEnabled(self::SITE_CONFIG['isEnabled']);

        $manager->persist($siteConfig);
        $manager->flush();
    }

    protected function getEnvs(): array
    {
        return ['test', 'dev'];
    }

}