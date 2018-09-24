<?php

namespace Tests\Bundle\BaseBundle\Controller;

use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\ChannelManagerBundle\Services\Airbnb\Airbnb;
use Symfony\Component\Routing\Route;

class BaseControllerTest extends WebTestCase
{
    const EXCLUDED_ROUTES = [
        'mb_site_main_settings',
        'fos_user_security_logout',
        'export_to_kontur',
        'add_tip',
        'user_tariff',
        'fos_user_profile_edit',
        'lexik_translation_invalidate_cache',
        'fos_user_profile_show',
        'booking_packages_sync',
        'remove_payment_system',
        'reset_color_settings',
        'booking_all_packages_sync',
        'user_payer',                       //500 billing
        'payments_list_json',               //500 billing
        '_welcome',
        'hoh_packages_sync',                //redirect
        'work_shift_wait',                  //redirect, need fixture
        'work_shift_new',                   //redirect, need fixture
        'work_shift_lock',                  //redirect, need fixture
        'api_success_url',                  //need fixture
        'api_fail_url',                     //need fixture
        'report_set_room_status',           //need params
        'report_work_shift_list',           //need params
        'report_work_shift_table',          //need params
        'work_shift_ajax_close',            //need params
        'fos_user_resetting_check_email',   //not found
        'restaurant_table_save',            //need params
        'site_hotel_settings',              //need params
        'save_list',                        //need params
        'site_settings'
    ];

    private const ROUTES_WITH_OWN_TEST = [
        'reservation_report_table',
        'sales_channels_report_table',
        'dynamic_sales_table',
        'restriction_overview',
        'restriction_generator',
        'restriction_overview_table',
        'restriction_overview_save',
        'restriction_generator_save',
        'hotel',
        'hotel_new',
        'hotel_edit_extended',
        'housing',
        'housing_new',
        'room_type',
        'room_type_new',
        'room_type_create',
        'room_type_edit',
        'room_type_update',
        'room_type_delete',
        'room_cache_overview',
        'room_cache_overview_graph',
        'room_cache_overview_table',
        'room_cache_generator',
        'room_cache_generator_save',
        'special',
        'special_new',
        'special_edit',
        'special_delete',
        'tariff',
        'tariff_new',
        'tariff_edit',
        'tariff_delete',
        'user_profile',
        'price_cache_overview',
        'price_cache_overview_table',
        'price_cache_overview_save',
        'price_cache_generator',
        'price_cache_generator_save',
        'cash',
        'cash_json',
        'cash_new',
        'cash_edit',
        'cash_delete',
        'cash_confirm',
        'cash_pay',
        'cash_1c_export',
        'online_form',
        'online_form_new',
        'online_form_edit',
        'online_form_delete',
        'online_payment_form',
        'online_payment_form_new',
        'online_payment_form_edit',
        'online_payment_form_delete',
        'wizard_info',
        'cm_data_warnings',
        'client_payment_systems',
        'remove_payment_system',
        'client_payment_system_save',
        'client_payment_urls',
        'client_save_payment_urls',
    ];

    private const ROUTERS_CHANNEL_MANAGER = [
        'vashotel',
        'vashotel_room',
        'vashotel_tariff',
        'vashotel_service',
        'booking',
        'booking_room',
        'booking_tariff',
        'booking_service',
        'expedia',
        'expedia_tariff',
        'expedia_room',
        'expedia_packages_sync',
        'hundred_one_hotels',
        'hundred_one_hotels_tariff',
        'hundred_one_hotels_room',
        'oktogo',
        'oktogo_room',
        'oktogo_tariff_sync',
        'oktogo_tariff',
        'oktogo_service',
        'myallocator',
        'myallocator_room',
        'myallocator_tariff',
        'myallocator_service',
        'myallocator_user_unlink',
        'ostrovok',
        'ostrovok_room',
        'ostrovok_tariff',
        'ostrovok_service',
        Airbnb::NAME,
        'airbnb_room',
        'airbnb_tariff',
        'airbnb_all_packages_sync'
    ];

    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    /**
     * Test basic get routes (without params)
     * @dataProvider urlProvider
     * @param string $url
     */
    public function testBasicGetRoutes(string $url)
    {
        $client = static::makeClient(true);
        $client->request('GET', $url);
        $response = $client->getResponse();

        if ($response->getStatusCode() == 200) {
            $this->isSuccessful($response);
            $this->assertGreaterThan(0, mb_strlen($response->getContent()));
        }
    }

    /**
     * Get urls
     * @return array
     */
    public function urlProvider()
    {
        $routers = array_filter($this->getContainer()->get('router')->getRouteCollection()->all(), function (Route $route, string $routeName) {
            $path = $route->getPath();
            if (isset($path[1]) && $path[1] == '_') {
                return false;
            }
            if (in_array($routeName, array_merge($this->getCommonArrayExcluded()))) {
                return false;
            }
            if (mb_strpos($path, '{') !== false) {
                return false;
            }
            return !$route->getMethods() || in_array('GET', $route->getMethods());
        }, ARRAY_FILTER_USE_BOTH);

        return array_map(function ($route) {
            return [$route->getPath()];
        }, $routers);
    }

    /**
     * @return array
     */
    private function getCommonArrayExcluded(): array
    {
        return array_merge(
            self::ROUTERS_CHANNEL_MANAGER,
            self::ROUTES_WITH_OWN_TEST,
            self::EXCLUDED_ROUTES
        );
    }
}
