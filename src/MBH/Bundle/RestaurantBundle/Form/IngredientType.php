<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 20.06.16
 * Time: 10:53
 */

namespace MBH\Bundle\RestaurantBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class IngredientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullTitle', TextType::class, [
                'label' => 'Название',
                'required' => true,
                'attr' => ['placeholder' => 'Яблоко']
            ])
            ->add('title', TextType::class, [
                'label' => 'Внутреннее название',
                'required' => false,
                'attr' => ['placeholder' => 'Яблоко санкционное, вкусное'],
                'help' => 'Название для использования внутри MaxiBooking'
            ])
            ->add('price', TextType::class, [
                'label' => 'Цена',
                'required' => false,
                'attr' => ['placeholder' => 'Услуга не используется', 'class' => 'spinner price-spinner'],
            ])
            ->add('calcType', ChoiceType::class, [
                'label' => 'Единицы',
                'required' => true,
                'empty_value' => '',
                'multiple' => false,
                'choices' => $options['calcTypes'],

            ])
            ->add('output', TextType::class, [
                'label' => 'Выход',
                'required' => true,
                'attr' => ['class' => 'fix-percent-spinner']
            ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'MBH\Bundle\RestaurantBundle\Document\Ingredient',
                'calcTypes' => []
            ]
        );
    }

    public function getName()
    {
        return 'mbh_bundle_restaurant_ingredient_type';
    }

}