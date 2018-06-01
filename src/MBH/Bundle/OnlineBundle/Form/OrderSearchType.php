<?php
/**
 * Created by PhpStorm.
 * Date: 01.06.18
 */

namespace MBH\Bundle\OnlineBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class OrderSearchType extends AbstractType
{
    const PREFIX = 'mbh_bundle_onlinebundle_order_search_type';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'numberOrder',
                TextType::class,
                [
                    'label' => 'form.online.order_search.numberOrder',
                    'group' => 'form.online.order_search'
                ]
            )
            ->add(
                'phoneOrEmail',
                TextType::class,
                [
                    'label' => 'form.online.order_search.phoneOrEmail',
                    'group' => 'form.online.order_search'
                ]
            );
        if (!empty($options['data']) && $options['data']->isUserNameVisible() ) {
            $builder->add(
                'userName',
                TextType::class,
                [
                    'label' => 'form.online.order_search.userName',
                    'group' => 'form.online.order_search'
                ]
            );
        }
    }

    public function getBlockPrefix()
    {
        return self::PREFIX;
    }
}