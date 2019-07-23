<?php

namespace MBH\Bundle\BaseBundle\Menu;

use Documents\User;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use MBH\Bundle\BaseBundle\Lib\Menu\BadgesHolder;
use MBH\Bundle\ChannelManagerBundle\Services\Airbnb\Airbnb;
use MBH\Bundle\HotelBundle\Document\QueryCriteria\TaskQueryCriteria;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Builder
{
    const ROOT_MENU_ITEM_CREATE_HOTEL_MENU = 'create-hotel-menu';

    const ROOT_MENU_ITEM_MANAGEMENT_MENU = 'management-menu';

    const ROOT_MENU_ITEM_MAIN_MENU = 'main-menu';

    /**
     * @var \MBH\Bundle\ClientBundle\Document\ClientConfig
     */
    protected $config;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var string|null
     */
    private $titleUrl = null;

    /**
     * @var bool
     */
    private $isCurrent = false;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    private $currentRoute;

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationChecker
     */
    private $security;

    /**
     * @var string
     */
    private $behavior;

    /**
     * @var int
     */
    protected $counter = 0;

    public function __construct(FactoryInterface $factory, ContainerInterface $container)
    {
        $this->factory = $factory;
        $this->container = $container;

        $this->currentRoute = $this->container->get('router');
        $this->currentRoute->getContext()->setMethod('GET');
        $this->security = $this->container->get('security.authorization_checker');

        $this->setConfig();
    }

    /**
     * Main menu
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array $options
     * @return \Knp\Menu\MenuItem
     */
    public function mainMenu(array $options)
    {
        $this->parseOptions($options);


        $dm = $this->container->get('doctrine_mongodb')->getManager();
        $hotel = $this->container->get('mbh.hotel.selector')->getSelected();

        $arrivals = $dm->getRepository('MBHPackageBundle:Package')->countByType('arrivals', true, $hotel);
        $out = $dm->getRepository('MBHPackageBundle:Package')->countByType('out', true, $hotel);

        $badges = new BadgesHolder();
        if ($arrivals) {
            $badges->addBadge(
                'arrivals',
                'bg-red',
                $this->container->get('translator')->trans('menu.help.noarrival'),
                $arrivals
            );
        }
        if ($out) {
            $badges->addBadge(
                'out',
                'bg-green',
                $this->container->get('translator')->trans('menu.help.nodepart'),
                $out
            );
        }


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


        $menu = $this->createRootItem(self::ROOT_MENU_ITEM_MAIN_MENU, 'menu.header.navigation', true, $badges, true);

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

        return $this->filter($menu);
    }

    /**
     * User menu
     * @param \Knp\Menu\FactoryInterface $factory
     * @return \Knp\Menu\MenuItem
     */
    public function managementMenu(array $options)
    {
        $this->parseOptions($options);

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
                'attributes' => ['icon' => 'fa fa-user-circle-o'],
            ],
        ];

        $menu = $this->createRootItemWithCollapse(self::ROOT_MENU_ITEM_MANAGEMENT_MENU, 'menu.settings.label.header');

        // Hotel links
        $menu->addChild($this->itemsHotelLinks());

        // Prices links
        $menu->addChild($this->itemsPricesLinks());

        // channel manager
        $menu->addChild($this->itemsChannelManager());

        // web site
        $menu->addChild($this->itemsWebSite());

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

        return $this->filter($menu);
    }

    /**
     * Create hotel menu
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array $options
     * @return \Knp\Menu\MenuItem
     */
    public function createHotelMenu(array $options)
    {
        $this->parseOptions($options);

        $menu = $this->createRootItem(self::ROOT_MENU_ITEM_CREATE_HOTEL_MENU,'menu.header.navigation');

        $menu->addChild('create_hotel', ['route' => 'hotel_new', 'label' => 'menu.hotel_new.label'])
            ->setAttribute('icon', 'fa fa-plus');

        return $this->filter($menu);
    }

    /**
     * @param ItemInterface $menu
     * @param FactoryInterface $factory
     * @return ItemInterface
     */
    public function filter(ItemInterface $menu)
    {
        $this->counter = 0;
        $menu = $this->filterMenu($menu);
        if ($this->behavior === 'default') {
            $menu = $this->checkAndOpenRootMenu($menu);
        }

        return empty($this->counter) ? $this->factory->createItem('root') : $menu;
    }

    /**
     * @param ItemInterface $menu
     * @return ItemInterface
     */
    public function filterMenu(ItemInterface $menu)
    {

//        $accessMap = $this->container->get('security.access.decision_manager');
//        $request = $this->container->get('request_stack')->getCurrentRequest();


        if ($menu->getUri() == $this->getTitleUrl()) {
            $menu->setCurrent(true);
            $this->isCurrent = true;
        }

        $reg = '/\@Security\(\"is_granted\(\'(ROLE\_ . +?)\'\)(?:\s((?:or|\|\|)|(?:and|\&\&))\sis_granted\(\'(ROLE\_ . +?)\'\))?\"\)/ixu';

        foreach ($menu->getChildren() as $child) {
            if (empty($child->getUri())) {
                continue;
            }
            $metadata = false;

            if ($child->getUri() == $this->getTitleUrl()) {
                $menu->setCurrent(true);
            }

            try {
                $url = str_replace('app_dev.php/', '', parse_url($child->getUri()))['path'];

                $controllerInfo = explode('::', $this->currentRoute->match($url)['_controller']);

                $rMethod = new \ReflectionMethod($controllerInfo[0], $controllerInfo[1]);

                $metadata = $rMethod->getDocComment();
            } catch (\Exception $e) {
                $menu->removeChild($child);
                continue;
            }
            preg_match($reg, $metadata, $roles);

            if (empty($metadata) || empty($roles[1])) {
                continue;
            }

            $isAccessed = $this->security->isGranted($roles[1]);

            if (!empty($roles[2])) {
                $or = ['or','||'];
                $and = ['and', '&&'];
                if ((in_array($roles[2],$and) && $isAccessed) || (!$isAccessed && in_array($roles, $or))) {
                    $isAccessed = $this->security->isGranted($roles[3]);
                }
            }

            if (!$isAccessed) {
                $menu->removeChild($child);
            } elseif (empty($child->getAttribute('dropdown'))) {
                $this->counter += 1;
            }

            $this->filterMenu($child);
        }

        return $menu;
    }

    protected function setConfig()
    {
        if (!$this->config) {
            $this->config = $this->container->get('doctrine_mongodb')->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();
        }

        $this->behavior = $this->container->getParameter('mbh.menu.behaviors.now');
    }

    /**
     * @param ItemInterface $menu
     * @return ItemInterface
     */
    private function checkAndOpenRootMenu(ItemInterface $menu): ItemInterface
    {
        if ($this->getTitleUrl() !== null && $this->isCurrent) {
            $attr = $menu->getChildrenAttributes();
            if (isset($attr['class']) && strpos($attr['class'], 'collapse') !== false) {
                $attr['class'] .= ' in';
                $menu->setChildrenAttributes($attr);
            }
            $this->isCurrent = false;
        }

        return $menu;
    }

    /**
     * @param array $options
     */
    private function parseOptions(array $options): void
    {
        if (!empty($options['title_url'])) {
            $this->setTitleUrl($options['title_url']);
        }
    }

    /**
     * @param $url
     */
    private function setTitleUrl($url): void
    {
        $this->titleUrl = $url;
    }

    /**
     * @return null|string
     */
    private function getTitleUrl(): ?string
    {
        return $this->titleUrl;
    }

    /**
     * @param string $id    css id
     * @param string $label
     * @param bool $isOpen
     * @return ItemInterface
     */
    private function createRootItemWithCollapse(string $id, string $label, bool $isOpen = false, BadgesHolder $badges = null): ItemInterface
    {
        return $this->createRootItem($id, $label, true, $badges, $isOpen);
    }

    /**
     * @param string $id     css id
     * @param string $label
     * @param bool $collapse
     * @param bool $isOpen
     * @return ItemInterface
     */
    private function createRootItem(
        string $id,
        string $label,
        bool $collapse = false,
        BadgesHolder $badges = null,
        bool $isOpen = false
    ): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        $cssClass = [];
        $cssClass[] = 'sidebar-menu';

        if ($collapse) {
            $cssClass[] = 'collapse';
            $this->behaviorHandler($cssClass,$id,$isOpen);
        }

        $attr = [
            'class'           => implode(' ', $cssClass),
            'id'              => $id,
            'enabledCollapse' => $collapse,
        ];

        if ($badges !== null) {
            $attr = array_merge($attr, $badges->addInAttributes());
        }

        $menu->setChildrenAttributes($attr);
        $menu->setLabel($label);

        return $menu;
    }

    /**
     * Поведение меню:
     * - alwaysOpen не в зависимости от выбранного пункта все главные меню открыты
     * - custom запоминается последнее открытое меню (на фронтэнде) и в следующий раз открывается именно оно
     * - default открыто то меню пункт которого выбран
     *
     * @param array $cssClass
     * @param string $id
     * @param bool $isOpen
     */
    private function behaviorHandler(array &$cssClass, string $id, bool $isOpen): void
    {
        switch ($this->behavior){
            case 'alwaysOpen':
                $cssClass[] = 'in';
                break;
            case 'custom':
                /*
                 * Можно перенести всю логику сюда с клиентской стороны,
                 * нужно добавить ajax`ом запись (допустим в клиет конфиг) о открытом меню
                 * и здесь уже "вершить дела"
                 */
                break;
            default:
                if (($this->getTitleUrl() === null && $isOpen)) {
                    $cssClass[] = 'in';
                }
        }
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

        if (!empty($params['attributes']['badges'])) {
            $item->setLinkAttribute('class', 'content-badge');
        }

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
            if ($item === []) {
                continue;
            }
            $items[array_keys($item)[0]] = $this->createItem($item);
        }

        return $items;
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
                    'route' => 'myallocator',
                    'label' => 'menu.communication.label.advanced',
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

        $airbnb = [
            Airbnb::NAME => [
                'options' => [
                    'route' => Airbnb::NAME,
                    'label' => 'Airbnb'
                ],
                'attributes' => ['icon' => 'fa fa-cloud-download'],
            ]
        ];
        $facebook = [
            'facebook' => [
                'options'    => [
                    'route' => 'facebook',
                    'label' => 'Facebook',
                ],
                'attributes' => ['icon' => 'fa fa-cloud-download'],
            ],
        ];

        $parent = $this->createItem($channelManager);

        return $parent->setChildren($this->getItemsInArray([
            $booking,
            $expedia,
            $myAllLocator,
            $ostrovok,
            $hundredOneHotel,
            $vashotel,
            $airbnb,
            $facebook
        ]));

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
        $parent = $this->createItem($webSite);

        $menuItems = [];

        if ($this->config->isMBSiteEnabled()) {
            $menuItems[] = [
                'site_settings' => [
                    'options'    => [
                        'route' => 'site_settings',
                        'label' => 'menu.communication.label.site_settings',
                    ],
                    'attributes' => [
                        'icon' => 'fa fa-cog',
                    ],
                ],
            ];
        }

        $menuItems[] = [
            'online_form' => [
                'options'    => [
                    'route' => 'online_form',
                    'label' => 'menu.communication.label.onlineform',
                ],
                'attributes' => ['icon' => 'fa fa-globe'],
            ],
        ];

        $menuItems[] = [
            'payment_form' => [
                'options'    => [
                    'route' => 'online_payment_form',
                    'label' => 'menu.communication.label.online_payment_form',
                ],
                'attributes' => ['icon' => 'fa fa-money'],
            ],
        ];

        $menuItems[] = [
            'online_polls' => [
                'options'    => [
                    'route' => 'online_poll_config',
                    'label' => 'menu.communication.label.polls',
                ],
                'attributes' => ['icon' => 'fa fa-star'],
            ],
        ];

        $menuItems[] = [
            'payment_systems' => [
                'options'    => [
                    'route' => 'client_payment_systems',
                    'label' => 'menu.label.payment_systems',
                ],
                'attributes' => ['icon' => 'fa fa-credit-card'],
            ],
        ];

        return $parent->setChildren($this->getItemsInArray($menuItems));
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

        $salesChannelsReport = [
            'sales_channels_report' => [
                'options'    => [
                    'route' => 'sales_channels_report',
                    'label' => 'sales_channels_report.title',
                ],
                'attributes' => ['icon' => 'fa fa-compass'],
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
                $salesChannelsReport,
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

        $reservationReport = [
            'reservation_report' => [
                'options'    => [
                    'route' => 'reservation_report',
                    'label' => 'reservation_report.title',
                ],
                'attributes' => ['icon' => 'fa fa-paper-plane-o'],
            ],
        ];

        $parent = $this->createItem($reports);

        return $parent->setChildren(
            $this->getItemsInArray([
                $serviceList,
                $reportPolls,
                $reportDistribution,
                $reservationReport,
            ])
        );
    }

    /**
     * @return ItemInterface
     */
    private function itemsHotelServices(): ItemInterface
    {
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

        $parent = $this->createItem($serviceHotel);

        return $parent->setChildren(
            $this->getItemsInArray([
//                $this->getTask(),
                $warehouse,
                $restaurant,
            ])
        );
    }

    private function getTask(): array
    {
        /** @var User $user */
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $hotel = $this->container->get('mbh.hotel.selector')->getSelected();

        $taskAttributes = ['icon' => 'fa fa-tasks'];

        if ($user instanceof User) {
            //Tasks links
            $queryCriteria = new TaskQueryCriteria();
            $queryCriteria->userGroups = $user->getGroups();
            $queryCriteria->performer = $user;
            $queryCriteria->onlyOwned = true;
            $queryCriteria->status = 'open';
            $queryCriteria->hotel = $hotel;

            $openTaskCount = $this->container->get('mbh.hotel.task_repository')->getCountByCriteria($queryCriteria);

            if ($openTaskCount > 0) {
                $taskAttributes = array_merge(
                    $taskAttributes,
                    BadgesHolder::createOne(
                        'task-counter',
                        'bg-red',
                        '',
                        $openTaskCount
                    )
                );
            }
        }

        $task = [
            'task' => [
                'options'    => [
                    'route' => 'task',
                    'label' => 'menu.label.task',
                ],
                'attributes' => $taskAttributes,
            ],
        ];

        return $task;
    }

    /**
     * @return ItemInterface
     */
    private function itemsReception(): ItemInterface
    {
        $parentOptions = [
            'route' => '_welcome',
            'label' => 'menu.label.portie',
        ];

        $porterLink = [
            'porter_links' => [
                'options'    => $parentOptions,
                'attributes' => ['dropdown' => true, 'icon' => 'fa fa-bell'],
            ],
        ];

        $reportRoomType = [
            'report_room_types' => [
                'options'    => [
                    'route' => 'report_room_types',
                    'label' => 'menu.label.navigation',
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
                $reportRoomType,
                $clients,
                $organizations,
            ])
        );
    }
}
