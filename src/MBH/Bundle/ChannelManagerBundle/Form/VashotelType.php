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
                'isEnabled',
                'checkbox',
                [
                    'label' => 'form.vashhotelType.in_included',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.vashhotelType.should_we_use_in_channel_manager'
                ]
            )
            ->add(
                'key',
                'text',
                [
                    'label' => 'form.vashhotelType.api_key',
                    'required' => true,
                    'attr' => ['placeholder' => 'MySecretKeyForVashotelAPI'],
                    'help' => 'form.vashhotelType.vashhotel_ru_settings_key'
                ]
            )
            ->add(
                'hotelId',
                'text',
                [
                    'label' => 'form.vashhotelType.hotel_id',
                    'required' => true,
                    'attr' => ['placeholder' => '1567'],
                    'help' => 'form.vashhotelType.vashhotel_ru_settings_hotel_id'
                ]
            )
            ->add(
                'isBreakfast',
                'checkbox',
                [
                    'label' => 'form.vashhotelType.breakfast',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.vashhotelType.r0_or_BB'
                ]
            )
        ;
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
