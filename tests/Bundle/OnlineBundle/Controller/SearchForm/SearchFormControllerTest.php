<?php
/**
 * Date: 21.05.19
 */

namespace Tests\Bundle\OnlineBundle\Controller\SearchForm;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfig;
use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfigManager;
use MBH\Bundle\OnlineBundle\Document\SiteConfig;
use MBH\Bundle\OnlineBundle\Document\SiteContent;

class SearchFormControllerTest extends WebTestCase
{

    private const PREFIX_URL = '/management/online/api/';

    private const URL_FILE = 'file/%s/load-search-form';
    private const URL_CALENDAR = 'form/iframe/calendar/%s';
    private const URL_ADDITIONAL_FORM = 'form/iframe/additional-form/%s';
    private const URL_SEARCH_IFRAME = 'form/search-iframe/%s';

    private const URL_FOR_OLD = 'form/iframe/%s';

    /**
     * @var string
     */
    private static $formConfigId;

    public static function setUpBeforeClass()
    {
        self::baseFixtures();

        $container = self::getContainerStat();

        $clientConfig = $container->get('mbh.client_config_manager')->fetchConfig();
        $clientConfig->setIsMBSiteEnabled(true);

        $dm = $container->get('doctrine.odm.mongodb.document_manager');

        $dm->flush($clientConfig);

        $formConfig = $container->get(FormConfigManager::class)->getForMBSite(false);
        $formConfig
            ->setResultsUrl('fake-url')
            ->setForMbSite(false);

        $dm->flush($formConfig);

        self::$formConfigId = $formConfig->getId();
    }

    public function setUp()
    {
        $this->client = $this->makeClient(false);
    }

    public function getUrls(): iterable
    {
        yield 'old file' => [self::URL_FOR_OLD];
        yield 'main iframe' => [self::URL_SEARCH_IFRAME];
        yield 'calendar iframe' => [self::URL_CALENDAR];
        yield 'additional form' => [self::URL_ADDITIONAL_FORM];
        yield 'load file' => [self::URL_FILE];
    }

    /**
     * @dataProvider getUrls
     */
    public function testAvalible($rawUrl)
    {
        $url = $this->generateUrl($rawUrl);

        $this->client->followRedirects();
        $this->getListCrawler($url);

        $this->assertStatusCodeWithMsg($url,200);
    }

    private function generateUrl(string $url): string
    {
        return sprintf(self::PREFIX_URL . $url, $this->getFormConfigId());
    }

    private function getFormConfigId(): string
    {
        return self::$formConfigId;
    }
}
