<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BookingType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'isEnabled',
                'checkbox',
                [
                    'label' => 'form.bookingType.is_included',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.bookingType.should_we_use_in_channel_manager'
                ]
            )
            ->add(
                'hotelId',
                'text',
                [
                    'label' => 'form.bookingType.hotel_id',
                    'required' => true,
                    'attr' => ['placeholder' => 'hotel id'],
                    'help' => 'form.bookingType.hotel_id_in_booking_com'
                ]
            )
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'MBH\Bundle\ChannelManagerBundle\Document\BookingConfig',
            )
        );
    }

    public function getName()
    {
        return 'mbh_bundle_channelmanagerbundle_booking_type';
    }

}
