<?php
/**
 * Created by PhpStorm.
 * Date: 30.05.18
 */

namespace Tests\Bundle\BaseBundle\Controller;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\BaseBundle\Menu\Builder;

class WelcomeControllerTest extends WebTestCase
{
    private const URL_INDEX = '/';

    private const URL_NOT_FOUND_HOTEL = '/management/hotel/notfound';

    /**
     * страница из mainMenu
     */
    private const URL_CHESSBOARD = '/package/chessboard/';

    /**
     * страница из managementMenu
     */
    private const URL_ROOMTYPE = '/management/hotel/roomtype/';

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

    public function testSidebarHotelNotFound()
    {
        $mongo = $this->getContainer()->get('mbh.mongo');

        $mongo->dropCollection('Hotels');


        $crawler = $this->getListCrawler(self::URL_NOT_FOUND_HOTEL);

        $createHotel = $crawler->filter('#' . Builder::ROOT_MENU_ITEM_CREATE_HOTEL_MENU);

        $this->assertCount(1, $createHotel);
    }
}