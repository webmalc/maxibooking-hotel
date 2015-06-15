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
                'hotelId',
                'text',
                [
                    'label' => 'form.vashhotelType.hotel_id',
                    'required' => true,
                    'attr' => ['placeholder' => '1567'],
                    'help' => 'form.vashhotelType.vashhotel_ru_settings_hotel_id'
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
