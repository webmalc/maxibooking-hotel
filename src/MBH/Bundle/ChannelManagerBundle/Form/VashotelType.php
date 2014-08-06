<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class VashotelType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'enabled',
                'checkbox',
                [
                    'label' => 'Включен?',
                    'value' => true,
                    'required' => false,
                    'help' => 'Использовать ли в Channel manager?'
                ]
            )
            ->add(
                'key',
                'text',
                [
                    'label' => 'Ключ API',
                    'required' => true,
                    'attr' => ['placeholder' => 'MySecretKeyForVashotelAPI'],
                    'help' => 'Ключ, который задается в настройках гостиницы ВашОтель.RU'
                ]
            )
            ->add(
                'hotelId',
                'text',
                [
                    'label' => 'ID отеля',
                    'required' => true,
                    'attr' => ['placeholder' => '1567'],
                    'help' => 'ID отеля в настройках ВашОтель.RU'
                ]
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'MBH\Bundle\ChannelManagerBundle\Document\VashotelConfig',
            )
        );
    }

    public function getName()
    {
        return 'mbh_bundle_channelmanagerbundle_vashotel_type';
    }

}
