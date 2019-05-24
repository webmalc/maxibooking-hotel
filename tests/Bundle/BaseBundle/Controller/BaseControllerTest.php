<?php

namespace Tests\Bundle\BaseBundle\Controller;

use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\ChannelManagerBundle\Services\Airbnb\Airbnb;
use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfig;
use Symfony\Component\Routing\Route;

class BaseControllerTest extends WebTestCase
{
    private const AUTO_SITE_V2 = [
        'mb_site_api_v2_settings',
        'mb_site_api_v2_additional_content',
        'mb_site_api_v2_hotels',
        'mb_site_api_v2_room_types',
        'mb_site_api_v2_min_prices',
        'mb_site_api_v2_facilities_data',
    ];

    private const SEARCH_FORM = [
        FormConfig::ROUTER_NAME_ADDITIONAL_IFRAME,
        FormConfig::ROUTER_NAME_CALENDAR_IFRAME,
        FormConfig::ROUTER_NAME_LOAD_ALL_IFRAME,
        FormConfig::ROUTER_NAME_SEARCH_IFRAME,
        'online_form_iframe'
    ];

    const EXCLUDED_ROUTES_FOR_INVALID_AUTH = [
        "create_region",                //TODO: Какие права нужны? src/MBH/Bundle/BillingBundle/Controller/BillingDataController.php
        "create_city",                  //TODO: Какие права нужны? src/MBH/Bundle/BillingBundle/Controller/BillingDataController.php
    ];

    /**
     * TODO: нет разрешений
     */
    const PREXIX_EXCLUDED_ROUTES_FOR_INVALID_AUTH = 'mbh_online_externalapi';

    const ROUTES_ALWAYS_302 = [
        '_welcome',
        'fos_user_security_logout',
        'fos_user_resetting_check_email',
    ];

    const ROUTES_ALWAYS_200 = [
        'successful_payment',
        'fail_payment',
        'online_poll_js',
        "fos_user_security_login",
        "fos_user_resetting_request",
        'online_api_payment_form_payment',
    ];

    const EXCLUDED_ROUTES = [
        'export_to_kontur',
        'add_tip',
        'user_tariff',
        'lexik_translation_invalidate_cache',
        'booking_packages_sync',
        'remove_payment_system',
        'reset_color_settings',
        'booking_all_packages_sync',
        'user_payer',                       //500 billing
        'payments_list_json',               //500 billing
        'hoh_packages_sync',                //redirect
        'work_shift_wait',                  //redirect, need fixture
        'work_shift_new',                   //redirect, need fixture
        'work_shift_lock',                  //redirect, need fixture
        'report_set_room_status',           //need params
        'report_work_shift_list',           //need params
        'report_work_shift_table',          //need params
        'work_shift_ajax_close',            //need params
        'restaurant_table_save',            //need params
        'site_hotel_settings',              //need params
        'save_list',                        //need params
        'fos_user_profile_edit',            //not used
        'fos_user_profile_show',            //not used
        'api_success_url',                  //master test there is, but if not setting client config -> 404, so common exclude
        'api_fail_url',                     //master test there is, but if not setting client config -> 404, so common exclude
        'reset_login_alias',                //redirect
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
        'document_templates',
        'document_templates_new',
        'document_templates_edit',
        'document_templates_show',
        'document_templates_delete',
        'site_config_social_networking_services',
        'site_settings',
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
        'airbnb_all_packages_sync',
        'airbnb_room_links',
        'ical_room_calendar',
    ];

    /**
     * @var array
     */
    private static $cacheExcludedFor200 = [];

    /**
     * @var array
     */
    private static $cacheExcludedFor401 = [];

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
        /**пустым переопределением убираем двойное создание клиета */
    }

    /**
     * @return array
     */
    public function getRouterFor302(): array
    {
        $routeCollection = $this->getContainer()->get('router')->getRouteCollection();

        $data = [];

        foreach (self::ROUTES_ALWAYS_302 as $routeName) {
            $data[$routeName] = [$routeCollection->get($routeName)->getPath()];
        }

        return $data;
    }

    /**
     * @dataProvider getRouterFor302
     * @param string $url
     */
    public function testRouteAlways302(string $url)
    {
        $client = $this->makeClient(true);
        $client->followRedirects(true);

        $client->request('GET', $url);

        $this->assertStatusCodeWithMsg($url, 200, $client);
    }

    /**
     * @dataProvider urlProvider401
     * @param string $url
     */
    public function testBasicGetRouterInvalidAuth(string $url)
    {
        $client = $this->makeClient(false);
        $client->request('GET', $url);

        $this->assertStatusCodeWithMsg($url, 401, $client);
    }

    /**
     * Test basic get routes (without params)
     * @dataProvider urlProvider200
     * @param string $url
     */
    public function testBasicGetRoutes(string $url)
    {
        $client = $this->makeClient(true);
        $client->request('GET', $url);
        $response = $client->getResponse();

        $this->isSuccessful($response);
        $this->assertGreaterThan(0, mb_strlen($response->getContent()));
    }

    /**
     * @return array
     */
    public function urlProvider401(): array
    {
        return $this->urlProvider(401);
    }

    /**
     * @return array
     */
    public function urlProvider200(): array
    {
        return $this->urlProvider(200);
    }

    /**
     * Get urls
     * @return array
     */
    public function urlProvider(int $forStatus): array
    {
        $routers = array_filter($this->getContainer()->get('router')->getRouteCollection()->all(), function (Route $route, string $routeName) use ($forStatus) {
            $path = $route->getPath();
            if (isset($path[1]) && $path[1] == '_') {
                return false;
            }

            if ($forStatus === 200) {
                if (in_array($routeName, $this->getExcludedFor200())) {
                    return false;
                }
            } elseif ($forStatus === 401) {
                if (in_array($routeName, $this->getExcludedFor401())) {
                    return false;
                }

                if (mb_strpos($routeName, self::PREXIX_EXCLUDED_ROUTES_FOR_INVALID_AUTH) !== false) {
                    return false;
                }
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
    private function commonExclude(): array
    {
        return array_merge(
            self::ROUTERS_CHANNEL_MANAGER,
            self::EXCLUDED_ROUTES,
            self::ROUTES_ALWAYS_302,
            self::AUTO_SITE_V2,
            self::SEARCH_FORM
        );
    }

    /**
     * @return array
     */
    private function getExcludedFor401(): array
    {
        if (self::$cacheExcludedFor401 === []) {
            self::$cacheExcludedFor401 = array_merge(
                $this->commonExclude(),
                self::EXCLUDED_ROUTES_FOR_INVALID_AUTH,
                self::ROUTES_ALWAYS_200
            );
        }

        return self::$cacheExcludedFor401;
    }

    /**
     * @return array
     */
    private function getExcludedFor200(): array
    {
        if (self::$cacheExcludedFor200 === []) {
            self::$cacheExcludedFor200 = array_merge(
                $this->commonExclude(),
                self::ROUTES_WITH_OWN_TEST
            );
        }

        return self::$cacheExcludedFor200;
    }
}
