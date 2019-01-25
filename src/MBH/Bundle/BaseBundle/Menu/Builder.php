<?php

namespace MBH\Bundle\BaseBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use MBH\Bundle\HotelBundle\Document\QueryCriteria\TaskQueryCriteria;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Core\User\UserInterface;

class Builder implements ContainerAwareInterface
{

    use ContainerAwareTrait;

    /**
     * @var \MBH\Bundle\ClientBundle\Document\ClientConfig
     */
    protected $config;

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
    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $dm = $this->container->get('doctrine_mongodb')->getManager();
        $hotel = $this->container->get('mbh.hotel.selector')->getSelected();
        $this->setConfig();

        /** @var UserInterface $user */
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        $menu = $factory->createItem('root');

        $menu->setChildrenAttributes([
            'class' => 'sidebar-menu', 'id' => 'main-menu'
        ]);

        $menu->addChild('header', [])->setAttributes(['header' => 'menu.header.navigation']);

        // packages
        $menu->addChild('package', ['route' => 'package', 'label' => 'menu.label.broni'])
            ->setAttributes(['icon' => 'fa fa-paper-plane-o']);
        ;
        // search
        $menu->addChild('reservations', ['route' => 'package_search', 'label' => 'menu.label.podbor'])
            ->setAttributes(['icon' => 'fa fa-search']);

        $arrivals = $dm->getRepository('MBHPackageBundle:Package')->countByType('arrivals', true, $hotel);
        $out = $dm->getRepository('MBHPackageBundle:Package')->countByType('out', true, $hotel);

        $porterBadges = [];
        if ($arrivals) {
            $porterBadges += [
                'badge_left' => true,
                'badge_class_left' => 'bg-red badge-sidebar-left badge-sidebar-margin',
                'badge_id_left' => 'arrivals',
                'badge_value_left' => $arrivals,
                'badge_title_left' => $this->container->get('translator')->trans('menu.help.noarrival')
            ];
        }
        if ($out) {
            $porterBadges += [
                'badge_right' => true,
                'badge_class_right' => 'bg-green badge-sidebar-right badge-sidebar-margin',
                'badge_id_right' => 'out',
                'badge_value_right' => $out,
                'badge_title_right' => $this->container->get('translator')->trans('menu.help.nodepart')
            ];
        }

        //porter
        $menu->addChild('porter_links', ['route' => '_welcome', 'label' => 'menu.label.portie'])
            ->setAttributes(['dropdown' => true, 'icon' => 'fa fa-bell']  + $porterBadges);

        $menu['porter_links']->addChild('report_room_types', ['route' => 'report_room_types', 'label' => 'menu.header.navigation'])
            ->setAttributes(['icon' => 'fa fa-bed']);

        $menu['porter_links']->addChild('report_porter', [
            'route' => 'report_porter',
            'label' => 'menu.label.portie.arrdep',
        ])
            ->setAttributes(['icon' => 'fa fa-exchange']);

        $menu['porter_links']->addChild('chessboard', ['route' => 'chess_board_home', 'label' => 'menu.label.portie.shah'])
            ->setAttributes(['icon' => 'fa fa-table']);

        //Prices links
        $menu->addChild('prices', ['route' => '_welcome', 'label' => 'menu.label.nomandprice'])
            ->setAttributes(['dropdown' => true, 'icon' => $this->container->get('mbh.currency')->info()['icon']]);


//        //Tasks links
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
//
//        $menu->addChild('task', ['route' => 'task', 'label' => 'menu.label.task'])->setAttributes($taskAttributes);

        $menu['prices']->addChild('tariff', ['route' => 'tariff', 'label' => 'menu.label.nomandprice.tariff'])
            ->setAttributes(['icon' => 'fa fa-sliders']);
        $menu['prices']->addChild('overview', ['route' => 'room_overview', 'label' => 'menu.label.nomandprice.overview'])
            ->setAttributes(['icon' => 'fa fa-info-circle']);
        $menu['prices']->addChild('room_cache', ['route' => 'room_cache_overview', 'label' => 'menu.label.nomandprice.roomsell'])
            ->setAttributes(['icon' => 'fa fa-bed']);
        $menu['prices']->addChild('price_cache', ['route' => 'price_cache_overview', 'label' => 'menu.label.nomandprice.prices'])
            ->setAttributes(['icon' => $this->container->get('mbh.currency')->info()['icon']]);
        $menu['prices']->addChild('restrictions', ['route' => 'restriction_overview', 'label' => 'menu.label.nomandprice.restriction'])
            ->setAttributes(['icon' => 'fa fa-exclamation-circle']);
        $menu['prices']->addChild('services', ['route' => 'price_service_category', 'label' => 'menu.label.nomandprice.services'])
            ->setAttributes(['icon' => 'fa fa-plug']);
        $menu['prices']->addChild('promotions', ['route' => 'promotions', 'label' => 'menu.label.nomandprice.promotions'])
            ->setAttributes(['icon' => 'fa fa-bookmark']);
        $menu['prices']->addChild('special', ['route' => 'special', 'label' => 'special.title'])
            ->setAttributes(['icon' => 'fa fa-star']);

        // cash
        $menu->addChild('cash', ['route' => 'cash', 'label' => 'mbhbasebundle.view.navbar.kassa'])
            ->setAttribute('icon', $this->container->get('mbh.currency')->info()['icon'])
        ;

        // warehouse
        $menu->addChild('warehouse_record', ['route' => 'warehouse_record', 'label' => 'menu.label.warehouse'])->setAttributes(['icon' => 'fa fa-book']);

        //restaurant
        $menu->addChild('restaurant', ['route' => 'restaurant_dishorder', 'label' => 'menu.label.restaurant'])->setAttributes(['icon' => 'fa fa-cutlery']);

        // report
        $menu->addChild('reports', ['route' => '_welcome', 'label' => 'menu.label.reports'])
            ->setAttributes(['dropdown' => true, 'icon' => 'fa fa-bar-chart']);
        $menu['reports']->addChild('service_list', ['route' => 'service_list', 'label' => 'menu.label.reports.services'])
            ->setAttributes(['icon' => 'fa fa-plug']);
        $menu['reports']->addChild('clients', ['route' => 'tourist', 'label' => 'menu.label.reports.clients'])
            ->setAttributes(['icon' => 'fa fa-male']);
        $menu['reports']->addChild('organizations', ['route' => 'organizations', 'label' => 'menu.label.reports.organizations'])
            ->setAttributes(['icon' => 'fa fa-users']);
        $menu['reports']->addChild('report_filling', ['route' => 'report_filling', 'label' => 'menu.label.reports.filling'])
            ->setAttributes(['icon' => 'fa fa-hourglass-half']);
        $menu['reports']->addChild('report_user', ['route' => 'report_users', 'label' => 'menu.label.reports.managers'])
            ->setAttributes(['icon' => 'fa fa-user']);
//        $menu['reports']->addChild('report_invite', ['route' => 'report_invite', 'label' => 'menu.label.reports.invite'])
//            ->setAttributes(['icon' => 'fa fa-map']);
        $menu['reports']->addChild('analytics', ['route' => 'analytics', 'label' => 'menu.label.reports.analystics'])
            ->setAttributes(['icon' => 'fa fa-area-chart']);
        $menu['reports']->addChild('report_polls', ['route' => 'report_polls', 'label' => 'menu.label.reports.polls'])
            ->setAttributes(['icon' => 'fa fa-star']);
        $menu['reports']->addChild('dynamic_sale', ['route' => 'dynamic_sales', 'label' => 'Динамика продаж'])
            ->setAttributes(['icon' => 'fa fa-bar-chart']);
        $menu['reports']->addChild('packages_daily_report', ['route' => 'packages_daily_report', 'label' => 'menu.label.reports.daily_report'])
            ->setAttributes(['icon' => 'fa fa-money']);
        $menu['reports']->addChild('distribution_report', ['route' => 'distribution_by_days_of_the_week', 'label' => 'distribution_by_days_report.title'])
            ->setAttributes(['icon' => 'fa fa-check-square-o']);

        if ($this->config && $this->config->getSearchWindows()) {
            $menu['reports']->addChild('report_windows', ['route' => 'report_windows', 'label' => 'menu.label.reports.windows'])
                ->setAttributes(['icon' => 'fa fa-windows']);
        }
        //$token = $this->container->get('security.token_storage')->getToken();
        //if ($token && $token->getUser() instanceof User && $token->getUser()->getIsEnabledWorkShift()) {
//        $menu['reports']->addChild(
//            'report_work_shift',
//            ['route' => 'report_work_shift', 'label' => 'menu.label.reports.work_shift']
//        )
//            ->setAttributes(['icon' => 'fa fa-clock-o']);
        //}

        return $this->filter($menu, $factory, $options);
    }

    /**
     * @param ItemInterface $menu
     * @param FactoryInterface $factory
     * @param array $options
     * @return ItemInterface
     */
    public function filter(ItemInterface $menu, FactoryInterface $factory, array $options)
    {
        $this->counter = 0;
        $menu = $this->filterMenu($menu, $options);

        return empty($this->counter) ? $factory->createItem('root') : $menu;
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
     * User menu
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array $options
     * @return \Knp\Menu\MenuItem
     */
    public function managementMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');

        $menu->setChildrenAttributes([
            'class' => 'sidebar-menu', 'id' => 'management-menu'
        ]);

        $menu->addChild('header', [])->setAttributes(['header' => 'menu.settings.label.header']);


        //Hotels links
        $menu->addChild('hotels', ['route' => '_welcome', 'label' => 'menu.settings.label.hotels'])
            ->setAttributes(['dropdown' => true, 'icon' => 'fa fa-home']);
        $menu['hotels']->addChild('hotelsList', ['route' => 'hotel', 'label' => 'menu.settings.label.hotels'])
            ->setAttributes(['icon' => 'fa fa-home']);
        $menu['hotels']->addChild('corpusList', ['route' => 'housing', 'label' => 'menu.settings.label.housing'])
            ->setAttributes(['icon' => 'fa fa-building']);
        $menu['hotels']->addChild('hotelsRoomTypes', ['route' => 'room_type', 'label' => 'menu.settings.label.room_types'])
            ->setAttributes(['icon' => 'fa fa-bed']);

        if ($this->config && $this->config->getUseRoomTypeCategory()) {
            $menu['hotels']->addChild('room_type_category', ['route' => 'room_type_category', 'label' => 'menu.room_type_category'])
                ->setAttributes(['icon' => 'fa fa-bed']);
        }

        //Restaurant links
        $menu->addChild('restaurant', ['route' => '_welcome', 'label' => 'menu.label.restaurant'])
            ->setAttributes(['dropdown' => true, 'icon' => 'fa fa-cutlery']);
        $menu['restaurant']->addChild('ingredients', ['route'=>'restaurant_ingredient_category', 'label' => 'menu.settings.label.restaurant.ingredients'])
            ->setAttributes(['icon'=> 'fa fa-cutlery']);
        $menu['restaurant']->addChild('dishmenu', ['route'=>'restaurant_dishmenu_category', 'label' => 'menu.settings.label.restaurant.dishmenu'])
            ->setAttributes(['icon'=> 'fa fa-cutlery']);
        $menu['restaurant']->addChild('tables', ['route'=>'restaurant_table_category', 'label' => 'menu.settings.label.restaurant.tables'])
            ->setAttributes(['icon'=> 'fa fa-cutlery']);


        //Users links
        $menu->addChild('configs', ['route' => '_welcome', 'label' => 'menu.configs.config'])
            ->setAttributes(['dropdown' => true, 'icon' => 'fa fa-cogs'])
        ;
        $menu['configs']->addChild('users', ['route' => 'user', 'label' => 'menu.configs.users'])
            ->setAttributes(['icon' => 'fa fa-user'])
        ;
        $menu['configs']->addChild('groups', ['route' => 'group', 'label' => 'menu.configs.groups'])
            ->setAttributes(['icon' => 'fa fa-users'])
        ;
        $menu['configs']->addChild('sources', ['route' => 'package_source', 'label' => 'menu.configs.sources'])
            ->setAttributes(['icon' => 'fa fa-compass'])
        ;
        $menu['configs']->addChild('delete_reasons', ['route' => 'package_delete_reasons', 'label' => 'menu.configs.delete_reasons'])
            ->setAttributes(['icon' => 'fa fa-compass'])
        ;
        $menu['configs']->addChild('document_templates', ['route' => 'document_templates', 'label' => 'menu.configs.templates'])
            ->setAttributes(['icon' => 'fa fa-file'])
        ;
        $menu['configs']->addChild('config', ['route' => 'client_config', 'label' => 'menu.configs.parameters'])
            ->setAttributes(['icon' => 'fa fa-cog'])
        ;

        $menu['configs']->addChild('tasktype', ['route' => 'tasktype', 'label' => 'menu.configs.tasktype'])
            ->setAttributes(['icon' => 'fa fa-cog']);

        // Warehouse link
        $menu['configs']->addChild('warehouse_category', ['route' => 'warehouse_category', 'label' => 'menu.configs.warehouse'])
            ->setAttributes(['icon' => 'fa fa-book']) ;

        //Services links
        $menu->addChild('services', ['route' => '_welcome', 'label' => 'menu.communication.label'])
            ->setAttributes(['dropdown' => true, 'icon' => 'fa fa fa-arrows-h'])
        ;

        if ($this->container->getParameter('mbh.environment') == 'prod') {
            $menu['services']->addChild('booking', ['route' => 'booking', 'label' => 'Booking.com'])
                ->setAttributes(['icon' => 'fa fa-cloud-download']);
            $menu['services']->addChild('myallocator', ['route' => 'channels', 'label' => 'menu.communication.label.advanced'])
                ->setAttributes(['icon' => 'fa fa-cloud-download']);
            $menu['services']->addChild('ostrovok', ['route' => 'ostrovok', 'label' => 'Ostrovok'])
              ->setAttributes(['icon' => 'fa fa-cloud-download']);
            $menu['services']->addChild('vashotel', ['route' => 'vashotel', 'label' => 'menu.communication.label.your_hotel'])
                ->setAttributes(['icon' => 'fa fa-cloud-download']);

            //$menu['services']->addChild('hotelinn', ['route' => 'hotelinn', 'label' => 'Hotel-inn']);
            $menu['services']->addChild('expedia', ['route' => 'expedia', 'label' => 'Expedia'])
                ->setAttributes(['icon' => 'fa fa-cloud-download']);
            $menu['services']->addChild('oktogo', ['route' => 'oktogo', 'label' => 'Oktogo.ru'])
                ->setAttributes(['icon' => 'fa fa-cloud-download']);
        }
        $menu['services']->addChild('hundredOneHotel', ['route' => 'hundred_one_hotels', 'label' => 'menu.communication.label.hundred_one_hotels'])
            ->setAttributes(['icon' => 'fa fa-cloud-download']);
        $menu['services']->addChild('online_form', ['route' => 'online_form', 'label' => 'menu.communication.label.onlineform'])
            ->setAttributes(['icon' => 'fa fa-globe']);
        $menu['services']->addChild('online_polls', ['route' => 'online_poll_config', 'label' => 'menu.communication.label.polls'])
            ->setAttributes(['icon' => 'fa fa-star']);
        $menu['services']->addChild('invite', ['route' => 'invite', 'label' => 'menu.communication.label.invite'])
            ->setAttributes(['icon' => 'fa fa-star']);

        return $this->filter($menu, $factory, $options);
    }

    /**
     * Create hotel menu
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array $options
     * @return \Knp\Menu\MenuItem
     */
    public function createHotelMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');

        $menu->setChildrenAttributes([
            'class' => 'sidebar-menu', 'id' => 'create-hotel-menu'
        ]);

        $menu->addChild('header', [])->setAttributes(['header' => 'menu.header.navigation']);

        $menu->addChild('create_hotel', ['route' => 'hotel_new', 'label' => 'menu.hotel_new.label'])
            ->setAttribute('icon', 'fa fa-plus')
        ;

        return $this->filter($menu, $factory, $options);
        ;
    }

}
