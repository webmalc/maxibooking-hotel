<?php
/**
 * Created by PhpStorm.
 * Date: 04.12.18
 */

namespace Tests\Bundle\OnlineBundle\Controller;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\OnlineBundle\Document\SiteConfig;
use MBH\Bundle\OnlineBundle\Document\SiteContent;
use MBH\Bundle\OnlineBundle\Form\SocialNetworking\ManySocialNetworkingServicesType;
use MBH\Bundle\OnlineBundle\Services\SiteManager;
use MBH\Bundle\UserBundle\DataFixtures\MongoDB\UserData;

class MBSiteControllerTest extends WebTestCase
{
    private const URL_PREFIX = '/management/online/mb-site/';
    private const URL_SOCIAL_NETWORKING_SERVICES = 'social-networking-services';

    /**
     * @var SiteManager
     */
    private static $siteManager;

    public static function setUpBeforeClass()
    {
        self::baseFixtures();

        $container = self::getContainerStat();

        /** TODO: move to fixture? */
        $dm = $container->get('doctrine.odm.mongodb.document_manager');
        $clientConfig = $container->get('mbh.client_config_manager')->fetchConfig();
        $clientConfig->setIsMBSiteEnabled(true);
        $dm->persist($clientConfig);
        $dm->flush();

        self::$siteManager = $container->get('mbh.site_manager');

        $siteConfig = self::$siteManager->getSiteConfig();

        /** TODO: move to fixture? */
        if ($siteConfig === null) {
            $hotel = $dm->getRepository(Hotel::class)->findOneBy([]);

            $siteContent = new SiteContent();
            $siteContent->setHotel($hotel);

            $dm->persist($siteContent);

            $siteConfig = new SiteConfig();
            $siteConfig->getHotels()->add($hotel);
            $siteConfig->getContents(true)->add($siteContent);
            $siteConfig
                ->setSiteDomain('best-site');


            $dm->persist($siteConfig);
        }

        $dm->flush();
    }

    public function setUp()
    {
    }

    public function getDataForTestSocialNetworkingServicesUrl(): iterable
    {
        $data = [
            'user anon' => [[], 401],
            'user manager' => [
                [
                    'PHP_AUTH_USER' => UserData::USER_MANAGER,
                    'PHP_AUTH_PW'   => UserData::USER_MANAGER,
                ],
                403
            ],
            'user admin' => [
                [
                    'PHP_AUTH_USER' => UserData::USER_ADMIN,
                    'PHP_AUTH_PW'   => UserData::USER_ADMIN,
                ],
                200
            ]
        ];

        yield from $data;
    }

    /**
     * @dataProvider getDataForTestSocialNetworkingServicesUrl
     */
    public function testSocialNetworkingServicesUrl(array $authData, int $statusCode)
    {
        $url = $this->getUrlForSocialNetworkingServices();

        $client = self::createClient([],$authData);

        $client->request('GET', $url);

        $this->assertStatusCodeWithMsg($url, $statusCode, $client);
    }

    public function getDataForCreate(): iterable
    {
        $data = [
            'invalid' => [false, 'invalid url'],
            'valid'   => [true, 'http://twitter.com'],
        ];

        yield from $data;
    }

    /**
     * @dataProvider getDataForCreate
     *
     * @param bool $valid
     * @param string $url
     */
    public function testCreateSocialNetworkingServices(bool $valid, string $url)
    {
        $nameService = 'twitter';
        $this->client = $this->makeClient(true);
        $blockPrefix = (new ManySocialNetworkingServicesType())->getBlockPrefix();

        $crawler = $this->getListCrawler($this->getUrlForSocialNetworkingServices());

        $form = $crawler->filter(sprintf('form[name="%s"]', $blockPrefix))->form();

        $form->setValues([
            $blockPrefix . '[many][0][socialServices][' . $nameService . '][url]' => $url,
        ]);

        $this->client->followRedirects(true);
        $this->client->submit($form);

        if ($valid) {
            /** @var SiteConfig $siteConfig */
            $siteConfig = $this->getSiteManager()->getSiteConfig();

            $dm = self::getContainerStat()->get('doctrine.odm.mongodb.document_manager');
            $dm->persist($siteConfig);
            $dm->refresh($siteConfig);

            /** @var SiteContent $siteContent */
            $siteContent = $siteConfig->getContents()->first();

            $keys = implode(', ', $siteContent->getSocialNetworkingServices()->getKeys());

            $this->assertCount(
                1,
                $siteContent->getSocialNetworkingServices(),
                'Amount services greater that 1: ' . $keys
            );

            $this->assertContains(
                $nameService,
                $keys,
                sprintf('In the test created a "%s", but installed "%s".', $nameService, $keys)
            );
        } else {
            $this->assertValidationErrors(
                ['children[many].children[0].children[socialServices].children['. $nameService .'].children[url].data'],
                $this->client->getContainer()
            );
        }
    }

    private function getUrlForSocialNetworkingServices(): string
    {
        return self::URL_PREFIX . self::URL_SOCIAL_NETWORKING_SERVICES;
    }

    private function getSiteManager(): SiteManager
    {
        return self::$siteManager;
    }
}
