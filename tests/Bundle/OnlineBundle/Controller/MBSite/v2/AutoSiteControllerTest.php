<?php
/**
 * Date: 21.05.19
 */

namespace Tests\Bundle\OnlineBundle\Controller\MBSite\v2;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\OnlineBundle\Document\SiteConfig;

class AutoSiteControllerTest extends WebTestCase
{
    private const URL_PREFIX = '/management/online/api/mb-site/v2/';

    private const URL_SETTING = 'settings';
    private const URL_ADDITIONAL_CONTENT = 'additional-content';
    private const URL_HOTELS = 'hotels';
    private const URL_ROOM_TYPES = 'room-types';
    private const URL_MIN_PRICES = 'min-prices';
    private const URL_FACILITIES_DATA = 'facilities-data';
    private const URL_PERSONAL_DATA_POLICIES = 'personal-data-policies';
    private const URL_ORGANIZATION_BY_HOTEL = 'organization-by-hotel';

    private const KEY_RESPONSE_SUCCESS = 'success';
    private const KEY_RESPONSE_DATA = 'data';
    private const KEY_RESPONSE_ERRORS = 'errors';

    private const ALL_KEYS_RESPONSE = [
        self::KEY_RESPONSE_SUCCESS,
        self::KEY_RESPONSE_DATA,
        self::KEY_RESPONSE_ERRORS
    ];

    private static $siteConfigIsEnabled = false;

    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public function setUp()
    {
        $this->client = $this->makeClient(false);
    }

    public function getAllUrlWithOutParameters(): iterable
    {
        yield self::URL_SETTING => [$this->addPrefix(self::URL_SETTING), false];
        yield self::URL_ADDITIONAL_CONTENT => [$this->addPrefix(self::URL_ADDITIONAL_CONTENT) . '/fake-id', true];
        yield self::URL_HOTELS => [$this->addPrefix(self::URL_HOTELS), false];
        yield self::URL_ROOM_TYPES => [$this->addPrefix(self::URL_ROOM_TYPES), true];
        yield self::URL_MIN_PRICES => [$this->addPrefix(self::URL_MIN_PRICES), true];
        yield self::URL_FACILITIES_DATA => [$this->addPrefix(self::URL_FACILITIES_DATA), false];
        yield self::URL_PERSONAL_DATA_POLICIES => [$this->addPrefix(self::URL_PERSONAL_DATA_POLICIES), false];
        yield self::URL_ORGANIZATION_BY_HOTEL => [$this->addPrefix(self::URL_ORGANIZATION_BY_HOTEL . '/fake-id'), false];
    }

    /**
     * @dataProvider getAllUrlWithOutParameters
     */
    public function testWithSiteIsDisabled(string $url)
    {
        $this->getListCrawler($url);

        $this->assertStatusCodeWithMsg($url, 410);
    }

    /**
     * @dataProvider getAllUrlWithOutParameters
     */
    public function testResponseWithFakeData(string $url, bool $error)
    {
        $this->setAvalibleMBSite();
        $this->getListCrawler($url);

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertStatusCodeWithMsg($url,200);
        $this->assertEquals('application/json', $this->client->getResponse()->headers->get('Content-Type'));

        $msg = $this->generateMsg($response);

        $this->assertArrayHasKey(self::KEY_RESPONSE_SUCCESS, $response, $msg);
        $this->assertArrayHasKey(self::KEY_RESPONSE_DATA, $response, $msg);

        if ($error) {
            $this->assertArrayHasKey(self::KEY_RESPONSE_ERRORS, $response, $msg);
        }
    }

    private function generateMsg(array $response): string
    {
        return sprintf(
            'Expected keys in json response: "%s", give: "%s".',
            implode(', ', self::ALL_KEYS_RESPONSE),
            implode(', ', array_keys($response))
        );
    }

    private function setAvalibleMBSite()
    {
        if (!self::$siteConfigIsEnabled) {
            $dm = self::getContainerStat()->get('doctrine.odm.mongodb.document_manager');

            $clientConfig = self::getContainer()->get('mbh.client_config_manager')->fetchConfig();
            $clientConfig->setIsMBSiteEnabled(true);
            $dm->persist($clientConfig);

            $siteConfig = new SiteConfig();
            $siteConfig->setSiteDomain('best-site');

            $dm->persist($siteConfig);
            $dm->flush();

            self::$siteConfigIsEnabled = true;
        }
    }

    private function addPrefix(string $url): string
    {
        return self::URL_PREFIX . $url;
    }

}
