<?php

namespace MBH\Bundle\BaseBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class Builder
{
    /**
     * @var \MBH\Bundle\ClientBundle\Document\ClientConfig
     */
    protected $config;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(FactoryInterface $factory, ContainerInterface $container)
    {
        $this->factory = $factory;
        $this->container = $container;
    }

    protected function setConfig()
    {
        if (!$this->config) {
            $this->config = $this->container->get('doctrine_mongodb')->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();
        }
    }

    protected $counter = 0;

    /**
     * Main menu
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array $options
     * @return \Knp\Menu\MenuItem
     */
    public function mainMenu(array $options)
    {
        $this->setConfig();

        /** @var UserInterface $user */
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        $packages = [
            'package' => [
                'options'    => [
                    'route' => 'package',
                    'label' => 'menu.label.broni',
                ],
                'attributes' => ['icon' => 'fa fa-paper-plane-o'],
            ],
        ];

        $search = [
            'reservations' => [
                'options'    => [
                    'route' => 'package_search',
                    'label' => 'menu.label.podbor',
                ],
                'attributes' => ['icon' => 'fa fa-search'],
            ],
        ];

        $cash = [
            'cash' => [
                'options'    => [
                    'route' => 'cash',
                    'label' => 'mbhbasebundle.view.navbar.kassa',
                ],
                'attributes' => [
                    'icon' => $this->container->get('mbh.currency')->info()['icon'],
                ],
            ],
        ];

        $menu = $this->createRootItemWithCollapse('main-menu', 'menu.header.navigation', true);

        // chessboard
        $menu->addChild($this->createItem($this->getChessboardData()));

        // search
        $menu->addChild($this->createItem($search));

        // packages
        $menu->addChild($this->createItem($packages));

        // Reception
        $menu->addChild($this->itemsReception());

        // cash
        $menu->addChild($this->createItem($cash));

        // hotel services
        $menu->addChild($this->itemsHotelServices());

        // reports
        $menu->addChild($this->itemsReport());

        // financial analytics
        $menu->addChild($this->itemsFinancialAnalytics());

        return $this->filter($menu, $options);
    }

    /**
     * User menu
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array $options
     * @return \Knp\Menu\MenuItem
     */
    public function managementMenu(array $options)
    {
        $onlineForm = [
            'online_form' => [
                'options'    => [
                    'route' => 'online_form',
                    'label' => 'menu.communication.label.onlineform',
                ],
                'attributes' => ['icon' => 'fa fa-globe'],
            ],
        ];

        $parameters = [
            'config' => [
                'options'    => [
                    'route' => 'client_config',
                    'label' => 'menu.configs.parameters',
                ],
                'attributes' => ['icon' => 'fa fa-cog'],
            ],
        ];

        $profile = [
            'profile' => [
                'options'    => [
                    'route' => 'user_profile',
                    'label' => 'menu.label.profile',
                ],
                'attributes' => ['icon' => 'fa fa-cog'],
            ],
        ];

        $menu = $this->createRootItemWithCollapse('management-menu', 'menu.settings.label.header');

        // Hotel links
        $menu->addChild($this->itemsHotelLinks());

        // Prices links
        $menu->addChild($this->itemsPricesLinks());

        // channel manager
        $menu->addChild($this->itemsChannelManager());

        // web site
        $menu->addChild($this->itemsWebSite());

        // online form
        $menu->addChild($this->createItem($onlineForm));

        // analytics
        $menu->addChild($this->itemsAnalytics());

        // hotel services management
        $menu->addChild($this->itemsHotelServicesManagement());

        // users and roles
        $menu->addChild($this->itemsUsersAndRoles());

        // parameters
        $menu->addChild($this->createItem($parameters));

        // profile
        $menu->addChild($this->createItem($profile));


//        $menu['services']->addChild('invite', ['route' => 'invite', 'label' => 'menu.communication.label.invite'])
//            ->setAttributes(['icon' => 'fa fa-star']);

        return $this->filter($menu, $options);
    }

    /**
     * Create hotel menu
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array $options
     * @return \Knp\Menu\MenuItem
     */
    public function createHotelMenu(array $options)
    {

        $menu = $this->createRootItem('create-hotel-menu','menu.header.navigation');

        $menu->addChild('create_hotel', ['route' => 'hotel_new', 'label' => 'menu.hotel_new.label'])
            ->setAttribute('icon', 'fa fa-plus');

        return $this->filter($menu, $options);
    }

    /**
     * @param ItemInterface $menu
     * @param FactoryInterface $factory
     * @param array $options
     * @return ItemInterface
     */
    public function filter(ItemInterface $menu, array $options)
    {
        $this->counter = 0;
        $menu = $this->filterMenu($menu, $options);

        return empty($this->counter) ? $this->factory->createItem('root') : $menu;
    }

    /**
     * @param ItemInterface $menu
     * @param array $options
     * @return ItemInterface
     */
    public function filterMenu(ItemInterface $menu, array $options)
    {
        $router = $this->container->get('router');
        $router->getContext()->setMethod('GET');
        $security = $this->container->get('security.authorization_checker');
        $this->setConfig();

        !empty($options['title_url']) ? $title_url = $options['title_url'] : $title_url = null;

        if ($menu->getUri() == $title_url) {
            $menu->setCurrent(true);
        }

        foreach ($menu->getChildren() as $child) {
            if (empty($child->getUri())) {
                continue;
            }
            $metadata = false;

            if ($child->getUri() == $title_url) {
                $menu->setCurrent(true);
            }

            try {
                $url = str_replace('app_dev.php/', '', parse_url($child->getUri()))['path'];

                $controllerInfo = explode('::', $router->match($url)['_controller']);

                $rMethod = new \ReflectionMethod($controllerInfo[0], $controllerInfo[1]);

                $metadata = $rMethod->getDocComment();
            } catch (\Exception $e) {
                $menu->removeChild($child);
                continue;
            }

            preg_match('/\@Security\(\"is_granted\(\'(.*)\'\)\"\)/ixu', $metadata, $roles);

            if (empty($metadata) || empty($roles[1])) {
                continue;
            }

            if (!$security->isGranted($roles[1])) {
                $menu->removeChild($child);
            } elseif (empty($child->getAttribute('dropdown'))) {
                $this->counter += 1;
            }

            $this->filterMenu($child, $options);
        }

        return $menu;
    }

    /**
     * @param string $id    css id
     * @param string $label
     * @param bool $isOpen
     * @return ItemInterface
     */
    private function createRootItemWithCollapse(string $id, string $label, bool $isOpen = false): ItemInterface
    {
        return $this->createRootItem($id, $label, true, $isOpen);
    }

    /**
     * @param string $id     css id
     * @param string $label
     * @param bool $collapse
     * @param bool $isOpen
     * @return ItemInterface
     */
    private function createRootItem(string $id, string $label, bool $collapse = false, bool $isOpen = false): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        $cssClass = [];
        $cssClass[] = 'sidebar-menu';
        if ($collapse) {
            $cssClass[] = 'collapse';
            if ($isOpen) {
                $cssClass[] = 'in';
            }
        }

        $menu->setChildrenAttributes(
            [
                'class' => implode(' ', $cssClass),
                'id'    => $id,
            ]
        );
        $menu->setLabel($label);

        return $menu;
    }

    /**
     * @return array
     */
    private function getChessboardData(): array
    {
        return [
            'chessboard' => [
                'options'    => [
                    'route' => 'chess_board_home',
                    'label' => 'menu.label.portie.shah',
                ],
                'attributes' => ['icon' => 'fa fa-table'],
            ],
        ];
    }

    /**
     * @param array $data
     * @return ItemInterface
     */
    private function createItem(array $data): ItemInterface
    {
        $child = array_keys($data)[0];

        $params = $data[$child];

        $item = $this->factory->createItem($child, $params['options']);
        $item->setAttributes($params['attributes']);

        return $item;
    }

    /**
     * array $items An array of ItemInterface objects
     *
     * @param array $data
     * @return array
     */
    private function getItemsInArray(array $data): array
    {
        $items = [];
        foreach ($data as $item) {
            $items[] = $this->createItem($item);
        }

        return $items;
    }

    /**
     * @param string $attrHeader
     * @return ItemInterface
     */
    private function getHeaderItem(string $attrHeader): ItemInterface
    {
        $headers = [
            'header' => [
                'options'    => [],
                'attributes' => ['header' => $attrHeader],
            ],
        ];

        return $this->createItem($headers);
    }

    /**
     * @return ItemInterface
     */
    private function itemsUsersAndRoles(): ItemInterface
    {
        $usersAndRoles = [
            'users_and_roles' => [
                'options'    => [
                    'route' => '_welcome',
                    'label' => 'menu.label.users_and_roles',
                ],
                'attributes' => [
                    'dropdown' => true,
                    'icon'     => 'fa fa-male',
                ],
            ],
        ];

        $users = [
            'users' => [
                'options'    => [
                    'route' => 'user',
                    'label' => 'menu.configs.users',
                ],
                'attributes' => ['icon' => 'fa fa-user'],
            ],
        ];

        $groups = [
            'groups' => [
                'options'    => [
                    'route' => 'group',
                    'label' => 'menu.configs.groups',
                ],
                'attributes' => ['icon' => 'fa fa-users'],
            ],
        ];

        $parent = $this->createItem($usersAndRoles);

        return $parent->setChildren(
            $this->getItemsInArray([
                $users,
                $groups,
            ])
        );
    }

    /**
     * @return ItemInterface
     */
    private function itemsHotelServicesManagement(): ItemInterface
    {
        $serviceHotel = [
            'hotel_services' => [
                'options'    => [
                    'route' => '_welcome',
                    'label' => 'menu.label.hotel_services',
                ],
                'attributes' => [
                    'dropdown' => true,
                    'icon'     => 'fa fa-cogs',
                ],
            ],
        ];

        $taskType = [
            'tasktype' => [
                'options'    => [
                    'route' => 'tasktype',
                    'label' => 'menu.configs.tasktype',
                ],
                'attributes' => ['icon' => 'fa fa-cog'],
            ],
        ];

        $warehouseConfig = [
            'warehouse_category' => [
                'options'    => [
                    'route' => 'warehouse_category',
                    'label' => 'menu.configs.warehouse',
                ],
                'attributes' => ['icon' => 'fa fa-book'],
            ],
        ];

        $restaurantsAttribute = ['icon' => 'fa fa-cutlery'];
        $ingredients = [
            'ingredients' => [
                'options'    => [
                    'route' => 'restaurant_ingredient_category',
                    'label' => 'menu.settings.label.restaurant.ingredients',
                ],
                'attributes' => $restaurantsAttribute,
            ],
        ];

        $dishmenu = [
            'dishmenu' => [
                'options'    => [
                    'route' => 'restaurant_dishmenu_category',
                    'label' => 'menu.settings.label.restaurant.dishmenu',
                ],
                'attributes' => $restaurantsAttribute,
            ],
        ];

        $tables = [
            'tables' => [
                'options'    => [
                    'route' => 'restaurant_table_category',
                    'label' => 'menu.settings.label.restaurant.tables',
                ],
                'attributes' => $restaurantsAttribute,
            ],
        ];

        $documentTemplates = [
            'document_templates' => [
                'options'    => [
                    'route' => 'document_templates',
                    'label' => 'menu.configs.templates',
                ],
                'attributes' => ['icon' => 'fa fa-file'],
            ],
        ];

        $parent = $this->createItem($serviceHotel);

        return $parent->setChildren(
            $this->getItemsInArray([
                $taskType,
                $warehouseConfig,
                $ingredients,
                $dishmenu,
                $tables,
                $documentTemplates,
            ])
        );
    }

    /**
     * @return ItemInterface
     */
    private function itemsChannelManager(): ItemInterface
    {
        $channelManager = [
            'channel_manager' => [
                'options'    => [
                    'route' => '_welcome',
                    'label' => 'Channel Manager',
                ],
                'attributes' => [
                    'dropdown' => true,
                    //                    'icon'     => 'fa fa-diamond',
                    'icon'     => 'fa fa fa-arrows-h',
                ],
            ],
        ];

        $hundredOneHotel = [
            'hundredOneHotel' => [
                'options'    => [
                    'route' => 'hundred_one_hotels',
                    'label' => 'menu.communication.label.hundred_one_hotels',
                ],
                'attributes' => ['icon' => 'fa fa-cloud-download'],
            ],
        ];

        $children = [];

        if ($this->container->getParameter('mbh.environment') == 'prod') {
            $booking = [
                'booking' => [
                    'options'    => [
                        'route' => 'booking',
                        'label' => 'Booking.com',
                    ],
                    'attributes' => ['icon' => 'fa fa-cloud-download'],
                ],
            ];

            $myAllLocator = [
                'myallocator' => [
                    'options'    => [
                        'route' => 'channels',
                        'label' => 'enu.communication.label.advanced',
                    ],
                    'attributes' => ['icon' => 'fa fa-cloud-download'],
                ],
            ];

            $ostrovok = [
                'ostrovok' => [
                    'options'    => [
                        'route' => 'ostrovok',
                        'label' => 'Ostrovok',
                    ],
                    'attributes' => ['icon' => 'fa fa-cloud-download'],
                ],
            ];

            $vashotel = [
                'vashotel' => [
                    'options'    => [
                        'route' => 'vashotel',
                        'label' => 'menu.communication.label.your_hotel',
                    ],
                    'attributes' => ['icon' => 'fa fa-cloud-download'],
                ],
            ];

            $hotelInn = [
                'hotelinn' => [
                    'options'    => [
                        'route' => 'hotelinn',
                        'label' => 'Hotel-inn',
                    ],
                    'attributes' => ['icon' => 'fa fa-cloud-download'],
                ],
            ];

            $expedia = [
                'expedia' => [
                    'options'    => [
                        'route' => 'expedia',
                        'label' => 'Expedia',
                    ],
                    'attributes' => ['icon' => 'fa fa-cloud-download'],
                ],
            ];

            $children[] = $booking;
            $children[] = $myAllLocator;
            $children[] = $ostrovok;
            $children[] = $vashotel;
            $children[] = $expedia;
        }

        $children[] = $hundredOneHotel;

        $parent = $this->createItem($channelManager);

        return $parent->setChildren($this->getItemsInArray($children));

    }

    /**
     * @return ItemInterface
     */
    private function itemsAnalytics(): ItemInterface
    {
        $analytic = [
            'analytics' => [
                'options'    => [
                    'route' => '_welcome',
                    'label' => 'menu.label.analytics',
                ],
                'attributes' => [
                    'dropdown' => true,
                    'icon'     => 'fa fa-pie-chart',
                ],
            ],
        ];

        $source = [
            'sources' => [
                'options'    => [
                    'route' => 'package_source',
                    'label' => 'menu.configs.sources',
                ],
                'attributes' => ['icon' => 'fa fa-compass'],
            ],
        ];

        $deleteReasons = [
            'delete_reasons' => [
                'options'    => [
                    'route' => 'package_delete_reasons',
                    'label' => 'menu.configs.delete_reasons',
                ],
                'attributes' => ['icon' => 'fa fa-compass'],
            ],
        ];

        $parent = $this->createItem($analytic);

        return $parent->setChildren(
            $this->getItemsInArray([
                $source,
                $deleteReasons,
            ])
        );
    }

    /**
     * @return ItemInterface
     */
    private function itemsWebSite(): ItemInterface
    {
        $webSite = [
            'web_site' => [
                'options'    => [
                    'route' => '_welcome',
                    'label' => 'menu.label.web_site',
                ],
                'attributes' => [
                    'dropdown' => true,
                    'icon'     => 'fa fa-globe',
                ],
            ],
        ];

        $siteSettings = [
            'site_settings' => [
                'options'    => [
                    'route' => '_welcome',
                    'label' => 'Site Settings',
                ],
                'attributes' => [
                    'icon' => 'fa fa-cog',
                ],
            ],
        ];

        $onlinePolls = [
            'online_polls' => [
                'options'    => [
                    'route' => 'online_poll_config',
                    'label' => 'menu.communication.label.polls',
                ],
                'attributes' => ['icon' => 'fa fa-star'],
            ],
        ];

        $paymentSystem = [
            'payment_systems' => [
                'options'    => [
                    'route' => 'client_payment_systems',
                    'label' => 'menu.label.payment_systems',
                ],
                'attributes' => ['icon' => 'fa fa-paperclip'],
            ],
        ];

        $parent = $this->createItem($webSite);

        return $parent->setChildren(
            $this->getItemsInArray([
//                $siteSettings,
$onlinePolls,
$paymentSystem,
            ])
        );
    }

    /**
     * @return ItemInterface
     */
    private function itemsPricesLinks(): ItemInterface
    {
        $prices = [
            'prices' => [
                'options'    => [
                    'route' => '_welcome',
                    'label' => 'menu.label.nomandprice',
                ],
                'attributes' => [
                    'dropdown' => true,
                    'icon'     => $this->container->get('mbh.currency')->info()['icon'],
                ],
            ],
        ];

        $overview = [
            'overview' => [
                'options'    => [
                    'route' => 'room_overview',
                    'label' => 'menu.label.nomandprice.overview',
                ],
                'attributes' => ['icon' => 'fa fa-info-circle'],
            ],
        ];

        $tariff = [
            'tariff' => [
                'options'    => [
                    'route' => 'tariff',
                    'label' => 'menu.label.nomandprice.tariff',
                ],
                'attributes' => ['icon' => 'fa fa-sliders'],
            ],
        ];

        $roomCache = [
            'room_cache' => [
                'options'    => [
                    'route' => 'room_cache_overview',
                    'label' => 'menu.label.nomandprice.roomsell',
                ],
                'attributes' => ['icon' => 'fa fa-bed'],
            ],
        ];

        $priceCache = [
            'price_cache' => [
                'options'    => [
                    'route' => 'price_cache_overview',
                    'label' => 'menu.label.nomandprice.prices',
                ],
                'attributes' => [
                    'icon' => $this->container->get('mbh.currency')->info()['icon'],
                ],
            ],
        ];

        $restrictions = [
            'restrictions' => [
                'options'    => [
                    'route' => 'restriction_overview',
                    'label' => 'menu.label.nomandprice.restriction',
                ],
                'attributes' => ['icon' => 'fa fa-exclamation-circle'],
            ],
        ];

        $services = [
            'services' => [
                'options'    => [
                    'route' => 'price_service_category',
                    'label' => 'menu.label.nomandprice.services',
                ],
                'attributes' => ['icon' => 'fa fa-plug'],
            ],
        ];

        $promotions = [
            'service' => [
                'options'    => [
                    'route' => 'promotions',
                    'label' => 'menu.label.nomandprice.promotions',
                ],
                'attributes' => ['icon' => 'fa fa-bookmark'],
            ],
        ];

        $special = [
            'special' => [
                'options'    => [
                    'route' => 'special',
                    'label' => 'special.title',
                ],
                'attributes' => ['icon' => 'fa fa-star'],
            ],
        ];

        $parent = $this->createItem($prices);

        return $parent->setChildren(
            $this->getItemsInArray([
                $overview,
                $tariff,
                $roomCache,
                $priceCache,
                $restrictions,
                $promotions,
                $special,
                $services,
            ])
        );
    }

    /**
     * @return ItemInterface
     */
    private function itemsHotelLinks(): ItemInterface
    {
        $hotels = [
            'hotels' => [
                'options'    => [
                    'route' => '_welcome',
                    'label' => 'menu.settings.label.hotels',
                ],
                'attributes' => [
                    'dropdown' => true,
                    'icon'     => 'fa fa-home',
                ],
            ],
        ];

        $hotelsLink = [
            'hotelsList' => [
                'options'    => [
                    'route' => 'hotel',
                    'label' => 'menu.settings.label.hotels',
                ],
                'attributes' => ['icon' => 'fa fa-home'],
            ],
        ];

        $corpus = [
            'corpusList' => [
                'options'    => [
                    'route' => 'housing',
                    'label' => 'menu.settings.label.housing',
                ],
                'attributes' => ['icon' => 'fa fa-building'],
            ],
        ];

        $hotelRoomType = [
            'hotelsRoomTypes' => [
                'options'    => [
                    'route' => 'room_type',
                    'label' => 'menu.settings.label.room_types',
                ],
                'attributes' => ['icon' => 'fa fa-bed'],
            ],
        ];

        $children = [$hotelsLink, $corpus, $hotelRoomType];

        if ($this->config && $this->config->getUseRoomTypeCategory()) {
            $roomTypeCategory = [
                'room_type_category' => [
                    'options'    => [
                        'route' => 'room_type_category',
                        'label' => 'menu.room_type_category',
                    ],
                    'attributes' => ['icon' => 'fa fa-bed'],
                ],
            ];

            $children[] = $roomTypeCategory;
        }

        $parent = $this->createItem($hotels);

        return $parent->setChildren($this->getItemsInArray($children));
    }

    /**
     * @return ItemInterface
     */
    private function itemsFinancialAnalytics(): ItemInterface
    {
        $finAn = [
            'financial_analytics' => [
                'options'    => [
                    'route' => '_welcome',
                    'label' => 'menu.label.financial_analytics',
                ],
                'attributes' => [
                    'dropdown' => true,
                    'icon'     => 'fa fa-line-chart',
                ],
            ],
        ];

        $filling = [
            'report_filling' => [
                'options'    => [
                    'route' => 'report_filling',
                    'label' => 'menu.label.reports.filling',
                ],
                'attributes' => ['icon' => 'fa fa-hourglass-half'],
            ],
        ];

        $dinamicSale = [
            'dynamic_sale' => [
                'options'    => [
                    'route' => 'dynamic_sales',
                    'label' => 'menu.label.reports.dynamic_sales',
                ],
                'attributes' => ['icon' => 'fa fa-bar-chart'],
            ],
        ];

        $packagesDailyReport = [
            'packages_daily_report' => [
                'options'    => [
                    'route' => 'packages_daily_report',
                    'label' => 'menu.label.reports.daily_report',
                ],
                'attributes' => ['icon' => 'fa fa-money'],
            ],
        ];

        $analytic = [
            'analytics' => [
                'options'    => [
                    'route' => 'analytics',
                    'label' => 'menu.label.reports.analystics',
                ],
                'attributes' => ['icon' => 'fa fa-area-chart'],
            ],
        ];

        $manager = [
            'report_user' => [
                'options'    => [
                    'route' => 'report_users',
                    'label' => 'menu.label.reports.managers',
                ],
                'attributes' => ['icon' => 'fa fa-user'],
            ],
        ];

        $parent = $this->createItem($finAn);

        return $parent->setChildren(
            $this->getItemsInArray([
                $filling,
                $dinamicSale,
                $packagesDailyReport,
                $manager,
                $analytic,
            ])
        );
    }

    /**
     * @return ItemInterface
     */
    private function itemsReport(): ItemInterface
    {
        //      $token = $this->container->get('security.token_storage')->getToken();
        //if ($token && $token->getUser() instanceof User && $token->getUser()->getIsEnabledWorkShift()) {
//        $menu['reports']->addChild(
//            'report_work_shift',
//            ['route' => 'report_work_shift', 'label' => 'menu.label.reports.work_shift']
//        )
//            ->setAttributes(['icon' => 'fa fa-clock-o']);
        //}
//
//
////        $menu['reports']->addChild('report_invite', ['route' => 'report_invite', 'label' => 'menu.label.reports.invite'])
////            ->setAttributes(['icon' => 'fa fa-map']);
//
//        $menu['reports']->addChild('reservation_report',
//            ['route' => 'reservation_report', 'label' => 'reservation_report.title'])
//            ->setAttributes(['icon' => 'fa fa-paper-plane-o']);
//        $menu['reports']->addChild('sales_channels_report',
//            ['route' => 'sales_channels_report', 'label' => 'sales_channels_report.title'])
//            ->setAttributes(['icon' => 'fa fa-compass']);
//
//        if ($this->config && $this->config->getSearchWindows()) {
//            $menu['reports']->addChild('report_windows',
//                ['route' => 'report_windows', 'label' => 'menu.label.reports.windows'])
//                ->setAttributes(['icon' => 'fa fa-windows']);
//        }

        $serviceList = [
            'service_list' => [
                'options'    => [
                    'route' => 'service_list',
                    'label' => 'menu.label.reports.services',
                ],
                'attributes' => ['icon' => 'fa fa-plug'],
            ],
        ];

        $reportPolls = [
            'report_polls' => [
                'options'    => [
                    'route' => 'report_polls',
                    'label' => 'menu.label.reports.polls',
                ],
                'attributes' => ['icon' => 'fa fa-star'],
            ],
        ];

        $reportDistribution = [
            'distribution_report' => [
                'options'    => [
                    'route' => 'distribution_by_days_of_the_week',
                    'label' => 'distribution_by_days_report.title',
                ],
                'attributes' => ['icon' => 'fa fa-check-square-o'],
            ],
        ];

        $reports = [
            'reports' => [
                'options'    => [
                    'route' => '_welcome',
                    'label' => 'menu.label.reports',
                ],
                'attributes' => [
                    'dropdown' => true,
                    'icon'     => 'fa fa-bar-chart',
                ],
            ],
        ];

        $parent = $this->createItem($reports);

        return $parent->setChildren(
            $this->getItemsInArray([
                $serviceList,
                $reportPolls,
                $reportDistribution,
            ])
        );
    }

    /**
     * @return ItemInterface
     */
    private function itemsHotelServices(): ItemInterface
    {
        //Tasks links
//        $queryCriteria = new TaskQueryCriteria();
//        $queryCriteria->userGroups = $user->getGroups();
//        $queryCriteria->performer = $user;
//        $queryCriteria->onlyOwned = true;
//        $queryCriteria->status = 'open';
//        $queryCriteria->hotel = $hotel;
//
//        $openTaskCount = $this->container->get('mbh.hotel.task_repository')->getCountByCriteria($queryCriteria);
//
//        $taskAttributes = ['icon' => 'fa fa-tasks'];
//
//        if ($openTaskCount > 0) {
//            $taskAttributes += [
//                'badge' => true,
//                'badge_class' => 'bg-red',
//                'badge_id' => 'task-counter',
//                'badge_value' => $openTaskCount
//            ];
//        }

//        $menu->addChild('task', ['route' => 'task', 'label' => 'menu.label.task'])->setAttributes($taskAttributes);

        $serviceHotel = [
            'hotel_services' => [
                'options'    => [
                    'route' => '_welcome',
                    'label' => 'menu.label.hotel_services',
                ],
                'attributes' => [
                    'dropdown' => true,
                    'icon'     => 'fa fa-cog',
                ],
            ],
        ];

        $restaurant = [
            'restaurant' => [
                'options'    => [
                    'route' => 'restaurant_dishorder',
                    'label' => 'menu.label.restaurant',
                ],
                'attributes' => ['icon' => 'fa fa-cutlery'],
            ],
        ];

        $warehouse = [
            'warehouse_record' => [
                'options'    => [
                    'route' => 'warehouse_record',
                    'label' => 'menu.label.warehouse',
                ],
                'attributes' => ['icon' => 'fa fa-book'],
            ],
        ];

        $task = [
            'task' => [
                'options'    => [
                    'route' => 'task',
                    'label' => 'menu.label.task',
                ],
                'attributes' => ['icon' => 'fa fa-tasks'],
            ],
        ];

        $parent = $this->createItem($serviceHotel);

        return $parent->setChildren(
            $this->getItemsInArray([
                $task,
                $warehouse,
                $restaurant,
            ])
        );
    }

    /**
     * @return ItemInterface
     */
    private function itemsReception(): ItemInterface
    {
        $dm = $this->container->get('doctrine_mongodb')->getManager();
        $hotel = $this->container->get('mbh.hotel.selector')->getSelected();

        $arrivals = $dm->getRepository('MBHPackageBundle:Package')->countByType('arrivals', true, $hotel);
        $out = $dm->getRepository('MBHPackageBundle:Package')->countByType('out', true, $hotel);

        $porterBadges = [];
        if ($arrivals) {
            $porterBadges += [
                'badge_left'       => true,
                'badge_class_left' => 'bg-red badge-sidebar-left badge-sidebar-margin',
                'badge_id_left'    => 'arrivals',
                'badge_value_left' => $arrivals,
                'badge_title_left' => $this->container->get('translator')->trans('menu.help.noarrival'),
            ];
        }
        if ($out) {
            $porterBadges += [
                'badge_right'       => true,
                'badge_class_right' => 'bg-green badge-sidebar-right badge-sidebar-margin',
                'badge_id_right'    => 'out',
                'badge_value_right' => $out,
                'badge_title_right' => $this->container->get('translator')->trans('menu.help.nodepart'),
            ];
        }

        $parentOptions = ['route' => '_welcome', 'label' => 'menu.label.portie',];
        $parentAttr = ['dropdown' => true, 'icon' => 'fa fa-bell'] + $porterBadges;

        $porterLink = [
            'porter_links' => [
                'options'    => $parentOptions,
                'attributes' => $parentAttr,
            ],
        ];

        $reportRoomType = [
            'report_room_types' => [
                'options'    => [
                    'route' => 'report_room_types',
                    'label' => 'menu.header.navigation',
                ],
                'attributes' => ['icon' => 'fa fa-bed'],
            ],
        ];

        $reportPorter = [
            'report_porter' => [
                'options'    => [
                    'route' => 'report_porter',
                    'label' => 'menu.label.portie.arrdep',
                ],
                'attributes' => ['icon' => 'fa fa-exchange'],
            ],
        ];

        $clients = [
            'clients' => [
                'options'    => [
                    'route' => 'tourist',
                    'label' => 'menu.label.reports.clients',
                ],
                'attributes' => ['icon' => 'fa fa-male'],
            ],
        ];

        $organizations = [
            'organizations' => [
                'options'    => [
                    'route' => 'organizations',
                    'label' => 'menu.label.reports.organizations',
                ],
                'attributes' => ['icon' => 'fa fa-users'],
            ],
        ];

        $parent = $this->createItem($porterLink);

        return $parent->setChildren(
            $this->getItemsInArray([
                $reportPorter,
                $this->getChessboardData(),
                $reportRoomType,
                $clients,
                $organizations,
            ])
        );
    }
}
