<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 05.07.16
 * Time: 14:31
 */

namespace MBH\Bundle\RestaurantBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TableType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullTitle', TextType::class, [
                'label' => 'restaurant.table.form.fullTitle.label',
                'required' => true,
                'attr' => ['placeholder' => 'restaurant.table.form.fullTitle.placeholder'],
                'help' => 'restaurant.table.form.fullTitle.help',
                'group' => 'restaurant.group'

            ])
            ->add('title', TextType::class, [
                'label' => 'restaurant.table.form.title.label',
                'required' => false,
                'attr' => ['placeholder' => 'restaurant.table.form.title.placeholder'],
                'help' => 'restaurant.table.form.title.help',
                'group' => 'restaurant.group'
            ])
            ->add('isEnabled', CheckboxType::class, [
                'label' => 'restaurant.table.form.is_enable.label',
                'required' => false,
                'value' => true,
                'help' => 'restaurant.table.form.is_enable.help',
                'group' => 'restaurant.group'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => 'MBH\Bundle\RestaurantBundle\Document\Table'
            ]);
    }

    public function getName()
    {
        return 'mbh_bundle_restaurantbundle_table_type';
    }


}