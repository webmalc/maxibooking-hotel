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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
            ])
            ->add('costPrice', TextType::class, [
                'label' => 'restaurant.dishmenu.item.form.costprice.label',
                'required' => false,
                'attr' => ['class' => 'costprice price-spinner', 'disabled'=>true],
                'help' => 'restaurant.dishmenu.item.form.costprice.help',
                'mapped' => false
            ])
            ->add('margin', TextType::class, [
                'label' => 'restaurant.dishmenu.item.form.margin.label',
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
            //TODO: Тут возможно есть смысл сохранять при включенной марже текущую цену, если она была,
            //чтоб при выключении маржи она показывалась иначе из за того что там форма disabled - цена ожидается, но ее нет.
            //Или скорее лучше сделать все же проверку состояния включенной маржи и нужным полям формы задать disabledlo
            /*->addEventListener(
                FormEvents::PRE_SUBMIT,
                [
                    $this, 'onPreSubmit'
                ]
            )*/
        ;

    }

    /*public function onPreSubmit(FormEvent $event)
    {
        $dishItem = $event->getData();
        $form = $event->getForm();
        return true;
    }*/

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