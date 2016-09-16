<?php
/**
 * Created by PhpStorm.
 * User: danya
 * Date: 13.09.16
 * Time: 17:51
 */

namespace MBH\Bundle\ChannelManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HundredOneHotelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'isEnabled',
                CheckboxType::class,
                [
                    'label' => 'form.hundredOneHotels.is_included',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.hundredOneHotels.should_we_use_in_channel_manager'
                ]
            )
            ->add(
                'hotelId',
                TextType::class,
                [
                    'label' => 'form.hundredOneHotels.hotel_id',
                    'required' => true,
                    'attr' => ['placeholder' => 'hotel id'],
                    'help' => 'form.hundredOneHotels.hotel_id_in_booking_com'
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\ChannelManagerBundle\Document\HundredOneHotelsConfig',
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_channelmanagerbundle_booking_type';
    }
}