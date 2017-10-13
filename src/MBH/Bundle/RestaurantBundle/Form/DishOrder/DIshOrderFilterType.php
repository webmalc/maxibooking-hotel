<?php


namespace MBH\Bundle\RestaurantBundle\Form\DishOrder;


use MBH\Bundle\RestaurantBundle\Document\DishOrderCriteria;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DIshOrderFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('begin', DateType::class, [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false,
                'attr' => [
                    'class' => 'datepicker begin-datepicker'
                ]
            ])
            ->add('end', DateType::class, [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false,
                'attr' => [
                    'class' => 'datepicker end-datepicker'
                ]
            ])
            ->add('is_freezed',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'required' => false,
                'choices' => [
                    DishOrderCriteria::ORDER_PAID => 'restaurantbundle.form.paid',
                    DishOrderCriteria::ORDER_NOT_PAID => 'restaurantbundle.form.not_paid'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('search', TextType::class,[
                'required' => false
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'restaurant_dishorder_filter_type';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                    'data_class' => DishOrderCriteria::class,
                    'attr' => [
                        'class' => 'form-inline filter-form icon-label-form',
                        'id' => 'dishorder-form'
                    ]
                ]
            );
    }


}