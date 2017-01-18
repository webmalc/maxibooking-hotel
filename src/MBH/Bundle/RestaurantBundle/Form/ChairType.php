<?php

namespace MBH\Bundle\RestaurantBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class ChairType extends  AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', TextType::class, [
                'label' => 'restaurant.table.item.helper.counts',
                'required' => true,
                'attr' => ['placeholder' => 'restaurant.table.item.helper.inputcountchairs'],
                'help' => 'restaurant.table.item.helper.countchairs',

            ])
            ->add('type', CheckboxType::class, [
                'label' => 'restaurant.table.item.helper.childrenchairs',
                'required' => false,
                'value' => false,
                'help' => 'restaurant.table.item.helper.addtype',

            ])
        ;

    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => null,
            ]);
    }
    public function getBlockPrefix()
    {
        return 'mbh_bundle_restaurantbundle_chair_type';
    }

}