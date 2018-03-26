<?php

namespace MBH\Bundle\BillingBundle\Form;

use MBH\Bundle\BillingBundle\Lib\Model\Region;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'form.region_type.name.label',
            ])
            ->add('country', TextType::class, [
                'label' => 'form.region_type.country.label',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Region::class,
            'attr' => ['id' => 'region-form']
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbhbilling_bundle_region_type';
    }
}
