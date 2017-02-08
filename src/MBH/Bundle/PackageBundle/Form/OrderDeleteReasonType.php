<?php

namespace MBH\Bundle\PackageBundle\Form;

use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Form\Extension\DeleteReasonTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class OrderDeleteReasonType extends AbstractType
{
    use DeleteReasonTrait;

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Order::class);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_packagebundle_order_delete_reason_type';
    }

}