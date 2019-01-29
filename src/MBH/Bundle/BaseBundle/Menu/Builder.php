<?php

namespace MBH\Bundle\BaseBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use MBH\Bundle\BaseBundle\Lib\Menu\BadgesHolder;
use MBH\Bundle\HotelBundle\Document\QueryCriteria\TaskQueryCriteria;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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
        $this->setConfig();
        $this->parseOptions($options);

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
                    'route' => 'package_new_search',
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

        $warehouse = [
            'warehouse_record' => [
                'options'    => [
                    'route' => 'warehouse_record',
                    'label' => 'menu.label.warehouse',
                ],
                'attributes' => ['icon' => 'fa fa-book'],
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

        $menu =  $this->createRootItem(self::ROOT_MENU_ITEM_MAIN_MENU, 'menu.header.navigation', false);

        // packages
        $menu->addChild($this->createItem($packages));

        // search
        $menu->addChild($this->createItem($search));

        // Reception
        $menu->addChild($this->itemsReception());

        // Task
        $menu->addChild($this->createItem($this->getTask()));

        // Prices links
        $menu->addChild($this->itemsPricesLinks());

        // cash
        $menu->addChild($this->createItem($cash));

        // warehouse
        $menu->addChild($this->createItem($warehouse));

        // restaurant
        $menu->addChild($this->createItem($restaurant));

        // reports
        $menu->addChild($this->itemsReport());

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

        $menu =  $this->createRootItem(self::ROOT_MENU_ITEM_MANAGEMENT_MENU, 'Настройки', false);

        // Hotel links
        $menu->addChild($this->itemsHotelLinks());

        // Restaurant links
        $menu->addChild($this->itemsRestaurantLinks());

        // Users links
        $menu->addChild($this->itemsUsersLinks());

        // Services links
        $menu->addChild($this->itemsServicesManager());

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
        $menu = $this->checkAndOpenRootMenu($menu);

        return empty($this->counter) ? $this->factory->createItem('root') : $menu;
    }

    /**
     * @param ItemInterface $menu
     * @return ItemInterface
     */
    public function filterMenu(ItemInterface $menu)
    {
        if ($menu->getUri() == $this->getTitleUrl()) {
            $menu->setCurrent(true);
            $this->isCurrent = true;
        }

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
            $reg = '/\@Security\(\"is_granted\(\'(ROLE\_ . +?)\'\)(?:\s((?:or|\|\|)|(?:and|\&\&))\sis_granted\(\'(ROLE\_ . +?)\'\))?\"\)/ixu';
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
            if ($this->getTitleUrl() === null && $isOpen) {
                $cssClass[] = 'in';
            }
        }

        $menu->setChildrenAttributes(
            [
                'class'           => implode(' ', $cssClass),
                'id'              => $id,
                'enabledCollapse' => $collapse,
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
                    'route' => 'report_accommodation',
                    'label' => 'menu.label.portie.shah',
                ],
                'attributes' => ['icon' => 'fa fa-table'],
            ],
        ];
    }

    private function getTask(): array
    {
        /** @var UserInterface $user */
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

    private function itemsServicesManager(): ItemInterface
    {
        $channelManager = [
            'channel_manager' => [
                'options'    => [
                    'route' => '_welcome',
                    'label' => 'Взаимодействие',
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


        $booking = [];
        $myAllLocator = [];
        $ostrovok = [];
        $vashotel = [];
        $oktogo = [];

        if ($this->container->get('kernel')->getEnvironment() === 'prod') {
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

            $oktogo = [
                'oktogo' => [
                    'options'    => [
                        'route' => 'oktogo',
                        'label' => 'Oktogo.ru',
                    ],
                    'attributes' => ['icon' => 'fa fa-cloud-download'],
                ],
            ];

        }

        $onlineForm = [
            'online_form' => [
                'options'    => [
                    'route' => 'online_form',
                    'label' => 'menu.communication.label.onlineform',
                ],
                'attributes' => ['icon' => 'fa fa-globe'],
            ],
        ];

        $paymentForm = [
            'payment_form' => [
                'options'    => [
                    'route' => 'online_payment_form',
                    'label' => 'menu.communication.label.online_payment_form',
                ],
                'attributes' => ['icon' => 'fa fa-money'],
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

        $invite = [
            'invite' => [
                'options'    => [
                    'route' => 'invite',
                    'label' => 'Визовое приглашение',
                ],
                'attributes' => ['icon' => 'fa fa-star'],
            ],
        ];

        $parent = $this->createItem($channelManager);

        return $parent->setChildren($this->getItemsInArray([
            $booking,
            $myAllLocator,
            $ostrovok,
            $vashotel,
            $oktogo,
            $hundredOneHotel,
            $onlineForm,
            $paymentForm,
            $onlinePolls,
            $invite
        ]));

    }

    /**
     * @return ItemInterface
     */
    private function itemsUsersLinks(): ItemInterface
    {
        $configs = [
            'configs' => [
                'options'    => [
                    'route' => '_welcome',
                    'label' => 'Настройки',
                ],
                'attributes' => [
                    'dropdown' => true,
                    'icon'     => 'fa fa-cog',
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
                    'label' => 'Причины удаления',
                ],
                'attributes' => ['icon' => 'fa fa-compass'],
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

        $parameters = [
            'config' => [
                'options'    => [
                    'route' => 'client_config',
                    'label' => 'menu.configs.parameters',
                ],
                'attributes' => ['icon' => 'fa fa-cog'],
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

        $parent = $this->createItem($configs);

        return $parent->setChildren(
            $this->getItemsInArray([
                $users,
                $groups,
                $source,
                $deleteReasons,
                $documentTemplates,
                $parameters,
                $taskType,
                $warehouseConfig,
            ])
        );
    }

    /**
     * @return ItemInterface
     */
    private function itemsRestaurantLinks(): ItemInterface
    {

        $restaurant = [
            'restaurant' => [
                'options'    => [
                    'route' => '_welcome',
                    'label' => 'Ресторан',
                ],
                'attributes' => [
                    'dropdown' => true,
                    'icon'     => 'fa fa-cutlery',
                ],
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

        $parent = $this->createItem($restaurant);

        return $parent->setChildren(
            $this->getItemsInArray([
                $ingredients,
                $dishmenu,
            ])
        );
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
                $tariff,
                $overview,
                $roomCache,
                $priceCache,
                $restrictions,
                $services,
                $promotions,
                $special,
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

        $parentOptions = ['route' => '_welcome', 'label' => 'menu.label.portie',];
        $parentAttr = ['dropdown' => true, 'icon' => 'fa fa-bell'] + $badges->addInAttributes();

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

        $parent = $this->createItem($porterLink);

        return $parent->setChildren(
            $this->getItemsInArray([
                $reportRoomType,
                $reportPorter,
                $this->getChessboardData(),
            ])
        );
    }

    private function itemsReport(): ItemInterface
    {
//        /*$menu['reports']->addChild('report_fms', ['route' => 'report_fms', 'label' => 'Для ФМС'])
//            ->setAttributes(['icon' => 'fa fa-file-archive-o']);*/

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

        $reportFilling = [
            'report_filling' => [
                'options'    => [
                    'route' => 'report_filling',
                    'label' => 'menu.label.reports.filling',
                ],
                'attributes' => ['icon' => 'fa fa-hourglass-half'],
            ],
        ];

        $reportUser = [
            'report_user' => [
                'options'    => [
                    'route' => 'report_users',
                    'label' => 'menu.label.reports.managers',
                ],
                'attributes' => ['icon' => 'fa fa-user'],
            ],
        ];

        $reportInvite = [
            'report_invite' => [
                'options' => [
                    'route' => 'report_invite',
                    'label' => 'Визовые приглашения',
                ],
                'attributes' => ['icon' => 'fa fa-map'],
            ]
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

        $packageMoving = [
            'package_moving' => [
                'options'    => [
                    'route' => 'package_moving',
                    'label' => 'Перемешение',
                ],
                'attributes' => ['icon' => 'fa fa-arrows-v'],
            ],
        ];

        $dinamicSale = [
            'dynamic_sale' => [
                'options'    => [
                    'route' => 'dynamic_sales',
                    'label' => 'Динамика продаж',
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

        $reportDistribution = [
            'distribution_report' => [
                'options'    => [
                    'route' => 'distribution_by_days_of_the_week',
                    'label' => 'distribution_by_days_report.title',
                ],
                'attributes' => ['icon' => 'fa fa-check-square-o'],
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

        $reportWindows = [];

        if ($this->config && $this->config->getSearchWindows()) {
            $reportWindows = [
                'report_windows' => [
                    'options'    => [
                        'route' => 'report_windows',
                        'label' => 'Окна',
                    ],
                    'attributes' => ['icon' => 'fa fa-windows'],
                ],
            ];
        }

        $reportWorkShift = [
            'report_work_shift' => [
                'options'    => [
                    'route' => 'report_work_shift',
                    'label' => 'Рабочие смены',
                ],
                'attributes' => ['icon' => 'fa fa-clock-o'],
            ],
        ];

        $parent = $this->createItem($reports);

        return $parent->setChildren(
            $this->getItemsInArray([
                $serviceList,
                $clients,
                $organizations,
                $reportFilling,
                $reportUser,
                $reportInvite,
                $analytic,
                $reportPolls,
                $packageMoving,
                $dinamicSale,
                $packagesDailyReport,
                $reportDistribution,
                $reservationReport,
                $reportWindows,
                $reportWorkShift,
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
}
