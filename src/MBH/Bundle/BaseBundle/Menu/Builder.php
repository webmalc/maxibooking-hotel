<?php

namespace MBH\Bundle\BaseBundle\Menu;

use Knp\Menu\FactoryInterface;
use MBH\Bundle\HotelBundle\Document\QueryCriteria\TaskQueryCriteria;
use Symfony\Component\DependencyInjection\ContainerAware;
use Knp\Menu\ItemInterface;
use JMS\SecurityExtraBundle\Metadata\Driver\AnnotationDriver;
use Symfony\Component\Security\Core\User\UserInterface;

class Builder extends ContainerAware
{

    /**
     * Main menu
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array $options
     * @return \Knp\Menu\MenuItem
     */
    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $menu->setChildrenAttributes([
            'class' => 'nav navbar-nav', 'id' => 'main-menu'
        ]);

        if (!empty($options['management'])) {
            $menu->setChildrenAttribute('style', 'display: none');
        }

        // search
        $menu->addChild('reservations', ['route' => 'package_search', 'label' => 'Подбор'])
             ->setAttributes(['icon' => 'fa fa-search'])
        ;
        // packages
        $menu->addChild('package', ['route' => 'package', 'label' => 'Брони'])
            ->setAttributes(['icon' => 'fa fa fa-paper-plane-o'])
        ;
        //Prices links
        $menu->addChild('prices', ['route' => '_welcome', 'label' => 'Номера и цены'])
            ->setAttributes(['dropdown' => true, 'icon' => $this->container->get('mbh.currency')->info()['icon']]);

        $dm = $this->container->get('doctrine_mongodb')->getManager();
        /** @var UserInterface $user */
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        $queryCriteria = new TaskQueryCriteria();
        $queryCriteria->roles = $user->getRoles();
        $queryCriteria->performer = $user->getId();
        $queryCriteria->onlyOwned = true;
        $queryCriteria->status = 'open';

        $openTaskCount = $dm->getRepository('MBHHotelBundle:Task')->getCountByCriteria($queryCriteria);

        $taskAttributes = ['icon' => 'fa fa-tasks'];
        if($openTaskCount > 0) {
            $taskAttributes += [
                'badge' => true,
                'badge_class' => 'label-danger',
                'badge_id' => 'task-counter',
                'badge_value' => $openTaskCount
            ];
        }

        $menu->addChild('task', ['route' => 'task', 'label' => 'Задачи'])->setAttributes($taskAttributes);

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

        // cash
        $menu->addChild('cash', ['route' => 'cash', 'label' => 'Касса'])
            ->setAttribute('icon', $this->container->get('mbh.currency')->info()['icon'])
        ;

        // report
        $menu->addChild('reports', ['route' => '_welcome', 'label' => 'Отчеты'])
             ->setAttributes(['dropdown' => true, 'icon' => 'fa fa-bar-chart']);
        
        $menu['reports']->addChild('accommodations', ['route' => 'report_accommodation', 'label' => 'Размещение'])
            ->setAttributes(['icon' => 'fa fa-table']);
        $menu['reports']->addChild('report_porter', ['route' => 'report_porter', 'label' => 'Портье'])
            ->setAttributes(['icon' => 'fa fa-bell']);
        $menu['reports']->addChild('service_list', ['route' => 'service_list', 'label' => 'Услуги'])
            ->setAttributes(['icon' => 'fa fa-plug']);
        $menu['reports']->addChild('clients', ['route' => 'tourist', 'label' => 'Клиенты'])
            ->setAttributes(['icon' => 'fa fa-male']);
        $menu['reports']->addChild('organizations', ['route' => 'organizations', 'label' => 'Организации'])
            ->setAttributes(['icon' => 'fa fa-users']);
        $menu['reports']->addChild('report_user', ['route' => 'report_users', 'label' => 'Менеджеры'])
            ->setAttributes(['icon' => 'fa fa-user']);
        $menu['reports']->addChild('analytics', ['route' => 'analytics', 'label' => 'Аналитика'])
            ->setAttributes(['icon' => 'fa fa-area-chart']);
        $menu['reports']->addChild('report_polls', ['route' => 'report_polls', 'label' => 'Оценки'])
            ->setAttributes(['icon' => 'fa fa-star']);



        /*$menu['reports']->addChild('report_fms', ['route' => 'report_fms', 'label' => 'Для ФМС'])
            ->setAttributes(['icon' => 'fa fa-file-archive-o']);*/

        return $this->filterMenu($menu);
    }

    /**
     * Filter menu by roles
     * @param \Knp\Menu\ItemInterface $menu
     * @return \Knp\Menu\ItemInterface
     */
    public function filterMenu(ItemInterface $menu)
    {
        $router = $this->container->get('router');
        $router->getContext()->setMethod('GET');
        $security = $this->container->get('security.context');

        foreach ($menu->getChildren() as $child) {

            if (empty($child->getUri())) {
                continue;
            }
            $metadata = false;

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
            }

            $this->filterMenu($child);
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
            'class' => 'nav navbar-nav', 'id' => 'management-menu'
        ]);

        if (!empty($options['management'])) {
            $menu->setChildrenAttribute('style', 'display: block');
        }

        //Hotels links
        $menu->addChild('hotels', ['route' => '_welcome', 'label' => 'Отели'])
                ->setAttributes(['dropdown' => true, 'icon' => 'fa fa-building-o']);
        $menu['hotels']->addChild('hotelsList', ['route' => 'hotel', 'label' => 'Отели'])
                ->setAttributes(['icon' => 'fa fa-building']);
        $menu['hotels']->addChild('corpusList', ['route' => 'housings', 'label' => 'Корпуса'])
            ->setAttributes(['icon' => 'fa fa-home']);
        $menu['hotels']->addChild('hotelsRoomTypes', ['route' => 'room_type', 'label' => 'Номерной фонд'])
                ->setAttributes(['icon' => 'fa fa-bed']);
        $menu['hotels']->addChild('tariff', ['route' => 'tariff', 'label' => 'Тарифы'])
            ->setAttributes(['icon' => 'fa fa-sliders']);

        //Users links
        $menu->addChild('configs', ['route' => '_welcome', 'label' => 'Настройки'])
            ->setAttributes(['dropdown' => true, 'icon' => 'fa fa-cogs'])
        ;
        $menu['configs']->addChild('users', ['route' => 'user', 'label' => 'Пользователи'])
            ->setAttributes(['icon' => 'fa fa-user'])
        ;
        $menu['configs']->addChild('sources', ['route' => 'package_source', 'label' => 'Источники'])
             ->setAttributes(['icon' => 'fa fa-compass'])
        ;
        $menu['configs']->addChild('config', ['route' => 'client_config', 'label' => 'Параметры'])
            ->setAttributes(['icon' => 'fa fa-cog'])
        ;

        $menu['configs']->addChild('tasktype', ['route' => 'tasktype', 'label' => 'Типы задач'])
            ->setAttributes(['icon' => 'fa fa-cog'])
        ;

        //Services links
        $menu->addChild('services', ['route' => '_welcome', 'label' => 'Взаимодействие'])
             ->setAttributes(['dropdown' => true, 'icon' => 'fa fa fa-arrows-h'])
        ;

        if ($this->container->getParameter('mbh.environment') == 'prod') {
            $menu['services']->addChild('booking', ['route' => 'booking', 'label' => 'Booking.com'])
                ->setAttributes(['header' => 'Channel manager', 'header_icon' => 'fa fa-cloud-download']);
            $menu['services']->addChild('ostrovok', ['route' => 'ostrovok', 'label' => 'Ostrovok']);
            $menu['services']->addChild('vashotel', ['route' => 'vashotel', 'label' => 'ВашОтель']);
            //$menu['services']->addChild('hotelinn', ['route' => 'hotelinn', 'label' => 'Hotel-inn']);
            //$menu['services']->addChild('oktogo', ['route' => 'oktogo', 'label' => 'Oktogo.ru']);

        }

        $menu['services']->addChild('online_form', ['route' => 'online_form', 'label' => 'Онлайн форма'])
            ->setAttributes(['divider_prepend' => true, 'header' => 'Другое', 'icon' => 'fa fa-globe']);
        $menu['services']->addChild('online_polls', ['route' => 'online_poll_config', 'label' => 'Оценки'])
            ->setAttributes(['icon' => 'fa fa-star']);

        return $this->filterMenu($menu);
    }

    /**
     * User menu
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array $options
     * @return \Knp\Menu\MenuItem
     */
    public function userMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');

        $menu->setChildrenAttributes([
            'class' => 'nav navbar-nav navbar-right', 'id' => 'user-menu'
        ]);

        $user = $this->container->get('security.context')
                ->getToken()
                ->getUser()
        ;
        if ($this->container->get('security.context')->isGranted('ROLE_ADMIN_HOTEL')) {
            $menu->addChild('management', ['url' => '#', 'label' => '&nbsp;'])
                    ->setAttributes([
                        'icon' => 'fa fa-gears fa-lg',
                        'id' => 'menu-toggle-link',
                        'data-toggle' => 'tooltip',
                        'data-placement' => "bottom",
                        'title' => "Перейти к настройкам",
                    ])
            ;
            if (!empty($options['management'])) {
                $menu['management']->setAttribute('icon', 'fa fa-home fa-lg');
                $menu['management']->setAttribute('title', 'Назад к главному меню');
            }
        }
        $menu->addChild('login', ['route' => 'user_profile', 'label' => $user->getFullName(true)])
                ->setAttributes([
                        'icon' => 'fa fa-user',
                        'dropdown' => true
                    ])
        ;
        $menu['login']->addChild('user_edit', [
            'route' => 'user_edit',
            'routeParameters' => ['id' => $user->getId()],
            'label' => 'Профиль'
        ])
            ->setAttributes(['icon' => 'fa fa-user'])
        ;
        $menu['login']->addChild('profile', [
                    'route' => 'user_profile',
                    'label' => 'Смена пароля'
                ])
                ->setAttributes(['icon' => 'fa fa-lock'])
        ;
        $menu['login']->addChild('version', [
            'route' => '_welcome',
            'label' => 'Версия ' . $this->container->getParameter('mbh.version')
        ])
            ->setAttributes(['icon' => 'fa fa-info-circle'])
        ;
        $menu['login']->addChild('logout', ['route' => 'fos_user_security_logout', 'label' => 'Выйти'])
                ->setAttributes(['divider_prepend' => true, 'icon' => 'fa fa-sign-out'])
        ;

        return $this->filterMenu($menu);
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
            'class' => 'nav navbar-nav',
            'id' => 'create-hotel-menu',
            'style' => 'display: block;'
        ]);

        $menu->addChild('create_hotel', ['route' => 'hotel_new', 'label' => 'Создать новый отель'])
                ->setAttribute('icon', 'fa fa-plus')
        ;

        return $menu;
    }

}
