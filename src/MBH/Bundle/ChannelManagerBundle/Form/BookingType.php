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
                'username',
                'text',
                [
                    'label' => 'form.bookingType.login',
                    'required' => true,
                    'attr' => ['placeholder' => 'username'],
                    'help' => 'form.bookingType.booking_api_com_access_login'
                ]
            )
            ->add(
                'password',
                'text',
                [
                    'label' => 'form.bookingType.password',
                    'required' => true,
                    'attr' => ['placeholder' => 'password'],
                    'help' => 'form.bookingType.booking_api_com_password_access'
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
