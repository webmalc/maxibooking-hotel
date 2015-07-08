<?php

namespace MBH\Bundle\PackageBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\MenuItem;
use MBH\Bundle\PackageBundle\Component\DocumentTemplateGenerator\DocumentTemplateGeneratorFactory;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * Class Builder
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class Builder extends ContainerAware
{
    public function templateDocuments(FactoryInterface $factory, array $options)
    {
        $package = $options['package'];
        $searchQuery = $options['searchQuery'];
        if(!$package instanceof Package) {
            throw new \InvalidArgumentException();
        }

        $menu = $factory->createItem('Docs Generation');
        $translator = $this->container->get('translator');


        $menu->setAttribute('class', 'dropdown-menu');
        $menu->setChildrenAttributes([
            'class' => 'dropdown-menu',
            'role' => 'menu',
            'data-id' => $package->getId()
        ]);

        $menu
            ->addChild('Docs header', [
                'label' => $translator->trans('package.actions.docs', [], 'MBHPackageBundle')
            ])
            ->setAttribute('dropdown_header', true);

        $this->addDocumentTemplateItems($menu, $package);

        $menu
            ->addChild('Search Header', [
                'label' => $translator->trans('package.actions.search', [], 'MBHPackageBundle')
            ])
            ->setAttributes([
                'divider_prepend' => true,
                'dropdown_header' => true
            ]);

        $menu
            ->addChild('Package search', [
                'uri' => $this->container->get('router')->generate('package_search').'#'.twig_urlencode_filter(['s' => $searchQuery]),
                'label' => $translator->trans('package.actions.find_similar', [], 'MBHPackageBundle')
            ])
            ->setAttribute('icon', 'fa fa-search');

        $menu
            ->addChild('Order search ', [
                'route' => 'package_search',
                'routeParameters' => ['order' => $package->getOrder()->getId()],
                'label' => $translator->trans('order.package.add', [], 'MBHPackageBundle')
            ])
            ->setAttributes([
                'icon' => 'fa fa-search',
                'divider_append' => true,
                'level' => 2,
            ]);

        $menu
            ->addChild('Delete Header', [
                'label' => $translator->trans('package.actions.delete', [], 'MBHPackageBundle')
            ])
            ->setAttribute('dropdown_header', true);

        $menu
            ->addChild('Delete', [
                'route' => 'package_delete',
                'routeParameters' => ['id' => $package->getId()],
                'label' => $translator->trans('package.actions.delete', [], 'MBHPackageBundle')
            ])
            ->setAttributes([
                'icon' => 'fa fa-trash-o',
                'class' => 'delete-link'
            ]);
        $menu
            ->addChild('Order delete', [
                'route' => 'package_search',
                'routeParameters' => ['order' => $package->getOrder()->getId()],
                'label' => $translator->trans('order.navbar.delete_order', [], 'MBHPackageBundle')
            ])
            ->setAttributes([
                'icon' => 'fa fa-trash-o',
                'class' => 'delete-link'
            ]);

        return $menu;
    }

    private function addDocumentTemplateItems(MenuItem $menu, Package $package)
    {
        $translator = $this->container->get('translator');
        $types = DocumentTemplateGeneratorFactory::getAvailableTypes();
        $generateFactory = new DocumentTemplateGeneratorFactory($this->container);

        foreach($types as $type) {
            $hasForm = $generateFactory->hasForm($type);
            $options = [
                'label' => $translator->trans('package.actions.'.$type, [], 'MBHPackageBundle')
            ];
            if(!$hasForm) {
                $options['route'] = 'package_pdf';
                $options['routeParameters'] = ['type' => $type, 'id' => $package->getId()];
            }
            $item = $menu->addChild($type, $options);
            if($hasForm) {
                $item->setLinkAttributes([
                    'data-type' => $type,
                    'data-toggle' => 'modal',
                    'data-target' => '#template-document-modal'
                ]);
            }
            $item
                ->setAttribute('icon', 'fa fa-print')
                ->setLinkAttribute('target', '_blank');
        }
    }
}