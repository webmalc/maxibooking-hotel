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

        $menu->addChild('header', [])->setAttributes(['header' => 'Навигация']);

        // packages
        $menu->addChild('package', ['route' => 'package', 'label' => 'Брони'])
            ->setAttributes(['icon' => 'fa fa-paper-plane-o']);
        ;
        // search
        $menu->addChild('reservations', ['route' => 'package_search', 'label' => 'Подбор'])
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
                'badge_title_left' => 'Количество незаехавших броней'
            ];
        }
        if ($out) {
            $porterBadges += [
                'badge_right' => true,
                'badge_class_right' => 'bg-green badge-sidebar-right badge-sidebar-margin',
                'badge_id_right' => 'out',
                'badge_value_right' => $out,
                'badge_title_right' => 'Количество невыехавших броней'
            ];
        }

        //porter
        $menu->addChild('porter_links', ['route' => '_welcome', 'label' => 'Портье'])
            ->setAttributes(['dropdown' => true, 'icon' => 'fa fa-bell']  + $porterBadges);

        $menu['porter_links']->addChild('report_room_types', ['route' => 'report_room_types', 'label' => 'Номерной фонд'])
            ->setAttributes(['icon' => 'fa fa-bed']);

        $menu['porter_links']->addChild('report_porter', [
            'route' => 'report_porter',
            'label' => 'Заезд/Выезд',
        ])
            ->setAttributes(['icon' => 'fa fa-exchange']);
        $menu['porter_links']->addChild('accommodations', ['route' => 'report_accommodation', 'label' => 'Шахматка'])
            ->setAttributes(['icon' => 'fa fa-table']);

        //Prices links
        $menu->addChild('prices', ['route' => '_welcome', 'label' => 'Номера и цены'])
            ->setAttributes(['dropdown' => true, 'icon' => $this->container->get('mbh.currency')->info()['icon']]);


        //Tasks links
        $queryCriteria = new TaskQueryCriteria();
        $queryCriteria->userGroups = $user->getGroups();
        $queryCriteria->performer = $user;
        $queryCriteria->onlyOwned = true;
        $queryCriteria->status = 'open';
        $queryCriteria->hotel = $hotel;

        $openTaskCount = $this->container->get('mbh.hotel.task_repository')->getCountByCriteria($queryCriteria);

        $taskAttributes = ['icon' => 'fa fa-tasks'];
		
        if ($openTaskCount > 0) {
            $taskAttributes += [
                'badge' => true,
                'badge_class' => 'bg-red',
                'badge_id' => 'task-counter',
                'badge_value' => $openTaskCount
            ];
        }

        $menu->addChild('task', ['route' => 'task', 'label' => 'Задачи'])->setAttributes($taskAttributes);

        $menu['prices']->addChild('tariff', ['route' => 'tariff', 'label' => 'Тарифы'])
            ->setAttributes(['icon' => 'fa fa-sliders']);
        $menu['prices']->addChild('overview', ['route' => 'room_overview', 'label' => 'Обзор'])
            ->setAttributes(['icon' => 'fa fa-info-circle']);
        $menu['prices']->addChild('room_cache', ['route' => 'room_cache_overview', 'label' => 'Номера в продаже'])
            ->setAttributes(['icon' => 'fa fa-bed']);
        $menu['prices']->addChild('price_cache', ['route' => 'price_cache_overview', 'label' => 'Цены'])
            ->setAttributes(['icon' => $this->container->get('mbh.currency')->info()['icon']]);
        $menu['prices']->addChild('restrictions', ['route' => 'restriction_overview', 'label' => 'Условия и ограничения'])
            ->setAttributes(['icon' => 'fa fa-exclamation-circle']);
        $menu['prices']->addChild('services', ['route' => 'price_service_category', 'label' => 'Услуги'])
            ->setAttributes(['icon' => 'fa fa-plug']);
        $menu['prices']->addChild('promotions', ['route' => 'promotions', 'label' => 'Акции'])
            ->setAttributes(['icon' => 'fa fa-bookmark']);
        $menu['prices']->addChild('special', ['route' => 'special', 'label' => 'special.title'])
            ->setAttributes(['icon' => 'fa fa-star']);

        // cash
        $menu->addChild('cash', ['route' => 'cash', 'label' => 'Касса'])
            ->setAttribute('icon', $this->container->get('mbh.currency')->info()['icon'])
        ;

        // warehouse
        $menu->addChild('warehouse_record', ['route' => 'warehouse_record', 'label' => 'Склад'])->setAttributes(['icon' => 'fa fa-book']);

        //restaurant
        $menu->addChild('restaurant', ['route' => 'restaurant_dishorder', 'label' => 'Ресторан'])->setAttributes(['icon' => 'fa fa-cutlery']);

        // report
        $menu->addChild('reports', ['route' => '_welcome', 'label' => 'Отчеты'])
            ->setAttributes(['dropdown' => true, 'icon' => 'fa fa-bar-chart']);
        $menu['reports']->addChild('service_list', ['route' => 'service_list', 'label' => 'Услуги'])
            ->setAttributes(['icon' => 'fa fa-plug']);
        $menu['reports']->addChild('clients', ['route' => 'tourist', 'label' => 'Клиенты'])
            ->setAttributes(['icon' => 'fa fa-male']);
        $menu['reports']->addChild('organizations', ['route' => 'organizations', 'label' => 'Организации'])
            ->setAttributes(['icon' => 'fa fa-users']);
        $menu['reports']->addChild('report_filling', ['route' => 'report_filling', 'label' => 'Заполняемость'])
            ->setAttributes(['icon' => 'fa fa-hourglass-half']);
        $menu['reports']->addChild('report_user', ['route' => 'report_users', 'label' => 'Менеджеры'])
            ->setAttributes(['icon' => 'fa fa-user']);
        $menu['reports']->addChild('report_invite', ['route' => 'report_invite', 'label' => 'Визовые приглашения'])
            ->setAttributes(['icon' => 'fa fa-map']);
        $menu['reports']->addChild('analytics', ['route' => 'analytics', 'label' => 'Аналитика'])
            ->setAttributes(['icon' => 'fa fa-area-chart']);
        $menu['reports']->addChild('report_polls', ['route' => 'report_polls', 'label' => 'Оценки'])
            ->setAttributes(['icon' => 'fa fa-star']);

        if ($this->config && $this->config->getSearchWindows()) {
            $menu['reports']->addChild('report_windows', ['route' => 'report_windows', 'label' => 'Окна'])
                ->setAttributes(['icon' => 'fa fa-windows']);
        }
        //$token = $this->container->get('security.token_storage')->getToken();
        //if ($token && $token->getUser() instanceof User && $token->getUser()->getIsEnabledWorkShift()) {
        $menu['reports']->addChild('report_work_shift',
            ['route' => 'report_work_shift', 'label' => 'Рабочие смены'])
            ->setAttributes(['icon' => 'fa fa-clock-o']);
        //}

        /*$menu['reports']->addChild('report_fms', ['route' => 'report_fms', 'label' => 'Для ФМС'])
            ->setAttributes(['icon' => 'fa fa-file-archive-o']);*/

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

        $menu->addChild('header', [])->setAttributes(['header' => 'Настройки']);


        //Hotels links
        $menu->addChild('hotels', ['route' => '_welcome', 'label' => 'Отели'])
            ->setAttributes(['dropdown' => true, 'icon' => 'fa fa-home']);
        $menu['hotels']->addChild('hotelsList', ['route' => 'hotel', 'label' => 'Отели'])
            ->setAttributes(['icon' => 'fa fa-home']);
        $menu['hotels']->addChild('corpusList', ['route' => 'housing', 'label' => 'Корпуса'])
            ->setAttributes(['icon' => 'fa fa-building']);
        $menu['hotels']->addChild('hotelsRoomTypes', ['route' => 'room_type', 'label' => 'Номерной фонд'])
            ->setAttributes(['icon' => 'fa fa-bed']);

        if ($this->config && $this->config->getUseRoomTypeCategory()) {
            $menu['hotels']->addChild('room_type_category', ['route' => 'room_type_category', 'label' => 'Группы номеров'])
                ->setAttributes(['icon' => 'fa fa-bed']);
        }

        //Restaurant links
        $menu->addChild('restaurant', ['route' => '_welcome', 'label' => 'Ресторан'])
            ->setAttributes(['dropdown' => true, 'icon' => 'fa fa-cutlery']);
        $menu['restaurant']->addChild('ingredients', ['route'=>'restaurant_ingredient_category', 'label' => 'Ингредиенты'])
            ->setAttributes(['icon'=> 'fa fa-cutlery']);
        $menu['restaurant']->addChild('dishmenu', ['route'=>'restaurant_dishmenu_category', 'label' => 'Меню блюд'])
            ->setAttributes(['icon'=> 'fa fa-cutlery']);
        $menu['restaurant']->addChild('tables', ['route'=>'restaurant_table_category', 'label' => 'Столы'])
            ->setAttributes(['icon'=> 'fa fa-cutlery']);


        //Users links
        $menu->addChild('configs', ['route' => '_welcome', 'label' => 'Настройки'])
            ->setAttributes(['dropdown' => true, 'icon' => 'fa fa-cogs'])
        ;
        $menu['configs']->addChild('users', ['route' => 'user', 'label' => 'Пользователи'])
            ->setAttributes(['icon' => 'fa fa-user'])
        ;
        $menu['configs']->addChild('groups', ['route' => 'group', 'label' => 'Группы'])
            ->setAttributes(['icon' => 'fa fa-users'])
        ;
        $menu['configs']->addChild('sources', ['route' => 'package_source', 'label' => 'Источники'])
            ->setAttributes(['icon' => 'fa fa-compass'])
        ;
        $menu['configs']->addChild('delete_reasons', ['route' => 'package_delete_reasons', 'label' => 'Причины удаления'])
            ->setAttributes(['icon' => 'fa fa-compass'])
        ;
        $menu['configs']->addChild('document_templates', ['route' => 'document_templates', 'label' => 'Шаблоны документов'])
            ->setAttributes(['icon' => 'fa fa-file'])
        ;
        $menu['configs']->addChild('config', ['route' => 'client_config', 'label' => 'Параметры'])
            ->setAttributes(['icon' => 'fa fa-cog'])
        ;

        $menu['configs']->addChild('tasktype', ['route' => 'tasktype', 'label' => 'Типы задач'])
            ->setAttributes(['icon' => 'fa fa-cog']);

		// Warehouse link
        $menu['configs']->addChild('warehouse_category', ['route' => 'warehouse_category', 'label' => 'Склад'])
			->setAttributes(['icon' => 'fa fa-book']) ;

        //Services links
        $menu->addChild('services', ['route' => '_welcome', 'label' => 'Взаимодействие'])
            ->setAttributes(['dropdown' => true, 'icon' => 'fa fa fa-arrows-h'])
        ;

        if ($this->container->getParameter('mbh.environment') == 'prod') {
            $menu['services']->addChild('booking', ['route' => 'booking', 'label' => 'Booking.com'])
                ->setAttributes(['icon' => 'fa fa-cloud-download']);
            $menu['services']->addChild('myallocator', ['route' => 'channels', 'label' => 'Дополнительные каналы'])
                ->setAttributes(['icon' => 'fa fa-cloud-download']);
            //$menu['services']->addChild('ostrovok', ['route' => 'ostrovok', 'label' => 'Ostrovok'])
            //  ->setAttributes(['icon' => 'fa fa-cloud-download']);
            $menu['services']->addChild('vashotel', ['route' => 'vashotel', 'label' => 'ВашОтель'])
                ->setAttributes(['icon' => 'fa fa-cloud-download']);

            //$menu['services']->addChild('hotelinn', ['route' => 'hotelinn', 'label' => 'Hotel-inn']);
            //$menu['services']->addChild('oktogo', ['route' => 'oktogo', 'label' => 'Oktogo.ru']);

        }
        $menu['services']->addChild('hundredOneHotel', ['route' => 'hundred_one_hotels', 'label' => 'menu.communication.label.hundred_one_hotels'])
            ->setAttributes(['icon' => 'fa fa-cloud-download']);
        $menu['services']->addChild('online_form', ['route' => 'online_form', 'label' => 'Онлайн форма'])
            ->setAttributes(['icon' => 'fa fa-globe']);
        $menu['services']->addChild('online_polls', ['route' => 'online_poll_config', 'label' => 'Оценки'])
            ->setAttributes(['icon' => 'fa fa-star']);
        $menu['services']->addChild('invite', ['route' => 'invite', 'label' => 'Визовое приглашение'])
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

        $menu->addChild('header', [])->setAttributes(['header' => 'Навигация']);

        $menu->addChild('create_hotel', ['route' => 'hotel_new', 'label' => 'Создать новый отель'])
            ->setAttribute('icon', 'fa fa-plus')
        ;

        return $this->filter($menu, $factory, $options);;
    }

}
