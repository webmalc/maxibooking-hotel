<?php

namespace MBH\Bundle\BaseBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Knp\Menu\ItemInterface;
use JMS\SecurityExtraBundle\Metadata\Driver\AnnotationDriver;

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
            ->setAttributes(['icon' => 'fa fa-paper-plane'])
        ;

        // cash
        $menu->addChild('cash', ['route' => 'cash', 'label' => 'Касса'])
            ->setAttribute('icon', 'fa fa-ruble')
        ;

        // report
        $menu->addChild('report', ['route' => 'analytics', 'label' => 'Аналитика'])
             ->setAttributes(['icon' => 'fa fa-bar-chart-o'])
        ;

        return $this->filterMenu($menu);
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
                ->setAttributes(['dropdown' => true, 'icon' => 'fa fa-building-o'])
        ;
        $menu['hotels']->addChild('hotelsList', ['route' => 'hotel', 'label' => 'Отели']);
        $menu['hotels']->addChild('hotelsRoomTypes', ['route' => 'room_type', 'label' => 'Номера']);

        //Prices links
        $menu->addChild('prices', ['route' => '_welcome', 'label' => 'Цены'])
             ->setAttributes(['dropdown' => true, 'icon' => 'fa fa-ruble'])
        ;
        $menu['prices']->addChild('tariff', ['route' => 'tariff', 'label' => 'Тарифы']);
        $menu['prices']->addChild('services', ['route' => 'price_service_category', 'label' => 'Услуги']);
        
        //Users links
        $menu->addChild('configs', ['route' => '_welcome', 'label' => 'Настройки'])
            ->setAttributes(['dropdown' => true, 'icon' => 'fa fa-cogs'])
        ;
        $menu['configs']->addChild('users', ['route' => 'user', 'label' => 'Пользователи'])
            ->setAttributes(['icon' => 'fa fa-users'])
        ;
        $menu['configs']->addChild('sources', ['route' => 'package_source', 'label' => 'Источники'])
             ->setAttributes(['icon' => 'fa fa-compass'])
        ;
        $menu['configs']->addChild('config', ['route' => 'client_config', 'label' => 'Параметры'])
            ->setAttributes(['icon' => 'fa fa-cog'])
        ;
        
        //Services links
        $menu->addChild('services', ['route' => '_welcome', 'label' => 'Взаимодействие'])
             ->setAttributes(['dropdown' => true, 'icon' => 'fa fa fa-arrows-h'])
        ;

        $menu['services']->addChild('online_form', ['route' => 'online_form', 'label' => 'Онлайн форма']);
        $menu['services']->addChild('vashotel', ['route' => 'vashotel', 'label' => 'ВашОтель.RU']);

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
            $menu->addChild('management', ['url' => 'http://google.ru', 'label' => '&nbsp;'])
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
        $menu->addChild('login', ['route' => '_welcome', 'label' => $user->getFullName(true)])
                ->setAttribute('dropdown', true)
        ;
        $menu['login']->addChild('profile', [
                    'route' => 'user_profile',
                    'label' => 'Смена пароля'
                ])
                ->setAttributes(['divider_append' => true, 'icon' => 'fa fa-lock'])
        ;
        $menu['login']->addChild('logout', ['route' => 'fos_user_security_logout', 'label' => 'Выйти'])
                ->setAttribute('icon', 'fa fa-sign-out')
        ;

        return $menu;
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

}
