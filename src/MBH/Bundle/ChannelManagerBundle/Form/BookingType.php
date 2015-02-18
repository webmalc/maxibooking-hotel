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
                    'label' => 'Включен?',
                    'value' => true,
                    'required' => false,
                    'help' => 'Использовать ли в Channel manager?'
                ]
            )
            ->add(
                'username',
                'text',
                [
                    'label' => 'Логин',
                    'required' => true,
                    'attr' => ['placeholder' => 'username'],
                    'help' => 'Логин (username) для доступа к API Booking.com'
                ]
            )
            ->add(
                'password',
                'text',
                [
                    'label' => 'Пароль',
                    'required' => true,
                    'attr' => ['placeholder' => 'password'],
                    'help' => 'Пароль (password) для доступа к API Booking.com'
                ]
            )
            ->add(
                'hotelId',
                'text',
                [
                    'label' => 'ID отеля',
                    'required' => true,
                    'attr' => ['placeholder' => 'hotel id'],
                    'help' => 'ID отеля в Booking.com'
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
