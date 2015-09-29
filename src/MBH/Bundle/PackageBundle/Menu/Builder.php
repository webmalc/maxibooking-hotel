<?php

namespace MBH\Bundle\PackageBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\MenuItem;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\DocumentGenerator\Template\TemplateGeneratorFactory;
use MBH\Bundle\PackageBundle\DocumentGenerator\Xls\XlsGeneratorFactory;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * Class Builder
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class Builder extends ContainerAware
{
    public function templateDocuments(FactoryInterface $factory, array $options)
    {
        $package = $options['package'];
        $checker = $this->container->get('security.authorization_checker');

        $searchQuery = $options['searchQuery'];
        if (!$package instanceof Package) {
            throw new \InvalidArgumentException();
        }

        $menu = $factory->createItem('Docs Generation');
        $translator = $this->container->get('translator');
        $rootItem = $menu->addChild('actions', [
            'label' => $translator->trans('package.actions', [], 'MBHPackageBundle')
        ]);

        $rootItem->setAttribute('class', 'dropdown-menu');
        $rootItem->setChildrenAttributes([
            'class' => 'dropdown-menu',
            'role' => 'menu',
            'data-id' => $package->getId()
        ]);



        if ($checker->isGranted('ROLE_DOCUMENTS_GENERATOR')) {

            $rootItem
                ->addChild('Docs header', [
                    'label' => $translator->trans('package.actions.docs', [], 'MBHPackageBundle')
                ])
                ->setAttribute('dropdown_header', true);
                $this->addDocumentTemplateItems($rootItem, $package);
        }

        if ($checker->isGranted('ROLE_SEARCH')) {
            $rootItem
                ->addChild('Search Header', [
                    'label' => $translator->trans('package.actions.search', [], 'MBHPackageBundle')
                ])
                ->setAttributes([
                    'divider_prepend' => true,
                    'dropdown_header' => true
                ]);

            $rootItem
                ->addChild('Package search', [
                    'uri' => $this->container->get('router')->generate('package_search') . '#' . twig_urlencode_filter(['s' => $searchQuery]),
                    'label' => $translator->trans('package.actions.find_similar', [], 'MBHPackageBundle')
                ])
                ->setAttribute('icon', 'fa fa-search');

            if ($checker->isGranted('ROLE_ORDER_EDIT') && $checker->isGranted('ROLE_PACKAGE_NEW') && ($checker->isGranted('EDIT', $package->getOrder()) || $checker->isGranted('ROLE_PACKAGE_EDIT_ALL'))) {
                $rootItem
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
            }
        }



        if(!$package->getIsLocked()) {
            $rootItem
                ->addChild('Delete Header', [
                    'label' => $translator->trans('package.actions.delete', [], 'MBHPackageBundle')
                ])
                ->setAttribute('dropdown_header', true);

            if ($checker->isGranted('ROLE_PACKAGE_DELETE') && ($checker->isGranted('DELETE', $package) || $checker->isGranted('ROLE_PACKAGE_DELETE_ALL'))) {
                $rootItem
                    ->addChild('Delete', [
                        'route' => 'package_delete',
                        'routeParameters' => ['id' => $package->getId()],
                        'label' => $translator->trans('package.actions.delete', [], 'MBHPackageBundle'),
                    ])
                    ->setLinkAttribute('class', 'delete-link')
                    ->setAttributes([
                        'icon' => 'fa fa-trash-o',
                    ]);
            }

            if ($checker->isGranted('ROLE_PACKAGE_DELETE') && ($checker->isGranted('DELETE', $package->getOrder()) || $checker->isGranted('ROLE_PACKAGE_DELETE_ALL'))) {
                $rootItem
                    ->addChild('Order delete', [
                        'route' => 'package_order_delete',
                        'routeParameters' => ['id' => $package->getOrder()->getId()],
                        'label' => $translator->trans('order.navbar.delete_order', [], 'MBHPackageBundle'),
                    ])
                    ->setLinkAttribute('class', 'delete-link')
                    ->setAttributes([
                        'icon' => 'fa fa-trash-o'
                    ]);
            }

            if($package->getIsCheckOut() && $checker->isGranted('ROLE_ADMIN')) {
                $rootItem->addChild('Lock', [
                    'route' => 'package_lock',
                    'routeParameters' => ['id' => $package->getId()],
                    'label' => $translator->trans('order.navbar.lock', [], 'MBHPackageBundle'),
                ])
                    ->setAttributes([
                        'icon' => 'fa fa-lock',
                        'divider_prepend' => true,
                    ])
                ;
            }
        }
        return $menu;
    }

    private function addDocumentTemplateItems(MenuItem $menu, Package $package)
    {
        $translator = $this->container->get('translator');
        $generatorFactory = $this->container->get('mbh.package.document_factory');
        //$types = $generatorFactory->getAvailableTypes();

        $types = [
            TemplateGeneratorFactory::TYPE_CONFIRMATION,
            TemplateGeneratorFactory::TYPE_CONFIRMATION_EN,
            TemplateGeneratorFactory::TYPE_REGISTRATION_CARD,
            TemplateGeneratorFactory::TYPE_FMS_FORM_5,
            XlsGeneratorFactory::TYPE_NOTICE,
            TemplateGeneratorFactory::TYPE_EVIDENCE,
            TemplateGeneratorFactory::TYPE_FORM_1_G,
            TemplateGeneratorFactory::TYPE_RECEIPT,
            TemplateGeneratorFactory::TYPE_BILL,
            TemplateGeneratorFactory::TYPE_ACT,
        ];

        foreach ($types as $type) {
            $hasForm = $generatorFactory->hasForm($type);
            $options = [
                'label' => $translator->trans('package.actions.' . $type, [], 'MBHPackageBundle')
            ];
            if (!$hasForm) {
                $options['route'] = 'package_pdf';
                $options['routeParameters'] = ['type' => $type, 'id' => $package->getId()];
            }
            $item = $menu->addChild($type, $options);
            if ($hasForm) {
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