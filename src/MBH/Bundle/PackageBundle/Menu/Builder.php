<?php

namespace MBH\Bundle\PackageBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
    public function templateDocuments(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('Генерация документов');
/*
        $menu->setAttribute('class', 'dropdown-menu');
        $menu->setChildrenAttributes([
            'class' => 'nav navbar-nav', 'id' => 'main-menu'
        ]);
*/
        $menu->addChild('reservations', ['route' => 'package_search', 'label' => 'Подбор']);

        return $menu;
    }
}