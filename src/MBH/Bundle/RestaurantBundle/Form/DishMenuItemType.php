<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 22.06.16
 * Time: 17:13
 */

namespace MBH\Bundle\RestaurantBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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
                'attr' => ['class' => 'price-spinner price'],
                'help' => 'restaurant.dishmenu.item.form.price.help',
                'disabled' => $options['is_margin']
            ])
            ->add('costPrice', TextType::class, [
                'label' => 'restaurant.dishmenu.item.form.costprice.label',
                'required' => false,
                'attr' => ['class' => 'costprice price-spinner', 'disabled'=>true],
                'help' => 'restaurant.dishmenu.item.form.costprice.help'
            ])
            ->add('margin', TextType::class, [
                'label' => 'restaurant.dishmenu.item.form.margin.label',
                'disabled' => !$options['is_margin'],
                'required' => false,
                'attr' => [
                    'class' => 'percent-margin'
                ]
            ])
            ->add('is_margin', CheckboxType::class, [
                'label' => 'Маржа',
                'required' => false,
                'attr' => [
                    'class' => 'is_margin'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'restaurant.dishmenu.item.form.description.label',
                'required' => false,
                'attr' => ['placeholder' => 'restaurant.dishmenu.item.form.description.placeholder'],
                'help' => 'restaurant.dishmenu.item.form.description.help'
            ])
            ->add('dishIngredients', CollectionType::class, [
                'entry_type' => DishMenuIngredientEmbeddedType::class,
                'label' => 'Ингредиенты блюда',
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => true
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
                'data_class' => 'MBH\Bundle\RestaurantBundle\Document\DishMenuItem',
                'is_margin' => false
            ]
        );
    }

    
    public function getName()
    {
        return 'mbh_bundle_restaurantbundle_dishmenu_item_type';
    }

}