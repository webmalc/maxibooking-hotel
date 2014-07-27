<?php

namespace MBH\Bundle\PackageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PackageMainType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                /*->add('adults', 'text', [
                    'label' => 'Взрослые',
                    'required' => true,
                    'attr' => ['class' => 'spinner'],
                ])
                ->add('children', 'text', [
                    'label' => 'Дети',
                    'required' => true,
                    'attr' => ['class' => 'spinner'],
                ])*/
                ->add('purposeOfArrival', 'choice', [
                    'label' => 'Цель приезда',
                    'required' => false,
                    'multiple' => false,
                    'error_bubbling' => true,
                    'choices' => $options['arrivals'],
                ])
                ->add('note', 'textarea', [
                    'label' => 'Комментарий',
                    'required' => false,
                ])
                
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\PackageBundle\Document\Package',
            'arrivals' => []
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_packagebundle_package_main_type';
    }

}
