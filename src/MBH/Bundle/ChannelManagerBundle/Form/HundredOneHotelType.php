<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use MBH\Bundle\BaseBundle\Service\Currency;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HundredOneHotelType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isEnabled', CheckboxType::class, [
                    'label' => 'form.hundredOneHotels.is_included',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.hundredOneHotels.should_we_use_in_channel_manager'
                ]
            )
            ->add(
                'hotelId', TextType::class, [
                    'label' => 'form.hundredOneHotels.hotel_id',
                    'required' => true,
                    'help' => 'form.hundredOneHotels.hotel_id_in_101_hotels'
                ]
            )
            ->add('apiKey', TextType::class, [
                    'label' => 'form.hundredOneHotels.api_key.label',
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'form.hundredOneHotels.api_key.placeholder'
                    ],
                    'help' => 'form.hundredIneHotels.api_key.help'
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\ChannelManagerBundle\Document\HundredOneHotelsConfig',
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_channelmanagerbundle_hundred_one_hotels_type';
    }
}