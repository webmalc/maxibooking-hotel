<?php

namespace MBH\Bundle\PackageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ODM\MongoDB\DocumentRepository;

class PackageMainType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        for ($i = 0; $i <= 23; $i++) {
            $hours[$i] = sprintf('%02d', $i).':00';
        }

        if ($options['price']) {
            $builder->add(
                'price',
                'text',
                [
                    'label' => 'form.packageMainType.price',
                    'required' => true,
                    'error_bubbling' => true,
                    'property_path' => 'packagePrice',
                    'attr' => [
                        'class' => 'price-spinner'
                    ],
                ]
            );
        }

        $builder
            ->add(
                'discount',
                'text',
                [
                    'label' => 'form.packageMainType.discount',
                    'required' => false,
                    'error_bubbling' => true,
                    'attr' => [
                        'class' => 'discount-spinner'
                    ],
                ]
            )
            ->add(
                'arrivalTime',
                'choice',
                [
                    'label' => 'form.packageMainType.check_in_time',
                    'required' => false,
                    'multiple' => false,
                    'error_bubbling' => true,
                    'choices' => $hours,
                ]
            )
            ->add(
                'departureTime',
                'choice',
                [
                    'label' => 'form.packageMainType.check_out_time',
                    'required' => false,
                    'multiple' => false,
                    'error_bubbling' => true,
                    'choices' => $hours,
                ]
            )
            ->add(
                'purposeOfArrival',
                'choice',
                [
                    'label' => 'form.packageMainType.arrival_purpose',
                    'required' => false,
                    'multiple' => false,
                    'error_bubbling' => true,
                    'choices' => $options['arrivals'],
                ]
            )
            ->add(
                'note',
                'textarea',
                [
                    'label' => 'form.packageMainType.comment',
                    'required' => false,
                ]
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'MBH\Bundle\PackageBundle\Document\Package',
                'arrivals' => [],
                'defaultTime' => null,
                'price' => false
            )
        );
    }

    public function getName()
    {
        return 'mbh_bundle_packagebundle_package_main_type';
    }

}
