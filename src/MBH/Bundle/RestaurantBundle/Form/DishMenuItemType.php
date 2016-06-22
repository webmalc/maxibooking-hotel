<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 22.06.16
 * Time: 17:13
 */

namespace MBH\Bundle\RestaurantBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DishMenuItemType extends AbstractType 
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullTitle', TextType::class, [
                'label' => 'restaurant.dishmenu.item.form.fullTitle.label',
                'required' => true,
                'attr' => ['placeholder' => 'restaurant.dishmenu.item.form.fullTitle.placeholder'],
                'help' => 'restaurant.dishmenu.item.form.fullTitle.help'

            ])
            ->add('title', TextType::class, [
                'label' => 'restaurant.dishmenu.item.form.title.label',
                'required' => false,
                'attr' => ['placeholder' => 'restaurant.dishmenu.item.form.title.placeholder'],
                'help' => 'restaurant.dishmenu.item.form.title.help'
            ])
            ->add('price', TextType::class, [
                'label' => 'restaurant.dishmenu.item.form.price.label',
                'required' => false,
                'attr' => ['class' => 'spinner price-spinner'],
                'help' => 'restaurant.dishmenu.item.form.price.help'
            ])
            ->add('cost_price', TextType::class, [
                'label' => 'restaurant.dishmenu.item.form.costprice.label',
                'required' => false,
                'attr' => ['class' => 'spinner price-spinner'],
                'help' => 'restaurant.dishmenu.item.form.costprice.help'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'restaurant.dishmenu.item.form.description.label',
                'required' => false,
                'attr' => ['placeholder' => 'restaurant.dishmenu.item.form.description.placeholder'],
                'help' => 'restaurant.dishmenu.item.form.description.help'
            ])
        ;

    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'MBH\Bundle\RestaurantBundle\Document\DishMenuItem'
            ]
        );
    }

    
    public function getName()
    {
        return 'mbh_bundle_restaurantbundle_dishmenu_item_type';
    }

}