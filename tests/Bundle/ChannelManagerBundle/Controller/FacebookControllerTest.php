<?php


namespace Tests\Bundle\ChannelManagerBundle\Controller;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\OnlineBundle\Document\SiteConfig;
use MBH\Bundle\OnlineBundle\Services\SiteManager;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FacebookControllerTest extends WebTestCase
{
    /** @var Client */
    protected $client;

    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    public function setUp()
    {
        parent::setUp();
        $this->client = self::makeClient(true);
        $this->container = self::getContainer();
        $this->dm = $this->container->get('doctrine.odm.mongodb.document_manager');

    }

    public function testInfoActionSuccess()
    {
        $crawler = $this->getCrawler();
        $this->assertEquals($this->client->getResponse()->getStatusCode(), 200);
        $this->assertEquals(
            1,
            $crawler->filter('a[href = "https://test.maaaxi.com' . SiteManager::DEFAULT_RESULTS_PAGE . '"]')->count()
        );
    }

    /**
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public function getCrawler()
    {
        return $this->client->request('GET', '/management/channelmanager/facebook/');
    }

    public function deleteSiteConfig()
    {
        $this->dm->getDocumentCollection(SiteConfig::class)->drop();
        $this->dm->flush();

    }

    public function testInfoActionFault()
    {
        $this->deleteSiteConfig();
        $message = $this->container->get('translator')->trans('cm_connection_instructions.part2.facebook.warning1');
        $crawler = $this->getCrawler();
        $this->assertEquals($this->client->getResponse()->getStatusCode(), 200);
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . $message . '")')->count()
        );
    }

}