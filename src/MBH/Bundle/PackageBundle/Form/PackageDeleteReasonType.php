<?php

namespace MBH\Bundle\PackageBundle\Form;

use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Form\Extension\DeleteReasonTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class PackageDeleteReasonType extends AbstractType
{
    use DeleteReasonTrait;

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Package::class);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_packagebundle_delete_reason_type';
    }

}