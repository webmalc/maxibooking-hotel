<?php

namespace MBH\Bundle\PackageBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\MenuItem;
use MBH\Bundle\ClientBundle\Document\DocumentTemplate;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\DocumentGenerator\Template\TemplateGeneratorFactory;
use MBH\Bundle\PackageBundle\DocumentGenerator\Xls\XlsGeneratorFactory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class Builder

 */
class Builder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

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
                    'label' => $translator->trans('package.action.action_header', [], 'MBHPackageBundle')
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

            $dateDifference = date_diff(new \DateTime(), $package->getBegin());
            if ($dateDifference->d > 0 || $dateDifference->m > 0 || $dateDifference->y > 0) {
                $linkClass = 'delete-link';
            } else {
                $linkClass = '';
            }

            if ($checker->isGranted('ROLE_ORDER_EDIT')
                    && $package->getTotalOverwrite()
                && $checker->isGranted('ROLE_PACKAGE_NEW')
                && ($checker->isGranted('EDIT', $package->getOrder())
                    || $checker->isGranted('ROLE_PACKAGE_EDIT_ALL'))) {
                $rootItem
                    ->addChild('Reset totalOverwrite value', [
                        'route' => 'reset_total_overwrite_value',
                        'routeParameters' => ['id' => $package->getId()],
                        'label' => $translator->trans('package.action.resetTotalOverwrite', [], 'MBHPackageBundle'),
                    ])
                    ->setLinkAttributes([
                        'class' => $linkClass,
                        'data-text' => $translator->trans('package.action.resetTotalOverwrite.modal_window_text', [], 'MBHPackageBundle'),
                        'data-button' => $translator->trans('package.action.resetTotalOverwrite.modal_window_delete_button_text', [], 'MBHPackageBundle'),
                        'data-button-icon' => 'fa fa-chain-broken'
                    ])
                    ->setAttributes([
                        'icon' => 'fa fa-chain-broken',
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
                        'uri' => '#modal_delete_package',
                        'label' => $translator->trans('package.actions.delete', [], 'MBHPackageBundle'),
                    ])
                    ->setLinkAttributes([
                        'class' => 'booking-delete-link',
                        'data-id' => $package->getId(),
                        'data-toggle' => 'modal',
                        'data-order-id' => $package->getOrder()->getId(),
                        'data-title' => $package->getTitle()
                    ])
                    ->setAttributes([
                        'icon' => 'fa fa-trash-o',
                    ]);
            }

            if ($checker->isGranted('ROLE_PACKAGE_DELETE') && ($checker->isGranted('DELETE', $package->getOrder()) || $checker->isGranted('ROLE_PACKAGE_DELETE_ALL'))) {
                $rootItem
                    ->addChild('Order delete', [
                        'uri' => '#modal_delete_package',
                        'label' => $translator->trans('order.navbar.delete_order', [], 'MBHPackageBundle'),
                    ])
                    ->setLinkAttributes([
                        'class' => 'order-booking-delete-link',
                        'data-id' => $package->getOrder()->getId(),
                        'data-toggle' => 'modal',
                        'data-title' => $package->getOrder()->getName()
                    ])
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
            XlsGeneratorFactory::TYPE_NOTICE,
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

        $customDocs = $this->container
            ->get('doctrine_mongodb')
            ->getRepository('MBHClientBundle:DocumentTemplate')
            ->findBy(['deletedAt' => null], ['title' => 'asc'])
        ;

        if (!count($customDocs)) {
            return true;
        }
        $menu->addChild('Additional docs header', [
            'label' => $translator->trans('mbh.package.builder.document_tempates')
        ])
        ->setAttribute('dropdown_header', true);
        /** @var DocumentTemplate $doc */
        foreach ($customDocs as $doc) {
            $menu->addChild('doc_' . $doc->getId(), [
                'label' => $doc->getName(),
                'route' => 'document_templates_show',
                'routeParameters' => [
                    'id' => $doc->getId(),
                    'packageId' => $package->getId()
                ]
            ])
                ->setAttribute('icon', 'fa fa-print')
                ->setLinkAttribute('target', '_blank')
            ;
        }
    }
}