<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 17.06.16
 * Time: 16:17
 */

namespace MBH\Bundle\RestaurantBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IngredientCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullTitle', TextType::class, [
                'label' => 'restaurant.category.form.fullTitle.label',
                'required' => true,
                'attr' => ['placeholder' => 'restaurant.category.form.fullTitle.placeholder'],
                'help' => 'restaurant.category.form.fullTitle.help'
            ])
            ->add('title', TextType::class, [
                'label' => 'restaurant.category.form.title.label',
                'required' => false,
                'help' => 'restaurant.category.form.title.help'
            ])

        ;

    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'MBH\Bundle\RestaurantBundle\Document\IngredientCategory'
            ]
        );
    }


    public function getBlockPrefix()
    {
        return 'mbh_bundle_restaurantbundle_ingredient_category_type';
    }

}