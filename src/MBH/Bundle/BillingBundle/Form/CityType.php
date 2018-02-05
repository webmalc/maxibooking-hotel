<?php

namespace MBH\Bundle\BillingBundle\Form;

use MBH\Bundle\BillingBundle\Lib\Model\City;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'form.city_type.name.label'
            ])
            ->add('region', TextType::class, [
                'label' => 'form.city_type.region.label'
            ])
            ->add('country', TextType::class, [
                'label' => 'form.city_type.country.label'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => City::class,
                'attr' => ['id' => 'city-form']
            ]);
    }

    public function getBlockPrefix()
    {
        return 'mbhbilling_bundle_city_type';
    }
}
