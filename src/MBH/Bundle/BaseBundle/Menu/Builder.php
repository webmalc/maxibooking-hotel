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

        // Reservations links
        $menu->addChild('reservations', ['route' => '_welcome', 'label' => 'Бронирование'])
                ->setAttributes(['dropdown' => true, 'icon' => 'fa fa-plane'])
        ;
        $menu['reservations']->addChild('search', ['route' => '_welcome', 'label' => 'Подбор брони']);
        $menu['reservations']->addChild('reservationNew', ['route' => '_welcome', 'label' => 'Новая бронь']);
        $menu['reservations']->addChild('reservationsList', ['route' => '_welcome', 'label' => 'Список броней']);
        $menu['reservations']->addChild('clients', ['route' => '_welcome', 'label' => 'Клиенты']);
        $menu['reservations']->addChild('reservationsRequests', ['route' => '_welcome', 'label' => 'Он-лайн заявки']);

        // Cash links
        $menu->addChild('cash', ['route' => '_welcome', 'label' => 'Касса'])
                ->setAttributes(['dropdown' => true, 'icon' => 'fa fa-money'])
        ;
        $menu['cash']->addChild('cashDocuments', ['route' => '_welcome', 'label' => 'Документы']);
        $menu['cash']->addChild('cashbook', ['route' => '_welcome', 'label' => 'Кассовая книга']);

        // Reports links
        $menu->addChild('reports', ['route' => '_welcome', 'label' => 'Отчеты'])
                ->setAttributes(['dropdown' => true, 'icon' => 'fa fa-bar-chart-o'])
        ;
        $menu['reports']->addChild('reportRooms', ['route' => '_welcome', 'label' => 'По номерам']);
        $menu['reports']->addChild('reportPrices', ['route' => '_welcome', 'label' => 'По ценам']);
        $menu['reports']->addChild('analytics', ['route' => '_welcome', 'label' => 'Аналитика']);


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
        $menu['hotels']->addChild('seasonsHeader', ['label' => 'Cезоны'])
                ->setAttribute('class', 'dropdown-header')
        ;
        $menu['hotels']->addChild('seasons', ['route' => '_welcome', 'label' => 'Список сезонов']);
        $menu['hotels']->addChild('seasonsRooms', ['route' => '_welcome', 'label' => 'Номера в продаже']);
        $menu['hotels']->addChild('seasonsPrices', ['route' => '_welcome', 'label' => 'Цены']);
        $menu['hotels']->addChild('seasonsDiscounts', ['route' => '_welcome', 'label' => 'Скидки']);
        $menu['hotels']->addChild('seasonsClosedRooms', ['route' => '_welcome', 'label' => 'Номера в ремонте']);
        $menu['hotels']->addChild('seasonsCalculationFormulas', ['route' => '_welcome', 'label' => 'Формулы рассчета'])
                ->setAttribute('divider_append', true)
        ;
        $menu['hotels']->addChild('hotelsHeader', ['label' => 'Отели'])
                ->setAttribute('class', 'dropdown-header')
        ;
        $menu['hotels']->addChild('hotelsList', ['route' => '_welcome', 'label' => 'Список отелей']);
        $menu['hotels']->addChild('hotelsBuildings', ['route' => '_welcome', 'label' => 'Корпуса']);
        $menu['hotels']->addChild('hotelsFloors', ['route' => '_welcome', 'label' => 'Этажи']);
        $menu['hotels']->addChild('hotelsRooms', ['route' => '_welcome', 'label' => 'Номера']);
        $menu['hotels']->addChild('hotelsRoomTypes', ['route' => '_welcome', 'label' => 'Типы номеров']);
        $menu['hotels']->addChild('hotelsRoomSubTypes', ['route' => '_welcome', 'label' => 'Подтипы номеров']);

        //Dictionaries links
        $menu->addChild('dictionaries', ['route' => '_welcome', 'label' => 'Справочники'])
                ->setAttributes(['dropdown' => true, 'icon' => 'fa fa-book'])
        ;
        $menu['dictionaries']->addChild('dictionariesWer', ['route' => '_welcome', 'label' => 'Список отелей']);

        //Users links
        $menu->addChild('users', ['route' => '_welcome', 'label' => 'Пользователи'])
                ->setAttributes(['dropdown' => true, 'icon' => 'fa fa-users'])
        ;
        $menu['users']->addChild('usersList', ['route' => 'user', 'label' => 'Список пользователей']);

        //Services links
        $menu->addChild('services', ['route' => '_welcome', 'label' => 'Взаимодействие'])
                ->setAttribute('icon', 'fa fa-arrows-h')
        ;

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
        if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
            $menu->addChild('management', ['route' => '_welcome', 'label' => '&nbsp;'])
                    ->setAttributes([
                        'icon' => 'fa fa-gears fa-lg',
                        'id' => 'menu-toggle-link',
                        'data-toggle' => 'tooltip',
                        'data-placement' => "bottom",
                        'title' => "Перейти к настройкам"
                        ])
            ;
            if (!empty($options['management'])) {
                $menu['management']->setAttribute('icon', 'fa fa-home fa-lg');
                $menu['management']->setAttribute('title', 'Назад к главному меню');
            }
        }
        $menu->addChild('login', ['route' => '_welcome', 'label' => 'Привет, ' . $user->getUsername()])
                ->setAttribute('dropdown', true)
        ;
        $menu['login']->addChild('profile', [
                    'route' => '_welcome',
                    'label' => 'Профиль',
                    'routeParameters' => ['id' => $user->getId()]
                ])
                ->setAttributes(['divider_append' => true, 'icon' => 'fa fa-user'])
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
        
        $menu->addChild('create_hotel', ['route' => '_welcome', 'label' => 'Создать новый отель'])
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
        $security = $this->container->get('security.context');
        $metadataReader = new AnnotationDriver(new \Doctrine\Common\Annotations\AnnotationReader());

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
