<?php

namespace MBH\Bundle\RestaurantBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DishMenuCategoryType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('full_title', TextType::class, [
                'label' => 'restaurant.dishmenu.category.form.fullTitle.label',
                'required' => true,
                'attr' => ['placeholder' => 'restaurant.dishmenu.category.form.fullTitle.placeholder'],
                'help' => 'restaurant.dishmenu.category.form.fullTitle.help'
            ])
            ->add('title', TextType::class, [
                'label' => 'restaurant.dishmenu.category.form.title.label',
                'required' => false,
                'help' => 'restaurant.dishmenu.category.form.title.help'
            ]);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\RestaurantBundle\Document\DishMenuCategory'
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_restaurantbundle_dishmenu_category_type';
    }
}