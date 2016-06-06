<?php

namespace MBH\Bundle\WarehouseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WareCategoryType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('fullTitle', 'text', [
                    'label' => 'form.promotionType.label.fullTitle',
                    'required' => true,
                    'attr' => ['placeholder' => 'warehouse.items.staple']
                ])
                ->add('title', 'text', [
                    'label' => 'form.promotionType.label.title',
                    'required' => false,
                    'attr' => ['placeholder' => 'warehouse.items.placeholder'],
                    'help' => 'warehouse.items.maxiTitle'
                ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\WarehouseBundle\Document\WareCategory'
        ]);
    }

    public function getName()
    {
        return 'mbh_bundle_warehousebundle_ware_category_type';
    }

}
