<?php
/**
 * Created by PhpStorm.
 * Date: 30.05.18
 */

namespace Tests\Bundle\BaseBundle\Controller;


use MBH\Bundle\BaseBundle\Lib\Test\Traits\HotelIdTestTrait;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\BaseBundle\Menu\Builder;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\UserBundle\Document\User;

class WelcomeControllerTest extends WebTestCase
{
    use HotelIdTestTrait;

    /**
     * @var string
     */
    private const URL_INDEX = '/';

    /**
     * @var string
     */
    private const URL_NOT_FOUND_HOTEL = '/management/hotel/notfound';

    /**
     * страница из mainMenu
     * @var string
     */
    private const URL_CHESSBOARD = '/package/chessboard/';

    /**
     * страница из managementMenu
     * @var string
     */
    private const URL_ROOMTYPE = '/management/hotel/roomtype/';

    /**
     * @var string
     */
    const URL_PACKAGE_ORGANIZATIONS_LIST = '/package/organizations/list';

    /**
     * @var string
     */
    private const USER_MANAGER = 'manager';

    /**
     * @var string
     */
    private const ACTUAL_AMOUNT_ITEMS_FOR_MANAGER = 20;

    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    public function testRedirectCode()
    {
        $this->getListCrawler(self::URL_INDEX);

        $this->assertStatusCode(
            302,
            $this->client
        );
    }

    public function testAfterRedirectCode()
    {
        $this->getListCrawler(self::URL_INDEX);
        $this->client->followRedirect();

        $this->assertStatusCode(
            200,
            $this->client
        );
    }

    public function testSidebarActiveMainMenu()
    {
        $crawler = $this->getListCrawler(self::URL_CHESSBOARD);

        $mainMenu = $crawler->filter('#' . Builder::ROOT_MENU_ITEM_MAIN_MENU . '.collapse.in');
        $managementMenu = $crawler->filter('#' . Builder::ROOT_MENU_ITEM_MANAGEMENT_MENU . '.collapse.in');

        $this->assertCount(1, $mainMenu);
        $this->assertCount(0, $managementMenu);
    }

    public function testSidebarActiveManagerMenu()
    {
        $crawler = $this->getListCrawler(self::URL_ROOMTYPE);

        $mainMenu = $crawler->filter('#' . Builder::ROOT_MENU_ITEM_MAIN_MENU . '.collapse.in');
        $managementMenu = $crawler->filter('#' . Builder::ROOT_MENU_ITEM_MANAGEMENT_MENU . '.collapse.in');

        $this->assertCount(0, $mainMenu);
        $this->assertCount(1, $managementMenu);
    }

    public function testBadgeDefault()
    {
        $crawler = $this->getListCrawler(self::URL_CHESSBOARD);

        $badge = $crawler->filter('.parent_badge');
        $arrivals = $badge->filter('.bg-red');
        $out = $badge->filter('.bg-green');

        $this->assertCount(1, $arrivals);
        $this->assertCount(0, $out);
    }

    /**
     * @depends testBadgeDefault
     */
    public function testBadgeOnlyOut()
    {
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');

        $packages = $dm->getRepository('MBHPackageBundle:Package')->findAll();

        foreach ($packages as &$package) {
            $package->setIsCheckIn(true);
            $package->setEnd(new \DateTime('midnight'));
        }
        unset($package);
        $dm->flush($packages);

        $crawler = $this->getListCrawler(self::URL_CHESSBOARD);

        $badge = $crawler->filter('.parent_badge');
        $arrivals = $badge->filter('.bg-red');
        $out = $badge->filter('.bg-green');

        $this->assertCount(0, $arrivals);
        $this->assertCount(1, $out);
    }

    /**
     * @depends testBadgeOnlyOut
     */
    public function testBadgeBoth()
    {
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');

        $packages = $dm->getRepository('MBHPackageBundle:Package')->findAll();

        $halfAmount = count($packages) / 2;
        $i = 0;
        foreach ($packages as &$package) {
            if ($i > $halfAmount) {
                break;
            }
            $package->setIsCheckIn(false);
            $package->setEnd(new \DateTime('+20 days midnight'));
            $i++;
        }
        unset($package);

        $dm->flush($packages);

        $crawler = $this->getListCrawler(self::URL_CHESSBOARD);

        $badge = $crawler->filter('.parent_badge');
        $arrivals = $badge->filter('.bg-red');
        $out = $badge->filter('.bg-green');

        $this->assertCount(1, $arrivals);
        $this->assertCount(1, $out);
    }

    public function testBadgeWithoutPackeges()
    {
        $mongo = $this->getContainer()->get('mbh.mongo');

        $mongo->dropCollection('Packages');

        $crawler = $this->getListCrawler(self::URL_CHESSBOARD);

        $badge = $crawler->filter('.parent_badge');

        $this->assertCount(0, $badge);
    }

    public function testSecurity()
    {
        $this->attachManagerToTheHotel();

        $crawlerAdmin = $this->getListCrawler(self::URL_PACKAGE_ORGANIZATIONS_LIST);

        $amountAdminMenuItems = $crawlerAdmin->filter('.sidebar-accordion li')->count();


        $this->client = self::createClient(
            [],
            [
                'PHP_AUTH_USER' => self::USER_MANAGER,
                'PHP_AUTH_PW'   => self::USER_MANAGER,
            ]
        );

        $crawlerManager = $this->client->request('GET', self::URL_PACKAGE_ORGANIZATIONS_LIST);
        $amountManagerMenuItems = $crawlerManager->filter('.sidebar-accordion li')->count();

        $this->assertGreaterThan($amountManagerMenuItems, $amountAdminMenuItems);

        $this->assertEquals(self::ACTUAL_AMOUNT_ITEMS_FOR_MANAGER, $amountManagerMenuItems);
    }

    public function testSidebarHotelNotFound()
    {
        $mongo = $this->getContainer()->get('mbh.mongo');

        $mongo->dropCollection('Hotels');


        $crawler = $this->getListCrawler(self::URL_NOT_FOUND_HOTEL);

        $createHotel = $crawler->filter('#' . Builder::ROOT_MENU_ITEM_CREATE_HOTEL_MENU);

        $this->assertCount(1, $createHotel);
    }

    private function attachManagerToTheHotel(): void
    {
        $container = $this->getContainer();

        /** @var User $user */
        $user = $container->get('doctrine.odm.mongodb.document_manager')
            ->getRepository('MBHUserBundle:User')
            ->findOneBy([
                'username' => self::USER_MANAGER,
            ]);

        /** @var Hotel $hotel */
        $hotel = $container->get('doctrine.odm.mongodb.document_manager')
            ->getRepository('MBHHotelBundle:Hotel')
            ->findOneBy(['fullTitle' => $this->nameTestHotel]);

        $container
            ->get('mbh.acl_document_owner_maker')
            ->insertAcl($user, $hotel);
    }
}