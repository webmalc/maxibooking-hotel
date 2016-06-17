<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 17.06.16
 * Time: 16:17
 */

namespace MBH\Bundle\RestaurantBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class IngredientCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullTitle', 'text', [
                'label' => 'Название',
                'required' => true,
                'attr' => ['placeholder' => 'Имя категории ингредиентов']
            ])
            ->add('title', 'text', [
                'label' => 'Внутреннее название',
                'required' => false,
                'attr' => ['placeholder' => 'Овощи свежие'],
                'help' => 'Название для использования внутри MaxiBooking'
            ])

        ;

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'MBH\Bundle\RestaurantBundle\Document\IngredientCategory'
            ]
        );
    }

    public function getName()
    {
        return 'mbh_bundle_restaurantbundle_ingredient_category_type';
    }

}