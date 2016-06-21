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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class IngredientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullTitle', TextType::class, [
                'label' => 'restaurant.ingredient.form.fullTitle.label',
                'required' => true,
                'attr' => ['placeholder' => 'restaurant.ingredient.form.fullTitle.placeholder'],
                'help' => 'restaurant.ingredient.form.fullTitle.help'

            ])
            ->add('title', TextType::class, [
                'label' => 'restaurant.ingredient.form.title.label',
                'required' => false,
                'attr' => ['placeholder' => 'restaurant.ingredient.form.title.placeholder'],
                'help' => 'restaurant.ingredient.form.title.help'
            ])
            ->add('price', TextType::class, [
                'label' => 'restaurant.ingredient.form.price.label',
                'required' => false,
                'attr' => ['class' => 'spinner price-spinner'],
                'help' => 'restaurant.ingredient.form.price.help'

            ])
            ->add('calcType', ChoiceType::class, [
                'label' => 'restaurant.ingredient.form.calcType.label',
                'required' => true,
                'empty_value' => '',
                'multiple' => false,
                'choices' => $options['calcTypes'],
                'help' => 'restaurant.ingredient.form.calcType.help'


            ])
            ->add('output', TextType::class, [
                'label' => 'restaurant.ingredient.form.output.label',
                'required' => true,
                'attr' => ['class' => 'fix-percent-spinner'],
                'help' => 'restaurant.ingredient.form.output.help'

            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
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