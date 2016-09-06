<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 08.07.16
 * Time: 13:46
 */

namespace MBH\Bundle\RestaurantBundle\Form;


use MBH\Bundle\BaseBundle\Form\Extension\DateTimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dishorder-filter-begin', DateTimeType::class, [
                'date_format' => "dd.MM.yyyy",
                'attr' => [
                    'class' => 'datepicker package-filter begin-datepicker form-control input-sm'
                ],
                'widget' => 'single_text'
            ]);
    }

    public function getName()
    {
        return 'restaurant_dishorder_filter_type';
    }

}