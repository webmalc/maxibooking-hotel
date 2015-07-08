<?php

namespace MBH\Bundle\PackageBundle\Menu;

use Knp\Menu\FactoryInterface;
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
        if(!$package instanceof Package) {
            throw new \InvalidArgumentException();
        }

        $menu = $factory->createItem('Генерация документов');
        $menu->setAttribute('class', 'dropdown-menu');

        $menu->setChildrenAttributes([
            'class' => 'dropdown-menu',
            'role' => 'menu',
            'data-id' => $package->getId()
        ]);

        $types = DocumentTemplateGeneratorFactory::getAvailableTypes();
        $translator = $this->container->get('translator');

        /*$menu
            ->addChild('presentation', [
                'label' => $translator->trans('package.actions.docs', [], 'MBHPackageBundle')
            ])
            ->setAttribute('class', 'dropdown-header');*/

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
            $item = $menu->addChild($type, $options)->setAttribute('icon', 'fa fa-print');
            if($hasForm) {
                $item->setLinkAttributes([
                    'data-type' => $type,
                    'data-toggle' => 'modal',
                    'data-target' => '#template-document-modal'
                ]);
            }
            $item->setLinkAttribute('target', '_blank');
        }

        return $menu;
    }
}