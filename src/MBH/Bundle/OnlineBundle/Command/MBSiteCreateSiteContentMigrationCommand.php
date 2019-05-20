<?php
/**
 * Date: 15.05.19
 */

namespace MBH\Bundle\OnlineBundle\Command;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FieldsName;
use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfigManager;
use MBH\Bundle\OnlineBundle\Document\SiteConfig;
use MBH\Bundle\OnlineBundle\Document\SiteContent;
use MBH\Bundle\OnlineBundle\Document\SocialLink\SocialService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MBSiteCreateSiteContentMigrationCommand extends ContainerAwareCommand
{
    public const COMMAND_NAME = 'mbh:mb_site:create_site_content_document:migration';

    public const FILE_LOG_NAME = 'mb_site.log';

    private const TYPE_LOG_INFO = 'info';
    private const TYPE_LOG_ERROR = 'error';

    /**
     * @var DocumentManager
     */
    private $dm;

    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('begin migration');
        $siteConfig = $this->getContainer()->get('mbh.site_manager')->getSiteConfig();

        if ($siteConfig === null) {
            $msg = 'not found siteConfig';
            $output->writeln($msg);
            $this->logger($msg);

            return 0;
        }

        $this->dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');

        $this->moveContent($siteConfig);
        $this->addNameFields();

        $msg = 'create siteContent. updated siteConfig';
        $output->writeln($msg. '. end migration');
        $this->logger($msg);

        return 0;
    }

    private function moveContent(SiteConfig $siteConfig): void
    {
        $dm = $this->dm;
        $repoSiteConfig = $dm->getDocumentCollection(SiteConfig::class);
        $qb = $repoSiteConfig->createQueryBuilder();
        $preQuery = $qb
            ->field('_id')->equals(new \MongoId($siteConfig->getId()))
            ->field('socialNetworkingServices')->notEqual(null);
        $siteConfigArray = (clone ($preQuery))->getQuery()->execute()->toArray();

        $holderSocial = new ArrayCollection();
        if ($siteConfigArray !== []) {
            foreach ($siteConfigArray[$siteConfig->getId()]['socialNetworkingServices'] as $socialService) {
                $key = $socialService['key'] ?? null;
                $name = $socialService['name'] ?? null;
                $url = $socialService['url'] ?? null;

                $holderSocial->set($key, new SocialService($key, $name, $url));
            }

            (clone ($preQuery))
                ->findAndUpdate()
                ->field('socialNetworkingServices')->unsetField()->exists(true)
                ->getQuery()
                ->execute();
        }

        $holder = [];

        foreach ($siteConfig->getHotels() as $hotel) {
            $siteContent = new SiteContent();
            $siteContent
                ->setUnUseBanner()
                ->setHotel($hotel);

            if ($holderSocial->count() !== 0) {
                $siteContent->setSocialNetworkingServices($holderSocial);
            }

            $dm->persist($siteContent);
            $holder[] = $siteContent;
        }

        $siteConfig->setContents($holder);

        $dm->persist($siteConfig);
        $dm->flush();
    }

    private function addNameFields(): void
    {
        if ($this->getContainer()->getParameter('locale') !== 'ru') {
            return;
        }

        $formConfig = $this->getContainer()->get(FormConfigManager::class)->getForMBSite(false);

        if ($formConfig === null) {
            return;
        }

        $fields = new FieldsName();
        $fields
            ->setBegin('заезд')
            ->setEnd('выезд');

        $formConfig
            ->setFieldsName($fields);

        $this->dm->persist($formConfig);
        $this->dm->flush();
    }

    private function logger(string $msg, string $type = self::TYPE_LOG_INFO): void
    {
        $logger = $this->getContainer()->get('mbh.mb_site.logger');

        $msg = 'UPDATE CMD.' . $msg;

        switch ($type) {
            case self::TYPE_LOG_INFO:
                $logger->addInfo($msg);
                break;
            case self::TYPE_LOG_ERROR:
                $logger->addError($msg);
                break;
            default:
                $logger->addNotice($msg);
        }
    }
}
