<?php
/**
 * Created by PhpStorm.
 * Date: 04.12.18
 */

namespace Tests\Bundle\OnlineBundle\Controller;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\OnlineBundle\Document\SiteConfig;
use MBH\Bundle\OnlineBundle\Form\SocialNetworking\SiteSocialNetworkingServicesType;
use MBH\Bundle\UserBundle\DataFixtures\MongoDB\UserData;

class MBSiteControllerTest extends WebTestCase
{
    private const URL_PREFIX = '/management/online/mb_site/';

    public static function setUpBeforeClass()
    {
        self::baseFixtures();

        $siteConfig = new SiteConfig();

        $dm = self::getContainerStat()->get('doctrine.odm.mongodb.document_manager');
        $dm->persist($siteConfig);
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
        $this->client = self::makeClient(true);
        $blockPrefix = (new SiteSocialNetworkingServicesType())->getBlockPrefix();

        $crawler = $this->getListCrawler($this->getUrlForSocialNetworkingServices());

        $form = $crawler->filter(sprintf('form[name="%s"]', $blockPrefix))->form();

        $form->setValues([
            $blockPrefix . '[snss][' . $nameService . '][url]' => $url,
        ]);

        $this->client->submit($form);

        if ($valid) {
            /** @var SiteConfig $siteConfig */
            $siteConfig = self::getContainerStat()->get('doctrine.odm.mongodb.document_manager')
                    ->getRepository(SiteConfig::class)->findOneBy([]);

            $keys = implode(', ', $siteConfig->getSocialNetworkingServices()->getKeys());

            $this->assertCount(
                1,
                $siteConfig->getSocialNetworkingServices(),
                'Amount services greater that 1: ' . $keys
            );

            $this->assertContains(
                $nameService,
                $keys,
                sprintf('In the test created a "%s", but installed "%s".', $nameService, $keys)
            );
        } else {
            $this->assertValidationErrors(
                ['children[snss].children[twitter].children[url].data'],
                $this->client->getContainer()
            );
        }
    }

    private function getUrlForSocialNetworkingServices(): string
    {
        return self::URL_PREFIX . 'social_networking_services';
    }
}