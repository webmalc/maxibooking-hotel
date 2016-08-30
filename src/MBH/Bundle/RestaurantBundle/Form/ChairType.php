<?php

namespace MBH\Bundle\RestaurantBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChairType extends  AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('adult', TextType::class, [
                'label' => 'Количество',
                'required' => true,
                'attr' => ['placeholder' => 'Введите количество взрослых стульев'],
                'help' => 'Количество взрослых стульев'
            ])
            ->add('type', CheckboxType::class, [
                'label' => 'Детский стул',
                'required' => false,
                'value' => false,
                'help' => 'Добавлять детский стол',
            ])
        ;

    }
    public function getName()
    {
        return 'mbh_bundle_restaurantbundle_chair_type';
    }

}