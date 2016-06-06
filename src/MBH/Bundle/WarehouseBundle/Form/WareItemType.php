<?php

namespace MBH\Bundle\WarehouseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class ServiceType
 */
class WareItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullTitle', 'text', [
                'label' => 'form.promotionType.label.fullTitle',
                'required' => true,
                'group' => 'warehouse.common.info',
                'attr' => ['placeholder' => 'warehouse.items.placeholderShort']
            ])
            ->add('title', 'text', [
                'label' => 'form.promotionType.label.title',
                'group' => 'warehouse.common.info',
                'required' => false,
                'attr' => ['placeholder' => 'warehouse.items.placeholder'],
                'help' => 'warehouse.items.maxiTitle'
            ])
            ->add('price', 'text', [
                'label' => 'warehouse.items.price',
                'group' => 'warehouse.common.info',
                'required' => false,
                'attr' => ['placeholder' => 'warehouse.items.notused', 'class' => 'spinner price-spinner'],
            ])
            ->add('unit', 'text', [
                'label' => 'warehouse.field.unit',
                'group' => 'warehouse.common.info',
                'required' => false,
                'attr' => ['placeholder' => 'warehouse.field.unitPlaceholder'],
            ])
            ->add('isEnabled', 'checkbox', [
                'label' => 'warehouse.common.included',
                'group' => 'warehouse.common.settings',
                'value' => true,
                'required' => false,
            ])        
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\WarehouseBundle\Document\WareItem',
        ]);
    }

    public function getName()
    {
        return 'mbh_bundle_warehousebundle_wareitem_type';
    }

}
