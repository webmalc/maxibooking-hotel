<?php

namespace MBH\Bundle\WarehouseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WareCategoryType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('fullTitle', TextType::class, [
                    'label' => 'form.promotionType.label.fullTitle',
                    'required' => true,
                    'attr' => ['placeholder' => 'warehouse.items.placeholder']
                ])
                ->add('title', TextType::class, [
                    'label' => 'form.promotionType.label.title',
                    'required' => false,
                    'attr' => ['placeholder' => 'warehouse.items.placeholder'],
                    'help' => 'warehouse.items.maxiTitle'
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\WarehouseBundle\Document\WareCategory'
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_warehousebundle_ware_category_type';
    }

}
